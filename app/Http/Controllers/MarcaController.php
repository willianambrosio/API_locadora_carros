<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use App\Models\Marca;
use Illuminate\Http\Request;
use App\Repositories\MarcaRepository;

class MarcaController extends Controller
{
    public function __construct(Marca $marca)
    {
        $this->marca = $marca;
    }
    
    /*****************************************************/ 
    public function index(Request $request)
    {

        $marcaRepository = new MarcaRepository($this->marca);

        if($request->has('atributos_modelos')) {
            $atributos_modelos = 'modelos:id,'.$request->atributos_modelos;        
            $marcaRepository->selectAtributosRegistrosRelacionados($atributos_modelos);
        }else{
            $marcaRepository->selectAtributosRegistrosRelacionados('modelos');
        }

        if($request->has('filtro')){
            $marcaRepository->filtro($request->filtro);
        }

        if($request->has('atributos')) {
            $marcaRepository->selectAtributos($request->atributos);      
        } 
        
        return response()->json($marcaRepository->getResultado(), 201);
    }
    
    
    /*****************************************************/ 
    public function store(Request $request)
    {
        $request->validate($this->marca->rules(), $this->marca->feedback());
        $imagem = $request->file('imagem');
        //salva a imagem no local pre-definido
        $imagem_urn = $imagem->store('imagens', 'public');
        
        //salva o nome da imagem no banco
        $marca = $this->marca->create([
            'nome' => $request->nome,
            'imagem' => $imagem_urn
        ]);
        
        return response()->json($marca, 200);
    }
    
    /*****************************************************/ 
    public function show($id)
    {
        $marca = $this->marca->with('modelos')->find($id);
        if($marca === null){
            return response()->json(['erro' => 'Recurso pesquisado nao existe'], 404);
        }
        return response()->json($marca, 200);
    }
    /*****************************************************/ 
    public function update(Request $request, $id)
    {
        // print_r($request->all());
        // echo '<hr>';
        // print_r($marca->getAttributes());
        // $marca->update($request->all());

        $marca = $this->marca->find($id);
        if($marca === null){
            return response()->json(['erro' => 'Impossivel realizar a atualizacao. O recurso solicitado nao existe'], 404);
        }

        if($request->method() === 'PATCH') {

            $regrasDinamicas = array();

            foreach($marca->rules() as $input => $regra) {

                if(array_key_exists($input, $request->all())) {
                    $regrasDinamicas[$input] = $regra;
                }
            }

            $request->validate($regrasDinamicas, $marca->feedback());
        }else {
            $request->validate($marca->rules(), $marca->feedback());
        }

        //remove o arquivo antigo caso um novo arquivo tenha sido enviado no request
        if($request->file('imagem')) {
            Storage::disk('public')->delete($marca->imagem);
        }

        $imagem = $request->file('imagem');
        $imagem_urn = $imagem->store('imagens', 'public');
        
        //preencher o objeto $marca com os dados do request
        $marca->fill($request->all());
        $marca->imagem = $imagem_urn;
        // dd($marca->getAttributes());
        $marca->imagem = $imagem_urn;
        // $marca->update([
        //     'nome' => $request->nome,
        //     'imagem' => $imagem_urn
        // ]);
        return response()->json($marca, 200);
    }

    /*****************************************************/ 
    public function destroy($id)
    {
        $marca = $this->marca->find($id);

        if($marca === null){
            return response()->json(['erro' => 'Impossivel realizar a exclusao. O recurso solicitado nao existe'], 404);
        }

        //remove o arquivo antigo caso um novo arquivo tenha sido enviado no request
        Storage::disk('public')->delete($marca->imagem);
        

        $marca->delete();
        return response()->json(['msg' => 'A marca foi removida com sucesso'], 200);
    }
}
