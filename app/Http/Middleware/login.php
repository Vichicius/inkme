<?php

namespace App\Http\Middleware;

use App\Models\Usuario;
use Closure;
use Exception;
use Illuminate\Http\Request;

class login
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $jsondata = $request->getContent();
        $data = json_decode($jsondata);

        $response["status"] = 1;

        try{
            $usuario = Usuario::where('api_token', $data->api_token)->first();

            if(!isset($usuario)){
                throw new Exception("Error: ese token no existe");
            }

            return $next($request);

        }catch(\Exception $e){
            $response["status"] = 0;
            $response["msg"] = $e->getMessage();
        }

        return response()->json($response);
    }
}
