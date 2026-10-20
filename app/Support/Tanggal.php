<?php
namespace App\Support;

use Carbon\CarbonInterface;

/** Format tanggal berbahasa Indonesia, tidak bergantung pada locale aplikasi. */
class Tanggal
{
    private const BULAN = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

    /** 28 September 2026 */
    public static function panjang(?CarbonInterface $d): string
    {
        return $d ? $d->day . ' ' . self::BULAN[$d->month] . ' ' . $d->year : '-';
    }

    /** 28 Sep 2026 */
    public static function pendek(?CarbonInterface $d): string
    {
        return $d ? $d->format('d') . ' ' . mb_substr(self::BULAN[$d->month], 0, 3) . ' ' . $d->year : '-';
    }

    /** 14.32 WIB (singkatan zona mengikuti timezone aplikasi, mis. Asia/Jakarta -> WIB) */
    public static function jam(?CarbonInterface $d): string
    {
        return $d ? $d->format('H.i') . ' ' . $d->format('T') : '';
    }

    /** "2026-09" -> "September 2026" */
    public static function bulanTahun(string $ym): string
    {
        [$tahun, $bulan] = explode('-', $ym);

        return self::BULAN[(int) $bulan] . ' ' . $tahun;
    }
}
