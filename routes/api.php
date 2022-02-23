<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InkmeController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::put('/register', [InkmeController::class,'register']);
Route::put('/login', [InkmeController::class,'login']);


Route::middleware('login')->group(function () {
    Route::put('/crearPost', [InkmeController::class,'crearPost']);
    Route::put('/crearMerch', [InkmeController::class,'crearMerch']);
    Route::put('/editarPerfil', [InkmeController::class,'editarPerfil']);
    Route::put('/borrarPost', [InkmeController::class,'borrarPost']);
    Route::put('/borrarArticulo', [InkmeController::class,'borrarArticulo']);
    Route::put('/viewStats', [InkmeController::class,'viewStats']);
});


Route::put('/cargarPost', [InkmeController::class,'cargarPost']);
Route::put('/cargarPerfil', [InkmeController::class,'cargarPerfil']);
Route::put('/cargarMerchLista', [InkmeController::class,'cargarMerchLista']);
Route::put('/cargarMerchArticulo', [InkmeController::class,'cargarMerchArticulo']);
Route::put('/fetchFeed', [InkmeController::class,'fetchFeed']);
Route::put('/listaDeFavs', [InkmeController::class,'listaDeFavs']);
Route::put('/sumarViewPost', [InkmeController::class,'sumarViewPost']);
Route::put('/sumarViewPerfil', [InkmeController::class,'sumarViewPerfil']);


