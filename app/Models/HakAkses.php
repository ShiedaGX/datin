<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class HakAkses extends Model
{
    protected $table = 'hak_akses';
    protected $primaryKey = 'id_hak_akses';
    public $timestamps = false;
    protected $fillable = ['kode', 'nama'];

    public function role(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_hak_akses', 'id_hak_akses', 'id_role');
    }
}
