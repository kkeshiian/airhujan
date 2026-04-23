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
        Schema::table('device_settings', function (Blueprint $table) {
            $table->decimal('sim_water_depth_cm', 8, 2)->default(0)->after('sim_interval_minutes');
            $table->decimal('sim_ultrasonic_distance_cm', 8, 2)->default(0)->after('sim_water_depth_cm');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('device_settings', function (Blueprint $table) {
            $table->dropColumn([
                'sim_water_depth_cm',
                'sim_ultrasonic_distance_cm',
            ]);
        });
    }
};
