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
        Schema::create('sync_point', function (Blueprint $table) {
            $table->id();
            $table->string('table_a')->index();
            $table->string('table_b')->index();
            $table->integer('last_sync');
            $table->dateTime('last_sync_date')->nullable();
            $table->double('seconds_elapsed')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
