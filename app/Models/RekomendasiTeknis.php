<?php
namespace App\Models;

use App\Support\SuratOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RekomendasiTeknis extends Model
{
    protected $table = 'rekomendasi_teknis';
    protected $primaryKey = 'id_rekomendasi_teknis';
    protected $fillable = ['id_pengajuan_surat', 'id_user', 'nama_file', 'nomor_surat', 'path_file', 'ukuran', 'catatan'];

    public function pengajuan(): BelongsTo { return $this->belongsTo(PengajuanSurat::class, 'id_pengajuan_surat', 'id_pengajuan_surat'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class, 'id_user', 'id_user'); }

    public function getUkuranTeksAttribute(): string
    {
        return SuratOptions::formatUkuran((int) $this->ukuran);
    }

    public function getEkstensiAttribute(): string
    {
        return strtoupper(pathinfo($this->nama_file, PATHINFO_EXTENSION));
    }
}
