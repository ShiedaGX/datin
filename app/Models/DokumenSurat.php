<?php
namespace App\Models;

use App\Support\SuratOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DokumenSurat extends Model
{
    protected $table = 'dokumen_surat';
    protected $primaryKey = 'id_dokumen_surat';
    protected $fillable = ['id_pengajuan_surat', 'jenis', 'nama_file', 'path_file', 'ukuran'];

    public function pengajuan(): BelongsTo { return $this->belongsTo(PengajuanSurat::class, 'id_pengajuan_surat', 'id_pengajuan_surat'); }

    public function getNamaJenisAttribute(): string
    {
        return SuratOptions::JENIS[$this->jenis]['nama'] ?? $this->jenis;
    }

    public function getUkuranTeksAttribute(): string
    {
        return SuratOptions::formatUkuran((int) $this->ukuran);
    }

    public function getEkstensiAttribute(): string
    {
        return strtoupper(pathinfo($this->nama_file, PATHINFO_EXTENSION));
    }
}
