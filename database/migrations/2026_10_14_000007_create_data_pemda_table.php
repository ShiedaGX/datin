<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('data_pemda', function (Blueprint $t) {
            $t->id('id_pemda');
            $t->foreignId('id_user')->nullable()->unique()->constrained('users', 'id_user')->cascadeOnUpdate()->nullOnDelete(); // relasi 1:1 sesuai ERD
            $t->foreignId('id_kab_kota')->constrained('kab_kota', 'id_kab_kota')->cascadeOnUpdate()->restrictOnDelete();
            $t->text('potensi_pemda')->nullable();
            $t->string('nama_pemda', 150)->nullable();
            $t->string('kontak_pemda', 100)->nullable();
            $t->string('klasifikasi_pemda', 100)->nullable();
            $t->text('keterangan_pemda')->nullable();
        });
    }
    public function down(): void { Schema::dropIfExists('data_pemda'); }
};
