<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MasterSatuanData extends Model
{
    protected $table = 'master_satuan_data';

    protected $primaryKey = 'id_satuan_data';

    protected $fillable = [
        'nama_satuan',
        'status_aktif',
    ];

    protected $casts = [
        'status_aktif' => 'boolean',
    ];

    public function scopeAktif($query)
    {
        return $query->where('status_aktif', true);
    }

    public function getSudahDigunakanAttribute(): bool
    {
        return DB::table('rute_subsidi_tujuan_komoditas')
            ->where('satuan_data', $this->nama_satuan)
            ->exists();
    }
}
