<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cek_duplikasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artikel_id')
                ->constrained('artikel')
                ->onDelete('cascade');
            $table->unsignedTinyInteger('skor_keunikan')->nullable();
            $table->json('kata_duplikat')->nullable();
            $table->json('hasil')->nullable();
            $table->unsignedTinyInteger('percobaan_ke')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cek_duplikasi');
    }
};
