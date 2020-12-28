<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FlightController extends Controller
{    
    public function listarDadosDeVoo(){
        try {
            $json_file = file_get_contents("http://prova.123milhas.net/api/flights");
            $voos = json_decode($json_file, true);

            return app(ResponseController::class)->retornaJson(200, $voos);
        } catch (\Throwable $th) {
            return $th;
        }
    }

    public function listarDadosDeVooAgrupado(){
        try {
            $grupos = array();
            $ida = [];
            $volta = [];
            $preco_ida = [];
            $preco_volta = [];

            $grupos_ordenados = [];
            $ida_ordenada = [];
            $volta_ordenada = [];
            $id_group = 0;

            $json_return = [];

            $json_file = file_get_contents("http://prova.123milhas.net/api/flights");
            $voos = json_decode($json_file, true);

            if($voos){
                //separa os voos pela 'fare'
                foreach ($voos as $voo){
                    $grupos[$voo['fare']][] = $voo;
                } 
                
                foreach ($grupos as $key => $grupo){
                    //separa os voos por grupos de ida e volta
                    foreach($grupo as $item){
                        if ($item['outbound'] == 1) {
                            $ida[] = $item;
                        }else if($item['inbound'] == 1){
                            $volta[] = $item;
                        }
                    }
                    
                    //separa os precos dos voos de ida
                    foreach($ida as $preco){
                        $preco_ida[$preco['price']][] = $preco;
                    }

                    //separa os precos dos voos de volta
                    foreach($volta as $preco){
                        $preco_volta[$preco['price']][] = $preco;
                    }

                    //atribui os precos no vetor "grupos"
                    $grupos[$key]['ida'] = $preco_ida;
                    $grupos[$key]['volta'] = $preco_volta;
                    $ida = [];
                    $volta = [];
                    $preco_ida = [];
                    $preco_volta = [];
                }

                //monta os grupos 
                foreach($grupos as $grupo){
                    foreach($grupo["ida"] as $item_ida){
                        foreach($grupo["volta"] as $item_volta){
                            for ($i = 0; $i <= max(count($item_ida), count($item_volta)) - 1; $i++) { 
                                if (array_key_exists($i, $item_ida)) {
                                    $ida_ordenada[] = $item_ida[$i]["id"];
                                }
                                if (array_key_exists($i, $item_volta)) {
                                    $volta_ordenada[] = $item_volta[$i]["id"]; 
                                }
                            }
                            
                            $grupos_ordenados[] = array(
                                "uniqueId" => $id_group++,
                                "total_price" => $item_ida[0]['price'] + $item_volta[0]['price'],
                                "outbound" => $ida_ordenada,
                                "inbound" =>  $volta_ordenada
                            );
                            
                            $ida_ordenada = [];
                            $volta_ordenada = [];
                        }
                    }
                }
                
                //ordena os grupos em ordem crescente pelo preço
                foreach ($grupos_ordenados as $key => $row) {
                    $preco_grupo_ordenado[$key]  = $row['total_price'];;
                }

                array_multisort($preco_grupo_ordenado, SORT_ASC, $grupos_ordenados);

                //define o array de retorno da api
                $json_return["flights"] = $voos;
                $json_return["groups"] = $grupos_ordenados;
                $json_return["totalGroups"] = count($grupos_ordenados);
                $json_return["totalFlights"] = count($voos);
                $json_return["cheapestPrice"] = $grupos_ordenados[0]["total_price"];
                $json_return["cheapestGroup"] = $grupos_ordenados[0]["uniqueId"];

                return app(ResponseController::class)->retornaJson(200, $json_return);
            }else{
                return app(ResponseController::class)->retornaJson(401, "Não foi possível encontrar dados na API da 123 milhas");
            }
        } catch (\Throwable $th) {
            return $th;
        }
    }
}