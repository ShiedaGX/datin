<?php

namespace App\Console\Commands;

use App\Models\FasilitasTransportasi;
use Illuminate\Console\Command;

class ImporFasilitasKemenhub extends Command
{
    protected $signature = 'fasilitas:impor-kemenhub {--simpan : Simpan hasil pemeriksaan ke database}';
    protected $description = 'Impor bandara, pelabuhan, dan stasiun Kemenhub tanpa mengubah data yang sudah ada';

    public function handle(): int
    {
        $files = [
            ['bandara.json', 3, 'Kemenhub - Bandara'],
            ['pelabuhan.json', 2, 'Kemenhub - Pelabuhan Umum'],
            ['stasiun.json', 4, 'Kemenhub - Stasiun Kereta Api'],
        ];
        $simpan = (bool) $this->option('simpan');
        $existing = FasilitasTransportasi::query()->get();
        $knownReferences = array_fill_keys($existing->pluck('referensi_sumber')->filter()->all(), true);
        $knownLocations = [];
        foreach ($existing as $row) {
            $knownLocations[$this->locationKey((int) $row->id_moda, (float) $row->latitude, (float) $row->longitude)] = true;
        }
        $created = $duplicates = $invalid = $inactive = 0;

        foreach ($files as [$filename, $moda, $source]) {
            $path = storage_path('app/kemenhub/' . $filename);
            if (!is_file($path)) {
                $this->error("Berkas tidak ditemukan: {$path}");
                return self::FAILURE;
            }
            $rows = json_decode(file_get_contents($path), true);
            if (!is_array($rows) || !array_is_list($rows)) {
                $this->error("Format JSON bukan daftar objek: {$filename}");
                return self::FAILURE;
            }
            foreach ($rows as $row) {
                $name = trim((string) ($row['namobj'] ?? ''));
                $lat = $row['lat'] ?? null;
                $lon = $row['lon'] ?? null;
                if ($name === '' || !is_numeric($lat) || !is_numeric($lon)
                    || abs((float) $lat) > 90 || abs((float) $lon) > 180
                    || ((float) $lat === 0.0 && (float) $lon === 0.0)) {
                    $invalid++;
                    continue;
                }
                // Stasiun yang secara eksplisit dinyatakan nonaktif tidak menjadi kandidat.
                if ($moda === 4 && strtolower(trim((string) ($row['status_operasi'] ?? ''))) === 'non aktif') {
                    $inactive++;
                    continue;
                }
                $id = $row['objectid'] ?? null;
                $reference = $id === null ? null : 'kemenhub-' . $moda . '-' . $id;
                $key = $this->locationKey($moda, (float) $lat, (float) $lon);
                if (($reference && isset($knownReferences[$reference])) || isset($knownLocations[$key])) {
                    $duplicates++;
                    continue;
                }

                if ($simpan) {
                    FasilitasTransportasi::create([
                        'nama' => mb_substr($name, 0, 150),
                        'id_moda' => $moda,
                        'id_kab_kota' => null, // Tidak menebak FK wilayah dari teks kab/kota.
                        'latitude' => (float) $lat,
                        'longitude' => (float) $lon,
                        'sumber_data' => $source,
                        'referensi_sumber' => $reference,
                        'aktif' => true,
                    ]);
                }
                if ($reference) $knownReferences[$reference] = true;
                $knownLocations[$key] = true;
                $created++;
            }
        }

        $this->info(($simpan ? 'Impor selesai' : 'Pratinjau, belum menyimpan') .
            ": {$created} baru, {$duplicates} sudah ada, {$invalid} koordinat/nama tidak valid, {$inactive} stasiun nonaktif dilewati.");
        if (!$simpan) $this->line('Jalankan php artisan fasilitas:impor-kemenhub --simpan jika hasilnya sesuai.');
        return self::SUCCESS;
    }

    private function locationKey(int $moda, float $lat, float $lon): string
    {
        return sprintf('%d:%.4f:%.4f', $moda, $lat, $lon);
    }
}
