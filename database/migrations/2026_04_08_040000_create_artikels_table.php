<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('artikel', function (Blueprint $table) {
            $table->id();

            // Relasi
            $table->foreignId('website_klien_id')
                ->constrained('website_klien')
                ->onDelete('cascade');
            $table->foreignId('ai_agent_prompt_id')
                ->nullable()
                ->constrained('ai_agent_prompt')
                ->onDelete('set null');

            // Konten Artikel
            $table->string('judul');
            $table->string('slug')->nullable();
            $table->longText('konten')->nullable();

            // SEO
            $table->string('seo_title')->nullable();
            $table->text('meta_deskripsi')->nullable();
            $table->text('kata_kunci')->nullable();
            $table->text('deskripsi_yoast')->nullable();
            $table->unsignedTinyInteger('skor_seo')->nullable();
            $table->unsignedTinyInteger('skor_readability')->nullable();

            // Taksonomi (di-generate AI)
            $table->text('tags')->nullable();
            $table->text('kategori')->nullable();

            // Status & Penjadwalan
            $table->enum('status', [
                'diproses',
                'gagal',
                'terjadwal',
                'terpublish',
            ])->default('diproses');

            $table->dateTime('tanggal_jadwal')->nullable();
            $table->dateTime('tanggal_terbit')->nullable();
            $table->boolean('use_cta')->default(false);

            $table->unsignedBigInteger('wp_id')->nullable();
            $table->string('wp_url')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artikel');
    }
};
