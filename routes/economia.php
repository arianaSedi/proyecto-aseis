<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Economia\MatematicaFinancieraController;

Route::prefix('economia')->name('economia.')->group(function () {

    Route::get('/matematica', [MatematicaFinancieraController::class, 'index'])
        ->name('matematica');

    Route::post('/matematica/calcular', [MatematicaFinancieraController::class, 'calcular'])
        ->name('matematica.calcular');


});