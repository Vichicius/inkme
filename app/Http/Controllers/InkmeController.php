<?php

namespace App\Http\Controllers;

use App\Models\Articulo;
use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Models\Post;
use App\Models\Estudio;
use Exception;
use DateTime;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
//use App\Mail\OrderShipped;
use Illuminate\Support\Str;

class InkmeController extends Controller
{

    public function register(Request $req){ //Pide: api_token, name, email, password, y numtlf || Devuelve: status, msg y usuario_id
        $jdata = $req->getContent();
        $data = json_decode($jdata);
        $response["status"]=1;


        try{
            if(isset($data->name) && isset($data->email) && isset($data->password) && isset($data->numtlf)){
                $user = new Usuario;
                $user->name = $data->name;
                //validar email
                if(!preg_match("^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$^", $data->email)) {
                    throw new Exception("Error: Email no válido");
                }
                $user->email = $data->email;
                //validar contraseña
                if(preg_match("/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{6,}/", $data->password)){
                    $user->password = Hash::make($data->password);
                }else{
                    throw new Exception("Contraseña insegura. Mínimo: 1 Mayúscula, 1 minúscula, 1 caracter especial y 1 número");
                }
                //validar tlf
                if($data->numtlf >= 100000000 && $data->numtlf <= 999999999){
                    $user->numtlf = $data->numtlf;
                }else{
                    throw new Exception("Número de teléfono debe ser de 9 dígitos");
                }
                $user->views = 0;
                $user->save();
                $response["msg"]="Guardado con éxito";
                $response["usuario_id"]=$user->id;
            }else{
                throw new Exception("Introduce name, email, password, profile_picture y location");
            }
        }catch(\Exception $e){
            $response["status"]=0;
            $response["msg"]="Error al intentar guardar el usuario: ".$e->getMessage();
        }

        return response()->json($response);
    }

    public function login(Request $req){ //Pide: api_token, email y password || Devuelve: "status" "msg" y "api_token" (si ha iniciado bien)
        $jdata = $req->getContent();
        $data = json_decode($jdata);

        $response["status"]=1;
        try{
            if(isset($data->email) && isset($data->password)){
                //comprobar email
                $user = Usuario::where('email', $data->email)->first();
                if ($user == null){
                    throw new Exception("Error: Email no existe");
                }
                //comprobar que la contraseña coincide con la asociada al email
                if(!Hash::check($data->password, $user->password)){
                    throw new Exception("Contraseña incorrecta");
                }
                //crear token y guardarlo
                $allTokens = Usuario::pluck('api_token')->toArray();
                do {
                    $user->api_token = Hash::make(now().$user->email);
                } while (in_array($user->api_token, $allTokens)); //En bucle mientras que el apitoken esté duplicado
                $user->save();
                //responder con token
                $response["msg"]="Sesion iniciada";
                $response["api_token"]=$user->api_token;

            }else{
                throw new Exception("Error: Introduce email y password");
            }
        }catch(\Exception $e){
            $response["status"]=0;
            $response["msg"]=$e->getMessage();
        }
        return response()->json($response);
    }

    public function crearPost(Request $req){ //Pide:api_token title, description(opcional) photo style bcolor || Devuelve: "status" "msg" y "post_id"
        $jdata = $req->getContent();
        $data = json_decode($jdata);

        $response["status"]=1;
        try{
            $validator = Validator::make(json_decode($data, true), [
                'title' => 'required|string|max:40',
                'description' => 'required|string|max:255',
                'photo' => 'required|string',
                'style' => 'required|string',
                'bcolor' => 'required|boolean',
            ]);

            if ($validator->fails()) {
                throw new Exception($validator->errors()->first());
            }

            $post = new Post;
            //coger el usuario que ha sido guardado en el middleware de login
            $user = $data->usuario;

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
            $response["status"]=0;
            $response["msg"]=$e->getMessage();
        }
        return response()->json($response);
    }

    public function cargarPost(Request $req){ //Pide: post_id || Devuelve: "status" "msg" y "post"
        $jdata = $req->getContent();
        $data = json_decode($jdata);

        $response["status"]=1;
        try{
            if(isset($data->post_id)){
                $post = Post::find($data->post_id);
                if(!isset($post) || $post->active == false){
                    throw new Exception("Error: No se encuentra el post.");
                }
                $response["msg"]="Post encontrado.";
                $response["post"] = $post;
            }else{
                throw new Exception("Error: Introduce post_id");
            }
        }catch(\Exception $e){
            $response["status"]=0;
            $response["msg"]=$e->getMessage();
        }
        return response()->json($response);
    }

