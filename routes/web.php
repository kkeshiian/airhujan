<?php

use App\Http\Controllers\AudioController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DataExportController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/live', [DashboardController::class, 'live'])->name('dashboard.live');
    Route::get('/dashboard/chart-data', [DashboardController::class, 'chartData'])->name('dashboard.chart-data');
    Route::post('/dashboard/command', [DashboardController::class, 'sendCommand'])->name('dashboard.command');
    Route::post('/dashboard/config', [DashboardController::class, 'updateDeviceConfig'])->name('dashboard.config');
    Route::post('/locations/{deviceCode}', [DashboardController::class, 'updateLocation'])
        ->middleware('admin')
        ->name('locations.update');

    Route::get('/logs', [LogController::class, 'index'])->name('logs.index');
    Route::get('/logs/live', [LogController::class, 'live'])->name('logs.live');
    Route::delete('/logs/{sensorLog}', [LogController::class, 'destroy'])->middleware('admin')->name('logs.destroy');

    Route::get('/audio', [AudioController::class, 'index'])->name('audio.index');
    Route::get('/audio/{audioRecord}/download', [AudioController::class, 'download'])->name('audio.download');
    Route::delete('/audio/{audioRecord}', [AudioController::class, 'destroy'])->middleware('admin')->name('audio.destroy');

    Route::get('/exports/sensor-csv', [DataExportController::class, 'sensorCsv'])->name('exports.sensor.csv');

    Route::middleware('admin')->group(function (): void {
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
        Route::put('/settings/locations', [SettingsController::class, 'updateLocations'])->name('settings.locations.update');
        Route::post('/settings/purge', [SettingsController::class, 'purgeData'])->name('settings.purge');

        Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserManagementController::class, 'create'])->name('users.create');
        Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserManagementController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');
    });
});
