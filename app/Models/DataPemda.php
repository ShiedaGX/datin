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
        $teks = mb_strtolower((string) $this->potensi_pemda);
        if ($teks === '') {
            return [];
        }

        return collect(config('sektor'))
            ->filter(fn ($kataKunci) => collect($kataKunci)->contains(fn ($k) => str_contains($teks, $k)))
            ->keys()
            ->all();
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

    public function getStatusLabelAttribute(): string
    {
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

    public function getPotensiRingkasAttribute(): string
    {
        return implode(', ', $this->potensi_daftar);
    }

    /** Hanya pemilik data yang boleh menghapus, dan tidak setelah Verifikasi Final. */
    public function bisaDiubahOleh(User $user): bool
    {
        return $this->id_user !== null
            && (int) $this->id_user === (int) $user->id_user
            && $this->status_label !== StatusPengajuan::FINAL;
    }

    /** Dokumen pengajuan hanya untuk pemilik data dan staf. */
    public function dokumenBisaDiunduhOleh(User $user): bool
    {
        return $user->isStaf() || ($this->id_user !== null && (int) $this->id_user === (int) $user->id_user);
    }
}
