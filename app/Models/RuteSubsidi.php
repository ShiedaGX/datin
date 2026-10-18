<?php
namespace App\Models;

use App\Support\StatusPengajuan;
use App\Support\Tanggal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RuteSubsidi extends Model
{
    protected $table = 'rute_subsidi';
    protected $primaryKey = 'id_rute_subsidi';
    public $timestamps = true;
    protected $casts = ['tanggal_pengajuan' => 'date'];
    protected $fillable = ['id_pemda', 'id_moda', 'id_kab_kota_asal', 'id_kab_kota_tujuan', 'status_pengajuan', 'tanggal_pengajuan'];

    public function pemda(): BelongsTo { return $this->belongsTo(DataPemda::class, 'id_pemda', 'id_pemda'); }
    public function moda(): BelongsTo { return $this->belongsTo(Moda::class, 'id_moda', 'id_moda'); }
    public function kabKotaAsal(): BelongsTo { return $this->belongsTo(KabKota::class, 'id_kab_kota_asal', 'id_kab_kota'); }
    public function kabKotaTujuan(): BelongsTo { return $this->belongsTo(KabKota::class, 'id_kab_kota_tujuan', 'id_kab_kota'); }
    public function verifikasi(): HasMany { return $this->hasMany(VerifikasiPengajuan::class, 'id_rute_subsidi', 'id_rute_subsidi'); }
    public function dokumen(): HasMany { return $this->hasMany(DokumenPengajuan::class, 'id_rute_subsidi', 'id_rute_subsidi'); }

    // ---- Tampilan halaman Permohonan ----

    /** Contoh: LP-2026-00482 (tahun pengajuan + id laporan). */
    public function getNomorLaporanAttribute(): string
    {
        $waktu = $this->created_at ?? $this->tanggal_pengajuan;

        return 'LP-' . ($waktu ? $waktu->format('Y') : date('Y')) . '-' . str_pad((string) $this->id_rute_subsidi, 5, '0', STR_PAD_LEFT);
    }

    public function getWaktuPengajuanAttribute()
    {
        return $this->created_at ?? $this->tanggal_pengajuan;
    }

    public function getTanggalLengkapAttribute(): string
    {
        return Tanggal::panjang($this->waktu_pengajuan);
    }

    public function getJamPengajuanAttribute(): string
    {
        return $this->created_at ? Tanggal::jam($this->created_at) : '';
    }

    /** menunggu | disetujui | revisi | ditolak | lainnya */
    public function getGrupStatusAttribute(): string
    {
        return StatusPengajuan::grup($this->status_pengajuan);
    }

    public function getSedangMenungguAttribute(): bool
    {
        return $this->grup_status === 'menunggu';
    }

    public function getKelasStatusModaAttribute(): string
    {
        return 's-' . $this->grup_status;
    }

    /** Label status untuk Moda: "Verifikasi 1" dan "Verifikasi Final" ditampilkan sebagai "Disetujui". */
    public function getLabelStatusModaAttribute(): string
    {
        return StatusPengajuan::LABEL_GRUP[$this->grup_status] ?? $this->status_pengajuan;
    }
}
