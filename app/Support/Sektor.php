<?php

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
                    // kata dari nama sektor ("Perikanan & Kelautan" => perikanan, kelautan) + semua komoditas
                    $dariNama = preg_split('/\s*[&,]\s*/', mb_strtolower($s->nama_sektor));
                    $kunci = collect($dariNama)
                        ->merge($s->komoditas->pluck('nama_komoditas')->map(fn ($k) => mb_strtolower($k)))
                        ->filter()->unique()->values()->all();

                    return [$s->nama_sektor => $kunci];
                })->all();
        });
    }

    /**
     * Daftar komoditas beserta sektornya (untuk kolom Komoditas -> Sektor pada form).
     *
     * @return array<int, array{nama: string, sektor: string[]}>
     */
    public static function komoditas(): array
    {
        return Cache::rememberForever(self::CACHE_KEY_KOMODITAS, function () {
            return MasterSektor::with('komoditas:id_komoditas,id_sektor,nama_komoditas')
                ->orderBy('urutan')->get()
                ->flatMap(fn ($s) => $s->komoditas->map(fn ($k) => [
                    'nama'   => $k->nama_komoditas,
                    'sektor' => $s->nama_sektor,
                ]))
                ->groupBy(fn ($r) => mb_strtolower($r['nama']))
                ->map(fn ($g) => [
                    'nama'   => $g->first()['nama'],
                    'sektor' => $g->pluck('sektor')->unique()->values()->all(),
                ])
                ->sortBy('nama', SORT_NATURAL | SORT_FLAG_CASE)
                ->values()->all();
        });
    }

    /** @return string[] daftar nama sektor, sesuai urutan master */
    public static function daftar(): array
    {
        return array_keys(self::peta());
    }

    /**
     * Nama sektor yang terdeteksi dari teks potensi: dihitung bila minimal satu
     * kata kunci ditemukan (tanpa membedakan huruf besar/kecil).
     *
     * @return string[]
     */
    public static function deteksi(string $teks): array
    {
        $teks = mb_strtolower($teks);
        if (trim($teks) === '') {
            return [];
        }

        $hasil = [];
        foreach (self::peta() as $nama => $kunci) {
            foreach ($kunci as $k) {
                if ($k !== '' && str_contains($teks, $k)) {
                    $hasil[] = $nama;
                    break;
                }
            }
        }

        return $hasil;
    }

    public static function lupakan(): void
    {
        Cache::forget(self::CACHE_KEY);
        Cache::forget(self::CACHE_KEY_KOMODITAS);
    }
}