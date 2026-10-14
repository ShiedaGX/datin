<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class KabKota extends Model
{
    protected $table = 'kab_kota';
    protected $primaryKey = 'id_kab_kota';
    public $timestamps = false;
    protected $fillable = ['id_wilayah', 'id_provinsi', 'nama_kab_kota', 'jenis', 'kode_kab_kota'];

    public function wilayah(): BelongsTo { return $this->belongsTo(Wilayah::class, 'id_wilayah', 'id_wilayah'); }
    public function provinsi(): BelongsTo { return $this->belongsTo(Provinsi::class, 'id_provinsi', 'id_provinsi'); }
    public function dataPemda(): HasMany { return $this->hasMany(DataPemda::class, 'id_kab_kota', 'id_kab_kota'); }
    public function ruteAsal(): HasMany { return $this->hasMany(RuteSubsidi::class, 'id_kab_kota_asal', 'id_kab_kota'); }
    public function ruteTujuan(): HasMany { return $this->hasMany(RuteSubsidi::class, 'id_kab_kota_tujuan', 'id_kab_kota'); }
}
