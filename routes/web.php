<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('inicio');
})->name('inicio');

require __DIR__.'/economia.php';
require __DIR__.'/maquetas.php';