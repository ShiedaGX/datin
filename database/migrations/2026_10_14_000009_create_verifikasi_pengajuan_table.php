<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('verifikasi_pengajuan', function (Blueprint $t) {
            $t->id('id_verifikasi_pengajuan');
            $t->foreignId('id_rute_subsidi')->constrained('rute_subsidi', 'id_rute_subsidi')->cascadeOnUpdate()->cascadeOnDelete();
            $t->foreignId('id_user')->constrained('users', 'id_user')->cascadeOnUpdate()->restrictOnDelete();
            $t->string('tahap_verifikasi', 50);
            $t->string('status_verifikasi', 30);
            $t->text('catatan_verifikasi')->nullable();
            $t->dateTime('tanggal_verifikasi')->nullable();
            $t->timestamp('created_at')->nullable();   // ERD hanya punya created_at
        });
    }
    public function down(): void { Schema::dropIfExists('verifikasi_pengajuan'); }
};
