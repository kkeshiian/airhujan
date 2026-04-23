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
            $table->decimal('sim_night_target_cm', 8, 2)
                ->default(120)
                ->after('sim_ultrasonic_distance_cm');
            $table->decimal('sim_noon_peak_target_cm', 8, 2)
                ->default(95)
                ->after('sim_night_target_cm');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('device_settings', function (Blueprint $table) {
            $table->dropColumn([
                'sim_night_target_cm',
                'sim_noon_peak_target_cm',
            ]);
        });
    }
};
