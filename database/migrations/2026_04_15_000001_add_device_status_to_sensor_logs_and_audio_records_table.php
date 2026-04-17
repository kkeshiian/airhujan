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
        Schema::table('sensor_logs', function (Blueprint $table) {
            $table->string('device_status', 120)->nullable()->after('solar_power_watts');
        });

        Schema::table('audio_records', function (Blueprint $table) {
            $table->string('device_status', 120)->nullable()->after('duration_seconds');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sensor_logs', function (Blueprint $table) {
            $table->dropColumn('device_status');
        });

        Schema::table('audio_records', function (Blueprint $table) {
            $table->dropColumn('device_status');
        });
    }
};
