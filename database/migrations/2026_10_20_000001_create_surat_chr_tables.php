<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Surat CHR (Catatan Hasil Review) yang diunggah user Moda dan dikirim ke user INTRAM.
 * ERD awal belum memiliki tabel untuk ini.
 *  - pengajuan_surat : satu pengajuan = satu paket surat (status Draf, Terkirim, Dalam Review, Disetujui, Perlu Perbaikan)
 *  - dokumen_surat   : berkas pada pengajuan, satu berkas per jenis surat
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('pengajuan_surat', function (Blueprint $t) {
            $t->id('id_pengajuan_surat');
            $t->foreignId('id_user')->constrained('users', 'id_user')->cascadeOnUpdate()->restrictOnDelete();
            $t->foreignId('id_moda')->nullable()->constrained('moda', 'id_moda')->cascadeOnUpdate()->nullOnDelete();
            $t->string('tujuan', 30)->default('Intram');          // role penerima
            $t->string('status', 30)->default('Draf')->index();
            $t->text('catatan_pengiriman')->nullable();           // dari Moda
            $t->text('catatan_review')->nullable();              // dari penerima (mis. alasan Perlu Perbaikan)
            $t->timestamp('dikirim_at')->nullable();
            $t->timestamps();
        });

        Schema::create('dokumen_surat', function (Blueprint $t) {
            $t->id('id_dokumen_surat');
            $t->foreignId('id_pengajuan_surat')->constrained('pengajuan_surat', 'id_pengajuan_surat')->cascadeOnUpdate()->cascadeOnDelete();
            $t->string('jenis', 30);                              // kode jenis surat, lihat App\Support\SuratOptions
            $t->string('nama_file', 255);
            $t->string('path_file', 500);
            $t->unsignedInteger('ukuran')->default(0);            // byte
            $t->timestamps();

            $t->unique(['id_pengajuan_surat', 'jenis']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dokumen_surat');
        Schema::dropIfExists('pengajuan_surat');
    }
};
