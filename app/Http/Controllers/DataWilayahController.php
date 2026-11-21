<?php

namespace App\Http\Controllers;

use App\Models\DataPemda;
use App\Models\DokumenPengajuan;
use App\Models\RuteSubsidi;
use App\Models\User;
use App\Models\Wilayah;
use App\Support\StatusPengajuan;
use App\Support\XlsxExporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DataWilayahController extends Controller
{
    private const PER_HALAMAN = 8;

    private const KLASIFIKASI = [
        'prioritas-strategis' => 'Prioritas Strategis',
        'prioritas'           => 'Prioritas',
        'strategis'           => 'Strategis',
    ];

    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $user = $request->user();

        $filter = $this->filter($request);

        $data = $this->query($filter, $user)
            ->paginate(self::PER_HALAMAN)
            ->withQueryString();

        $this->isiBaris($data, $user);

        return view('data-wilayah.index', [
            'data'          => $data,
            'filter'        => $filter,
            'wilayah'       => Wilayah::orderBy('nama_wilayah')->get(),
            'klasifikasi'   => self::KLASIFIKASI,
            'pilihanStatus' => $this->pilihanStatus($user),

            'halaman' => $this->nomorHalaman(
                $data->currentPage(),
                $data->lastPage()
            ),

            'adaFilter' => $filter['wilayah'] !== null
                || $filter['klasifikasi'] !== null
                || $filter['status'] !== null
                || $filter['q'] !== '',

            'lingkup' => $this->lingkup($user),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */

    public function show(Request $request, DataPemda $pemda)
    {
        $user = auth()->user();

        abort_unless(
            DataPemda::terlihatOleh($user)
                ->whereKey($pemda->id_pemda)
                ->exists(),
            404
        );

        $pemda->load(
            'kabKota.provinsi',
            'kabKota.wilayah',
            'user',
            'ruteSubsidi.moda',
            'ruteSubsidi.dokumen',
            'ruteSubsidi.verifikasi.verifikator',
            'ruteSubsidi.pengajuanSurat.dokumen',
            'ruteSubsidi.rekomendasi',
            'ruteSubsidi.tujuan',
            'ruteSubsidi.detailKomoditasTujuan',
            'permintaanRevisi.moda',
            'permintaanRevisi.peminta'
        );

        if ($user->isModa()) {
            $pemda->setRelation(
                'ruteSubsidi',
                $pemda->ruteSubsidi
                    ->where('id_moda', $user->id_moda)
                    ->values()
            );

            $pemda->setRelation('permintaanRevisi', collect());
        }

        $rute = $pemda->ruteSubsidi->firstWhere(
            'id_rute_subsidi',
            (int) $request->query('pengajuan')
        ) ?? $pemda->pengajuan_terbaru;

        return view('data-wilayah.show', [
            'pemda'     => $pemda,
            'rute'      => $rute,
            'bisaUnduh' => $pemda->dokumenBisaDiunduhOleh($user),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | EXPORT
    |--------------------------------------------------------------------------
    */

    public function export(Request $request)
    {
        if (! class_exists(\ZipArchive::class)) {
            return back()->with(
                'error',
                'Ekspor Excel membutuhkan ekstensi PHP "zip". Aktifkan extension=zip di php.ini lalu restart server.'
            );
        }

        $rows = [];

        $user = $request->user();

        // Semua filter, termasuk status, berlaku untuk Excel.
        $koleksi = $this->query(
            $this->filter($request),
            $user
        )->get();

        $this->isiBaris($koleksi, $user);

        foreach ($koleksi as $i => $b) {
            $p = $b->pemda;
            $isi = $b->rute ?? $p;

            $rows[] = [
                $i + 1,
                $b->rute?->nomor_laporan ?? '-',
                $p->kabKota->provinsi->nama_provinsi,
                $p->kabKota->wilayah->nama_wilayah,
                $p->kabKota->nama_kab_kota,
                $b->rute?->moda->nama_moda ?? '-',
                $isi->klasifikasi_label,
                $isi->keterangan_pemda,
                $isi->potensi_ringkas,
                $isi->kebutuhan_konektivitas,
                $isi->jumlah_tujuan,
                $isi->jumlah_sektor,
                implode(', ', $isi->sektor_terdeteksi),
                $b->status,
            ];
        }

        $header = [
            'No',
            'Nomor Laporan',
            'Provinsi',
            'Wilayah',
            'Kabupaten/Kota',
            'Moda',
            'Klasifikasi Wilayah',
            'Keterangan Daerah',
            'Potensi Unggulan Daerah',
            'Kebutuhan Konektivitas',
            'Jumlah Tujuan',
            'Jumlah Sektor',
            'Sektor Potensi',
            'Status',
        ];

        $path = XlsxExporter::buat(
            $header,
            $rows,
            'Data Wilayah'
        );

        return response()->download(
            $path,
            'data-wilayah-' . now()->format('Ymd-His') . '.xlsx',
            [
                'Content-Type' =>
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]
        )->deleteFileAfterSend(true);
    }

    /*
    |--------------------------------------------------------------------------
    | DESTROY
    |--------------------------------------------------------------------------
    */

    public function destroy(Request $request, DataPemda $pemda)
    {
        $user = $request->user();

        $pemda->load('ruteSubsidi');

        $idRute = (int) $request->input('pengajuan');

        $target = $idRute
            ? $pemda->ruteSubsidi->firstWhere(
                'id_rute_subsidi',
                $idRute
            )
            : null;

        abort_if($idRute && ! $target, 404);

        abort_unless(
            $target
                ? $pemda->bisaDibatalkanOleh($user, $target)
                : $pemda->bisaDiubahOleh($user),
            403
        );

        $nama = $pemda->kabKota->nama_kab_kota;

        $folder = [];

        try {
            DB::transaction(function () use (
                $pemda,
                $target,
                &$folder
            ) {
                foreach (
                    $target
                        ? [$target]
                        : $pemda->ruteSubsidi
                    as $rute
                ) {
                    $folder[] =
                        "dokumen_pengajuan/{$rute->id_rute_subsidi}";

                    $rute->delete();
                }

                if ($pemda->ruteSubsidi()->count() === 0) {
                    $pemda->update([
                        'id_user' => null,
                    ]);
                }
            });
        } catch (\Throwable $e) {
            report($e);

            return back()->with(
                'error',
                'Pengajuan gagal dihapus. Silakan coba lagi atau hubungi administrator.'
            );
        }

        foreach ($folder as $f) {
            Storage::disk('local')->deleteDirectory($f);
        }

        return redirect()
            ->route('data-wilayah.index')
            ->with(
                'success',
                'Pengajuan "' . $nama . '" berhasil dihapus. Data wilayah tetap tersimpan.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | UNDUH DOKUMEN
    |--------------------------------------------------------------------------
    */

    public function unduh(
        Request $request,
        DokumenPengajuan $dokumen
    ) {
        abort_unless(
            $dokumen->bisaDiunduhOleh(auth()->user()),
            403
        );

        abort_unless(
            Storage::disk('local')->exists($dokumen->path_file),
            404
        );

        return $request->boolean('lihat')
            ? Storage::disk('local')->response(
                $dokumen->path_file,
                $dokumen->nama_file
            )
            : Storage::disk('local')->download(
                $dokumen->path_file,
                $dokumen->nama_file
            );
    }

    /*
    |--------------------------------------------------------------------------
    | DAFTAR PILIHAN STATUS
    |--------------------------------------------------------------------------
    |
    | Status berasal dari pengajuan yang dapat dilihat pengguna.
    | Nama status dinormalisasi agar sama dengan tampilan tabel.
    |
    */

    private function pilihanStatus(User $user): array
    {
        return RuteSubsidi::query()
            ->whereIn(
                'id_pemda',
                DataPemda::query()
                    ->terlihatOleh($user)
                    ->select('data_pemda.id_pemda')
            )
            ->when(
                $user->isModa(),
                fn ($query) => $query->where(
                    'id_moda',
                    $user->id_moda ?? 0
                )
            )
            ->whereNotNull('status_pengajuan')
            ->distinct()
            ->pluck('status_pengajuan')
            ->map(
                fn ($status) =>
                    StatusPengajuan::normalisasi($status)
            )
            ->filter(
                fn ($status) =>
                    $status !== null
                    && trim((string) $status) !== ''
            )
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    /*
    |--------------------------------------------------------------------------
    | ISI BARIS
    |--------------------------------------------------------------------------
    */

    private function isiBaris($koleksi, User $user): void
    {
        $isPaginator =
            $koleksi instanceof \Illuminate\Contracts\Pagination\Paginator;

        $baris = $isPaginator
            ? $koleksi->getCollection()
            : $koleksi;

        $pemda = DataPemda::with(
            'kabKota.provinsi',
            'kabKota.wilayah',
            'permintaanRevisi.moda',
            'ruteSubsidi'
        )
            ->whereIn(
                'id_pemda',
                $baris->pluck('id_pemda')->unique()
            )
            ->get()
            ->keyBy('id_pemda');

        $rute = RuteSubsidi::with('moda')
            ->whereIn(
                'id_rute_subsidi',
                $baris->pluck('id_rute')->filter()->unique()
            )
            ->get()
            ->keyBy('id_rute_subsidi');

        $hasil = $baris->map(
            function ($r) use ($pemda, $rute, $user) {
                $p = $pemda[$r->id_pemda];

                $ru = $r->id_rute
                    ? ($rute[$r->id_rute] ?? null)
                    : null;

                if ($user->isModa()) {
                    $p->setRelation(
                        'permintaanRevisi',
                        collect()
                    );
                }

                $status = StatusPengajuan::normalisasi(
                    $ru?->status_pengajuan
                );

                return (object) [
                    'pemda'  => $p,
                    'rute'   => $ru,
                    'status' => $status,
                    'kelas'  => StatusPengajuan::kelas($status),

                    'revisi' => $ru
                        ? $p->permintaan_terbuka
                            ->where('id_moda', $ru->id_moda)
                            ->values()
                        : collect(),
                ];
            }
        )->values();

        if ($isPaginator) {
            $koleksi->setCollection($hasil);
        } else {
            $koleksi->splice(
                0,
                $koleksi->count(),
                $hasil->all()
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | LINGKUP PENGGUNA
    |--------------------------------------------------------------------------
    */

    private function lingkup(User $user): ?string
    {
        if ($user->isSuperAdmin() || $user->isIntram()) {
            return null;
        }

        if ($user->isModa()) {
            return 'Menampilkan data pemda yang ditujukan ke moda '
                . ($user->moda->nama_moda ?? '-');
        }

        return 'Menampilkan data yang Anda unggah';
    }

    /*
    |--------------------------------------------------------------------------
    | FILTER
    |--------------------------------------------------------------------------
    */

    private function filter(Request $r): array
    {
        $wilayah = (string) $r->query('wilayah', '');
        $klas = (string) $r->query('klasifikasi', '');

        $status = trim(
            (string) $r->query('status', '')
        );

        return [
            'q' => trim(
                (string) $r->query('q', '')
            ),

            'wilayah' => ctype_digit($wilayah)
                ? (int) $wilayah
                : null,

            'klasifikasi' => array_key_exists(
                $klas,
                self::KLASIFIKASI
            )
                ? $klas
                : null,

            'status' => $status !== ''
                && in_array(
                    $status,
                    $this->pilihanStatus($r->user()),
                    true
                )
                    ? $status
                    : null,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | QUERY DATA
    |--------------------------------------------------------------------------
    */

    private function query(array $f, User $user)
    {
        $klas = 'COALESCE(
            rs.klasifikasi_pemda,
            data_pemda.klasifikasi_pemda
        )';

        return DataPemda::query()
            ->terlihatOleh($user)

            ->leftJoin(
                'rute_subsidi as rs',
                function ($j) use ($user) {
                    $j->on(
                        'rs.id_pemda',
                        '=',
                        'data_pemda.id_pemda'
                    );

                    if ($user->isModa()) {
                        $j->where(
                            'rs.id_moda',
                            '=',
                            $user->id_moda ?? 0
                        );
                    }
                }
            )

            ->select(
                'data_pemda.id_pemda',
                'rs.id_rute_subsidi as id_rute'
            )

            // Pencarian kabupaten/kota dan provinsi
            ->when(
                $f['q'] !== '',
                function ($q) use ($f) {
                    $like = '%'
                        . addcslashes($f['q'], '\\%_')
                        . '%';

                    $q->whereHas(
                        'kabKota',
                        function ($k) use ($like) {
                            $k->where(
                                'nama_kab_kota',
                                'like',
                                $like
                            )
                            ->orWhereHas(
                                'provinsi',
                                fn ($p) => $p->where(
                                    'nama_provinsi',
                                    'like',
                                    $like
                                )
                            );
                        }
                    );
                }
            )

            // Filter wilayah
            ->when(
                $f['wilayah'],
                fn ($q, $w) => $q->whereHas(
                    'kabKota',
                    fn ($k) => $k->where(
                        'id_wilayah',
                        $w
                    )
                )
            )

            // Filter klasifikasi
            ->when(
                $f['klasifikasi'],
                function ($q, $kl) use ($klas) {
                    if ($kl === 'prioritas-strategis') {
                        $q->whereRaw(
                            "$klas like '%prioritas%'"
                        )->whereRaw(
                            "$klas like '%strategis%'"
                        );
                    } elseif ($kl === 'prioritas') {
                        $q->whereRaw(
                            "$klas like '%prioritas%'"
                        )->whereRaw(
                            "$klas not like '%strategis%'"
                        );
                    } else {
                        $q->whereRaw(
                            "$klas like '%strategis%'"
                        )->whereRaw(
                            "$klas not like '%prioritas%'"
                        );
                    }
                }
            )

            // Filter status pengajuan
            ->when(
                $f['status'],
                function ($q, $status) {
                    $nilaiStatus = RuteSubsidi::query()
                        ->whereNotNull('status_pengajuan')
                        ->distinct()
                        ->pluck('status_pengajuan')
                        ->filter(
                            fn ($nilai) =>
                                StatusPengajuan::normalisasi($nilai)
                                === $status
                        )
                        ->values()
                        ->all();

                    $q->whereIn(
                        'rs.status_pengajuan',
                        $nilaiStatus
                    );
                }
            )

            // Pengajuan terbaru di atas
            ->orderByDesc('rs.created_at')
            ->orderByDesc('rs.id_rute_subsidi')
            ->orderByDesc('data_pemda.id_pemda');
    }

    /*
    |--------------------------------------------------------------------------
    | PAGINATION
    |--------------------------------------------------------------------------
    */

    private function nomorHalaman(
        int $aktif,
        int $akhir
    ): array {
        $set = collect([
            1,
            $akhir,
            $aktif - 1,
            $aktif,
            $aktif + 1,
        ]);

        if ($aktif <= 3) {
            $set = $set->merge([2, 3]);
        }

        if ($aktif >= $akhir - 2) {
            $set = $set->merge([
                $akhir - 2,
                $akhir - 1,
            ]);
        }

        $nomor = $set
            ->filter(fn ($n) => $n >= 1 && $n <= $akhir)
            ->unique()
            ->sort()
            ->values();

        $hasil = [];
        $prev = 0;

        foreach ($nomor as $n) {
            if ($n - $prev > 1) {
                $hasil[] = '...';
            }

            $hasil[] = $n;
            $prev = $n;
        }

        return $hasil;
    }
}
