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
        Schema::table('sync_process_run', function (Blueprint $table) {
            $table->string('key', 50)->after('process_id');
        });

        Schema::table('sync_point', function (Blueprint $table) {
            $table->string('key', 50)->after('last_sync_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sync_process_run', function (Blueprint $table) {
            //
        });
    }
};
