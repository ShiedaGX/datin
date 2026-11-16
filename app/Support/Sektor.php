<?php
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
    public static function daftar(): array
    {
        return array_keys(self::peta());
    }

    /** Sektor yang terdeteksi dari teks potensi (tanpa membedakan huruf besar/kecil). */
    public static function deteksi(string $teks): array
    {
        $t = mb_strtolower($teks);
        if (trim($t) === '') {
            return [];
        }

        $hasil = [];
        foreach (self::peta() as $nama => $kunci) {
            foreach ($kunci as $k) {
                if (str_contains($t, $k)) {
                    $hasil[] = $nama;
                    break;
                }
            }
        }

        return $hasil;
    }

    /** "Industri, Tambang & Energi" -> ["industri", "tambang", "energi"] */
    private static function kataDariNama(string $nama): array
    {
        $bagian = preg_split('/\s*(?:&|,|\bdan\b)\s*/iu', mb_strtolower($nama)) ?: [];

        return array_values(array_filter(array_map('trim', $bagian), fn ($x) => $x !== ''));
    }
}
