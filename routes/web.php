<?php

use App\Http\Controllers\RemoteStyleController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaTeTiController;

Route::get('/', fn() => redirect('/emisor'));

Route::get('/emisor', [RemoteStyleController::class, 'emisor'])->name('emisor');

Route::post('/emitir', [RemoteStyleController::class, 'emitir'])->name('emitir');

Route::get('/receptor', [RemoteStyleController::class, 'receptor'])->name('receptor');

Route::get('/juego', [TaTeTiController::class, 'index'])->name('tateti');

Route::post('/juego/crear', [TaTeTiController::class, 'crearSala'])->name('tateti.crear');

Route::get('/juego/sala/{codigo}', [TaTeTiController::class, 'mostrarSala'])->name('tateti.sala');

Route::post('/juego/sala/{codigo}/unirse', [TaTeTiController::class, 'unirseSala'])->name('tateti.unirse');

Route::post('/juego/sala/{codigo}/jugar', [TaTeTiController::class, 'jugar'])->name('tateti.jugar');

Route::post('/juego/sala/{codigo}/reiniciar', [TaTeTiController::class, 'reiniciar'])->name('tateti.reiniciar');

Route::post('/juego/buscar', [TaTeTiController::class, 'buscarContrincante'])->name('tateti.buscar');


Route::post('/juego/cancelar', [TaTeTiController::class, 'cancelarBusqueda'])
    ->name('tateti.cancelar');

Route::post('/juego/otra-partida', [TaTeTiController::class, 'buscarOtraPartida'])
    ->name('tateti.otra-partida');
