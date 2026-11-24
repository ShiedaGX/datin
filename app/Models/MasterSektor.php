<?php

namespace App\Models;

use App\Support\Sektor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterSektor extends Model
{
    protected $table = 'master_sektor';
    protected $primaryKey = 'id_sektor';
    protected $fillable = ['nama_sektor', 'urutan'];

    protected static function booted(): void
    {
        static::saved(fn () => Sektor::lupakan());
        static::deleted(fn () => Sektor::lupakan());
    }

    public function komoditas(): HasMany
    {
        return $this->hasMany(MasterKomoditas::class, 'id_sektor', 'id_sektor');
    }
}