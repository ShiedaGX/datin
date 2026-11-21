<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FasilitasTransportasi extends Model
{
    protected $table = 'fasilitas_transportasi';

    protected $primaryKey = 'id_fasilitas';

    protected $fillable = [
        'id_moda',
        'id_kab_kota',
        'nama',
        'latitude',
        'longitude',
        'sumber_data',
        'referensi_sumber',
        'aktif',
        'tipe_terminal',
        'status_verifikasi',
        'status_pencocokan',
        'wilayah_osm',
        'catatan_validasi',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'aktif' => 'boolean',
    ];

    public function moda(): BelongsTo
    {
        return $this->belongsTo(
            Moda::class,
            'id_moda',
            'id_moda'
        );
    }

    public function kabKota(): BelongsTo
    {
        return $this->belongsTo(
            KabKota::class,
            'id_kab_kota',
            'id_kab_kota'
        );
    }
}
