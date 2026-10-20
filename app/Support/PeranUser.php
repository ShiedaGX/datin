<?php
namespace App\Support;

/** Aturan cakupan akun per role. Nama role harus sama dengan isi tabel role. */
class PeranUser
{
    public const SUPER_ADMIN = 'Super Admin';
    public const PEMDA       = 'Pemda';
    public const MODA        = 'Moda';
    public const INTRAM      = 'Intram';

    /** Isian cakupan yang WAJIB diisi untuk tiap role: wilayah, moda, instansi. */
    public const SYARAT = [
        self::SUPER_ADMIN => [],
        self::PEMDA       => ['wilayah', 'instansi'],
        self::MODA        => ['moda'],
        self::INTRAM      => ['wilayah', 'moda'],
    ];

    /** Teks "Cakupan: ..." pada kartu Role & hak akses. */
    public const CAKUPAN = [
        self::PEMDA  => 'wilayah & instansi Pemda',
        self::MODA   => 'Darat / Laut / Udara / Kereta Api',
        self::INTRAM => 'wilayah peninjauan & moda',
    ];

    public static function syarat(?string $namaRole): array
    {
        return self::SYARAT[$namaRole] ?? [];
    }
}
