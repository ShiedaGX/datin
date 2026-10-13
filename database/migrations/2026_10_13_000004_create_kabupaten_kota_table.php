<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('kabupaten_kota', function (Blueprint $t) {
            $t->id();
            $t->foreignId('provinsi_id')->constrained('provinsi')->cascadeOnUpdate()->restrictOnDelete();
            $t->unsignedSmallInteger('no_urut')->unique();   // kolom "No" pada Excel
            $t->string('nama', 100);                         // kolom "Kab/Kota"
            $t->boolean('is_prioritas')->default(false);     // dari Klasifikasi Wilayah
            $t->boolean('is_strategis')->default(false);     // dari Klasifikasi Wilayah
            // Hasil COUNTIFS ke sheet eksternal 'Pasangan Asal-Tujuan' (tidak ada di file) -> disimpan sebagai nilai
            $t->unsignedSmallInteger('jumlah_tujuan_valid')->default(0);
            $t->timestamps();

            $t->unique(['provinsi_id', 'nama']);
            $t->index('nama');
        });

        Schema::create('kabupaten_kota_program_kawasan', function (Blueprint $t) {
            $t->foreignId('kabupaten_kota_id')->constrained('kabupaten_kota')->cascadeOnDelete();
            $t->foreignId('program_kawasan_id')->constrained('program_kawasan')->cascadeOnDelete();
            $t->primary(['kabupaten_kota_id', 'program_kawasan_id']);
        });

        Schema::create('kabupaten_kota_sektor', function (Blueprint $t) {
            $t->foreignId('kabupaten_kota_id')->constrained('kabupaten_kota')->cascadeOnDelete();
            $t->foreignId('sektor_id')->constrained('sektor')->cascadeOnDelete();
            $t->primary(['kabupaten_kota_id', 'sektor_id']);
        });

        // "Potensi Daerah" : "1. Konstruksi; 2. Pertanian Sagu; ..." dipecah per item
        Schema::create('potensi_daerah', function (Blueprint $t) {
            $t->id();
            $t->foreignId('kabupaten_kota_id')->constrained('kabupaten_kota')->cascadeOnDelete();
            $t->unsignedTinyInteger('urutan');
            $t->string('deskripsi', 255);
            $t->timestamps();
            $t->index(['kabupaten_kota_id', 'urutan']);
        });

        // "Kebutuhan Kota/Kab. untuk Dihubungkan" dipecah per tujuan.
        // Sengaja teks bebas (bukan FK): di Excel penamaan tidak seragam
        // (mis. "Kab. Mimika", "Makassar", "Pulau Timor", "Timor Leste").
        Schema::create('tujuan_koneksi', function (Blueprint $t) {
            $t->id();
            $t->foreignId('kabupaten_kota_id')->constrained('kabupaten_kota')->cascadeOnDelete();
            $t->unsignedTinyInteger('urutan');
            $t->string('nama_tujuan', 150);
            $t->timestamps();
            $t->index(['kabupaten_kota_id', 'urutan']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('tujuan_koneksi');
        Schema::dropIfExists('potensi_daerah');
        Schema::dropIfExists('kabupaten_kota_sektor');
        Schema::dropIfExists('kabupaten_kota_program_kawasan');
        Schema::dropIfExists('kabupaten_kota');
    }
};
