<?php
namespace App\Models;

use App\Support\SuratOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PengajuanSurat extends Model
{
    protected $table = 'pengajuan_surat';
    protected $primaryKey = 'id_pengajuan_surat';
    protected $fillable = ['id_user', 'id_moda', 'tujuan', 'status', 'catatan_pengiriman', 'catatan_review', 'dikirim_at'];
    protected $casts = ['dikirim_at' => 'datetime'];

    public function user(): BelongsTo { return $this->belongsTo(User::class, 'id_user', 'id_user'); }
    public function moda(): BelongsTo { return $this->belongsTo(Moda::class, 'id_moda', 'id_moda'); }
    public function dokumen(): HasMany { return $this->hasMany(DokumenSurat::class, 'id_pengajuan_surat', 'id_pengajuan_surat'); }

    /** Contoh: SR-2026-0018 */
    public function getNomorAttribute(): string
    {
        return 'SR-' . ($this->created_at ? $this->created_at->format('Y') : date('Y')) . '-' . str_pad((string) $this->id_pengajuan_surat, 4, '0', STR_PAD_LEFT);
    }

    public function getKelasStatusAttribute(): string
    {
        return SuratOptions::kelasStatus($this->status);
    }

    public function getSudahDikirimAttribute(): bool
    {
        return $this->status !== SuratOptions::DRAF;
    }
}
