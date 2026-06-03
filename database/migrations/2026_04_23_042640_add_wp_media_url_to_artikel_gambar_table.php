<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('artikel_gambar', function (Blueprint $table) {
            // URL publik gambar di WordPress (diisi saat upload ke WP, sebelum dikirim ke n8n)
            $table->string('wp_media_url')->nullable()->after('wp_media_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('artikel_gambar', function (Blueprint $table) {
            $table->dropColumn('wp_media_url');
        });
    }
};
