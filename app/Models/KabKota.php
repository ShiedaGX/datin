<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KabKota extends Model
{
    protected $table = 'kab_kota';

    protected $primaryKey = 'id_kab_kota';

    public $timestamps = false;

    protected $fillable = [
        'id_wilayah',
        'id_provinsi',
        'nama_kab_kota',
        'jenis',
        'kode_kab_kota',
    ];


    public function wilayah(): BelongsTo
    {
        return $this->belongsTo(
            Wilayah::class,
            'id_wilayah',
            'id_wilayah'
        );
    }


    public function provinsi(): BelongsTo
    {
        return $this->belongsTo(
            Provinsi::class,
            'id_provinsi',
            'id_provinsi'
        );
    }
}