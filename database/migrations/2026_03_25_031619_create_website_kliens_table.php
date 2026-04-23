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
        Schema::create('website_klien', function (Blueprint $table) {
            $table->id();
            $table->string('nama_website');
            $table->string('url_website');
            $table->string('username');
            $table->string('password');
            $table->boolean('publikasi_otomatis')->default(true)->comment('true=langsung eksekusi, false=butuh konfirmasi');
            $table->string('no_telpon')->nullable();
            $table->text('alamat')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('website_klien');
    }
};
