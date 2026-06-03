<?php

use App\Http\Controllers\Api\LogStatsController;
use Illuminate\Support\Facades\Route;

Route::post('/log-stats', LogStatsController::class);
