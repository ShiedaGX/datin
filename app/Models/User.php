<?php
namespace App\Models;

use App\Support\PeranUser;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'id_user';

    protected $fillable = ['id_role', 'id_moda', 'id_wilayah', 'username', 'email', 'password_hash', 'full_name', 'instansi', 'diverifikasi_at'];
    protected $hidden = ['password_hash'];
    protected $casts = ['password_hash' => 'hashed', 'diverifikasi_at' => 'datetime'];

    // Kolom password pada ERD bernama password_hash
    public function getAuthPassword(): string { return $this->password_hash; }
    public function getAuthPasswordName(): string { return 'password_hash'; }

    /** Cache kode hak akses selama satu request. */
    private ?array $kodeHakAkses = null;

    public function isSuperAdmin(): bool
    {
        return ($this->role->nama_role ?? null) === PeranUser::SUPER_ADMIN;
    }

    public function isModa(): bool
    {
        return ($this->role->nama_role ?? null) === PeranUser::MODA;
    }

    public function isIntram(): bool
    {
        return ($this->role->nama_role ?? null) === PeranUser::INTRAM;
    }

    /** Super Admin selalu boleh; role lain mengikuti tabel role_hak_akses. */
    public function bisa(string $kode): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }
        $this->kodeHakAkses ??= $this->role ? $this->role->hakAkses()->pluck('kode')->all() : [];

        return in_array($kode, $this->kodeHakAkses, true);
    }

    /** Staf = pengguna dengan role selain 'Pemda' (mis. verifikator moda). */
    public function isStaf(): bool
    {
        return ($this->role->nama_role ?? 'Pemda') !== 'Pemda';
    }

    public function role(): BelongsTo { return $this->belongsTo(Role::class, 'id_role', 'id_role'); }
    public function wilayah(): BelongsTo { return $this->belongsTo(Wilayah::class, 'id_wilayah', 'id_wilayah'); }
    public function moda(): BelongsTo { return $this->belongsTo(Moda::class, 'id_moda', 'id_moda'); }
    public function dataPemda(): HasMany { return $this->hasMany(DataPemda::class, 'id_user', 'id_user'); }
    public function verifikasi(): HasMany { return $this->hasMany(VerifikasiPengajuan::class, 'id_user', 'id_user'); }
    public function pengajuanSurat(): HasMany { return $this->hasMany(PengajuanSurat::class, 'id_user', 'id_user'); }
    public function dokumen(): HasMany { return $this->hasMany(DokumenPengajuan::class, 'id_user', 'id_user'); }
}
