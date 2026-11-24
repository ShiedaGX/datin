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
<<<<<<< HEAD
        self::INTRAM      => ['wilayah', 'moda'],
=======
        self::INTRAM      => [],   // Intram tidak dibatasi cakupan wilayah maupun moda
>>>>>>> 0910d5c911ca44bd84ff0e009248ac8404333f54
    ];

    /** Teks "Cakupan: ..." pada kartu Role & hak akses. */
    public const CAKUPAN = [
        self::PEMDA  => 'wilayah & instansi Pemda',
        self::MODA   => 'Darat / Laut / Udara / Kereta Api',
<<<<<<< HEAD
        self::INTRAM => 'wilayah peninjauan & moda',
=======
        self::INTRAM => 'seluruh wilayah & moda',
>>>>>>> 0910d5c911ca44bd84ff0e009248ac8404333f54
    ];

    public static function syarat(?string $namaRole): array
    {
        return self::SYARAT[$namaRole] ?? [];
    }
}
