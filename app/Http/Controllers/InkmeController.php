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

    public function register(Request $req){ //Pide: api_token, name, email, password, profile_picture y location
        $jdata = $req->getContent();
        $data = json_decode($jdata);

        if($data->name && $data->email && $data->password && $data->profile_picture && $data->location){
            try{
                $user = new Usuario;
                $user->name = $data->name;
                $user->email = $data->email;
                if(preg_match("/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{6,}/", $data->password)){
                    $user->password = Hash::make($data->password);
                }else{
                    $response["status"]=0;
                    $response["msg"]="Contraseña insegura. Mínimo: 1 Mayúscula, 1 minúscula, 1 caracter especial y 1 número";
                    return response()->json($response);
                }
                $user->profile_picture = $data->profile_picture;
                $user->location = $data->location;
                $user->views = 0;
                $user->save();
                $response["status"]=1;
                $response["msg"]="Guardado con éxito";
            }catch(\Exception $e){
                $response["status"]=0;
                $response["msg"]="Error al intentar guardar el usuario: ".$e;
            }
            
        }else{
            $response["status"]=0;
            $response["msg"]="introduce name, email, password, profile_picture y location";
        }

        
        return response()->json($response);
    }
}
