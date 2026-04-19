<?php

use App\Http\Controllers\AudioController;
use Illuminate\Support\Facades\Route;

Route::post('/audio/upload-raw', [AudioController::class, 'uploadRaw'])
    ->name('api.audio.upload-raw');
