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
            $table->unsignedSmallInteger('sleep_minutes')->default(5)->after('deep_sleep_seconds');
            $table->unsignedSmallInteger('awake_minutes')->default(1)->after('sleep_minutes');
            $table->unsignedSmallInteger('rain_tip_threshold')->default(1)->after('awake_minutes');
            $table->unsignedInteger('rain_stop_timeout_ms')->default(300000)->after('rain_tip_threshold');
            $table->unsignedInteger('wifi_warmup_ms')->default(2000)->after('rain_stop_timeout_ms');
            $table->decimal('mm_per_tip', 8, 3)->default(0.300)->after('wifi_warmup_ms');
            $table->decimal('baseline_cm', 8, 2)->default(0)->after('mm_per_tip');
            $table->unsignedTinyInteger('esp_mode')->default(0)->after('baseline_cm');
            $table->boolean('force_rain')->default(false)->after('esp_mode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('device_settings', function (Blueprint $table) {
            $table->dropColumn([
                'sleep_minutes',
                'awake_minutes',
                'rain_tip_threshold',
                'rain_stop_timeout_ms',
                'wifi_warmup_ms',
                'mm_per_tip',
                'baseline_cm',
                'esp_mode',
                'force_rain',
            ]);
        });
    }
};
