<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProgramKawasan extends Model
{
    protected $table = 'program_kawasan';
    protected $fillable = ['kode', 'nama'];


    public function kabupatenKota(): BelongsToMany
    {
        return $this->belongsToMany(KabupatenKota::class, 'kabupaten_kota_program_kawasan', 'program_kawasan_id', 'kabupaten_kota_id');
    }
}
