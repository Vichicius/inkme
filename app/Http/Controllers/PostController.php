<?php

namespace App\Http\Controllers;

use App\Models\Articulo;
use App\Models\Post;
use App\Models\Usuario;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    public function crearPost(Request $req){ //Pide: api_token, title, description(opcional) photo style bcolor || Devuelve: "status" "msg" y "post_id"
        $jdata = $req->getContent();
        $data = json_decode($jdata);

        $response["status"]=1;
        try{
            $validator = Validator::make(json_decode($jdata, true), [
                'title' => 'required|string|max:40',
                'description' => 'required|string|max:255',
                'photo' => 'required|string',
                'style' => 'required|string',
                'bcolor' => 'required|boolean',
            ]);

            if ($validator->fails()) {
                throw new Exception($validator->errors()->first(),401);
            }

            $post = new Post();
            //coger el usuario que ha sido guardado en el middleware de login
            $user = $req->get('usuario');

            $post->user_id = $user->id;
            $post->title = $data->title;
            $post->description = $data->description;
            $post->photo = $data->photo;
            $post->style = $data->style;
            $post->bcolor = $data->bcolor;
            $post->save();
            $response["msg"]="Post creado";
            $response["post_id"]=$post->id;

        }catch(\Exception $e){
            $response["status"]=$e->getCode();
            $response["msg"]=$e->getMessage();
        }
        return response()->json($response);
    }

    public function cargarPost(Request $req){ //Pide: post_id || Devuelve: "status" "msg" y "post" MG:
        $jdata = $req->getContent();
        $data = json_decode($jdata);

        $response["status"]=1;
        try{
            if(isset($data->post_id)){
                $post = Post::find($data->post_id);
                if(!isset($post) || $post->active == false){
                    throw new Exception("Error: No se encuentra el post.",500);
                }
                $user = Usuario::find($post->user_id);
                $response["msg"]="Post encontrado.";
                $response["post"] = $post;
                $response["usuario"] = $user;
            }else{
                throw new Exception("Error: Introduce post_id",400);
            }
        }catch(\Exception $e){
            $response["status"]=$e->getCode();
            $response["msg"]=$e->getMessage();
        }
        return response()->json($response);
    }

    public function crearMerch(Request $req){ //Pide:api_token name, description(opcional) photo price || Devuelve: "status" "msg" y "articulo_id"
        $jdata = $req->getContent();
        $data = json_decode($jdata);

        $response["status"]=1;
        try{
            $validator = Validator::make(json_decode($jdata, true), [
                'name' => 'required|string|max:50',
                'description' => 'string|max:255',
                'photo' => 'required|string|max:255',
                'price' => 'required|integer|max:999999',
            ]);

            if ($validator->fails()) {
                throw new Exception($validator->errors()->first(),401);
            }

            $articulo = new Articulo;
            //coger el usuario que ha sido guardado en el middleware de login
            $user = $req->get('usuario');

            $articulo->user_id = $user->id;
            $articulo->name = $data->name;
            $articulo->description = $data->description;
            $articulo->photo = $data->photo;
            $articulo->price = $data->price;
            $articulo->save();
            $response["msg"]="Articulo creado";
            $response["articulo_id"]=$articulo->id;

        }catch(\Exception $e){
            $response["status"]=$e->getCode();
            $response["msg"]=$e->getMessage();
        }
        return response()->json($response);
    }

    public function cargarMerchLista(Request $req){ //Pide: usuario_id || Devuelve lista de los ids de los articulos que tiene el usuario junto con su foto
        $jdata = $req->getContent();
        $data = json_decode($jdata);

        $response["status"]=1;
        try{
            if(isset($data->usuario_id)){
                //compruebo que existe el usuario
                $usuario = Usuario::find($data->usuario_id);
                if(!isset($usuario)){
                    throw new Exception("Error: No se encuentra el usuario.",500);
                }
                $listaArticulos = Articulo::where('user_id', $data->usuario_id)->where('active',1)->get();
                if(count($listaArticulos) == 0){
                    throw new Exception("No tiene ningún articulo en venta.",500);
                }
                $response["articulos"] = $listaArticulos;
            }else{
                throw new Exception("Error: Introduce usuario_id",400);
            }
        }catch(\Exception $e){
            $response["status"]=$e->getCode();
            $response["msg"]=$e->getMessage();
        }
        return response()->json($response);
    }

    public function cargarMerchArticulo(Request $req){ //Pide: articulo_id || Devuelve objeto articulo
        $jdata = $req->getContent();
        $data = json_decode($jdata);

        $response["status"]=1;
        try{
            if(isset($data->articulo_id)){
                //compruebo que existe el articulo
                $articulo = Articulo::find($data->articulo_id);
                if(!isset($articulo)|| $articulo->active == false){
                    throw new Exception("Error: No se encuentra el articulo.",500);
                }
                $response["articulo"] = $articulo;
                // $response = [
                //     "status"=>1,
                //     [
                //         "id" => $articulo->id,
                //         "id" => $articulo
                //         "id" => $articulo
                //     ]
                // ];
            }else{
                throw new Exception("Error: Introduce articulo_id",400);
            }
        }catch(\Exception $e){
            $response["status"]=$e->getCode();
            $response["msg"]=$e->getMessage();
        }
        return response()->json($response);
    }

    public function borrarPost(Request $req){ //Pide: api_token, post_id || Devuelve: "status" "msg"
        $jdata = $req->getContent();
        $data = json_decode($jdata);

        $response["status"]=1;
        try{
            $validator = Validator::make(json_decode($jdata, true), [
                'post_id' => 'exists:posts,id',
            ]);

            if ($validator->fails()) {
                throw new Exception($validator->errors()->first(),401);
            }

            //coger el usuario que ha sido guardado en el middleware de login
            $user = $req->get('usuario');
            $post = Post::find($data->post_id);

            if($post->user_id != $user->id){
                throw new Exception("Error: Este post no corresponde a este usuario (usuario: $user->id, post: $post->id",401);
            }

            $post->active = false;

            $post->save();

            $response["msg"]="Post borrado";

        }catch(\Exception $e){
            $response["status"]=$e->getCode();
            $response["msg"]=$e->getMessage();
        }
        return response()->json($response);
    }

    public function borrarArticulo(Request $req){ //Pide: api_token, articulo_id || Devuelve: "status" "msg"
        $jdata = $req->getContent();
        $data = json_decode($jdata);

        $response["status"]=1;
        try{
            $validator = Validator::make(json_decode($jdata, true), [
                'articulo_id' => 'exists:articulos,id',
            ]);

            if ($validator->fails()) {
                throw new Exception($validator->errors()->first(),401);
            }

            //coger el usuario que ha sido guardado en el middleware de login
            $user = $req->get('usuario');
            $articulo = Articulo::find($data->articulo_id);

            if($articulo->user_id != $user->id){
                throw new Exception("Error: Este articulo no corresponde a este usuario (usuario: $user->id, post: $articulo->id",401);
            }

            $articulo->active = false;

            $articulo->save();

            $response["msg"]="Articulo borrado";

        }catch(\Exception $e){
            $response["status"]=$e->getCode();
            $response["msg"]=$e->getMessage();
        }
        return response()->json($response);
    }

    public function fetchFeed(Request $req){ //Pide: nada || Devuelve: Un usuario con 3 posts suyos (usuario_id, usuario_photo, usuario_name, )
        $jdata = $req->getContent();
        $data = json_decode($jdata);

        $response["status"]=1;
        try{
            $usuarios = [];
            //me puede pasar ubicacion y etiquetas
            if(isset($data->styles) && $data->styles != ""){
                $estilos = explode(',',$data->styles);
                $usuarios = [];
                $usuariosIDsYaObtenidos = [];
                foreach ($estilos as $key => $estilo) {
                    $usuariosCoincidenConUnEstilo = [];

                    if(isset($data->location) && $data->location != ""){ //busqueda estilos + ubicacion
                        $usuariosCoincidenConUnEstilo = Usuario::where('location',$data->location)->where('styles','like','%'.$estilo.','.'%')->get();
                    }else{ //solo estilos
                        $usuariosCoincidenConUnEstilo = Usuario::where('styles','like','%'.$estilo.','.'%')->get();
                    }

                    if(count($usuariosCoincidenConUnEstilo) == 0 ) continue;
                    foreach ($usuariosCoincidenConUnEstilo as $key => $value) {
                        if (!in_array($value->id, $usuariosIDsYaObtenidos)){ //probar si se puede quitar este array
                            array_push($usuariosIDsYaObtenidos, $value->id); //y si funciona con inarray $usuarios, $value
                            array_push($usuarios, $value);
                        }
                    }
                }
            }else if(isset($data->location) && $data->location != ""){ // solo ubicacion
                $usuarios = Usuario::where('location',$data->location)->get();
            }else{ //ningun filtro
                $usuarios = Usuario::all()->shuffle();
                //randomizar el orden de usuarios para que sea cada vez uno nuevo
            }
            if(isset($data->name) && $data->name != ""){ //filtro de nombre
                $usuarios1 = $usuarios;
                $usuarios = [];
                foreach ($usuarios1 as $key => $usuario) {
                    if(str_contains($usuario->name, $data->name)){
                        array_push($usuarios, $usuario);
                    }
                }
            }
            if(count($usuarios) == 0) {
                throw new Exception("No hay coincidencias.",500);
            }


            //Necesito un array de usuarios llamado $usuarios con id name profpic location styles
            $lista1 = [];
            foreach ($usuarios as $key => $usuario) {
                if(isset($data->api_token) && ($usuario == Usuario::where('api_token', $data->api_token)->first())){
                    continue;
                }
                if($usuario->active == 0){
                    continue;
                }
                //filtrar que los usuarios tengan al menos 3 posts
                $posts = Post::orderBy('id', 'DESC')->where('user_id', $usuario->id)->where('active',1)->get(['id', 'user_id', 'photo']);
                if(count($posts) >= 3){
                    array_push($lista1, [
                        "id"=>$usuario->id,
                        "name"=>$usuario->name,
                        "profile_picture"=>$usuario->profile_picture,
                        "location"=>$usuario->location,
                        "styles"=>$usuario->styles,
                        "posts"=>$posts
                    ]);
                }
            }
            if(count($lista1) == 0) {
                throw new Exception("Ninguna de las coincidencias tiene al menos 3 posts.",500);
            }
            $response["usuarios"] = $lista1;
        }catch(\Exception $e){
            $response["status"]=$e->getCode();
            $response["msg"]=$e->getMessage();
        }
        return response()->json($response);
    }

    public function listaDeFavs(Request $req){ //Pide: ids: [post_id] || Devuelve: "status" "msg" y "[{post}]"
        $jdata = $req->getContent();
        $data = json_decode($jdata);

        $response["status"]=1;
        try{
            if(isset($data->ids)){
                $ids = $data->ids;
                $posts = [];
                // $posts = Post::find($ids)->where('active', 1)->toArray();
                foreach ($ids as $key => $id) {
                    $post = Post::where('id', $id)->where('active', 1)->first();
                    array_push($posts, $post);
                }
                if(count($posts) != 0){
                    $array1 = [];
                    foreach ($posts as $key => $post) {
                        array_push($array1, $post);
                    }

                    $response["posts"] = $array1;
                }else{
                    throw new Exception("Error: No se encuentra ningun post. Puede que haya sido borrado",500);
                }
                //comprobar que todos son válidos (que no se haya ocultado ninguno)
                //devolverlos
            }else{
                throw new Exception("Error: Introduce post_ids",400);
            }
        }catch(\Exception $e){
            $response["status"]=$e->getCode();
            $response["msg"]=$e->getMessage();
        }
        return response()->json($response);
    }

    public function sumarViewPost(Request $req){ //Pide: post_id, api_token (opcional para saber que es un tatuador) || Devuelve: "status" "msg" y "[{post}]"
        $jdata = $req->getContent();
        $data = json_decode($jdata);

        $response["status"]=1;
        try{
            if(isset($data->post_id) && isset($data->api_token)){
                $post = Post::find($data->post_id)->where('active', 1)->first();
                if(!isset($post)) throw new Exception("Error: Post no existe",500);
                if($data->api_token != ""){//si es tatuador: añadir una view como tatuador
                    $post->viewsTatuadores += 1;
                    $post->viewsTotales += 1;
                }else{//si es cliente: añadir una view como cliente
                    $post->viewsClientes += 1;
                    $post->viewsTotales += 1;
                }
                $post->save();
            }else{
                throw new Exception("Error: Introduce post_id y api_token (aunque esté vacio)",400);
            }
        }catch(\Exception $e){
            $response["status"]=$e->getCode();
            $response["msg"]=$e->getMessage();
        }
        return response()->json($response);
    }

    public function subirImagen(Request $req){ //Pide: imagen || Devuelve: "status" y "url" !!!!ES UN POST!!!!

        $response["status"]=1;
        try{
            $tipo = $req->file("imagen")->getClientMimeType();
            if(explode("/",$tipo)[0] != "image"){
                throw new Exception("Error: Introduce una imagen",400);
            }
            $filename = $req->file("imagen")->store('public/archivos');
            $response["url"] = "http://www.desarrolladorapp.com/inkme/storage/app/".$filename;

        }catch(\Exception $e){
            $response["status"]=$e->getCode();
            $response["msg"]=$e->getMessage();
        }
        return response()->json($response);
    }
}
