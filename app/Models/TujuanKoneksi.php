<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TujuanKoneksi extends Model
{
    protected $table = 'tujuan_koneksi';
    protected $fillable = ['kabupaten_kota_id', 'urutan', 'nama_tujuan'];


    public function kabupatenKota(): BelongsTo { return $this->belongsTo(KabupatenKota::class, 'kabupaten_kota_id'); }
}
