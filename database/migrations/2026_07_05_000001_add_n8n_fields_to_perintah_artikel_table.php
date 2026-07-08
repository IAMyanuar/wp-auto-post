<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('perintah_artikel', function (Blueprint $table) {
            $table->string('n8n_execution_id')->nullable()->after('jumlah_artikel');

            $table->enum('status', ['pending', 'selesai', 'timeout', 'error'])
                ->default('pending')
                ->after('n8n_execution_id');

            $table->string('n8n_status')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('perintah_artikel', function (Blueprint $table) {
            $table->dropColumn([
                'n8n_execution_id',
                'status',
                'n8n_status',
            ]);
        });
    }
};
