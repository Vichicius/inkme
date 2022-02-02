<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Models\Post;
use App\Models\Estudio;
use Exception;
use DateTime;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
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
            if(isset($data->title) && isset($data->photo) && isset($data->style) && isset($data->bcolor)){
                $post = new Post;
                //checkear q existe el usuario con el apitoken
                $user = Usuario::where('api_token', $data->api_token)->first();
                if(!isset($user)){ //no deberia entrar aqui si el middleware va bn
                    throw new Exception("Error: No se encuentra el usuario");
                }
                $post->user_id = $user->id;
                $post->title = $data->title;
                $post->description = $data->description;
                $post->photo = $data->photo;
                $post->style = $data->style;
                $post->bcolor = $data->bcolor;
                $post->save();
                $response["msg"]="Post creado";
                $response["post_id"]=$post->id;
            }else{
                throw new Exception("Error: Introduce api_token, titulo, descripcion, foto, estilo y bcolor (boolean)");
            }
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
                if(!isset($post)){
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
                if(!isset($usuario)){
                    throw new Exception("Error: No se encuentra el usuario.");
                }
                $response["msg"]="usuario encontrado.";

                $response["usuario"]["nombre"] = $usuario->name;
                $response["usuario"]["email"] = $usuario->email;
                $response["usuario"]["foto"] = $usuario->profile_picture;
                $response["usuario"]["ubicacion"] = $usuario->location;
                $response["usuario"]["estudio_id"] = $usuario->estudio_id;

                $arrayImagenesURL = Post::where('user_id',$data->usuario_id)->pluck('id','photo')->toArray();
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

}
