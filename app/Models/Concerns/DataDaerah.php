<?php
namespace App\Models\Concerns;

use App\Models\DataPemda;

/**
 * Tampilan data yang diinput Pemda (klasifikasi, keterangan, potensi, kebutuhan konektivitas, sektor).
 * Dipakai model yang memiliki kolom klasifikasi_pemda, keterangan_pemda, potensi_pemda, kebutuhan_konektivitas.
 * Sektor dibaca dari teks potensi memakai kata kunci di config/sektor.php.
 */
trait DataDaerah
{
    public function getKlasifikasiLabelAttribute(): string
    {
        return DataPemda::normalisasiKlasifikasi($this->klasifikasi_pemda);
    }

    public function getKeteranganDaftarAttribute(): array
    {
        return $this->pecah((string) $this->keterangan_pemda, '/,/');
    }

    /** Butir potensi tanpa penomoran: ["Konstruksi", "Pertanian Sagu", ...] */
    public function getPotensiDaftarAttribute(): array
    {
        $butir = array_map(
            fn ($x) => trim(preg_replace('/^\s*\d+\.\s*/', '', trim($x))),
            explode(';', (string) $this->potensi_pemda)
        );

        return array_values(array_filter($butir, fn ($x) => $x !== ''));
    }

    public function getPotensiRingkasAttribute(): string
    {
        return implode(', ', $this->potensi_daftar);
    }

    public function getSektorTerdeteksiAttribute(): array
    {
        $teks = mb_strtolower((string) $this->potensi_pemda);
        if ($teks === '') {
            return [];
        }

        return collect(config('sektor'))
            ->filter(fn ($kataKunci) => collect($kataKunci)->contains(fn ($k) => str_contains($teks, $k)))
            ->keys()
            ->all();
    }

    public function getJumlahSektorAttribute(): int
    {
        return count($this->sektor_terdeteksi);
    }

    public function getTujuanDaftarAttribute(): array
    {
        return $this->pecah((string) $this->kebutuhan_konektivitas, '/[,;\n]+/');
    }

    public function getJumlahTujuanAttribute(): ?int
    {
        $n = count($this->tujuan_daftar);

        return $n > 0 ? $n : null;
    }

    private function pecah(string $teks, string $pola): array
    {
        $butir = array_map('trim', preg_split($pola, $teks));

        return array_values(array_filter($butir, fn ($x) => $x !== ''));
    }
}
