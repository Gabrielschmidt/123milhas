<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResponseController extends Controller
{    
    public function retornaJson($codigo, $mensagem){
        try {
            $data['codigo'] = $codigo;
            $data['mensagem'] = $mensagem;

            return response()->json($data);
        } catch (\Throwable $th) {
            return $th;
        }
    }
}