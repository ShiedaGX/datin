<?php
namespace App\Support;

/** Status pengajuan yang ditampilkan di daftar Data Wilayah (disimpan di rute_subsidi.status_pengajuan). */
class StatusPengajuan
{
    public const MENUNGGU = 'Menunggu Persetujuan';
    public const REVISI   = 'Perlu Revisi';
    public const V1       = 'Verifikasi 1';
    public const FINAL    = 'Verifikasi Final';
    public const BELUM    = 'Belum Diajukan';   // daerah yang belum punya pengajuan (mis. hasil impor Excel)

    private const KELAS = [
        self::MENUNGGU => 'status-menunggu',
        self::REVISI   => 'status-revisi',
        self::V1       => 'status-v1',
        self::FINAL    => 'status-final',
        self::BELUM    => 'status-belum',
    ];

    public static function normalisasi(?string $status): string
    {
        $status = trim((string) $status);
        if ($status === '') {
            return self::BELUM;
        }
        if ($status === 'Diajukan') {      // nilai awal versi sebelumnya
            return self::MENUNGGU;
        }

        return $status;
    }

    public static function kelas(string $label): string
    {
        return self::KELAS[$label] ?? 'status-belum';
    }
}
