<?php

namespace App\Http\Controllers;

use App\Models\FasilitasTransportasi;
use App\Models\RuteSubsidi;
use App\Support\StatusPengajuan;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class PetaGeospasialController extends Controller
{
    private const BERKAS = [
        'daerah-asal' => 'DAERAH_ASAL.geojson',
        'simpul-tujuan' => 'SIMPUL_TUJUAN.geojson',
        'asal-tujuan' => 'PASANGAN_ASAL_TUJUAN.geojson',
        'fasilitas' => 'TITIK_FASILITAS_TERDEKAT.geojson',
        'akses-fasilitas' => 'PASANGAN_FASILITAS_TERDEKAT.geojson',
    ];

    private const RADIUS_KANDIDAT_DUPLIKAT_KM = 5;

    /*
    |--------------------------------------------------------------------------
    | PEMERIKSAAN ROLE
    |--------------------------------------------------------------------------
    */

    private function bisaMelihatSemua($pengguna): bool
    {
        return $pengguna->isSuperAdmin()
            || $pengguna->isIntram();
    }

    private function adalahPemda($pengguna): bool
    {
        return (int) $pengguna->id_role === 2;
    }

    /*
    |--------------------------------------------------------------------------
    | QUERY PENGAJUAN BERDASARKAN HAK AKSES
    |--------------------------------------------------------------------------
    */

    private function queryPengajuan($pengguna)
    {
        $query = RuteSubsidi::query()
            ->where(
                'status_pengajuan',
                StatusPengajuan::FINAL
            );

        /*
         * INTRAM dan SuperAdmin melihat seluruh
         * pengajuan yang sudah final.
         */
        if ($this->bisaMelihatSemua($pengguna)) {
            return $query;
        }

        /*
         * Moda hanya melihat pengajuan sesuai
         * dengan id_moda milik akunnya.
         */
        if ($pengguna->isModa()) {
            if ($pengguna->id_moda === null) {
                return $query->whereRaw('1 = 0');
            }

            return $query->where(
                'id_moda',
                $pengguna->id_moda
            );
        }

        /*
         * Pemda hanya melihat pengajuan
         * yang berasal dari akun Pemda tersebut.
         */
        if ($this->adalahPemda($pengguna)) {
            return $query->whereHas(
                'pemda',
                fn ($q) => $q->where(
                    'id_user',
                    $pengguna->id_user
                )
            );
        }

        /*
         * Role lain tidak diberikan akses
         * terhadap data pengajuan.
         */
        return $query->whereRaw('1 = 0');
    }

    /*
    |--------------------------------------------------------------------------
    | HALAMAN PETA GEOSPASIAL
    |--------------------------------------------------------------------------
    */

    public function index(): View
    {
        $pengguna = auth()->user();

        abort_unless($pengguna, 403);

        /*
        |--------------------------------------------------------------------------
        | 1. DATA PENGAJUAN PEMDA
        |--------------------------------------------------------------------------
        */

        $query = $this->queryPengajuan($pengguna);

        $pengajuan = [];

        $query
            ->with([
                'kabKotaAsal',
                'tujuan',
            ])
            ->orderBy('id_rute_subsidi')
            ->chunk(150, function ($batch) use (&$pengajuan) {

                foreach ($batch as $rute) {

                    $asal = $rute->kabKotaAsal;

                    if (! $this->valid($asal)) {
                        continue;
                    }

                    foreach ($rute->tujuan as $tujuan) {

                        if (! $this->valid($tujuan)) {
                            continue;
                        }

                        $pengajuan[] = [
                            'id' => (int) $rute->id_rute_subsidi,

                            'laporan' => $rute->nomor_laporan,

                            'moda' => (int) $rute->id_moda,

                            'id_asal' => (int) $asal->id_kab_kota,

                            'asal' => [
                                'nama' => $asal->nama_lengkap,
                                'lat' => (float) $asal->latitude,
                                'lon' => (float) $asal->longitude,
                            ],

                            'tujuan' => [
                                'nama' => $tujuan->nama_lengkap,
                                'lat' => (float) $tujuan->latitude,
                                'lon' => (float) $tujuan->longitude,
                            ],
                        ];

                    }
                }
            });

        /*
        |--------------------------------------------------------------------------
        | 2. MENENTUKAN MODA FASILITAS YANG BOLEH DITAMPILKAN
        |--------------------------------------------------------------------------
        */

        $modaDiizinkan = [];

        /*
         * SuperAdmin dan INTRAM:
         * Semua moda fasilitas.
         */
        if ($this->bisaMelihatSemua($pengguna)) {

            $modaDiizinkan = null;

        /*
         * Moda:
         * Hanya fasilitas sesuai moda akun.
         */
        } elseif ($pengguna->isModa()) {

            $modaDiizinkan = $pengguna->id_moda !== null
                ? [(int) $pengguna->id_moda]
                : [];

        /*
         * Pemda:
         * Fasilitas mengikuti moda pengajuan
         * yang boleh dilihat Pemda.
         */
        } elseif ($this->adalahPemda($pengguna)) {

            $modaDiizinkan = collect($pengajuan)
                ->pluck('moda')
                ->unique()
                ->values()
                ->all();
        }

        /*
        |--------------------------------------------------------------------------
        | 3. DATA FASILITAS TRANSPORTASI
        |--------------------------------------------------------------------------
        |
        | Data tidak dihapus dari database.
        | Pembatasan hanya pada query.
        |
        */

        $queryFasilitas = FasilitasTransportasi::query()
            ->with('kabKota.provinsi')
            ->where('aktif', true);

        if ($modaDiizinkan !== null) {

            $queryFasilitas->whereIn(
                'id_moda',
                $modaDiizinkan
            );

        }

        $semuaFasilitas = $queryFasilitas
            ->get()
            ->filter(
                fn ($f) => $this->validKoordinatFasilitas($f)
            )
            ->values();

        /*
        |--------------------------------------------------------------------------
        | 4. FASILITAS KEMENHUB
        |--------------------------------------------------------------------------
        */

        $kemenhub = $semuaFasilitas
            ->filter(
                fn ($f) => str_starts_with(
                    (string) $f->sumber_data,
                    'Kemenhub'
                )
            )
            ->values();

        /*
        |--------------------------------------------------------------------------
        | 5. FASILITAS GEOJSON TIM STATISTIKA
        |--------------------------------------------------------------------------
        */

        $geojson = $semuaFasilitas
            ->filter(
                fn ($f) =>
                    $f->sumber_data ===
                    'GeoJSON Tim Statistika (Sementara)'
            )
            ->values();

        /*
        |--------------------------------------------------------------------------
        | 6. FASILITAS SUMBER LAIN
        |--------------------------------------------------------------------------
        */

        $lainnya = $semuaFasilitas
            ->filter(
                fn ($f) =>
                    ! str_starts_with(
                        (string) $f->sumber_data,
                        'Kemenhub'
                    )
                    && $f->sumber_data !==
                    'GeoJSON Tim Statistika (Sementara)'
            )
            ->values();

        /*
        |--------------------------------------------------------------------------
        | 7. PEMERIKSAAN KANDIDAT DUPLIKAT
        |--------------------------------------------------------------------------
        |
        | Fasilitas GeoJSON dengan pasangan Kemenhub
        | pada moda sama dalam radius 5 km
        | tidak ditampilkan dua kali.
        |
        | Tidak menghapus data database.
        |
        */

        $geojsonTambahan = $geojson
            ->reject(function ($geo) use ($kemenhub) {

                return $this->memilikiPasanganKemenhub(
                    $geo,
                    $kemenhub
                );

            })
            ->values();

        /*
        |--------------------------------------------------------------------------
        | 8. MENGGABUNGKAN FASILITAS
        |--------------------------------------------------------------------------
        */

        $fasilitasPeta = $kemenhub
            ->concat($geojsonTambahan)
            ->concat($lainnya)
            ->values();

        /*
        |--------------------------------------------------------------------------
        | 9. FORMAT DATA FASILITAS UNTUK BLADE
        |--------------------------------------------------------------------------
        */

        $hasilFasilitas = $fasilitasPeta
            ->map(function ($f) {

                $perluVerifikasi = in_array(
                    (int) $f->id_fasilitas,
                    [1, 160],
                    true
                ) && $f->sumber_data ===
                    'GeoJSON Tim Statistika (Sementara)';

                return [
                    'id' => (int) $f->id_fasilitas,

                    'nama' => $f->nama,

                    'id_moda' => (int) $f->id_moda,

                    'id_kab_kota' => $f->id_kab_kota,

                    'kab_kota' => $f->kabKota?->nama_lengkap,

                    'provinsi' =>
                        $f->kabKota?->provinsi?->nama_provinsi,

                    'lat' => (float) $f->latitude,

                    'lon' => (float) $f->longitude,

                    'sumber' => $f->sumber_data,


                    'perlu_verifikasi' =>
                        $perluVerifikasi ||
                        str_starts_with(
                            (string) $f->referensi_sumber,
                            'osm:'
                        ),

                    'layak_rekomendasi' =>
                        ! $perluVerifikasi &&
                        ! str_starts_with(
                            (string) $f->referensi_sumber,
                            'osm:'
                        ),



                        'tipe_terminal' => $f->tipe_terminal,

                        'status_verifikasi' => $f->status_verifikasi,

                        'jenis_fasilitas_osm' => $f->jenis_fasilitas_osm,

                        'status_pencocokan' => $f->status_pencocokan,

                        'is_osm' => str_starts_with(
                            (string) $f->referensi_sumber,
                            'osm:'
                        ),

                ];
            })
            ->values();

        /*
        |--------------------------------------------------------------------------
        | 10. KIRIM KE VIEW
        |--------------------------------------------------------------------------
        */

        return view('peta.geospasial', [

            'pengajuanPeta' => $pengajuan,

            'fasilitasDatabase' => $hasilFasilitas,

            'statistikFasilitas' => [

                'total_database' => $semuaFasilitas->count(),

                'kemenhub' => $kemenhub->count(),

                'geojson_total' => $geojson->count(),

                'geojson_tambahan' => $geojsonTambahan->count(),

                'geojson_kandidat_duplikat' =>
                    $geojson->count() - $geojsonTambahan->count(),

                'ditampilkan' => $hasilFasilitas->count(),
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | CEK KANDIDAT DUPLIKAT GEOJSON DAN KEMENHUB
    |--------------------------------------------------------------------------
    */

    private function memilikiPasanganKemenhub(
        $fasilitas,
        Collection $kemenhub
    ): bool {

        foreach ($kemenhub as $referensi) {

            if (
                (int) $fasilitas->id_moda !==
                (int) $referensi->id_moda
            ) {
                continue;
            }

            if (
                abs(
                    (float) $fasilitas->latitude -
                    (float) $referensi->latitude
                ) > 0.05
            ) {
                continue;
            }

            if (
                abs(
                    (float) $fasilitas->longitude -
                    (float) $referensi->longitude
                ) > 0.05
            ) {
                continue;
            }

            $jarak = $this->haversine(
                (float) $fasilitas->latitude,
                (float) $fasilitas->longitude,
                (float) $referensi->latitude,
                (float) $referensi->longitude
            );

            if (
                $jarak <= self::RADIUS_KANDIDAT_DUPLIKAT_KM
            ) {
                return true;
            }
        }

        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | PERHITUNGAN JARAK HAVERSINE
    |--------------------------------------------------------------------------
    */

    private function haversine(
        float $lat1,
        float $lon1,
        float $lat2,
        float $lon2
    ): float {

        $radiusBumi = 6371.0088;

        $bedaLat = deg2rad($lat2 - $lat1);
        $bedaLon = deg2rad($lon2 - $lon1);

        $a =
            sin($bedaLat / 2) ** 2
            + cos(deg2rad($lat1))
            * cos(deg2rad($lat2))
            * sin($bedaLon / 2) ** 2;

        return $radiusBumi
            * 2
            * asin(min(1, sqrt($a)));
    }

    /*
    |--------------------------------------------------------------------------
    | VALIDASI KOORDINAT KABUPATEN/KOTA
    |--------------------------------------------------------------------------
    */

    private function valid($kab): bool
    {
        return $kab
            && is_numeric($kab->latitude)
            && is_numeric($kab->longitude)
            && abs((float) $kab->latitude) <= 90
            && abs((float) $kab->longitude) <= 180;
    }

    /*
    |--------------------------------------------------------------------------
    | VALIDASI KOORDINAT FASILITAS
    |--------------------------------------------------------------------------
    */

    private function validKoordinatFasilitas($fasilitas): bool
    {
        return is_numeric($fasilitas->latitude)
            && is_numeric($fasilitas->longitude)
            && abs((float) $fasilitas->latitude) <= 90
            && abs((float) $fasilitas->longitude) <= 180;
    }

    /*
    |--------------------------------------------------------------------------
    | ENDPOINT DATA GEOJSON
    |--------------------------------------------------------------------------
    */

    public function data(string $layer): Response
    {
        $pengguna = auth()->user();

        abort_unless($pengguna, 403);

        /*
        |--------------------------------------------------------------------------
        | JARINGAN REL
        |--------------------------------------------------------------------------
        |
        | Dapat diakses oleh:
        | - SuperAdmin
        | - INTRAM
        | - Moda Kereta Api
        | - Pemda dengan pengajuan final Kereta Api
        |
        */

        if ($layer === 'jaringan-rel') {

            $bolehMelihatRel = $this->bisaMelihatSemua(
                $pengguna
            );

            if (
                ! $bolehMelihatRel
                && $pengguna->isModa()
            ) {
                $bolehMelihatRel =
                    (int) $pengguna->id_moda === 4;
            }

            if (
                ! $bolehMelihatRel
                && $this->adalahPemda($pengguna)
            ) {
                $bolehMelihatRel = $this
                    ->queryPengajuan($pengguna)
                    ->where('id_moda', 4)
                    ->exists();
            }

            abort_unless($bolehMelihatRel, 403);

            $file = storage_path(
                'app/kemenhub/rel.json'
            );

            abort_unless(is_file($file), 404);

            $rows = json_decode(
                file_get_contents($file),
                true
            );

            abort_unless(is_array($rows), 404);

            $features = [];

            foreach ($rows as $row) {

                if (
                    ! is_numeric($row['lat_start'] ?? null)
                    || ! is_numeric($row['lon_start'] ?? null)
                    || ! is_numeric($row['lat_end'] ?? null)
                    || ! is_numeric($row['lon_end'] ?? null)
                ) {
                    continue;
                }

                $features[] = [
                    'type' => 'Feature',

                    'properties' => [
                        'nama' => $row['namobj'] ?? '',
                        'dari' => $row['petak1'] ?? '',
                        'ke' => $row['petak2'] ?? '',
                    ],

                    'geometry' => [
                        'type' => 'LineString',

                        'coordinates' => [
                            [
                                (float) $row['lon_start'],
                                (float) $row['lat_start'],
                            ],

                            [
                                (float) $row['lon_end'],
                                (float) $row['lat_end'],
                            ],
                        ],
                    ],
                ];
            }

            return response(
                json_encode([
                    'type' => 'FeatureCollection',
                    'features' => $features,
                ]),
                200,
                [
                    'Content-Type' =>
                        'application/geo+json; charset=utf-8',

                    'Cache-Control' =>
                        'private, no-store',

                    'X-Content-Type-Options' =>
                        'nosniff',
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | GEOJSON STATIS
        |--------------------------------------------------------------------------
        |
        | GeoJSON statis belum mempunyai informasi
        | kepemilikan pengajuan per pengguna.
        |
        | Untuk mencegah data pengajuan pengguna lain
        | terbuka melalui endpoint, akses dibatasi
        | untuk INTRAM dan SuperAdmin.
        |
        */

        abort_unless(
            $this->bisaMelihatSemua($pengguna),
            403
        );

        abort_unless(
            isset(self::BERKAS[$layer]),
            404
        );

        $file = storage_path(
            'app/geospasial/' . self::BERKAS[$layer]
        );

        abort_unless(
            is_file($file),
            404,
            'Data geospasial belum tersedia.'
        );

        return response(
            file_get_contents($file),
            200,
            [
                'Content-Type' =>
                    'application/geo+json; charset=utf-8',

                'Cache-Control' =>
                    'private, no-store',

                'X-Content-Type-Options' =>
                    'nosniff',
            ]
        );
    }
}
