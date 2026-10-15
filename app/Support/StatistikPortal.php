<?php
namespace App\Support;

use App\Models\DataPemda;
use App\Models\KabKota;
use App\Models\Provinsi;

/** Angka ringkasan pada panel kiri halaman login & registrasi. */
class StatistikPortal
{
    public static function ringkasan(): array
    {
        $total = DataPemda::count();
        $lengkap = DataPemda::query()
            ->whereNotNull('potensi_pemda')->where('potensi_pemda', '!=', '')
            ->whereNotNull('klasifikasi_pemda')->where('klasifikasi_pemda', '!=', '')
            ->whereNotNull('keterangan_pemda')->where('keterangan_pemda', '!=', '')
            ->count();

        return [
            'provinsi'    => Provinsi::count(),
            'wilayah'     => KabKota::count(),
            // % data_pemda yang potensi, klasifikasi, dan keterangannya terisi
            'kelengkapan' => $total > 0 ? (int) round($lengkap / $total * 100) : 0,
        ];
    }
}
