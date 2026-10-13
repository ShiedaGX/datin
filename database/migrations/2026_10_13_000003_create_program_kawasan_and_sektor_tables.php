<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Master: 3TP, KSPEAN, KEK/KI, Pariwisata, Komoditas Unggulan/Ekonomi Biru, Transmigrasi/Perdesaan
        Schema::create('program_kawasan', function (Blueprint $t) {
            $t->id();
            $t->string('kode', 40)->unique();
            $t->string('nama', 100);
            $t->timestamps();
        });

        // Master: 10 sektor potensi (Perikanan & Kelautan, Pertanian & Pangan, dst.)
        Schema::create('sektor', function (Blueprint $t) {
            $t->id();
            $t->string('kode', 40)->unique();
            $t->string('nama', 100);
            $t->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('sektor');
        Schema::dropIfExists('program_kawasan');
    }
};
