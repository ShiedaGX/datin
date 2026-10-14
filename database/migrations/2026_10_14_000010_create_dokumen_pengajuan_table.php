<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('dokumen_pengajuan', function (Blueprint $t) {
            $t->id('id_dokumen_pengajuan');
            $t->foreignId('id_rute_subsidi')->constrained('rute_subsidi', 'id_rute_subsidi')->cascadeOnUpdate()->cascadeOnDelete();
            $t->foreignId('id_user')->constrained('users', 'id_user')->cascadeOnUpdate()->restrictOnDelete();
            $t->string('jenis_dokumen', 100);
            $t->string('nama_file', 255);
            $t->string('path_file', 500);
            $t->unsignedSmallInteger('versi_dokumen');
            $t->dateTime('tanggal_upload');
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('dokumen_pengajuan'); }
};
