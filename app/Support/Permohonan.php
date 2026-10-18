<?php
namespace App\Support;

use App\Models\RuteSubsidi;
use App\Models\User;

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
        $target = (int) config('datin.target_tinjau_hari', 3);

        return [
            'menunggu' => (clone $menunggu)->count(),
            'lewat'    => (clone $menunggu)->where('created_at', '<', now()->subDays($target))->count(),
            'target'   => $target,
        ];
    }
}
