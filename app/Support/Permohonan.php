<?php

namespace App\Support;

use App\Models\RuteSubsidi;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

/** Query dasar halaman Permohonan: laporan hanya untuk moda milik akun yang sedang login. */
class Permohonan
{
    /** Laporan (rute_subsidi) dengan moda yang sama seperti cakupan moda akun. Tanpa cakupan moda -> kosong. */
    public static function untukModa(User $user)
    {
        return RuteSubsidi::query()->where('id_moda', $user->id_moda ?? 0);
    }

    /** Angka kartu "Perlu ditinjau" pada sidebar. */
    public static function ringkasanSidebar(User $user): array
    {
        $menunggu = self::untukModa($user)->whereIn('status_pengajuan', StatusPengajuan::GRUP['menunggu']);
class Permohonan
{
    /**
     * Laporan sesuai cakupan moda akun.
     * Akun tanpa cakupan moda tidak mendapatkan laporan.
     */
    public static function untukModa(User $user)
    {
        $query = RuteSubsidi::query();

        if ($user->id_moda === null) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('id_moda', $user->id_moda);
    }

    /**
     * Ringkasan laporan yang perlu ditinjau pada sidebar.
     */
    public static function ringkasanSidebar(User $user): array
    {
        $menunggu = self::untukModa($user)
            ->whereIn(
                'status_pengajuan',
                StatusPengajuan::GRUP['menunggu']
            );

        $target = (int) config('datin.target_tinjau_hari', 3);

        return [
            'menunggu' => (clone $menunggu)->count(),
            'lewat'    => (clone $menunggu)->where('created_at', '<', now()->subDays($target))->count(),
            'target'   => $target,
        ];
    }

    /** Data detail laporan untuk tampilan: dokumen Pemda, riwayat peninjauan, dan data yang diinput Pemda. */
    public static function detail(RuteSubsidi $rute): array
    {
        $rute->load('pemda.user.role', 'kabKotaAsal.provinsi', 'kabKotaAsal.wilayah', 'moda', 'dokumen', 'verifikasi.verifikator', 'rekomendasi', 'pengajuanSurat');
        $disk = Storage::disk('local');
        $riwayat = $rute->verifikasi->sortBy('id_verifikasi_pengajuan')->values();

        return [
            'rute'     => $rute,
            'pemda'    => $rute->pemda,
            'pengaju'  => $rute->pemda->user,
            'berkas'   => $rute->dokumen->map(fn ($d) => [
                'model'  => $d,
                'jenis'  => strtoupper(pathinfo($d->nama_file, PATHINFO_EXTENSION)),
                'ukuran' => $disk->exists($d->path_file) ? SuratOptions::formatUkuran((int) $disk->size($d->path_file)) : null,
            ]),
            'rekomendasi' => $rute->rekomendasi_terbaru,
            'riwayat'  => $riwayat,

            'lewat' => (clone $menunggu)
                ->where('created_at', '<', now()->subDays($target))
                ->count(),

            'target' => $target,
        ];
    }

    /**
     * Data detail laporan untuk halaman review,
     * pengajuan CHR, dan halaman terkait.
     */
    public static function detail(RuteSubsidi $rute): array
    {
        $rute->load([
            'pemda.user.role',
            'kabKotaAsal.provinsi',
            'kabKotaAsal.wilayah',
            'moda',
            'tujuan',
            'detailKomoditasTujuan',
            'dokumen',
            'verifikasi.verifikator',
            'rekomendasi',
            'pengajuanSurat',
        ]);

        $disk = Storage::disk('local');

        $riwayat = $rute->verifikasi
            ->sortBy('id_verifikasi_pengajuan')
            ->values();

        return [
            'rute' => $rute,

            'pemda' => $rute->pemda,

            'pengaju' => $rute->pemda?->user,

            'berkas' => $rute->dokumen->map(fn ($d) => [
                'model' => $d,

                'jenis' => strtoupper(
                    pathinfo(
                        $d->nama_file,
                        PATHINFO_EXTENSION
                    )
                ),

                'ukuran' => $disk->exists($d->path_file)
                    ? SuratOptions::formatUkuran(
                        (int) $disk->size($d->path_file)
                    )
                    : null,
            ]),

            'rekomendasi' => $rute->rekomendasi_terbaru,

            'riwayat' => $riwayat,

            'terakhir' => $riwayat->last(),
        ];
    }
}
