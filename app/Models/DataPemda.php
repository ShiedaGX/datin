<?php
namespace App\Models;

use App\Support\StatusPengajuan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DataPemda extends Model
{
    protected $table = 'data_pemda';
    protected $primaryKey = 'id_pemda';
    public $timestamps = false;
    protected $fillable = [
        'id_user', 'id_kab_kota', 'potensi_pemda', 'nama_pemda', 'kontak_pemda',
        'klasifikasi_pemda', 'keterangan_pemda', 'kebutuhan_konektivitas',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class, 'id_user', 'id_user'); }
    public function kabKota(): BelongsTo { return $this->belongsTo(KabKota::class, 'id_kab_kota', 'id_kab_kota'); }
    public function permintaanRevisi(): HasMany { return $this->hasMany(PermintaanRevisi::class, 'id_pemda', 'id_pemda'); }
    public function ruteSubsidi(): HasMany { return $this->hasMany(RuteSubsidi::class, 'id_pemda', 'id_pemda'); }

    /** Prioritas Strategis | Prioritas | Strategis | Belum diklasifikasi */
    public static function normalisasiKlasifikasi(?string $nilai): string
    {
        $v = mb_strtolower((string) $nilai);
        $p = str_contains($v, 'prioritas');
        $s = str_contains($v, 'strategis');

        return $p && $s ? 'Prioritas Strategis' : ($p ? 'Prioritas' : ($s ? 'Strategis' : 'Belum diklasifikasi'));
    }

    public function getKlasifikasiLabelAttribute(): string
    {
        return self::normalisasiKlasifikasi($this->klasifikasi_pemda);
    }

    /** Daftar sektor yang terdeteksi dari teks potensi_pemda (lihat config/sektor.php). */
    public function getSektorTerdeteksiAttribute(): array
    {
        return \App\Support\Sektor::deteksi((string) $this->potensi_pemda);
    }

    public function getJumlahSektorAttribute(): int
    {
        return count($this->sektor_terdeteksi);
    }

    /** Jumlah tujuan yang tertulis pada kebutuhan_konektivitas (dipisah koma, titik koma, atau baris baru). null bila kosong. */
    public function getJumlahTujuanAttribute(): ?int
    {
        $teks = trim((string) $this->kebutuhan_konektivitas);
        if ($teks === '') {
            return null;
        }
        $butir = array_filter(array_map('trim', preg_split('/[,;\n]+/', $teks)), fn ($x) => $x !== '');

        return count($butir);
    }

    // ---- Tampilan daftar Data Wilayah ----

    /** Pengajuan terbaru (relasi ruteSubsidi sebaiknya sudah di-eager-load). */
    public function getPengajuanTerbaruAttribute(): ?RuteSubsidi
    {
        return $this->ruteSubsidi->sortByDesc('id_rute_subsidi')->first();
    }

    /** Permintaan revisi INTRAM yang belum dipenuhi Pemda. */
    public function getPermintaanTerbukaAttribute()
    {
        return $this->permintaanRevisi->where('status', PermintaanRevisi::MENUNGGU)->values();
    }

    public function getStatusLabelAttribute(): string
    {
        // Ada permintaan revisi dari INTRAM yang belum dipenuhi -> tampil "Perlu Revisi"
        if ($this->permintaan_terbuka->isNotEmpty()) {
            return StatusPengajuan::REVISI;
        }

        return StatusPengajuan::normalisasi($this->pengajuan_terbaru?->status_pengajuan);
    }

    public function getStatusKelasAttribute(): string
    {
        return StatusPengajuan::kelas($this->status_label);
    }

    /** Butir potensi tanpa penomoran: ["Konstruksi", "Pertanian Sagu", ...] */
    public function getPotensiDaftarAttribute(): array
    {
        $butir = array_map(
            fn ($x) => trim(preg_replace('/^\s*\d+\.\s*/', '', trim($x))),
            explode(';', (string) $this->potensi_pemda)
        );

        return array_values(array_filter($butir, fn ($x) => $x !== ''));
    }

    /** Daftar tujuan pada kebutuhan_konektivitas: ["Merauke", "Jayapura", ...] */
    public function getTujuanDaftarAttribute(): array
    {
        $butir = array_map('trim', preg_split('/[,;\n]+/', (string) $this->kebutuhan_konektivitas));

        return array_values(array_filter($butir, fn ($x) => $x !== ''));
    }

    /** Butir keterangan daerah: ["3TP", "KSPEAN", ...] */
    public function getKeteranganDaftarAttribute(): array
    {
        $butir = array_map('trim', explode(',', (string) $this->keterangan_pemda));

        return array_values(array_filter($butir, fn ($x) => $x !== ''));
    }

    public function getPotensiRingkasAttribute(): string
    {
        return implode(', ', $this->potensi_daftar);
    }

    /**
     * Hanya pemilik data yang boleh membatalkan pengajuan, dan tidak setelah disetujui Moda
     * (Verifikasi 1 / Verifikasi Final). Pengajuan yang ditolak atau perlu revisi boleh dibatalkan
     * agar Pemda dapat mengajukan ulang.
     */
    public function bisaDiubahOleh(User $user): bool
    {
        // Pengajuan pada moda mana pun yang sudah disetujui mengunci seluruh data daerah ini
        $adaYangDisetujui = $this->ruteSubsidi->contains(
            fn ($r) => StatusPengajuan::grup($r->status_pengajuan) === 'disetujui'
        );

        return $this->id_user !== null
            && (int) $this->id_user === (int) $user->id_user
            && ! $adaYangDisetujui;
    }

    /** Pemilik boleh membatalkan satu pengajuan selama pengajuan itu belum disetujui Moda. */
    public function bisaDibatalkanOleh(User $user, RuteSubsidi $rute): bool
    {
        return $this->id_user !== null
            && (int) $this->id_user === (int) $user->id_user
            && (int) $rute->id_pemda === (int) $this->id_pemda
            && StatusPengajuan::grup($rute->status_pengajuan) !== 'disetujui';
    }

    /**
     * Dokumen pengajuan hanya untuk pemilik data, Super Admin, Intram, dan Moda yang menjadi
     * tujuan salah satu pengajuan daerah ini.
     */
    public function dokumenBisaDiunduhOleh(User $user): bool
    {
        if ($this->id_user !== null && (int) $this->id_user === (int) $user->id_user) {
            return true;
        }
        if ($user->isSuperAdmin() || $user->isIntram()) {
            return true;
        }
        if ($user->isModa() && $user->id_moda !== null) {
            return $this->ruteSubsidi->contains(fn ($r) => (int) $r->id_moda === (int) $user->id_moda);
        }

        return false;
    }

    /**
     * Data Wilayah per role: Pemda hanya data yang diunggahnya; Moda hanya data pemda yang memiliki pengajuan
     * untuk moda akun itu; INTRAM dan Super Admin melihat semuanya.
     */
    public function scopeTerlihatOleh($query, User $user)
    {
        if ($user->isSuperAdmin() || $user->isIntram()) {
            return $query;
        }
        if ($user->isModa()) {
            return $user->id_moda === null
                ? $query->whereRaw('1 = 0')
                : $query->whereHas('ruteSubsidi', fn ($r) => $r->where('id_moda', $user->id_moda));
        }

        return $query->where('data_pemda.id_user', $user->id_user);
    }

    /** Pengajuan terbaru di atas; daerah tanpa pengajuan menyusul berdasarkan id terbesar. */
    public function scopeTerbaru($query)
    {
        return $query
            ->orderByRaw('(SELECT MAX(rute_subsidi.created_at) FROM rute_subsidi WHERE rute_subsidi.id_pemda = data_pemda.id_pemda) DESC')
            ->orderByDesc('id_pemda');
    }

    /**
     * Pemilik data boleh mengajukan berkali-kali. Data tanpa pemilik hanya bisa diambil alih
     * bila belum punya pengajuan; data milik akun lain tidak boleh diajukan.
     */
    public function bisaDiajukanOleh(User $user): bool
    {
        if ($this->id_user !== null) {
            return (int) $this->id_user === (int) $user->id_user;
        }

        return ! $this->ruteSubsidi()->exists();
    }
}
