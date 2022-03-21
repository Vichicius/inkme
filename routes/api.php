<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MailController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UsuarioController;

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


Route::put('/register', [UsuarioController::class,'register']);
Route::put('/login', [UsuarioController::class,'login']);


Route::middleware('login')->group(function () {
    Route::put('/crearPost', [PostController::class,'crearPost']);
    Route::put('/crearMerch', [PostController::class,'crearMerch']);
    Route::put('/editarPerfil', [UsuarioController::class,'editarPerfil']);
    Route::put('/borrarPost', [PostController::class,'borrarPost']);
    Route::put('/borrarArticulo', [PostController::class,'borrarArticulo']);
    Route::put('/viewStats', [UsuarioController::class,'viewStats']);
    Route::put('/cargarCitasPendientes', [MailController::class,'cargarCitasPendientes']);
    Route::put('/cargarCitasActivas', [MailController::class,'cargarCitasActivas']);
    Route::put('/desactivarCita', [MailController::class,'desactivarCita']);
    Route::put('/aceptarCita', [MailController::class,'aceptarCita']);

    Route::post('/subirImagen', [PostController::class,'subirImagen']);
});

//Route::post('/subirImagen', [PostController::class,'subirImagen']);//mirar si se puede pasar el apitoken a la vez que la imagen para ponerla dentro del middleware

Route::put('/cargarPost', [PostController::class,'cargarPost']);
Route::put('/cargarPerfil', [UsuarioController::class,'cargarPerfil']);
Route::put('/cargarPostPorEstilo', [PostController::class,'cargarPostPorEstilo']);
Route::put('/cargarMerchLista', [PostController::class,'cargarMerchLista']);
Route::put('/cargarMerchArticulo', [PostController::class,'cargarMerchArticulo']);
Route::put('/fetchFeed', [PostController::class,'fetchFeed']);
Route::put('/listaDeFavs', [PostController::class,'listaDeFavs']);
Route::put('/sumarViewPost', [PostController::class,'sumarViewPost']);
Route::put('/sumarViewPerfil', [UsuarioController::class,'sumarViewPerfil']);


Route::put('/enviarFormulario', [MailController::class,'enviarFormulario']);

