<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Nomor surat diisi MANUAL oleh pengunggah (Pemda, Moda, INTRAM), bukan lagi dibuat otomatis.
 * Kolom nullable agar dokumen yang diunggah sebelumnya tetap tersimpan (tampil "-").
 */
return new class extends Migration {
    private const TABEL = ['dokumen_pengajuan', 'dokumen_surat', 'rekomendasi_teknis'];

    public function up(): void
    {
        foreach (self::TABEL as $tabel) {
            if (Schema::hasTable($tabel) && ! Schema::hasColumn($tabel, 'nomor_surat')) {
                Schema::table($tabel, function (Blueprint $t) {
                    $t->string('nomor_surat', 100)->nullable()->after('nama_file');
                });
            }
        }
    }

    public function down(): void
    {
        foreach (self::TABEL as $tabel) {
            if (Schema::hasTable($tabel) && Schema::hasColumn($tabel, 'nomor_surat')) {
                Schema::table($tabel, function (Blueprint $t) {
                    $t->dropColumn('nomor_surat');
                });
            }
        }
    }
};
