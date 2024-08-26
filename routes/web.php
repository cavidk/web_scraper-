<?php

use App\Http\Controllers\Api\ProductParserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/parse', [ProductParserController::class, 'showParseForm']);
Route::post('/parse', [ProductParserController::class, 'handleParseRequest']);
