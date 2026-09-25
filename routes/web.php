<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'app' => 'SIGAP API',
        'status' => 'ok',
    ]);
});