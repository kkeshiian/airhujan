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
            $table->decimal('water_rise_cm', 8, 2)->nullable()->after('water_level_cm');
            $table->unsignedInteger('daily_tip_count')->nullable()->after('water_rise_cm');
            $table->boolean('is_raining')->nullable()->after('daily_tip_count');
            $table->boolean('force_rain')->nullable()->after('is_raining');
            $table->unsignedTinyInteger('esp_mode')->nullable()->after('force_rain');
            $table->string('esp_mode_name', 30)->nullable()->after('esp_mode');
            $table->unsignedSmallInteger('sleep_minutes')->nullable()->after('esp_mode_name');
            $table->unsignedSmallInteger('awake_minutes')->nullable()->after('sleep_minutes');
            $table->unsignedSmallInteger('rain_tip_threshold')->nullable()->after('awake_minutes');
            $table->unsignedInteger('rain_stop_timeout_ms')->nullable()->after('rain_tip_threshold');
            $table->unsignedInteger('wifi_warmup_ms')->nullable()->after('rain_stop_timeout_ms');
            $table->decimal('mm_per_tip', 8, 3)->nullable()->after('wifi_warmup_ms');
            $table->decimal('baseline_cm', 8, 2)->nullable()->after('mm_per_tip');
            $table->unsignedInteger('day_key')->nullable()->after('baseline_cm');
            $table->boolean('time_synced')->nullable()->after('day_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sensor_logs', function (Blueprint $table) {
            $table->dropColumn([
                'water_rise_cm',
                'daily_tip_count',
                'is_raining',
                'force_rain',
                'esp_mode',
                'esp_mode_name',
                'sleep_minutes',
                'awake_minutes',
                'rain_tip_threshold',
                'rain_stop_timeout_ms',
                'wifi_warmup_ms',
                'mm_per_tip',
                'baseline_cm',
                'day_key',
                'time_synced',
            ]);
        });
    }
};
