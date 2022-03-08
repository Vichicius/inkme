<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UsuarioController extends Controller
{
    public function register(Request $req){ //Pide: name, email, password, y numtlf || Devuelve: status, msg y usuario_id
        $jdata = $req->getContent();
        $data = json_decode($jdata);
        $response["status"]=1;


        try{
            if(isset($data->name) && isset($data->email) && isset($data->password) && isset($data->numtlf)){
                $user = new Usuario;
                $user->name = $data->name;
                //validar email
                if(!preg_match("^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$^", $data->email)) {
                    throw new Exception("Error: Email no válido",401);
                }
                $user->email = $data->email;
                //validar contraseña
                if(preg_match("/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).{6,}/", $data->password)){
                    $user->password = Hash::make($data->password);
                }else{
                    throw new Exception("Contraseña insegura. Mínimo: 1 Mayúscula, 1 minúscula, 1 número y 6 de longitud",401);
                }
                //validar tlf
                if($data->numtlf >= 100000000 && $data->numtlf <= 999999999){
                    $user->numtlf = $data->numtlf;
                }else{
                    throw new Exception("Número de teléfono debe ser de 9 dígitos",401);
                }
                //crear token y guardarlo
                $allTokens = Usuario::pluck('api_token')->toArray();
                do {
                    $user->api_token = Hash::make(now().$user->email);
                } while (in_array($user->api_token, $allTokens)); //En bucle mientras que el apitoken esté duplicado
                $user->save();
                $response["msg"]="Guardado con éxito";
                $response["api_token"]=$user->api_token;
                $response["user"]=$user;
            }else{
                throw new Exception("Introduce name, email, password y numtlf",400);
            }
        }catch(\Exception $e){
            $response["status"]=$e->getCode();
            $response["msg"]=$e->getMessage();
        }

        return response()->json($response);
    }

    public function login(Request $req){ //Pide:  email y password || Devuelve: "status" "msg" y "api_token" (si ha iniciado bien)
        $jdata = $req->getContent();
        $data = json_decode($jdata);

        $response["status"]=1;
        try{
            if(isset($data->email) && isset($data->password)){
                //comprobar email
                $user = Usuario::where('email', $data->email)->first();
                if ($user == null){
                    throw new Exception("Error: El email o la contraseña no es correcto",401);
                }
                //comprobar que la contraseña coincide con la asociada al email
                if(!Hash::check($data->password, $user->password)){
                    throw new Exception("Error: El email o la contraseña no es correcto",401);
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
                $response["user"]=$user;

            }else{
                throw new Exception("Error: Introduce email y password",400);
            }
        }catch(\Exception $e){
            $response["status"]=$e->getCode();
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
                    throw new Exception("Error: No se encuentra el usuario.", 500);
                }
                $response["msg"]="usuario encontrado.";

                $response["usuario"]["nombre"] = $usuario->name;
                $response["usuario"]["email"] = $usuario->email;
                $response["usuario"]["foto"] = $usuario->profile_picture;
                $response["usuario"]["ubicacion"] = $usuario->location;
                $response["usuario"]["estudio_id"] = $usuario->estudio_id;
                $response["usuario"]["styles"] = $usuario->styles;

                $ImagenesURLeID = Post::orderBy('id', 'DESC')->where('user_id',$data->usuario_id)->where('active',1)->get();

                $response["usuario"]["posts"] = $ImagenesURLeID;
            }else{
                throw new Exception("Error: Introduce usuario_id",400);
            }
        }catch(\Exception $e){
            $response["status"]=$e->getCode();
            $response["msg"]=$e->getMessage();
        }
        return response()->json($response);
    }

    public function editarPerfil(Request $req){ //Pide: api_token, campos a editar || Devuelve: "status" "msg"
        $jdata = $req->getContent();
        $data = json_decode($jdata);

        $response["status"]=1;
        try{
            $validator = Validator::make(json_decode($jdata, true), [
                'name' => 'string|max:40',
                'email' => 'string|max:255|email:rfc,dns',
                'password' => 'string',
                'numtlf' => 'integer|max:999999999',
                'profile_picture' => 'string',
                'location' => 'string',
                'styles' => 'string',
            ]);

            if ($validator->fails()) {
                throw new Exception($validator->errors()->first(),401);
            }

            //coger el usuario que ha sido guardado en el middleware de login
            $user = $req->get('usuario');

            if(isset($data->name)) $user->name = $data->name;
            if(isset($data->email)){
                $useremail = Usuario::where('email', $data->email)->first();
                if(isset($useremail)){
                    if($useremail->id != $user->id){
                        throw new Exception("Ese email ya está en uso",401);
                    }
                }else{
                    $user->email = $data->email;
                }
            }
            if(isset($data->password)){
                //validar contraseña
                if(preg_match("/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).{6,}/", $data->password)){
                    $user->password = Hash::make($data->password);
                }else{
                    throw new Exception("Contraseña insegura. Mínimo: 1 Mayúscula, 1 minúscula, 1 número y 6 de longitud",401);
                }
            }
            if(isset($data->numtlf)) $user->numtlf = $data->numtlf;
            if(isset($data->profile_picture)) $user->profile_picture = $data->profile_picture;
            if(isset($data->location)) $user->location = $data->location;
            if(isset($data->styles)){ //Probar----- lo esperado es que se guarde así-> estilo1, estilo2, estilo3,

                if(!str_ends_with($data->styles, ",")){ //si no termina en coma le añade una coma
                    $data->styles = $data->styles.',';  //Esto es para que las búsquedas de estilos sean más exactas (que no se confunda tradicional con tradicional-japones)
                }
                $estilosExistentes = ["blackwork", "tradicional", "tradicional-japones", "realista", "neotradi", "ignorant"];
                $inputEstilos = explode(",", $data->styles);
                array_pop($inputEstilos); //elimino el ultimo elemento ya que al hacer explode con , se queda un caracter vacío al final del array
                foreach ($inputEstilos as $key => $value) {
                    if(!in_array($value, $estilosExistentes)){
                        $response["estilos"] = $estilosExistentes;
                        throw new Exception("El estilo $value no existe. Introduce los estilos de la siguiente forma: 'estilo1, estilo2, estilo3, ...' ",401);
                    }
                }
                $user->styles = $data->styles;
            }
            $user->save();

            $response["msg"]="Usuario editado";
            $response["user_id"]=$user->id;

        }catch(\Exception $e){
            $response["status"]=$e->getCode();
            $response["msg"]=$e->getMessage();
        }
        return response()->json($response);
    }

    public function viewStats(Request $req){ //Pide: api_token || Devuelve: "status" "msg", "viewsTotales", "viewsTatuadores", "viewsClientes"
        $jdata = $req->getContent();
        $data = json_decode($jdata);

        $response["status"]=1;
        try{
            if(isset($data->api_token)){
                $user = $req->get('usuario');
                $posts = Post::orderBy('viewsTotales', 'desc')->where('user_id', $user->id)->get(['id','photo','viewsTatuadores', 'viewsClientes','viewsTotales']);
                $viewsTotales = 0;
                $viewsClientes = 0;
                $viewsTatuadores = 0;
                foreach ($posts as $key => $post) {
                    $viewsTotales += $post->viewsTotales;
                    $viewsClientes += $post->viewsClientes;
                    $viewsTatuadores += $post->viewsTatuadores;
                }
                $top3posts = $posts->take(3);
                $response['total'] = $viewsTotales;
                $response['clientes'] = $viewsClientes;
                $response['tatuadores'] = $viewsTatuadores;
                $response['top3'] = $top3posts;
                return response()->json($response);

            }else{
                throw new Exception("Error: Introduce api_token",400);
            }
        }catch(\Exception $e){
            $response["status"]=$e->getCode();
            $response["msg"]=$e->getMessage();
        }
        return response()->json($response);
    }

    public function sumarViewPerfil(Request $req){ //Pide: user_id, api_token (opcional para saber que es un tatuador) || Devuelve: "status" "msg" y "[{post}]"
        $jdata = $req->getContent();
        $data = json_decode($jdata);

        $response["status"]=1;
        try{
            if(isset($data->user_id) && isset($data->api_token)){
                $user = Usuario::find($data->user_id)->where('active', 1)->first();
                if(!isset($post)) throw new Exception("Error: usuario no existe",500);
                if($data->api_token != ""){//si es tatuador: añadir una view como tatuador
                    $user->viewsTatuadores += 1;
                    $user->viewsTotales += 1;
                }else{//si es cliente: añadir una view como cliente
                    $user->viewsClientes += 1;
                    $user->viewsTotales += 1;
                }
                $user->save();
            }else{
                throw new Exception("Error: Introduce user_id y api_token (aunque esté vacio)",400);
            }
        }catch(\Exception $e){
            $response["status"]=$e->getCode();
            $response["msg"]=$e->getMessage();
        }
        return response()->json($response);
    }


}
