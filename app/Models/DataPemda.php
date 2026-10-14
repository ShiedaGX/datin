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
}
