<?php
<<<<<<< HEAD
namespace App\Support;

/**
 * Daftar sektor potensi. Sumbernya config/sektor.php, dengan dua format yang sama-sama didukung:
 *   'Perikanan & Kelautan' => ['perikanan', 'kelautan', 'udang', ...]   (nama => kata kunci, deteksi paling akurat)
 *   'Perikanan & Kelautan'                                              (nama saja; kata kunci diambil dari namanya)
 */
class Sektor
{
    /** @return array<string, array<int,string>> nama sektor => kata kunci (huruf kecil) */
    public static function peta(): array
    {
        $peta = [];
        foreach ((array) config('sektor', []) as $kunci => $nilai) {
            if (is_array($nilai)) {
                $peta[(string) $kunci] = array_values(array_filter(array_map(fn ($k) => mb_strtolower(trim((string) $k)), $nilai), fn ($k) => $k !== ''));
            } else {
                $nama = trim((string) $nilai);
                if ($nama !== '') {
                    $peta[$nama] = self::kataDariNama($nama);
                }
            }
        }

        return $peta;
    }

    /** @return array<int,string> */
=======

namespace App\Support;

use App\Models\MasterSektor;
use Illuminate\Support\Facades\Cache;

class Sektor
{
    public const CACHE_KEY = 'master_sektor.peta';
    public const CACHE_KEY_KOMODITAS = 'master_sektor.komoditas';

    /** @return array<string, string[]> 'Nama Sektor' => [kata kunci...] */
    public static function peta(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            return MasterSektor::with('komoditas:id_komoditas,id_sektor,nama_komoditas')
                ->orderBy('urutan')->get()
                ->mapWithKeys(function ($s) {
                    $dariNama = preg_split('/\s*[&,]\s*/', mb_strtolower($s->nama_sektor));
                    $kunci = collect($dariNama)
                        ->merge($s->komoditas->pluck('nama_komoditas')->map(fn ($k) => mb_strtolower($k)))
                        ->filter()->unique()->values()->all();

                    return [$s->nama_sektor => $kunci];
                })->all();
        });
    }

    /** @return array<int, array{nama: string, sektor: string[]}> */
    public static function komoditas(): array
    {
        return Cache::rememberForever(self::CACHE_KEY_KOMODITAS, function () {
            return MasterSektor::with('komoditas:id_komoditas,id_sektor,nama_komoditas,satuan_default')
                ->orderBy('urutan')->get()
                ->flatMap(fn ($s) => $s->komoditas->map(fn ($k) => [
                    'nama'   => $k->nama_komoditas,
                    'sektor' => $s->nama_sektor,
                    'satuan_default' => $k->satuan_default,
                ]))
                ->groupBy(fn ($r) => mb_strtolower($r['nama']))
                ->map(fn ($g) => [
                    'nama'   => $g->first()['nama'],
                    'sektor' => $g->pluck('sektor')->unique()->values()->all(),
                    // Nama sama lintas sektor hanya disarankan jika semua default identik dan terisi.
                    'satuan_default' => $g->pluck('satuan_default')->filter(fn ($v) => $v !== null && $v !== '')->unique()->count() === 1
                        && $g->every(fn ($r) => ! empty($r['satuan_default']))
                        ? $g->first()['satuan_default'] : null,
                ])
                ->sortBy('nama', SORT_NATURAL | SORT_FLAG_CASE)
                ->values()->all();
        });
    }

    /**
     * Sektor dari teks komoditas: cocok persis dulu, lalu cocok sebagian.
     * @return string[]
     */
    public static function dariKomoditas(?string $teks): array
    {
        $t = mb_strtolower(trim((string) $teks));
        if (mb_strlen($t) < 2) {
            return [];
        }

        $semua = self::komoditas();
        foreach ($semua as $k) {
            if (mb_strtolower($k['nama']) === $t) {
                return $k['sektor'];
            }
        }
        if (mb_strlen($t) < 3) {
            return [];
        }

        $hasil = [];
        foreach ($semua as $k) {
            $n = mb_strtolower($k['nama']);
            if (str_contains($t, $n) || str_contains($n, $t)) {
                $hasil = array_merge($hasil, $k['sektor']);
            }
        }

        return array_values(array_unique($hasil));
    }

    /** @return string[] */
>>>>>>> 0910d5c911ca44bd84ff0e009248ac8404333f54
    public static function daftar(): array
    {
        return array_keys(self::peta());
    }

<<<<<<< HEAD
    /** Sektor yang terdeteksi dari teks potensi (tanpa membedakan huruf besar/kecil). */
    public static function deteksi(string $teks): array
    {
        $t = mb_strtolower($teks);
        if (trim($t) === '') {
=======
    /** @return string[] */
    public static function deteksi(string $teks): array
    {
        $teks = mb_strtolower($teks);
        if (trim($teks) === '') {
>>>>>>> 0910d5c911ca44bd84ff0e009248ac8404333f54
            return [];
        }

        $hasil = [];
        foreach (self::peta() as $nama => $kunci) {
            foreach ($kunci as $k) {
<<<<<<< HEAD
                if (str_contains($t, $k)) {
=======
                if ($k !== '' && str_contains($teks, $k)) {
>>>>>>> 0910d5c911ca44bd84ff0e009248ac8404333f54
                    $hasil[] = $nama;
                    break;
                }
            }
        }

        return $hasil;
    }

<<<<<<< HEAD
    /** "Industri, Tambang & Energi" -> ["industri", "tambang", "energi"] */
    private static function kataDariNama(string $nama): array
    {
        $bagian = preg_split('/\s*(?:&|,|\bdan\b)\s*/iu', mb_strtolower($nama)) ?: [];

        return array_values(array_filter(array_map('trim', $bagian), fn ($x) => $x !== ''));
    }
}
=======
    public static function lupakan(): void
    {
        Cache::forget(self::CACHE_KEY);
        Cache::forget(self::CACHE_KEY_KOMODITAS);
    }
}
>>>>>>> 0910d5c911ca44bd84ff0e009248ac8404333f54
