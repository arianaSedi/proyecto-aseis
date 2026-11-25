<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Economia\MatematicaFinancieraController;
use App\Http\Controllers\Economia\AlternativaController;
use App\Http\Controllers\Economia\CotizacionController;

Route::prefix('economia')->name('economia.')->group(function () {

    Route::get('/matematica', [MatematicaFinancieraController::class, 'index'])
        ->name('matematica');

    Route::post('/matematica/calcular', [MatematicaFinancieraController::class, 'calcular'])
        ->name('matematica.calcular');

});

Route::resource('alternativas', AlternativaController::class);

Route::post('/alternativas/{alternativa}/cotizaciones', [
    CotizacionController::class, 'store'
])->name('alternativas.cotizaciones.store');

Route::delete('/cotizaciones/{cotizacion}', [
    CotizacionController::class, 'destroy'
])->name('cotizaciones.destroy');