<?php

namespace App\Http\Controllers;

use App\Models\Carro;
use Illuminate\Http\Request;
use App\Repositories\CarroRepository;

class CarroController extends Controller
{


    public function __construct(Carro $carro)
    {
        $this->carro = $carro;
    }



    public function index(Request $request)
    {
        $carroRepository = new CarroRepository($this->carro);

        if($request->has('atributos_modelo')) {
            $atributos_modelo = 'modelo:id,'.$request->atributos_modelo;        
            $carroRepository->selectAtributosRegistrosRelacionados($atributos_modelo);
        }else{
            $carroRepository->selectAtributosRegistrosRelacionados('modelo');
        }

        if($request->has('filtro')){
            $carroRepository->filtro($request->filtro);
        }

        if($request->has('atributos')) {
            $carroRepository->selectAtributos($request->atributos);
        } 

        return response()->json($carroRepository->getResultado(), 201);
    }



    public function store(Request $request)
    {
        $request->validate($this->carro->rules());
       
        $carro = $this->carro->create([
            'modelo_id' => $request->modelo_id,
            'placa' => $request->placa,
            'disponivel' => $request->disponivel,
            'km' => $request->km,
        ]);
        
        return response()->json($carro, 200);
    }



    public function show($id)
    {
        $carro = $this->carro->with('modelo')->find($id);
        if($carro === null){
            return response()->json(['erro' => 'Recurso pesquisado nao existe'], 404);
        }
        return response()->json($carro, 200);
    }


    public function update(Request $request, $id)
    {
        $carro = $this->carro->find($id);
        if($carro === null){
            return response()->json(['erro' => 'Impossivel realizar a atualizacao. O recurso solicitado nao existe'], 404);
        }

        if($request->method() === 'PATCH') {

            $regrasDinamicas = array();

            foreach($carro->rules() as $input => $regra) {

                if(array_key_exists($input, $request->all())) {
                    $regrasDinamicas[$input] = $regra;
                }
            }

            $request->validate($regrasDinamicas);
        }else {
            $request->validate($carro->rules());
        }
       
        $carro->fill($request->all());
        $carro->save();
        return response()->json($carro, 200);
    }


    public function destroy($id)
    {
        $carro = $this->carro->find($id);

        if($carro === null){
            return response()->json(['erro' => 'Impossivel realizar a exclusao. O recurso solicitado nao existe'], 404);
        }

        $carro->delete();
        return response()->json(['msg' => 'O carro foi removido com sucesso'], 200);
    }

}
