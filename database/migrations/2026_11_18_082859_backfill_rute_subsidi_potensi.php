<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('rute_subsidi_potensi')) {
            return;
        }

        Schema::create('rute_subsidi_potensi', function (Blueprint $t) {
            $t->id('id_rute_subsidi_potensi');
            $t->unsignedBigInteger('id_rute_subsidi');
            $t->unsignedBigInteger('id_sektor');
            $t->unsignedBigInteger('id_komoditas')->nullable(); // null = sektor dipilih tanpa komoditas spesifik
            $t->timestamps();

            $t->foreign('id_rute_subsidi')->references('id_rute_subsidi')->on('rute_subsidi')->cascadeOnDelete();
            $t->foreign('id_sektor')->references('id_sektor')->on('master_sektor')->restrictOnDelete();
            $t->foreign('id_komoditas')->references('id_komoditas')->on('master_komoditas')->restrictOnDelete();
            $t->index(['id_rute_subsidi', 'id_sektor']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rute_subsidi_potensi');
    }
};