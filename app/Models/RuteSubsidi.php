<?php
namespace App\Models;

use App\Models\Concerns\DataDaerah;
use App\Support\StatusPengajuan;
use App\Support\SuratOptions;
use App\Support\Tanggal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;


class RuteSubsidi extends Model
{
    use DataDaerah;

    protected $table = 'rute_subsidi';
    protected $primaryKey = 'id_rute_subsidi';
    public $timestamps = true;
    protected $casts = ['tanggal_pengajuan' => 'date'];
    protected $fillable = ['id_pemda', 'id_moda', 'id_kab_kota_asal', 'id_kab_kota_tujuan', 'status_pengajuan', 'tanggal_pengajuan', 'klasifikasi_pemda', 'keterangan_pemda', 'potensi_pemda', 'kebutuhan_konektivitas'];

    public function pemda(): BelongsTo { return $this->belongsTo(DataPemda::class, 'id_pemda', 'id_pemda'); }
    public function moda(): BelongsTo { return $this->belongsTo(Moda::class, 'id_moda', 'id_moda'); }
    public function kabKotaAsal(): BelongsTo { return $this->belongsTo(KabKota::class, 'id_kab_kota_asal', 'id_kab_kota'); }
    public function kabKotaTujuan(): BelongsTo { return $this->belongsTo(KabKota::class, 'id_kab_kota_tujuan', 'id_kab_kota'); }

    /** Tujuan (banyak) beserta muatan per tujuan: $k->pivot->komoditas dan $k->pivot->berat (ton). */
    public function tujuan(): BelongsToMany
    {
        return $this->belongsToMany(KabKota::class, 'rute_subsidi_tujuan', 'id_rute_subsidi', 'id_kab_kota_tujuan')
            ->withPivot('komoditas', 'berat')
            ->withTimestamps()
            ->orderBy('nama_kab_kota');
    }

    public function verifikasi(): HasMany { return $this->hasMany(VerifikasiPengajuan::class, 'id_rute_subsidi', 'id_rute_subsidi'); }
    public function pengajuanSurat(): HasMany { return $this->hasMany(PengajuanSurat::class, 'id_rute_subsidi', 'id_rute_subsidi'); }
    public function rekomendasi(): HasManyThrough
    {
        return $this->hasManyThrough(RekomendasiTeknis::class, PengajuanSurat::class, 'id_rute_subsidi', 'id_pengajuan_surat', 'id_rute_subsidi', 'id_pengajuan_surat');
    }
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

    // ---- Surat CHR yang terhubung dengan laporan ini ----

    /** Surat CHR terbaru untuk laporan ini (relasi pengajuanSurat sebaiknya sudah di-eager-load). */
    public function getChrTerbaruAttribute(): ?PengajuanSurat
    {
        return $this->pengajuanSurat->sortByDesc('id_pengajuan_surat')->first();
    }

    /** Moda masih perlu/bisa mengunggah CHR: belum ada, masih draf, atau perlu perbaikan. */
    public function getPerluChrAttribute(): bool
    {
        $chr = $this->chr_terbaru;

        return ! $chr || in_array($chr->status, [SuratOptions::DRAF, SuratOptions::PERLU_PERBAIKAN, SuratOptions::DITOLAK], true);
    }

    /** Waktu laporan disetujui (keputusan "Disetujui" terakhir pada riwayat peninjauan). */
    public function getTanggalDisetujuiAttribute()
    {
        $v = $this->verifikasi->where('status_verifikasi', 'Disetujui')->sortByDesc('id_verifikasi_pengajuan')->first();

        return $v ? $v->tanggal_verifikasi : $this->updated_at;
    }

    /** Surat Rekomendasi Teknis terbaru dari INTRAM untuk laporan ini (relasi rekomendasi sebaiknya sudah di-eager-load). */
    public function getRekomendasiTerbaruAttribute(): ?RekomendasiTeknis
    {
        return $this->rekomendasi->sortByDesc('id_rekomendasi_teknis')->first();
    }

    /** [label, kelas CSS] posisi Surat Rekomendasi Teknis laporan ini (relasi pengajuanSurat dan rekomendasi di-eager-load). */
    public function getStatusRekomendasiAttribute(): array
    {
        if ($this->rekomendasi_terbaru) {
            return ['Terunggah', 'sr-disetujui'];
        }
        if ($this->pengajuanSurat->contains('status', SuratOptions::DISETUJUI)) {
            return ['Menunggu Rekomendasi Teknis', 'sr-dalam-review'];
        }
        $chr = $this->chr_terbaru;

        return $chr ? ['CHR ' . $chr->status, 'sr-draf'] : ['Belum ada CHR', 'sr-belum'];
    }

    public function detailKomoditasTujuan(): HasMany
    {
        return $this->hasMany(
            \App\Models\RuteSubsidiTujuanKomoditas::class,
            'id_rute_subsidi',
            'id_rute_subsidi'
        );
    }
}