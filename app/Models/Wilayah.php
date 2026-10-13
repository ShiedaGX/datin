<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wilayah extends Model
{
    protected $table = 'wilayah';
    protected $fillable = ['nama'];


    public function provinsi(): HasMany { return $this->hasMany(Provinsi::class, 'wilayah_id'); }
}
