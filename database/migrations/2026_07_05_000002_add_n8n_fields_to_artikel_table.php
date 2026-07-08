<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('artikel', function (Blueprint $table) {
            $table->string('n8n_execution_id')->nullable()->after('wp_url');
            $table->string('n8n_status')->nullable()->after('n8n_execution_id');
        });
    }

    public function down(): void
    {
        Schema::table('artikel', function (Blueprint $table) {
            $table->dropColumn(['n8n_execution_id', 'n8n_status']);
        });
    }
};
