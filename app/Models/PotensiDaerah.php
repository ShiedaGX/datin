<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PotensiDaerah extends Model
{
    protected $table = 'potensi_daerah';
    protected $fillable = ['kabupaten_kota_id', 'urutan', 'deskripsi'];


    public function kabupatenKota(): BelongsTo { return $this->belongsTo(KabupatenKota::class, 'kabupaten_kota_id'); }
}
