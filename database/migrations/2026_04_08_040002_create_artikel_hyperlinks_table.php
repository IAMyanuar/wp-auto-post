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
        Schema::create('artikel_hyperlink', function (Blueprint $table) {
            $table->id();

            $table->foreignId('artikel_id')
                ->constrained('artikel')
                ->onDelete('cascade');

            $table->string('url');
            $table->enum('tipe', [
                'internal',
                'external',
            ])->default('external');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('artikel_hyperlink');
    }
};
