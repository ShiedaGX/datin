<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeninjauanSurat extends Model
{
    const UPDATED_AT = null;   // tabel hanya punya created_at

    protected $table = 'peninjauan_surat';
    protected $primaryKey = 'id_peninjauan_surat';
    protected $fillable = ['id_pengajuan_surat', 'id_user', 'aksi', 'catatan'];

    public function pengajuan(): BelongsTo { return $this->belongsTo(PengajuanSurat::class, 'id_pengajuan_surat', 'id_pengajuan_surat'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class, 'id_user', 'id_user'); }
}
