<?php
namespace App\Models;

use App\Support\SuratOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PengajuanSurat extends Model
{
    protected $table = 'pengajuan_surat';
    protected $primaryKey = 'id_pengajuan_surat';
    protected $fillable = ['id_user', 'id_moda', 'id_rute_subsidi', 'tujuan', 'status', 'catatan_pengiriman', 'catatan_review', 'dikirim_at'];
    protected $casts = ['dikirim_at' => 'datetime'];

    public function user(): BelongsTo { return $this->belongsTo(User::class, 'id_user', 'id_user'); }
    public function moda(): BelongsTo { return $this->belongsTo(Moda::class, 'id_moda', 'id_moda'); }
    public function rute(): BelongsTo { return $this->belongsTo(RuteSubsidi::class, 'id_rute_subsidi', 'id_rute_subsidi'); }
    public function peninjauan(): HasMany { return $this->hasMany(PeninjauanSurat::class, 'id_pengajuan_surat', 'id_pengajuan_surat'); }
    public function rekomendasi(): HasOne { return $this->hasOne(RekomendasiTeknis::class, 'id_pengajuan_surat', 'id_pengajuan_surat'); }
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

    /**
     * Siapa yang boleh melihat/mengunduh berkas Surat CHR ini:
     * - Moda pemilik surat (kapan pun, termasuk draf);
     * - Pemda pemilik laporan, setelah surat dikirim (Moda sudah menyetujui laporan dan mengunggah CHR);
     * - Super Admin dan INTRAM yang berhak meninjau, setelah surat dikirim.
     */
    public function bisaDiaksesOleh(User $user): bool
    {
        if ($user->isModa()) {
            return $user->id_moda !== null && (int) $this->id_moda === (int) $user->id_moda;
        }
        if (! $this->sudah_dikirim) {
            return false;
        }
        if ($user->isSuperAdmin() || ($user->isIntram() && $user->bisa('chr.tinjau'))) {
            return true;
        }
        $pemda = $this->rute?->pemda;

        return $pemda !== null && $pemda->id_user !== null && (int) $pemda->id_user === (int) $user->id_user;
    }
}
