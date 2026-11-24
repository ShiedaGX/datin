<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RuteSubsidiTujuanKomoditas extends Model
{
    protected $table = 'rute_subsidi_tujuan_komoditas';

    protected $primaryKey = 'id_komoditas_tujuan';

    protected $fillable = [
        'id_rute_subsidi',
        'id_kab_kota_tujuan',
        'nama_komoditas',
        'jumlah',
        'satuan_data',

        // Dipertahankan untuk kompatibilitas data lama.
        'berat',
    ];

    protected $casts = [
        'jumlah' => 'decimal:2',
        'berat' => 'decimal:2',
    ];

    public function rute(): BelongsTo
    {
        return $this->belongsTo(
            RuteSubsidi::class,
            'id_rute_subsidi',
            'id_rute_subsidi'
        );
    }

    public function kabKotaTujuan(): BelongsTo
    {
        return $this->belongsTo(
            KabKota::class,
            'id_kab_kota_tujuan',
            'id_kab_kota'
        );
    }
}
