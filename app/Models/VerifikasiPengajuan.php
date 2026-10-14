<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class VerifikasiPengajuan extends Model
{
    protected $table = 'verifikasi_pengajuan';
    protected $primaryKey = 'id_verifikasi_pengajuan';
    public $timestamps = true;
    const UPDATED_AT = null;   // tabel hanya punya created_at
    protected $casts = ['tanggal_verifikasi' => 'datetime'];
    protected $fillable = ['id_rute_subsidi', 'id_user', 'tahap_verifikasi', 'status_verifikasi', 'catatan_verifikasi', 'tanggal_verifikasi'];

    public function rute(): BelongsTo { return $this->belongsTo(RuteSubsidi::class, 'id_rute_subsidi', 'id_rute_subsidi'); }
    public function verifikator(): BelongsTo { return $this->belongsTo(User::class, 'id_user', 'id_user'); }
}
