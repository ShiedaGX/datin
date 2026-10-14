<?php
namespace Database\Seeders;

use App\Models\DataPemda;
use App\Models\KabKota;
use App\Models\Provinsi;
use App\Models\Wilayah;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Mengisi wilayah, provinsi, kab_kota, dan data_pemda dari Excel (format_data_lengkap_web.xlsx).
 * id_user, nama_pemda, kontak_pemda tidak ada di Excel -> dibiarkan NULL.
 */
class WilayahPemdaSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/pemda_excel.csv');
        $h = fopen($path, 'r');
        if ($h === false) {
            throw new \RuntimeException("File tidak ditemukan: $path");
        }
        $header = fgetcsv($h, 0, ',', '"', '');

        DB::transaction(function () use ($h, $header) {
            while (($line = fgetcsv($h, 0, ',', '"', '')) !== false) {
                if (count($line) !== count($header)) {
                    continue;
                }
                $r = array_combine($header, $line);

                $wilayah  = Wilayah::firstOrCreate(['nama_wilayah' => trim($r['wilayah'])]);
                $provinsi = Provinsi::firstOrCreate(
                    ['nama_provinsi' => trim($r['provinsi'])],
                    ['id_wilayah' => $wilayah->id_wilayah]
                );
                $kab = KabKota::create([
                    'id_wilayah'    => $wilayah->id_wilayah,
                    'id_provinsi'   => $provinsi->id_provinsi,
                    'nama_kab_kota' => trim($r['kab_kota']),
                ]);
                DataPemda::create([
                    'id_kab_kota'       => $kab->id_kab_kota,
                    'potensi_pemda'     => $r['potensi'] !== '' ? $r['potensi'] : null,
                    'klasifikasi_pemda' => $r['klasifikasi'] !== '' ? $r['klasifikasi'] : null,
                    'keterangan_pemda'  => $r['keterangan'] !== '' ? $r['keterangan'] : null,
                ]);
            }
        });
        fclose($h);
    }
}
