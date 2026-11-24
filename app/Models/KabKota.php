<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class KabKota extends Model
{
    protected $table = 'kab_kota';
    protected $primaryKey = 'id_kab_kota';
    public $timestamps = false;
    protected $fillable = ['id_wilayah', 'id_provinsi', 'nama_kab_kota', 'jenis', 'kode_kab_kota'];

    public function wilayah(): BelongsTo { return $this->belongsTo(Wilayah::class, 'id_wilayah', 'id_wilayah'); }
    public function provinsi(): BelongsTo { return $this->belongsTo(Provinsi::class, 'id_provinsi', 'id_provinsi'); }
    public function dataPemda(): HasMany { return $this->hasMany(DataPemda::class, 'id_kab_kota', 'id_kab_kota'); }
    public function ruteAsal(): HasMany { return $this->hasMany(RuteSubsidi::class, 'id_kab_kota_asal', 'id_kab_kota'); }
    public function ruteTujuan(): HasMany { return $this->hasMany(RuteSubsidi::class, 'id_kab_kota_tujuan', 'id_kab_kota'); }

    /**
     * Alasan kab/kota tidak bisa dipilih pada form Input Data, atau null bila tersedia.
     * Tidak tersedia hanya bila datanya dikelola akun lain. Daerah milik akun ini boleh
     * diajukan berkali-kali (satu pengajuan = satu laporan/rute_subsidi baru).
     */
    public function alasanTidakTersedia(User $user): ?string
    {
        $this->loadMissing('dataPemda.ruteSubsidi', 'dataPemda.permintaanRevisi');

        foreach ($this->dataPemda as $pemda) {
            if ($pemda->ruteSubsidi->isEmpty()) {
                continue;
            }

            // Daerah yang sudah diajukan akun lain tidak dapat dipilih
            if ($pemda->id_user === null || (int) $pemda->id_user !== (int) $user->id_user) {
                return 'dikelola akun lain';
            }
<<<<<<< HEAD

=======
>>>>>>> 0910d5c911ca44bd84ff0e009248ac8404333f54
        }
        foreach ($this->dataPemda as $pemda) {
            if ($pemda->id_user !== null && (int) $pemda->id_user !== (int) $user->id_user) {
                return 'dikelola akun lain';
            }
        }

        return null;
    }

    /** id moda yang sudah diajukan akun ini pada daerah ini. */
    public function modaDiajukan(User $user): array
    {
        $this->loadMissing('dataPemda.ruteSubsidi');

        return $this->dataPemda
            ->filter(fn ($p) => $p->id_user !== null && (int) $p->id_user === (int) $user->id_user)
            ->flatMap(fn ($p) => $p->ruteSubsidi->pluck('id_moda'))
            ->map(fn ($id) => (int) $id)->unique()->values()->all();
    }

    /** id moda yang diminta INTRAM untuk diajukan lagi oleh akun ini pada daerah ini. */
    public function modaRevisiTerbuka(User $user): array
    {
        $this->loadMissing('dataPemda.permintaanRevisi');

        return $this->dataPemda
            ->filter(fn ($p) => $p->id_user !== null && (int) $p->id_user === (int) $user->id_user)
            ->flatMap(fn ($p) => $p->permintaan_terbuka->pluck('id_moda'))
            ->map(fn ($id) => (int) $id)->unique()->values()->all();
    }

    /** "Maluku Tenggara" -> "Kabupaten Maluku Tenggara"; nama berawalan Kota/Kabupaten dibiarkan. */
    public function getNamaLengkapAttribute(): string
    {
        $nama = (string) $this->nama_kab_kota;
        if (preg_match('/^(kota|kab\.?|kabupaten)\s/i', $nama)) {
            return $nama;
        }

        return ($this->jenis ?: 'Kabupaten') . ' ' . $nama;
    }
}
