<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('fasilitas_transportasi', function (Blueprint $table) {
            $table->id('id_fasilitas');
            $table->foreignId('id_moda')->constrained('moda', 'id_moda')->restrictOnDelete();
            $table->foreignId('id_kab_kota')->nullable()->constrained('kab_kota', 'id_kab_kota')->nullOnDelete();
            $table->string('nama', 150);
            $table->decimal('latitude', 12, 9);
            $table->decimal('longitude', 13, 9);
            $table->string('sumber_data', 100)->default('Input Admin');
            $table->string('referensi_sumber', 150)->nullable()->unique();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
            $table->index(['id_moda', 'id_kab_kota']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fasilitas_transportasi');
    }
};
