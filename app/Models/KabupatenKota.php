<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KabupatenKota extends Model
{
    protected $table = 'kabupaten_kota';
    protected $fillable = ['provinsi_id', 'no_urut', 'nama', 'is_prioritas', 'is_strategis', 'jumlah_tujuan_valid'];

    protected $casts = ['is_prioritas' => 'boolean', 'is_strategis' => 'boolean'];


    public function provinsi(): BelongsTo { return $this->belongsTo(Provinsi::class, 'provinsi_id'); }

    public function programKawasan(): BelongsToMany
    {
        return $this->belongsToMany(ProgramKawasan::class, 'kabupaten_kota_program_kawasan', 'kabupaten_kota_id', 'program_kawasan_id');
    }

    public function sektor(): BelongsToMany
    {
        return $this->belongsToMany(Sektor::class, 'kabupaten_kota_sektor', 'kabupaten_kota_id', 'sektor_id');
    }

    public function potensiDaerah(): HasMany { return $this->hasMany(PotensiDaerah::class, 'kabupaten_kota_id')->orderBy('urutan'); }
    public function tujuanKoneksi(): HasMany { return $this->hasMany(TujuanKoneksi::class, 'kabupaten_kota_id')->orderBy('urutan'); }

    // Pengganti kolom rumus "Jumlah Sektor Potensi" di Excel (=SUM sektor)
    public function getJumlahSektorPotensiAttribute(): int
    {
        return $this->relationLoaded('sektor') ? $this->sektor->count() : $this->sektor()->count();
    }

    // Pengganti kolom "Klasifikasi Wilayah" di Excel
    public function getKlasifikasiWilayahAttribute(): string
    {
        return collect([$this->is_prioritas ? 'Prioritas' : null, $this->is_strategis ? 'Strategis' : null])->filter()->implode(', ');
    }
}
