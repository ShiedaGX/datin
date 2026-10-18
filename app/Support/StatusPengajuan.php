<?php
namespace App\Support;

/**
 * Status pengajuan (disimpan di rute_subsidi.status_pengajuan).
 *
 * Alur: Pemda mengajukan -> "Menunggu Persetujuan" -> peninjauan Moda:
 *   Setujui      -> "Verifikasi 1"  (ditampilkan "Disetujui" pada halaman Permohonan Moda)
 *   Minta Revisi -> "Perlu Revisi"
 *   Tolak        -> "Ditolak"
 * "Verifikasi Final" disiapkan untuk tahap peninjauan berikutnya (Intram).
 */
class StatusPengajuan
{
    public const MENUNGGU = 'Menunggu Persetujuan';
    public const REVISI   = 'Perlu Revisi';
    public const V1       = 'Verifikasi 1';
    public const FINAL    = 'Verifikasi Final';
    public const DITOLAK  = 'Ditolak';
    public const BELUM    = 'Belum Diajukan';   // daerah yang belum punya pengajuan (mis. hasil impor Excel)

    public const TAHAP_MODA = 'Moda';

    /** Pengelompokan untuk halaman Permohonan: kunci => status yang termasuk. */
    public const GRUP = [
        'menunggu'  => [self::MENUNGGU, 'Diajukan'],   // 'Diajukan' = nilai awal versi lama
        'disetujui' => [self::V1, self::FINAL, 'Disetujui'],
        'revisi'    => [self::REVISI],
        'ditolak'   => [self::DITOLAK],
    ];

    public const LABEL_GRUP = [
        'menunggu'  => 'Menunggu Persetujuan',
        'disetujui' => 'Disetujui',
        'revisi'    => 'Perlu Revisi',
        'ditolak'   => 'Ditolak',
    ];

    private const KELAS = [
        self::MENUNGGU => 'status-menunggu',
        self::REVISI   => 'status-revisi',
        self::V1       => 'status-v1',
        self::FINAL    => 'status-final',
        self::DITOLAK  => 'status-ditolak',
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

    /** menunggu | disetujui | revisi | ditolak | lainnya */
    public static function grup(?string $status): string
    {
        foreach (self::GRUP as $kunci => $daftar) {
            if (in_array($status, $daftar, true)) {
                return $kunci;
            }
        }

        return 'lainnya';
    }
}
