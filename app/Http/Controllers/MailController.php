<?php

namespace App\Http\Controllers;

use App\Mail\Encargo;
use App\Models\Usuario;
use App\Models\Cita;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;


class MailController extends Controller
{
    //
    public function enviarFormulario(Request $req){//Pide: usuario_id, nombre, comentario, telefono
        $jdata = $req->getContent();
        $data = json_decode($jdata);

        $validator = Validator::make(json_decode($jdata, true), [
            'usuario_id' => 'required|exists:usuarios,id',
            'date' => 'required|date',
            'nombre' => 'required|string|max:80',
            'comentario' => 'required|string|max:1000',
            'telefono' => 'required|numeric|min:100000000|max:999999999',
        ]);

        if ($validator->fails()) {
            throw new Exception($validator->errors()->first());
        }

        try {
            $response["status"] = 1;
            $user = Usuario::find($data->usuario_id);

            if(!isset($user)){
                throw new Exception("No se encuentra el usuario");
            }
            $cita = $this->crearCita($data);
            Mail::to($user->email)->send(new Encargo (
                $data->nombre,$data->comentario,$data->telefono, $cita->hash_identifier
            ));
            $response["msg"] = "Enviado con éxito";
        } catch(\Exception $e){
            $response["status"]=0;
            $response["msg"]='MailController '.$e->getMessage();
        }
        return response()->json($response);
    }

    private function crearCita(mixed $data){
        try{
            $cita = Cita::create([
                'user_id' => $data->usuario_id,
                'date' => $data->date,
                'client_tlf' => $data->telefono,
                'client_name' => $data->nombre,
                'comment' => $data->comentario,
                'hash_identifier' => Str::random(16)
            ]);
        }catch(\Exception $e){
            $response["status"]=0;
            $response["msg"]=$e->getMessage();
            return response()->json($response);
        }
        return $cita;
    }
}
