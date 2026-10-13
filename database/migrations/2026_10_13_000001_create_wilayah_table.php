<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('wilayah', function (Blueprint $t) {
            $t->id();
            $t->string('nama', 50)->unique();   // Papua, Sulawesi, Maluku, dst.
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('wilayah'); }
};
