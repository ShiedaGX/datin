<?php
namespace App\Support;

/** Pengaturan surat CHR yang diunggah Moda. Ubah daftar jenis surat di sini. */
class SuratOptions
{
    /** kode => nama & apakah wajib. Satu berkas per jenis. */
    public const JENIS = [
        'chr'      => ['nama' => 'Surat Catatan Hasil Review', 'wajib' => true],
        'tambahan' => ['nama' => 'Surat Tambahan',             'wajib' => false],
    ];

    public const MAKS_KB = 10240;   // 10 MB per berkas

    public const DRAF            = 'Draf';
    public const TERKIRIM        = 'Terkirim';
    public const DALAM_REVIEW    = 'Dalam Review';
    public const DISETUJUI       = 'Disetujui';
    public const PERLU_PERBAIKAN = 'Perlu Perbaikan';

    /** kunci filter => status */
    public const STATUS_OPSI = [
        'draf'            => self::DRAF,
        'terkirim'        => self::TERKIRIM,
        'dalam-review'    => self::DALAM_REVIEW,
        'disetujui'       => self::DISETUJUI,
        'perlu-perbaikan' => self::PERLU_PERBAIKAN,
    ];

    public static function jumlahWajib(): int
    {
        return count(array_filter(self::JENIS, fn ($j) => $j['wajib']));
    }

    public static function jumlahOpsional(): int
    {
        return count(self::JENIS) - self::jumlahWajib();
    }

    public static function kelasStatus(string $status): string
    {
        return 'sr-' . array_search($status, self::STATUS_OPSI, true);
    }

    /** 2,4 MB atau 69 KB */
    public static function formatUkuran(int $byte): string
    {
        return $byte >= 1048576
            ? number_format($byte / 1048576, 1, ',', '.') . ' MB'
            : number_format(max($byte, 1) / 1024, 0, ',', '.') . ' KB';
    }
}
