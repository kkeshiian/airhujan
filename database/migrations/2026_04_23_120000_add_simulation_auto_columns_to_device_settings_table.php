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
            $table->boolean('sim_auto_enabled')->default(false)->after('force_rain');
            $table->unsignedSmallInteger('sim_interval_minutes')->default(15)->after('sim_auto_enabled');
            $table->decimal('sim_night_rise_cm', 6, 3)->default(0.250)->after('sim_interval_minutes');
            $table->decimal('sim_day_drop_cm', 6, 3)->default(0.200)->after('sim_night_rise_cm');
            $table->decimal('sim_rain_boost_cm', 6, 3)->default(0.100)->after('sim_day_drop_cm');
            $table->timestamp('sim_last_generated_at')->nullable()->after('sim_rain_boost_cm');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('device_settings', function (Blueprint $table) {
            $table->dropColumn([
                'sim_auto_enabled',
                'sim_interval_minutes',
                'sim_night_rise_cm',
                'sim_day_drop_cm',
                'sim_rain_boost_cm',
                'sim_last_generated_at',
            ]);
        });
    }
};
