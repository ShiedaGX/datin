<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('wilayah', function (Blueprint $t) {
            $t->id('id_wilayah');
            $t->string('nama_wilayah', 50)->unique();
        });
    }
    public function down(): void { Schema::dropIfExists('wilayah'); }
};
