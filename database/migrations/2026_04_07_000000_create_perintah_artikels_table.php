<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('perintah_artikel', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('user')
                ->onDelete('cascade');

            $table->foreignId('website_klien_id')
                ->constrained('website_klien')
                ->onDelete('cascade');

            $table->string('topik');
            $table->unsignedInteger('jumlah_artikel');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perintah_artikel');
    }
};
