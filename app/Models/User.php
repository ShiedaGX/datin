<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'id_user';

    protected $fillable = ['id_role', 'id_moda', 'username', 'email', 'password_hash', 'full_name'];
    protected $hidden = ['password_hash'];
    protected $casts = ['password_hash' => 'hashed'];

    // Kolom password di ERD bernama password_hash, bukan password
    public function getAuthPassword(): string { return $this->password_hash; }
    public function getAuthPasswordName(): string { return 'password_hash'; }

    public function role(): BelongsTo { return $this->belongsTo(Role::class, 'id_role', 'id_role'); }
    public function moda(): BelongsTo { return $this->belongsTo(Moda::class, 'id_moda', 'id_moda'); }
    public function dataPemda(): HasOne { return $this->hasOne(DataPemda::class, 'id_user', 'id_user'); }
    public function verifikasi(): HasMany { return $this->hasMany(VerifikasiPengajuan::class, 'id_user', 'id_user'); }
    public function dokumen(): HasMany { return $this->hasMany(DokumenPengajuan::class, 'id_user', 'id_user'); }
}
