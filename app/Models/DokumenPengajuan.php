<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DokumenPengajuan extends Model
{
    protected $table = 'dokumen_pengajuan';
    protected $primaryKey = 'id_dokumen_pengajuan';
    public $timestamps = true;
    protected $casts = ['tanggal_upload' => 'datetime'];
    protected $fillable = ['id_rute_subsidi', 'id_user', 'jenis_dokumen', 'nama_file', 'path_file', 'versi_dokumen', 'tanggal_upload'];

    public function rute(): BelongsTo { return $this->belongsTo(RuteSubsidi::class, 'id_rute_subsidi', 'id_rute_subsidi'); }
    public function pengunggah(): BelongsTo { return $this->belongsTo(User::class, 'id_user', 'id_user'); }
}
