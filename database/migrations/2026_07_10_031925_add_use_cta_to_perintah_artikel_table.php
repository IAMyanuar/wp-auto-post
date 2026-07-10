<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('perintah_artikel', function (Blueprint $table) {
            $table->boolean('use_cta')->default(false)->after('jumlah_artikel');
        });
    }

    public function down(): void
    {
        Schema::table('perintah_artikel', function (Blueprint $table) {
            $table->dropColumn('use_cta');
        });
    }
};

