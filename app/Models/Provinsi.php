<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Provinsi extends Model
{
    protected $table = 'provinsi';
    protected $primaryKey = 'id_provinsi';
    public $timestamps = false;
    protected $fillable = ['id_wilayah', 'nama_provinsi', 'kode_provinsi'];

    public function wilayah(): BelongsTo { return $this->belongsTo(Wilayah::class, 'id_wilayah', 'id_wilayah'); }
    public function kabKota(): HasMany { return $this->hasMany(KabKota::class, 'id_provinsi', 'id_provinsi'); }
}
