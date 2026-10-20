<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DokumenPengajuan extends Model
{
    protected $table = 'dokumen_pengajuan';
    protected $primaryKey = 'id_dokumen_pengajuan';
    public $timestamps = true;
    protected $casts = ['tanggal_upload' => 'datetime'];
    protected $fillable = ['id_rute_subsidi', 'id_user', 'jenis_dokumen', 'nama_file', 'path_file', 'versi_dokumen', 'tanggal_upload'];

    public function rute(): BelongsTo { return $this->belongsTo(RuteSubsidi::class, 'id_rute_subsidi', 'id_rute_subsidi'); }
    public function pengunggah(): BelongsTo { return $this->belongsTo(User::class, 'id_user', 'id_user'); }

    /** Pemilik pengajuan, Super Admin, Intram, dan Moda yang moda-nya sama dengan moda pengajuan. */
    public function bisaDiunduhOleh(User $user): bool
    {
        $rute = $this->rute;
        $pemda = $rute->pemda;

        if ($pemda->id_user !== null && (int) $pemda->id_user === (int) $user->id_user) {
            return true;
        }
        if ($user->isSuperAdmin() || $user->isIntram()) {
            return true;
        }

        return $user->isModa() && $user->id_moda !== null && (int) $rute->id_moda === (int) $user->id_moda;
    }
}
