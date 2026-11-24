<?php

namespace App\Console\Commands;

use App\Models\FasilitasTransportasi;
use App\Models\KabKota;
use Illuminate\Console\Command;

class CocokkanWilayahFasilitas extends Command
{
    protected $signature = 'fasilitas:cocokkan-wilayah {--simpan : Perbarui id_kab_kota setelah memeriksa hasil pratinjau}';
    protected $description = 'Cocokkan kabupaten/kota fasilitas Kemenhub dengan master DATIN secara konservatif';

    public function handle(): int
    {
        $fileByModa = [3 => 'bandara.json', 2 => 'pelabuhan.json', 4 => 'stasiun.json'];
        $sourceIndex = [];
        foreach ($fileByModa as $moda => $filename) {
            $path = storage_path('app/kemenhub/' . $filename);
            if (!is_file($path)) {
                $this->error('Berkas sumber tidak ditemukan: ' . $path);
                return self::FAILURE;
            }
            $rows = json_decode(file_get_contents($path), true);
            if (!is_array($rows)) {
                $this->error('JSON sumber tidak valid: ' . $filename);
                return self::FAILURE;
            }
            foreach ($rows as $row) {
                if (!isset($row['objectid'])) continue;
                $key = 'kemenhub-' . $moda . '-' . $row['objectid'];
                $sourceIndex[$key] = [
                    'kabkot' => trim((string) ($row['kabkot'] ?? '')),
                    'provinsi' => trim((string) ($row['prov'] ?? $row['provinsi'] ?? '')),
                ];
            }
        }

        $wilayah = [];
        foreach (KabKota::with('provinsi')->get() as $kab) {
            $province = $this->normalizeProvince($kab->provinsi?->nama_provinsi ?? '');
            $name = $this->normalizeName($kab->nama_lengkap);
            $wilayah[$province . '|' . $name][] = (int) $kab->id_kab_kota;
            $bareName = preg_replace('/^(kabupaten|kota)\s+/', '', $name);
            // Untuk data pelabuhan yang sering hanya menyebut 'Bontang' dan
            // 'Karimun'. Jika ada Kabupaten/Kota senama, hasilnya ambigu.
            if ($bareName !== $name) {
                $wilayah[$province . '|' . $bareName][] = (int) $kab->id_kab_kota;
            }
        }

        $counts = ['cocok' => 0, 'tidak_cocok' => 0, 'ambigu' => 0, 'sumber_tidak_ditemukan' => 0];
        $examples = [];
        FasilitasTransportasi::query()
            ->whereNull('id_kab_kota')
            ->where('sumber_data', 'like', 'Kemenhub - %')
            ->orderBy('id_fasilitas')
            ->chunkById(200, function ($facilities) use (&$counts, &$examples, $sourceIndex, $wilayah) {
                foreach ($facilities as $facility) {
                    $ref = $facility->referensi_sumber;
                    if (!$ref || !isset($sourceIndex[$ref])) {
                        $counts['sumber_tidak_ditemukan']++;
                        continue;
                    }
                    $source = $sourceIndex[$ref];
                    $prov = $this->normalizeProvince($source['provinsi']);
                    $name = $this->normalizeName($source['kabkot']);
                    if ($prov === '' || $name === '') {
                        $counts['tidak_cocok']++;
                        continue;
                    }
                    $matches = $wilayah[$prov . '|' . $name] ?? [];
                    if (count($matches) === 1) {
                        $counts['cocok']++;
                        if ($this->option('simpan')) {
                            // Jangan pernah menimpa pemetaan yang sudah dibuat oleh Admin.
                            FasilitasTransportasi::where('id_fasilitas', $facility->id_fasilitas)
                                ->whereNull('id_kab_kota')->update(['id_kab_kota' => $matches[0]]);
                        }
                    } else {
                        $reason = count($matches) > 1 ? 'ambigu' : 'tidak_cocok';
                        $counts[$reason]++;
                        if (count($examples) < 12) {
                            $examples[] = [$facility->nama, $source['kabkot'], $source['provinsi'], $reason];
                        }
                    }
                }
            }, 'id_fasilitas');

        $this->info($this->option('simpan') ? 'Pencocokan selesai.' : 'Pratinjau. Database belum diubah.');
        $this->table(['Hasil', 'Jumlah'], collect($counts)->map(fn ($n, $key) => [$key, $n])->values()->all());
        if ($examples) {
            $this->line('Contoh data yang belum dapat dipastikan:');
            $this->table(['Fasilitas', 'Kab/kota sumber', 'Provinsi sumber', 'Alasan'], $examples);
        }
        if (!$this->option('simpan')) {
            $this->line('Jika sudah sesuai, jalankan php artisan fasilitas:cocokkan-wilayah --simpan');
        }
        return self::SUCCESS;
    }

    private function normalizeProvince(string $value): string
    {
        $name = $this->normalize($value);
        return match ($name) {
            'dki jakarta', 'daerah khusus ibukota jakarta', 'daerah khusus jakarta' => 'jakarta',
            'di yogyakarta', 'd i yogyakarta', 'daerah istimewa yogyakarta' => 'yogyakarta',
            'kep bangka belitung', 'kepulauan bangka belitung' => 'bangka belitung',
            'kep riau', 'kepulauan riau' => 'kepulauan riau',
            default => $name,
        };
    }

    private function normalizeName(string $value): string
    {
        $name = $this->normalize($value);
        $name = preg_replace('/^kab(?:upaten)?\s+/', 'kabupaten ', $name);
        $name = preg_replace('/^kota\s+(?:administrasi\s+)?/', 'kota ', $name);
        // Hanya hapus awalan untuk pencocokan jika master tidak memiliki jenis
        // yang ambigu. Nama tanpa awalan tetap harus unik dalam satu provinsi.
        return trim($name);
    }

    private function normalize(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = preg_replace('/[^\pL\pN]+/u', ' ', $value);
        return trim(preg_replace('/\s+/u', ' ', $value));
    }
}
