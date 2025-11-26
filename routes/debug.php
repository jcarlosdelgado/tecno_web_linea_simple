<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/debug-user', function (Request $request) {
    return $request->user();
});
