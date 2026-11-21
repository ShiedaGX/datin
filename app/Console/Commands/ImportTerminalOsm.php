<?php

namespace App\Console\Commands;

use App\Models\FasilitasTransportasi;
use App\Models\KabKota;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class ImportTerminalOsm extends Command
{
    protected $signature = 'datin:import-terminal-osm
        {--dry-run : Periksa tanpa menyimpan data}';

    protected $description = 'Import objek terminal OSM ke fasilitas transportasi DATIN';

    private function normalisasi(string $teks): string
    {
        $teks = mb_strtolower(trim($teks));

        $teks = preg_replace(
            '/\bkab\.\s*/u',
            'kabupaten ',
            $teks
        );

        $teks = preg_replace(
            '/\bkab\s+/u',
            'kabupaten ',
            $teks
        );

        $teks = preg_replace(
            '/\bkota\s+adm\.\s*/u',
            'kota administrasi ',
            $teks
        );

        $teks = str_replace(
            'kota administratif ',
            'kota administrasi ',
            $teks
        );

        $teks = preg_replace('/[^\pL\pN]+/u', ' ', $teks);

        return trim(
            preg_replace('/\s+/u', ' ', $teks)
        );
    }

    private function normalisasiProvinsi(string $nama): string
    {
        $nama = $this->normalisasi($nama);

        if (in_array($nama, [
            'daerah khusus ibukota jakarta',
            'daerah khusus jakarta',
            'dki jakarta',
            'dk jakarta',
        ], true)) {
            return 'dki jakarta';
        }

        return $nama;
    }

    private function jenisWilayah(string $nama): ?string
    {
        $nama = $this->normalisasi($nama);

        if (
            str_starts_with($nama, 'kabupaten administrasi ') ||
            str_starts_with($nama, 'kabupaten ')
        ) {
            return 'kabupaten';
        }

        if (
            str_starts_with($nama, 'kota administrasi ') ||
            str_starts_with($nama, 'kota ')
        ) {
            return 'kota';
        }

        return null;
    }

    private function namaWilayah(
        string $nama,
        ?string $jenis = null
    ): string {
        $nama = $this->normalisasi($nama);
        $jenis = $this->normalisasi($jenis ?? '');

        if ($nama === '') {
            return '';
        }

        if ($this->jenisWilayah($nama) !== null) {
            return $nama;
        }

        if ($jenis === 'kabupaten') {
            return 'kabupaten ' . $nama;
        }

        if ($jenis === 'kota') {
            return 'kota ' . $nama;
        }

        return $nama;
    }

    private function namaTanpaJenis(string $nama): string
    {
        return trim(
            preg_replace(
                '/^(kabupaten administrasi|kota administrasi|kabupaten|kota)\s+/u',
                '',
                $this->normalisasi($nama)
            )
        );
    }

    private function tambahIndeks(
        array &$indeks,
        string $key,
        int $id
    ): void {
        if ($key === '' || str_ends_with($key, '|')) {
            return;
        }

        $indeks[$key][$id] = true;
    }

    private function cariWilayah(
        string $provinsiAsli,
        string $kabAsli,
        array $indeksLengkap,
        array $indeksSingkat
    ): array {
        $provinsi = $this->normalisasiProvinsi(
            $provinsiAsli
        );

        $namaLengkap = $this->namaWilayah($kabAsli);

        if ($provinsi === '' || $namaLengkap === '') {
            return [null, 'TIDAK_COCOK'];
        }

        $keyLengkap = $provinsi . '|' . $namaLengkap;

        $ids = array_keys(
            $indeksLengkap[$keyLengkap] ?? []
        );

        if (count($ids) === 1) {
            return [(int) $ids[0], 'COCOK_PASTI'];
        }

        if (count($ids) > 1) {
            return [null, 'AMBIGU'];
        }

        /*
         * Fallback hanya untuk nama wilayah
         * yang TIDAK mencantumkan jenis Kota/Kabupaten.
         *
         * Contoh:
         * "Bandung" dapat dicocokkan jika hanya
         * ada satu wilayah bernama Bandung
         * dalam provinsi tersebut.
         *
         * "Kota Bandung" tidak boleh fallback
         * ke "Kabupaten Bandung".
         */
        if ($this->jenisWilayah($namaLengkap) !== null) {
            return [null, 'TIDAK_COCOK'];
        }

        $namaSingkat = $this->namaTanpaJenis(
            $namaLengkap
        );

        $keySingkat = $provinsi . '|' . $namaSingkat;

        $ids = array_keys(
            $indeksSingkat[$keySingkat] ?? []
        );

        if (count($ids) === 1) {
            return [(int) $ids[0], 'COCOK_UNIK'];
        }

        if (count($ids) > 1) {
            return [null, 'AMBIGU'];
        }

        return [null, 'TIDAK_COCOK'];
    }

    private function buatIndeksWilayah(): array
    {
        $indeksLengkap = [];
        $indeksSingkat = [];

        $wilayah = KabKota::with('provinsi')->get();

        foreach ($wilayah as $kab) {
            $provinsi = $this->normalisasiProvinsi(
                (string) ($kab->provinsi?->nama_provinsi ?? '')
            );

            $namaLengkap = $this->namaWilayah(
                (string) $kab->nama_kab_kota,
                (string) $kab->jenis
            );

            $namaSingkat = $this->namaTanpaJenis(
                $namaLengkap
            );

            $id = (int) $kab->id_kab_kota;

            $this->tambahIndeks(
                $indeksLengkap,
                $provinsi . '|' . $namaLengkap,
                $id
            );

            $this->tambahIndeks(
                $indeksSingkat,
                $provinsi . '|' . $namaSingkat,
                $id
            );
        }

        return [$indeksLengkap, $indeksSingkat];
    }

    private function catatanValidasi(
        string $statusWilayah,
        array $data
    ): string {
        $bagian = [
            'Pencocokan wilayah: ' . $statusWilayah,
            'Objek OpenStreetMap belum diverifikasi sebagai terminal bus resmi',
        ];

        $statusTipeA = trim(
            (string) ($data['status_pencocokan_tipe_a'] ?? '')
        );

        $alasan = trim(
            (string) ($data['alasan_review'] ?? '')
        );

        if ($statusTipeA !== '') {
            $bagian[] = 'Status pencocokan Tipe A dari CSV: ' .
                $statusTipeA;
        }

        if ($alasan !== '') {
            $bagian[] = 'Catatan sumber: ' . $alasan;
        }

        return mb_substr(
            implode('. ', $bagian),
            0,
            1000
        );
    }

    private function simpanLaporan(
        array $laporan,
        string $path
    ): void {
        $output = fopen($path, 'w');

        if ($output === false) {
            throw new RuntimeException(
                'Tidak dapat membuat laporan: ' . $path
            );
        }

        try {
            fputcsv($output, [
                'osm_id',
                'nama',
                'provinsi_csv',
                'kabupaten_kota_csv',
                'id_kab_kota',
                'status',
                'aksi',
                'catatan',
            ]);

            foreach ($laporan as $item) {
                fputcsv($output, $item);
            }
        } finally {
            fclose($output);
        }
    }

    public function handle(): int
    {
        $folder = storage_path('app/datin');

        $path = $folder . '/master_terminal_datin.csv';

        $reportPath = $folder .
            '/laporan_pencocokan_terminal.csv';

        $dryRun = (bool) $this->option('dry-run');

        if (!is_file($path)) {
            $this->error(
                'CSV tidak ditemukan: ' . $path
            );

            return self::FAILURE;
        }

        $handle = fopen($path, 'r');

        if ($handle === false) {
            $this->error('Gagal membuka file CSV.');

            return self::FAILURE;
        }

        $header = fgetcsv($handle);

        if (!$header) {
            fclose($handle);

            $this->error('CSV kosong.');

            return self::FAILURE;
        }

        $header[0] = preg_replace(
            '/^\xEF\xBB\xBF/',
            '',
            $header[0]
        );

        $header = array_map('trim', $header);

        $required = [
            'osm_id',
            'nama_terminal_osm',
            'provinsi',
            'kabupaten_kota',
            'latitude',
            'longitude',
        ];

        $missing = array_diff($required, $header);

        if (!empty($missing)) {
            fclose($handle);

            $this->error(
                'Kolom CSV tidak ditemukan: ' .
                implode(', ', $missing)
            );

            return self::FAILURE;
        }

        if (count($header) !== count(array_unique($header))) {
            fclose($handle);

            $this->error(
                'CSV memiliki nama kolom duplikat.'
            );

            return self::FAILURE;
        }

        try {
            [$indeksLengkap, $indeksSingkat] =
                $this->buatIndeksWilayah();
        } catch (Throwable $e) {
            fclose($handle);

            $this->error(
                'Gagal membaca master wilayah: ' .
                $e->getMessage()
            );

            return self::FAILURE;
        }

        $statistik = [
            'total' => 0,
            'valid' => 0,
            'dilewati' => 0,
            'cocok_pasti' => 0,
            'cocok_unik' => 0,
            'tidak_cocok' => 0,
            'ambigu' => 0,
            'baru' => 0,
            'diperbarui' => 0,
            'konflik' => 0,
        ];

        $laporan = [];
        $referensiDiproses = [];

        /*
         * Baca dan validasi CSV lebih dahulu.
         * Belum ada perubahan database.
         */
        try {
            while (($row = fgetcsv($handle)) !== false) {
                $statistik['total']++;

                if (count($row) !== count($header)) {
                    $statistik['dilewati']++;
                    continue;
                }

                $data = array_combine($header, $row);

                $osmId = trim(
                    (string) ($data['osm_id'] ?? '')
                );

                $lat = trim(
                    (string) ($data['latitude'] ?? '')
                );

                $lon = trim(
                    (string) ($data['longitude'] ?? '')
                );

                if (
                    !preg_match(
                        '/^(node|way|relation)\/[0-9]+$/',
                        $osmId
                    ) ||
                    !is_numeric($lat) ||
                    !is_numeric($lon) ||
                    !is_finite((float) $lat) ||
                    !is_finite((float) $lon) ||
                    abs((float) $lat) > 90 ||
                    abs((float) $lon) > 180
                ) {
                    $statistik['dilewati']++;
                    continue;
                }

                $referensi = 'osm:' . $osmId;

                if (isset($referensiDiproses[$referensi])) {
                    $statistik['dilewati']++;
                    continue;
                }

                $referensiDiproses[$referensi] = true;

                $provinsiAsli = trim(
                    (string) ($data['provinsi'] ?? '')
                );

                $kabAsli = trim(
                    (string) ($data['kabupaten_kota'] ?? '')
                );

                [$kabId, $statusWilayah] =
                    $this->cariWilayah(
                        $provinsiAsli,
                        $kabAsli,
                        $indeksLengkap,
                        $indeksSingkat
                    );

                $nama = trim(
                    (string) ($data['nama_terminal_osm'] ?? '')
                );

                if ($nama === '') {
                    $nama = 'Terminal OSM (tanpa nama)';
                }

                $statistik['valid']++;

                if ($statusWilayah === 'COCOK_PASTI') {
                    $statistik['cocok_pasti']++;
                } elseif ($statusWilayah === 'COCOK_UNIK') {
                    $statistik['cocok_unik']++;
                } elseif ($statusWilayah === 'AMBIGU') {
                    $statistik['ambigu']++;
                } else {
                    $statistik['tidak_cocok']++;
                }

                $laporan[] = [
                    'osm_id' => $osmId,
                    'nama' => $nama,
                    'provinsi_csv' => $provinsiAsli,
                    'kabupaten_kota_csv' => $kabAsli,
                    'id_kab_kota' => $kabId ?? '',
                    'status' => $statusWilayah,
                    'aksi' => '',
                    'catatan' => '',
                    '_referensi' => $referensi,
                    '_latitude' => (float) $lat,
                    '_longitude' => (float) $lon,
                    '_data' => $data,
                ];
            }
        } finally {
            fclose($handle);
        }

        /*
         * Periksa referensi OSM terhadap database,
         * termasuk ketika dry-run.
         *
         * Referensi yang sama tidak boleh:
         * - dimiliki lebih dari satu fasilitas
         * - dimiliki moda selain Darat
         */
        $adaKonflik = false;

        try {
            $referensi = array_column(
                $laporan,
                '_referensi'
            );

            $existingByRef = [];

            foreach (array_chunk($referensi, 400) as $chunk) {
                $existingRows = FasilitasTransportasi::whereIn(
                    'referensi_sumber',
                    $chunk
                )->get();

                foreach ($existingRows as $existing) {
                    $ref = (string) $existing->referensi_sumber;

                    if (!isset($existingByRef[$ref])) {
                        $existingByRef[$ref] = [];
                    }

                    $existingByRef[$ref][] = $existing;
                }
            }

            foreach ($laporan as &$item) {
                $ref = $item['_referensi'];

                $existingRows = $existingByRef[$ref] ?? [];

                if (count($existingRows) > 1) {
                    $item['aksi'] = 'KONFLIK';
                    $item['catatan'] =
                        'Referensi OSM dimiliki lebih dari satu fasilitas';

                    $statistik['konflik']++;
                    $adaKonflik = true;

                    continue;
                }

                if (
                    count($existingRows) === 1 &&
                    (int) $existingRows[0]->id_moda !== 1
                ) {
                    $item['aksi'] = 'KONFLIK';
                    $item['catatan'] =
                        'Referensi OSM sudah digunakan moda lain';

                    $statistik['konflik']++;
                    $adaKonflik = true;

                    continue;
                }

                if (count($existingRows) === 1) {
                    $item['aksi'] = 'PERBARUI';
                    $statistik['diperbarui']++;
                } else {
                    $item['aksi'] = 'TAMBAH';
                    $statistik['baru']++;
                }
            }

            unset($item);

            /*
             * Laporan QA selalu dibuat sebelum
             * proses penyimpanan database.
             */
            $laporanPublik = array_map(
                function (array $item): array {
                    return [
                        'osm_id' => $item['osm_id'],
                        'nama' => $item['nama'],
                        'provinsi_csv' => $item['provinsi_csv'],
                        'kabupaten_kota_csv' =>
                            $item['kabupaten_kota_csv'],
                        'id_kab_kota' => $item['id_kab_kota'],
                        'status' => $item['status'],
                        'aksi' => $item['aksi'],
                        'catatan' => $item['catatan'],
                    ];
                },
                $laporan
            );

            $this->simpanLaporan(
                $laporanPublik,
                $reportPath
            );

            if (!$dryRun && !$adaKonflik) {
                DB::transaction(function () use ($laporan) {
                    foreach ($laporan as $item) {
                        $ref = $item['_referensi'];

                        $data = $item['_data'];

                        $kabId = $item['id_kab_kota'] === ''
                            ? null
                            : (int) $item['id_kab_kota'];

                        $catatan = $this->catatanValidasi(
                            $item['status'],
                            $data
                        );

                        $wilayahOsm = trim(
                            $item['kabupaten_kota_csv'] .
                            ', ' .
                            $item['provinsi_csv'],
                            ', '
                        );

                        $payload = [
                            'id_moda' => 1,
                            'nama' => mb_substr(
                                $item['nama'],
                                0,
                                150
                            ),
                            'latitude' => $item['_latitude'],
                            'longitude' => $item['_longitude'],
                            'sumber_data' =>
                                'OpenStreetMap via Overpass Turbo',
                            'status_pencocokan' =>
                                $item['status'],
                            'wilayah_osm' => mb_substr(
                                $wilayahOsm,
                                0,
                                180
                            ),
                        ];

                        /*
                         * Kunci baris jika sudah ada.
                         * Cek ulang untuk mencegah konflik
                         * antara pemeriksaan dan penyimpanan.
                         */
                        $existingRows =
                            FasilitasTransportasi::where(
                                'referensi_sumber',
                                $ref
                            )
                            ->lockForUpdate()
                            ->get();

                        if ($existingRows->count() > 1) {
                            throw new RuntimeException(
                                'Referensi duplikat: ' . $ref
                            );
                        }

                        $existing = $existingRows->first();

                        if (
                            $existing &&
                            (int) $existing->id_moda !== 1
                        ) {
                            throw new RuntimeException(
                                'Referensi dipakai moda lain: ' . $ref
                            );
                        }

                        if ($existing) {
                            /*
                             * Pertahankan:
                             * - id_kab_kota hasil koreksi manual
                             * - tipe_terminal
                             * - status_verifikasi
                             * - aktif
                             * - catatan_validasi
                             *
                             * Isi wilayah hanya jika sebelumnya NULL.
                             */
                            if (
                                $kabId !== null &&
                                $existing->id_kab_kota === null
                            ) {
                                $payload['id_kab_kota'] =
                                    $kabId;
                            }

                            $existing->update($payload);
                        } else {
                            $payload['id_kab_kota'] = $kabId;
                            $payload['referensi_sumber'] = $ref;
                            $payload['tipe_terminal'] = null;
                            $payload['status_verifikasi'] =
                                'BELUM_DIVERIFIKASI';
                            $payload['aktif'] = 1;
                            $payload['catatan_validasi'] =
                                $catatan;

                            FasilitasTransportasi::create(
                                $payload
                            );
                        }
                    }
                });
            }
        } catch (Throwable $e) {
            $this->error(
                'Pemeriksaan/impor gagal: ' .
                $e->getMessage()
            );

            return self::FAILURE;
        }

        $this->newLine();

        $this->table(
            ['Keterangan', 'Jumlah'],
            [
                ['Total baris', $statistik['total']],
                ['Data valid', $statistik['valid']],
                ['Dilewati', $statistik['dilewati']],
                ['Cocok pasti', $statistik['cocok_pasti']],
                ['Cocok unik', $statistik['cocok_unik']],
                ['Tidak cocok', $statistik['tidak_cocok']],
                ['Ambigu', $statistik['ambigu']],
                ['Rencana data baru', $statistik['baru']],
                ['Rencana diperbarui', $statistik['diperbarui']],
                ['Konflik', $statistik['konflik']],
            ]
        );

        $this->info('Laporan: ' . $reportPath);

        if ($adaKonflik) {
            $this->error(
                'Ditemukan konflik. Database tidak diubah.'
            );

            return self::FAILURE;
        }

        if ($dryRun) {
            $this->warn(
                'DRY RUN: Database tidak diubah.'
            );
        } else {
            $this->info(
                'Import berhasil. Transaksi database selesai.'
            );
        }

        return self::SUCCESS;
    }
}
