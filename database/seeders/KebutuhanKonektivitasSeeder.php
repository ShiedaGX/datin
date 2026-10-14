<?php
namespace Database\Seeders;

use App\Models\DataPemda;
use App\Models\KabKota;
use Illuminate\Database\Seeder;

/**
 * Mengisi data_pemda.kebutuhan_konektivitas dari kolom "Kebutuhan Kota/Kab. untuk Dihubungkan" pada Excel.
 * Aman dijalankan ulang: hanya mengisi baris yang masih kosong (isian dari form tidak ditimpa).
 */
class KebutuhanKonektivitasSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/kebutuhan_excel.csv');
        $h = fopen($path, 'r');
        if ($h === false) {
            throw new \RuntimeException("File tidak ditemukan: $path");
        }
        $header = fgetcsv($h, 0, ',', '"', '');

        $kabKota = KabKota::with('provinsi')->get()
            ->keyBy(fn ($k) => $k->provinsi->nama_provinsi . '|' . $k->nama_kab_kota);

        while (($line = fgetcsv($h, 0, ',', '"', '')) !== false) {
            if (count($line) !== count($header)) {
                continue;
            }
            $r = array_combine($header, $line);
            $kab = $kabKota[$r['provinsi'] . '|' . $r['kab_kota']] ?? null;
            if (! $kab || trim($r['kebutuhan']) === '') {
                continue;
            }
            DataPemda::where('id_kab_kota', $kab->id_kab_kota)
                ->whereNull('kebutuhan_konektivitas')
                ->update(['kebutuhan_konektivitas' => $r['kebutuhan']]);
        }
        fclose($h);
    }
}
