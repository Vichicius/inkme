<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Usuario;
use Illuminate\Http\Request;

class WebController extends Controller
{
    function cargarPost(Request $request,int $id){
        $post = Post::find($id);
        $fotopost = $post->photo;
        $descripcion = $post->description;
        $fecha = $post->created_at;

        $usuario = Usuario::find($post->user_id);
        $fotoperfil = $usuario->profile_picture;
        $nombre = $usuario->name;



        return view('post', [
            "fotoperfil"=>$fotoperfil,
            "nombre"=>$nombre,
            "fotopost"=>$fotopost,
            "descripcion"=>$descripcion,
            "fecha"=>$fecha
        ]);
        //fotoperfil, nombre, fotopost, descripcion,fecha
    }
}