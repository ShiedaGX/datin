<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('provinsi', function (Blueprint $t) {
            $t->id();
            $t->foreignId('wilayah_id')->constrained('wilayah')->cascadeOnUpdate()->restrictOnDelete();
            $t->string('nama', 80)->unique();
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('provinsi'); }
};
