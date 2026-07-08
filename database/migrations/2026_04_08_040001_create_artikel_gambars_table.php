<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('artikel_gambar', function (Blueprint $table) {
            $table->id();

            $table->foreignId('artikel_id')
                ->constrained('artikel')
                ->onDelete('cascade');

            $table->string('nama_gambar');
            $table->string('path')->nullable();
            $table->string('alt_text')->nullable();
            $table->unsignedBigInteger('wp_media_id')->nullable();
            $table->string('wp_media_url')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artikel_gambar');
    }
};
