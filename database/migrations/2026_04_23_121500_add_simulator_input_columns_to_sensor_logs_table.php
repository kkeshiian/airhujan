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
            $table->integer('sim_tip_delta')->nullable()->after('daily_tip_count');
            $table->decimal('sim_depth_cm', 8, 2)->nullable()->after('sim_tip_delta');
            $table->decimal('sim_sensor_height_cm', 8, 2)->nullable()->after('sim_depth_cm');
            $table->string('sim_rain_state', 20)->nullable()->after('sim_sensor_height_cm');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sensor_logs', function (Blueprint $table) {
            $table->dropColumn([
                'sim_tip_delta',
                'sim_depth_cm',
                'sim_sensor_height_cm',
                'sim_rain_state',
            ]);
        });
    }
};
