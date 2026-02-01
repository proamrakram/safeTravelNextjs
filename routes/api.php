<?php

use App\Http\Controllers\ContactMessageController;
use App\Http\Controllers\RegistrationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/registrations', [RegistrationController::class, 'store'])->middleware('throttle:20,1');
Route::post('/contact-messages', [ContactMessageController::class, 'contactMessage'])->middleware('throttle:20,1');