    public function cargarPerfil(Request $req){ //Pide: usuario_id || Devuelve el objeto usuario con nombre email foto ubicacion posts(con total de post e informacion de cada post (id y URL de la imagen))
        $jdata = $req->getContent();
        $data = json_decode($jdata);

        $response["status"]=1;
        try{
            if(isset($data->usuario_id)){
                $usuario = Usuario::find($data->usuario_id);
                if(!isset($usuario)|| $usuario->active == false){
                    throw new Exception("Error: No se encuentra el usuario.");
                }
                $response["msg"]="usuario encontrado.";

                $response["usuario"]["nombre"] = $usuario->name;
                $response["usuario"]["email"] = $usuario->email;
                $response["usuario"]["foto"] = $usuario->profile_picture;
                $response["usuario"]["ubicacion"] = $usuario->location;
                $response["usuario"]["estudio_id"] = $usuario->estudio_id;

                $arrayImagenesURL = Post::where('user_id',$data->usuario_id)->pluck('photo','id')->toArray();
                $numeroPosts = count($arrayImagenesURL);
                $response["usuario"]["posts"]["total"] = $numeroPosts;
                $response["usuario"]["posts"]["info"] = $arrayImagenesURL;
            }else{
                throw new Exception("Error: Introduce usuario_id");
            }
        }catch(\Exception $e){
            $response["status"]=0;
            $response["msg"]=$e->getMessage();
        }
        return response()->json($response);
    }

