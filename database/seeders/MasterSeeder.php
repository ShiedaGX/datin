<?php
namespace Database\Seeders;

use App\Models\ProgramKawasan;
use App\Models\Sektor;
use Illuminate\Database\Seeder;

class MasterSeeder extends Seeder
{
    public function run(): void
    {
        $program = [
            ['kode' => '3tp', 'nama' => '3TP'],
            ['kode' => 'kspean', 'nama' => 'KSPEAN'],
            ['kode' => 'kek_ki', 'nama' => 'KEK/KI'],
            ['kode' => 'pariwisata', 'nama' => 'Pariwisata'],
            ['kode' => 'komoditas_biru', 'nama' => 'Komoditas Unggulan / Kawasan Ekonomi Biru'],
            ['kode' => 'transmigrasi', 'nama' => 'Transmigrasi / Perdesaan Prioritas'],
        ];
        foreach ($program as $p) {
            ProgramKawasan::updateOrCreate(['kode' => $p['kode']], ['nama' => $p['nama']]);
        }

        $sektor = [
            ['kode' => 'perikanan_kelautan', 'nama' => 'Perikanan & Kelautan'],
            ['kode' => 'pertanian_pangan', 'nama' => 'Pertanian & Pangan'],
            ['kode' => 'perkebunan', 'nama' => 'Perkebunan'],
            ['kode' => 'peternakan', 'nama' => 'Peternakan'],
            ['kode' => 'pariwisata', 'nama' => 'Pariwisata'],
            ['kode' => 'industri_tambang_energi', 'nama' => 'Industri, Tambang & Energi'],
            ['kode' => 'perdagangan_jasa', 'nama' => 'Perdagangan & Jasa'],
            ['kode' => 'garam', 'nama' => 'Garam'],
            ['kode' => 'konstruksi', 'nama' => 'Konstruksi'],
            ['kode' => 'perbatasan_pertahanan', 'nama' => 'Perbatasan & Pertahanan'],
        ];
        foreach ($sektor as $s) {
            Sektor::updateOrCreate(['kode' => $s['kode']], ['nama' => $s['nama']]);
        }
    }
}
