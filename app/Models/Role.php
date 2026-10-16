<?php
namespace App\Models;

use App\Support\PeranUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $table = 'role';
    protected $primaryKey = 'id_role';
    public $timestamps = false;
    protected $fillable = ['nama_role'];

    public function users(): HasMany { return $this->hasMany(User::class, 'id_role', 'id_role'); }

    public function hakAkses(): BelongsToMany
    {
        return $this->belongsToMany(HakAkses::class, 'role_hak_akses', 'id_role', 'id_hak_akses')
            ->orderBy('hak_akses.id_hak_akses');
    }

    public function isSuperAdmin(): bool
    {
        return $this->nama_role === PeranUser::SUPER_ADMIN;
    }
}
