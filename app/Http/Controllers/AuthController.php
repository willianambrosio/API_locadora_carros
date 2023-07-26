<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(Request $request) {

        $credenciais = $request->all(['email','password']);

        //autenticacao (email e senha)
        $token = auth('api')->attempt($credenciais);

        if($token) { //usuario autenticado com sucesso
            return response()->json(['token' => $token]);
        }else{
            return response()->json(['erro' => 'usuario ou senha invalido!'], 403);

        }


        return 'Login';
    }

    public function logout() {
        auth('api')->logout();
        return response()->json(['msg' => 'logout foi realizado com sucesso!']);
    }

    public function refresh() {
        $token = auth('api')->refresh(); //cliente encaminhe um jwt valido
        return response()->json(['token' => $token]);
    }

    public function me() {
        return response()->json(auth()->user());
        
    }
}
