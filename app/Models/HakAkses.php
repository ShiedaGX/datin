<?php
namespace App\Models;

<<<<<<< HEAD
=======
/* 
 * HakAkses model represents the "hak_akses" table in the database.
 * It defines the relationship with the Role model through a many-to-many relationship.
 */

>>>>>>> 0910d5c911ca44bd84ff0e009248ac8404333f54
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
