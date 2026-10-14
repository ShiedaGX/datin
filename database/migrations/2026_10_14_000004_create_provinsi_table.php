<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('provinsi', function (Blueprint $t) {
            $t->id('id_provinsi');
            $t->foreignId('id_wilayah')->constrained('wilayah', 'id_wilayah')->cascadeOnUpdate()->restrictOnDelete();
            $t->string('nama_provinsi', 80)->unique();
            $t->string('kode_provinsi', 10)->nullable();
        });
    }
    public function down(): void { Schema::dropIfExists('provinsi'); }
};
