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
        Schema::create('sensor_logs', function (Blueprint $table) {
            $table->id();
            $table->string('device_code');
            $table->decimal('rainfall_mm', 8, 2)->nullable();
            $table->decimal('water_level_cm', 8, 2)->nullable();
            $table->enum('rain_status', ['Rain', 'No Rain'])->default('No Rain');
            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->index(['device_code', 'recorded_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sensor_logs');
    }
};
