<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('kab_kota', function (Blueprint $t) {
            $t->id('id_kab_kota');
            $t->foreignId('id_wilayah')->constrained('wilayah', 'id_wilayah')->cascadeOnUpdate()->restrictOnDelete();
            $t->foreignId('id_provinsi')->constrained('provinsi', 'id_provinsi')->cascadeOnUpdate()->restrictOnDelete();
            $t->string('nama_kab_kota', 100);
            $t->string('jenis', 20)->nullable();          // Kabupaten / Kota
            $t->string('kode_kab_kota', 10)->nullable();
            $t->unique(['id_provinsi', 'nama_kab_kota']);
        });
    }
    public function down(): void { Schema::dropIfExists('kab_kota'); }
};
