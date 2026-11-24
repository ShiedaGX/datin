<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('master_komoditas', function (Blueprint $table) {
            $table->string('satuan_default', 100)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('master_komoditas', function (Blueprint $table) {
            $table->dropColumn('satuan_default');
        });
    }
};
