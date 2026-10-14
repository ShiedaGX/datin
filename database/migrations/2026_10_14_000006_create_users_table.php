<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $t) {
            $t->id('id_user');
            $t->foreignId('id_role')->constrained('role', 'id_role')->cascadeOnUpdate()->restrictOnDelete();
            $t->foreignId('id_moda')->nullable()->constrained('moda', 'id_moda')->cascadeOnUpdate()->nullOnDelete();
            $t->string('username', 50)->unique();
            $t->string('email', 150)->unique();
            $t->string('password_hash');
            $t->string('full_name', 150);
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('users'); }
};
