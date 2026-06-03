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
        Schema::table('artikel', function (Blueprint $table) {
            // Check if column exists before changing or adding
            if (Schema::hasColumn('artikel', 'keterangan_proses')) {
                $table->string('keterangan_proses', 100)->nullable()->change();
            } else {
                $table->string('keterangan_proses', 100)->nullable()->after('status');
            }

            if (!Schema::hasColumn('artikel', 'persentase_proses')) {
                $table->integer('persentase_proses')->nullable()->after('keterangan_proses');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('artikel', function (Blueprint $table) {
            if (Schema::hasColumn('artikel', 'keterangan_proses')) {
                $table->string('keterangan_proses', 100)->nullable(false)->change();
            }
            if (Schema::hasColumn('artikel', 'persentase_proses')) {
                $table->dropColumn('persentase_proses');
            }
        });
    }
};
