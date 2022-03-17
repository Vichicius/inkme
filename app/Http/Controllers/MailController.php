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
    public function enviarFormulario(Request $req){//Pide: usuario_id, nombre, comentario, telefono, date
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
            $cita = $this->crearCita($req);
            if(!isset($cita->hash_identifier)){
                throw new Exception("Error al crear la cita, el formato de la fecha es YYYY-MM-DD");
            }
            Mail::to($user->email)->send(new Encargo (
                $data->nombre,$data->comentario,$data->telefono, $cita->hash_identifier, $data->date
            ));
            $response["msg"] = "Enviado con éxito";
        } catch(\Exception $e){
            $response["status"]=0;
            $response["msg"]='MailController '.$e->getMessage();
        }
        return response()->json($response);
    }

    private function crearCita(Request $req){
        $jdata = $req->getContent();
        $data = json_decode($jdata);
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

    public function cargarCitasPendientes(Request $req){//Pide: api_token

        //coger usuario del req
        //buscar todas las citas con "pending"
        //mirar que ninguna se pasa de fecha  (cambiar a denied)
        //devolver las pending

        try {
            $response["status"] = 1;

            $user = $req->get('usuario');
            $citasPending = Cita::where('user_id', $user->id)->where('state', 'pending')->get();

            if(count($citasPending) == 0){
                throw new Exception("No tienes citas pendientes");
            }
            $arrayCitasFinal = [];
            //COMPARAR FECHAS Y DESACTIVAR LAS QUE SON DE AYER O MAS ANTIGUAS
            foreach ($citasPending as $key => $cita) {
                if($cita->date < date('Y-m-d')){ //si es anterior a hoy
                    $cita->state = 'denied';
                    $cita->save();
                }else{//si es hoy o en un futuro:
                    array_push($arrayCitasFinal, $cita);
                }
            }
            if(count($arrayCitasFinal) == 0){
                throw new Exception("No tienes citas pendientes");
            }

            $response["citas"] = $arrayCitasFinal;


        } catch(\Exception $e){
            $response["status"]=0;
            $response["msg"]='MailController '.$e->getMessage();
        }
        return response()->json($response);
    }

    public function cargarCitasActivas(Request $req){//Pide: api_token
        //coger usuario del req
        //buscar todas las citas con "active"
        //mirar que ninguna se pasa de fecha  (cambiar a denied)
        //devolver las active

        try {
            $response["status"] = 1;

            $user = $req->get('usuario');
            $citasActive = Cita::where('user_id', $user->id)->where('state', 'active')->get();

            if(count($citasActive) == 0){
                throw new Exception("No tienes citas activas");
            }

            $arrayCitasFinal = [];
            //COMPARAR FECHAS Y DESACTIVAR LAS QUE SON DE AYER O MAS ANTIGUAS
            foreach ($citasActive as $key => $cita) {
                if($cita->date < date('Y-m-d')){ //si es anterior a hoy
                    $cita->state = 'denied';
                    $cita->save();
                }else{//si es hoy o en un futuro:
                    array_push($arrayCitasFinal, $cita);
                }
            }
            if(count($arrayCitasFinal) == 0){
                throw new Exception("No tienes citas activas");
            }

            $response["citas"] = $arrayCitasFinal;


        } catch(\Exception $e){
            $response["status"]=0;
            $response["msg"]='MailController '.$e->getMessage();
        }
        return response()->json($response);
    }

    public function desactivarCita(Request $req){//Pide: api_token, cita_id
        $jdata = $req->getContent();
        $data = json_decode($jdata);

        $validator = Validator::make(json_decode($jdata, true), [
            'cita_id' => 'required|exists:citas,id',
        ]);

        if ($validator->fails()) {
            throw new Exception($validator->errors()->first());
        }

        try {
            $response["status"] = 1;
            $user = $req->get('usuario');

            $cita = Cita::where('id', $data->cita_id)->where('user_id', $user->id)->first();
            if(!isset($cita)){
                throw new Exception('Error: no existe esa cita');
            }

            $cita->state = 'denied';
            $cita->save();

            $response["msg"] = "Desactivada con éxito";
        } catch(\Exception $e){
            $response["status"]=0;
            $response["msg"]='MailController '.$e->getMessage();
        }
        return response()->json($response);
    }

    public function aceptarCita(Request $req){//Pide: api_token, cita_id
        $jdata = $req->getContent();
        $data = json_decode($jdata);

        $validator = Validator::make(json_decode($jdata, true), [
            'cita_id' => 'required|exists:citas,id',
        ]);

        if ($validator->fails()) {
            throw new Exception($validator->errors()->first());
        }

        try {
            $response["status"] = 1;
            $user = $req->get('usuario');

            $cita = Cita::where('id', $data->cita_id)->where('user_id', $user->id)->first();
            if(!isset($cita)){
                throw new Exception('Error: no existe esa cita');
            }

            $cita->state = 'active';
            $cita->save();

            $response["msg"] = "Desactivada con éxito";
        } catch(\Exception $e){
            $response["status"]=0;
            $response["msg"]='MailController '.$e->getMessage();
        }
        return response()->json($response);
    }
}
