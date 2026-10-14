<?php
namespace App\Models;

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
}
