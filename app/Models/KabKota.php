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
     * Tidak tersedia bila sudah punya pengajuan atau datanya dikelola akun lain.
     */
    public function alasanTidakTersedia(User $user): ?string
    {
        $this->loadMissing('dataPemda.ruteSubsidi');

        foreach ($this->dataPemda as $pemda) {
            if ($pemda->ruteSubsidi->isNotEmpty()) {
                return 'sudah diajukan';
            }
        }
        foreach ($this->dataPemda as $pemda) {
            if ($pemda->id_user !== null && (int) $pemda->id_user !== (int) $user->id_user) {
                return 'dikelola akun lain';
            }
        }

        return null;
    }
}
