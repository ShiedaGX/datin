<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Wilayah extends Model
{
    protected $table = 'wilayah';
    protected $primaryKey = 'id_wilayah';
    public $timestamps = false;
    protected $fillable = ['nama_wilayah'];

    public function provinsi(): HasMany { return $this->hasMany(Provinsi::class, 'id_wilayah', 'id_wilayah'); }
    public function kabKota(): HasMany { return $this->hasMany(KabKota::class, 'id_wilayah', 'id_wilayah'); }
}
