<?php

namespace Database\Seeders;

use App\Models\AudioRecord;
use App\Models\DeviceLocation;
use App\Models\DeviceSetting;
use App\Models\SensorLog;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::updateOrCreate([
            'email' => 'admin@monitoring.local',
        ], [
            'name' => 'Admin Monitoring',
            'username' => 'admin',
            'password' => Hash::make('admin12345'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        User::updateOrCreate([
            'email' => 'user@monitoring.local',
        ], [
            'name' => 'User Monitoring',
            'username' => 'user1',
            'password' => Hash::make('user12345'),
            'role' => 'user',
            'email_verified_at' => now(),
        ]);

        DeviceLocation::updateOrCreate([
            'device_code' => 'alat_1',
        ], [
            'device_name' => 'Alat Sensor Hujan',
            'latitude' => -6.2000000,
            'longitude' => 106.8166660,
            'updated_by' => $admin->id,
        ]);

        DeviceLocation::updateOrCreate([
            'device_code' => 'alat_2',
        ], [
            'device_name' => 'Alat Perekam Audio',
            'latitude' => -6.2100000,
            'longitude' => 106.8266660,
            'updated_by' => $admin->id,
        ]);

        DeviceSetting::updateOrCreate([
            'id' => 1,
        ], [
            'deep_sleep_seconds' => 300,
            'last_published_at' => now(),
            'updated_by' => $admin->id,
        ]);

        if (SensorLog::count() < 30) {
            for ($i = 0; $i < 40; $i++) {
                $isRain = $i % 4 !== 0;

                SensorLog::create([
                    'device_code' => $i % 2 === 0 ? 'alat_1' : 'alat_2',
                    'rainfall_mm' => $isRain ? mt_rand(5, 70) / 10 : 0,
                    'water_level_cm' => mt_rand(20, 220) / 10,
                    'rain_status' => $isRain ? 'Rain' : 'No Rain',
                    'battery_percent' => mt_rand(65, 100),
                    'solar_power_watts' => mt_rand(50, 120),
                    'recorded_at' => now()->subMinutes((40 - $i) * 10),
                ]);
            }
        }

        if (AudioRecord::count() < 5) {
            for ($i = 1; $i <= 8; $i++) {
                $recordedAt = now()->subHours(9 - $i);

                AudioRecord::create([
                    'title' => 'hujan-'.$recordedAt->format('HisdmY'),
                    'device_code' => 'alat_2',
                    'file_path' => 'audio/rekaman-hujan-'.$i.'.mp3',
                    'duration_seconds' => mt_rand(30, 180),
                    'recorded_at' => $recordedAt,
                ]);
            }
        }
    }
}
