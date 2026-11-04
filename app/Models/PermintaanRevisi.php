<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Permintaan INTRAM agar Pemda mengajukan lagi untuk moda tambahan (pengajuan moda sebelumnya tidak terpengaruh). */
class PermintaanRevisi extends Model
{
    public const MENUNGGU = 'Menunggu';
    public const DIPENUHI = 'Dipenuhi';

    protected $table = 'permintaan_revisi';
    protected $primaryKey = 'id_permintaan_revisi';
    protected $fillable = ['id_pemda', 'id_rute_asal', 'id_pengajuan_surat', 'id_moda', 'id_user', 'catatan', 'status', 'id_rute_baru'];

    public function pemda(): BelongsTo { return $this->belongsTo(DataPemda::class, 'id_pemda', 'id_pemda'); }
    public function ruteAsal(): BelongsTo { return $this->belongsTo(RuteSubsidi::class, 'id_rute_asal', 'id_rute_subsidi'); }
    public function pengajuanSurat(): BelongsTo { return $this->belongsTo(PengajuanSurat::class, 'id_pengajuan_surat', 'id_pengajuan_surat'); }
    public function moda(): BelongsTo { return $this->belongsTo(Moda::class, 'id_moda', 'id_moda'); }
    public function peminta(): BelongsTo { return $this->belongsTo(User::class, 'id_user', 'id_user'); }
    public function ruteBaru(): BelongsTo { return $this->belongsTo(RuteSubsidi::class, 'id_rute_baru', 'id_rute_subsidi'); }

    public function getTerbukaAttribute(): bool
    {
        return $this->status === self::MENUNGGU;
    }
}
