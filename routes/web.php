<?php

use App\Http\Controllers\FlightController;

Route::get('/', function () {
    return view('index');
});

Route::get('api/flights', [FlightController::class, 'listarDadosDeVoo']);
Route::get('api/flights/group', [FlightController::class, 'listarDadosDeVooAgrupado']);



