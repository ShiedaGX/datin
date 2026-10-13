<?php
namespace Database\Seeders;

use App\Models\KabupatenKota;
use App\Models\PotensiDaerah;
use App\Models\ProgramKawasan;
use App\Models\Provinsi;
use App\Models\Sektor;
use App\Models\TujuanKoneksi;
use App\Models\Wilayah;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KabupatenKotaSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/kabupaten_kota.csv');
        $h = fopen($path, 'r');
        if ($h === false) {
            throw new \RuntimeException("File tidak ditemukan: $path");
        }
        $header = fgetcsv($h, 0, ',', '"', '');

        $programIds = ProgramKawasan::pluck('id', 'kode');   // kode => id
        $sektorIds  = Sektor::pluck('id', 'kode');

        DB::transaction(function () use ($h, $header, $programIds, $sektorIds) {
            while (($line = fgetcsv($h, 0, ',', '"', '')) !== false) {
                if (count($line) !== count($header)) {
                    continue; // lewati baris kosong
                }
                $r = array_combine($header, $line);

                $wilayah  = Wilayah::firstOrCreate(['nama' => trim($r['wilayah'])]);
                $provinsi = Provinsi::firstOrCreate(
                    ['nama' => trim($r['provinsi'])],
                    ['wilayah_id' => $wilayah->id]
                );

                $kab = KabupatenKota::create([
                    'provinsi_id'         => $provinsi->id,
                    'no_urut'             => (int) $r['no_urut'],
                    'nama'                => trim($r['kabupaten_kota']),
                    'is_prioritas'        => (bool) (int) $r['is_prioritas'],
                    'is_strategis'        => (bool) (int) $r['is_strategis'],
                    'jumlah_tujuan_valid' => (int) $r['jumlah_tujuan_valid'],
                ]);

                // Pivot program/kawasan (kolom prog_*) dan sektor (kolom sek_*)
                $progSync = [];
                $sekSync  = [];
                foreach ($r as $kolom => $nilai) {
                    if (str_starts_with($kolom, 'prog_') && (int) $nilai === 1) {
                        $progSync[] = $programIds[substr($kolom, 5)];
                    }
                    if (str_starts_with($kolom, 'sek_') && (int) $nilai === 1) {
                        $sekSync[] = $sektorIds[substr($kolom, 4)];
                    }
                }
                $kab->programKawasan()->sync($progSync);
                $kab->sektor()->sync($sekSync);

                // Potensi Daerah: "1. A; 2. B; 3. C" -> baris per item
                $urutan = 0;
                foreach (explode(';', $r['potensi_daerah']) as $item) {
                    $item = trim(preg_replace('/^\s*\d+\.\s*/', '', trim($item)));
                    if ($item === '') {
                        continue;
                    }
                    PotensiDaerah::create([
                        'kabupaten_kota_id' => $kab->id,
                        'urutan'            => ++$urutan,
                        'deskripsi'         => $item,
                    ]);
                }

                // Kebutuhan koneksi: "A, B, C" -> baris per tujuan
                $urutan = 0;
                foreach (explode(',', $r['kebutuhan_koneksi']) as $tujuan) {
                    $tujuan = trim($tujuan);
                    if ($tujuan === '') {
                        continue;
                    }
                    TujuanKoneksi::create([
                        'kabupaten_kota_id' => $kab->id,
                        'urutan'            => ++$urutan,
                        'nama_tujuan'       => $tujuan,
                    ]);
                }
            }
        });
        fclose($h);
    }
}
