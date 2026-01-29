<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::prefix('auth/')->middleware(['guest', 'web'])->group(function () {
    Route::livewire('login', 'pages::admin.auth.⚡login')->name('login');
});

Route::prefix('admin/')->as('admin.')
    ->middleware(['auth', 'web'])
    ->group(function () {

        Route::prefix('panel/')->as('panel.')->group(function () {

            Route::livewire('/', 'pages::admin.panel.⚡index')->name('index');
            Route::livewire('/users', 'pages::admin.panel.⚡users')->name('users');
            Route::livewire('/registrations', 'pages::admin.panel.⚡registrations')->name('registrations');
            Route::livewire('/contact-messages', 'pages::admin.panel.⚡contact-messages')->name('contact-messages');

            Route::get('logout', function () {
                Auth::guard('web')->logout();
                return redirect()->route('login');
            })->name('logout');


        });
    });
