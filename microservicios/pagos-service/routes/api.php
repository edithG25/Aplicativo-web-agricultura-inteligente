<?php

use App\Http\Controllers\PagoController;
use App\Http\Controllers\MetodoPagoController;

Route::middleware(['auth.service'])->group(function () {
    Route::post('pagos', [PagoController::class, 'store']);
    Route::apiResource('metodos-pago', MetodoPagoController::class);
});