<?php
namespace App\Support;

class Paginasi
{
    /** Contoh: [1, 2, 3, '...', 24] */
    public static function nomor(int $aktif, int $akhir): array
    {
        $set = collect([1, $akhir, $aktif - 1, $aktif, $aktif + 1]);
        if ($aktif <= 3) {
            $set = $set->merge([2, 3]);
        }
        if ($aktif >= $akhir - 2) {
            $set = $set->merge([$akhir - 2, $akhir - 1]);
        }
        $nomor = $set->filter(fn ($n) => $n >= 1 && $n <= $akhir)->unique()->sort()->values();

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
