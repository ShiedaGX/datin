<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rute_subsidi', function (Blueprint $t) {
            $t->id('id_rute_subsidi');
            $t->foreignId('id_pemda')->constrained('data_pemda', 'id_pemda')->cascadeOnUpdate()->restrictOnDelete();
            $t->foreignId('id_moda')->constrained('moda', 'id_moda')->cascadeOnUpdate()->restrictOnDelete();
            $t->foreignId('id_kab_kota_asal')->constrained('kab_kota', 'id_kab_kota')->cascadeOnUpdate()->restrictOnDelete();
            $t->foreignId('id_kab_kota_tujuan')->constrained('kab_kota', 'id_kab_kota')->cascadeOnUpdate()->restrictOnDelete();
            $t->string('status_pengajuan', 30);
            $t->date('tanggal_pengajuan');
            $t->timestamps();
            $t->index('status_pengajuan');
        });
    }
    public function down(): void { Schema::dropIfExists('rute_subsidi'); }
};
