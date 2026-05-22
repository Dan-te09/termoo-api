<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JogoController;

Route::post('/iniciar-jogo', [JogoController::class, 'iniciarJogo']);
Route::post('/validar-tentativa', [JogoController::class, 'validarTentativa']);