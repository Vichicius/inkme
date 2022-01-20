<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;
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
            if($data->name && $data->email && $data->password && $data->numtlf){
                $user = new Usuario;
                $user->name = $data->name;
                $user->email = $data->email;//validar email
                if(preg_match("/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{6,}/", $data->password)){
                    $user->password = Hash::make($data->password);
                }else{
                    throw new Exception("Contraseña insegura. Mínimo: 1 Mayúscula, 1 minúscula, 1 caracter especial y 1 número");
                }
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
            if($data->email && $data->password){
                //comprobar email
                $user = User::where('email', $data->email)->first();
                if ($user == null){
                    throw new Exception("Error: Email no existe");
                }
                //comprobar que la contraseña coincide con la asociada al email
                if(!Hash::check($data->password, $user->password)){
                    throw new Exception("Contraseña incorrecta");
                }                
                //crear token y guardarlo
                $allTokens = User::pluck('api_token')->toArray();
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

}
