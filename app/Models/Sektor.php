<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sektor extends Model
{
    protected $table = 'sektor';
    protected $fillable = ['kode', 'nama'];


    public function kabupatenKota(): BelongsToMany
    {
        return $this->belongsToMany(KabupatenKota::class, 'kabupaten_kota_sektor', 'sektor_id', 'kabupaten_kota_id');
    }
}
