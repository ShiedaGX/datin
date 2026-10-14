<?php
namespace App\Models;

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
}
