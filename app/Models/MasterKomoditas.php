<?php

namespace App\Models;

use App\Support\Sektor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MasterKomoditas extends Model
{
    protected $table = 'master_komoditas';
    protected $primaryKey = 'id_komoditas';
    protected $fillable = ['id_sektor', 'nama_komoditas'];

    protected static function booted(): void
    {
        static::saved(fn () => Sektor::lupakan());
        static::deleted(fn () => Sektor::lupakan());
    }

    public function sektor(): BelongsTo
    {
        return $this->belongsTo(MasterSektor::class, 'id_sektor', 'id_sektor');
    }
}