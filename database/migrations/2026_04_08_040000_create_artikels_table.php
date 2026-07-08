<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('artikel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perintah_artikel_id')
                ->constrained('perintah_artikel')
                ->onDelete('cascade');
            $table->foreignId('website_klien_id')
                ->constrained('website_klien')
                ->onDelete('cascade');
            $table->string('judul');
            $table->string('slug')->nullable();
            $table->longText('konten')->nullable();
            $table->text('meta_deskripsi')->nullable();
            $table->text('kata_kunci')->nullable();
            $table->text('tags')->nullable();
            $table->text('kategori')->nullable();
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