    public function crearMerch(Request $req){ //Pide:api_token name, description(opcional) photo price || Devuelve: "status" "msg" y "articulo_id"
        $jdata = $req->getContent();
        $data = json_decode($jdata);

        $response["status"]=1;
        try{
            $validator = Validator::make(json_decode($data, true), [
                'name' => 'required|string|max:50',
                'description' => 'string|max:255',
                'photo' => 'required|string|max:255',
                'price' => 'required|integer|max:999999',
            ]);

            if ($validator->fails()) {
                throw new Exception($validator->errors()->first());
            }

            $articulo = new Articulo;
            //coger el usuario que ha sido guardado en el middleware de login
            $user = $data->usuario;

            $articulo->user_id = $user->id;
            $articulo->name = $data->name;
            $articulo->description = $data->description;
            $articulo->photo = $data->photo;
            $articulo->price = $data->price;
            $articulo->save();
            $response["msg"]="Articulo creado";
            $response["articulo_id"]=$articulo->id;

        }catch(\Exception $e){
            $response["status"]=0;
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
                    throw new Exception("Error: No se encuentra el usuario.");
                }
                $listaArticulos = Articulo::where('usuario_id', $data->usuario_id)->get();
                if(count($listaArticulos) == 0){
                    throw new Exception("No tiene ningún articulo en venta.");
                }

                foreach ($listaArticulos as $key => $articulo) {
                    $response[$articulo->id] = $articulo->photo;
                }
            }else{
                throw new Exception("Error: Introduce usuario_id");
            }
        }catch(\Exception $e){
            $response["status"]=0;
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
                    throw new Exception("Error: No se encuentra el articulo.");
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
                throw new Exception("Error: Introduce articulo_id");
            }
        }catch(\Exception $e){
            $response["status"]=0;
            $response["msg"]=$e->getMessage();
        }
        return response()->json($response);
    }

    public function editarPerfil(Request $req){ //Pide: api_token, campos a editar || Devuelve: "status" "msg"
        $jdata = $req->getContent();
        $data = json_decode($jdata);

        $response["status"]=1;
        try{
            $validator = Validator::make(json_decode($data, true), [
                'name' => 'string|max:40',
                'email' => 'string|max:255|email:rfc,dns|unique:usuarios',
                'password' => 'string',
                'numtlf' => 'integer|max:999999999',
                'profile_picture' => 'string',
                'location' => 'string',
                'styles' => 'string',
            ]);

            if ($validator->fails()) {
                throw new Exception($validator->errors()->first());
            }

            //coger el usuario que ha sido guardado en el middleware de login
            $user = $data->usuario;

            if(isset($data->name)) $user->name = $data->name;
            if(isset($data->email)) $user->email = $data->email;
            if(isset($data->password)){
                //validar contraseña
                if(preg_match("/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{6,}/", $data->password)){
                    $user->password = Hash::make($data->password);
                }else{
                    throw new Exception("Contraseña insegura. Mínimo: 1 Mayúscula, 1 minúscula, 1 caracter especial y 1 número");
                }
            }
            if(isset($data->numtlf)) $user->numtlf = $data->numtlf;
            if(isset($data->profile_picture)) $user->profile_picture = $data->profile_picture;
            if(isset($data->location)) $user->location = $data->location;
            if(isset($data->styles)){ //Probar----- lo esperado es que se guarde así-> estilo1, estilo2, estilo3,

                if(!preg_match("/%,/", $data->styles)){ //si no termina en coma le añade una coma
                    $data->styles = $data->styles.',';
                }
                if(preg_match("/((blackwork|tradicional|tradicional-japones|realista|neotradi|ignorant),)+/", $data->styles)){
                    $user->styles = $data->styles;
                }else{
                    throw new Exception("Introduce los estilos de la siguiente forma: 'estilo1, estilo2, estilo3, ...' ");
                }
            }
            $user->save();

            $response["msg"]="Usuario editado";
            $response["user_id"]=$user->id;

        }catch(\Exception $e){
            $response["status"]=0;
            $response["msg"]=$e->getMessage();
        }
        return response()->json($response);
    }

    public function borrarPost(Request $req){ //Pide: api_token, post_id || Devuelve: "status" "msg"
        $jdata = $req->getContent();
        $data = json_decode($jdata);

        $response["status"]=1;
        try{
            $validator = Validator::make(json_decode($data, true), [
                'post_id' => 'exists:posts, id',
            ]);

            if ($validator->fails()) {
                throw new Exception($validator->errors()->first());
            }

            //coger el usuario que ha sido guardado en el middleware de login
            $user = $data->usuario;
            $post = Post::find($data->post_id);

            if($post->user_id != $user->id){
                throw new Exception("Error: Este post no corresponde a este usuario (usuario: $user->id, post: $post->id");
            }

            $post->active = false;

            $post->save();

            $response["msg"]="Post borrado";

        }catch(\Exception $e){
            $response["status"]=0;
            $response["msg"]=$e->getMessage();
        }
        return response()->json($response);
    }

    public function borrarArticulo(Request $req){ //Pide: api_token, articulo_id || Devuelve: "status" "msg"
        $jdata = $req->getContent();
        $data = json_decode($jdata);

        $response["status"]=1;
        try{
            $validator = Validator::make(json_decode($data, true), [
                'articulo_id' => 'exists:articulos, id',
            ]);

            if ($validator->fails()) {
                throw new Exception($validator->errors()->first());
            }

            //coger el usuario que ha sido guardado en el middleware de login
            $user = $data->usuario;
            $articulo = Articulo::find($data->articulo_id);

            if($articulo->user_id != $user->id){
                throw new Exception("Error: Este articulo no corresponde a este usuario (usuario: $user->id, post: $articulo->id");
            }

            $articulo->active = false;

            $articulo->save();

            $response["msg"]="Articulo borrado";

        }catch(\Exception $e){
            $response["status"]=0;
            $response["msg"]=$e->getMessage();
        }
        return response()->json($response);
    }

    public function fetchFeed(Request $req){ //Pide: nada || Devuelve: Un usuario con 3 posts suyos (usuario_id, usuario_photo, usuario_name, )
        $jdata = $req->getContent();
        $data = json_decode($jdata);

        $response["status"]=1;
        try{
            //me puede pasar ubicacion y etiquetas
            if(isset($data->styles)){
                $estilos = explode(',',$data->styles);
                $usuarios = [];
                $usuariosIDsYaObtenidos = [];
                foreach ($estilos as $key => $estilo) {
                    $usuariosCoincidenConUnEstilo = [];

                    if(isset($data->location)){ //si además de estilos me pasa ubicacion
                        $usuariosCoincidenConUnEstilo = Usuario::where('location',$data->location)->where('style','like','%'.$estilo.','.'%')->get('id','name','profile_picture','location','styles');
                    }else{
                        $usuariosCoincidenConUnEstilo = Usuario::where('style','like','%'.$estilo.','.'%')->get('id','name','profile_picture','location','styles');
                    }

                    if(count($usuariosCoincidenConUnEstilo) == 0 ) continue;
                    foreach ($usuariosCoincidenConUnEstilo as $key => $value) {
                        if (!in_array($value->id, $usuariosIDsYaObtenidos)){ //probar si se puede quitar este array
                            array_push($usuariosIDsYaObtenidos, $value->id); //y si funciona con inarray $usuarios, $value
                            array_push($usuarios, $value);
                        }
                    }
                }
            }else if(isset($data->location)){
                $usuarios = Usuario::where('location',$data->location)->get('id','name','profile_picture','location','styles');
            }else{
                $usuarios = Usuario::all('id','name','profile_picture','location','styles');
            }
            if(count($usuarios) == 0) {
                throw new Exception("No hay coincidencias.");
            }
            //Necesito un array de usuarios llamado $usuarios con id name profpic location styles
            $lista1 = [];
            foreach ($usuarios as $key => $usuario) {
                $posts = Post::where('usuario_id', $usuario->id)->get('id', 'usuario_id', 'photo');
                array_push($lista1, [
                    "id"=>$usuario->id,
                    "name"=>$usuario->name,
                    "profile_picture"=>$usuario->profile_picture,
                    "location"=>$usuario->location,
                    "styles"=>$usuario->styles,
                    "posts"=>$posts
                ]);
            }
            $response["usuarios"] = $lista1;
        }catch(\Exception $e){
            $response["status"]=0;
            $response["msg"]=$e->getMessage();
        }
        return response()->json($response);
    }
    /*
    if(preg_match("/((blackwork|tradicional|tradicional-japones|realista|neotradi|ignorant),)+/", $data->styles)){
        $user->styles = $data->styles;
    }else{
        throw new Exception("Introduce los estilos de la siguiente forma: 'estilo1','estilo2','estilo3',...");
    }
    */
}
