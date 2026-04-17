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
        Schema::create('audio_records', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('device_code')->default('alat_2');
            $table->string('file_path');
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->index('recorded_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audio_records');
    }
};
