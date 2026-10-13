<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Provinsi extends Model
{
    protected $table = 'provinsi';
    protected $fillable = ['wilayah_id', 'nama'];


    public function wilayah(): BelongsTo { return $this->belongsTo(Wilayah::class, 'wilayah_id'); }
    public function kabupatenKota(): HasMany { return $this->hasMany(KabupatenKota::class, 'provinsi_id'); }
}
