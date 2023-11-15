<?php

namespace App\Http\Controllers;

use App\Models\Simulation;
use Illuminate\Http\Request;


use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;


class SimulationController extends Controller
{



    //GET|HEAD        api/simulation .................... simulation.index
    public function index()
    {
       return Simulation::select('id', 'client', 'simulationsCredit', 'simulationsOffer')->get();
    }


    // GET|HEAD        api/simulation/create ........... simulation.create ›
    public function create()
    {
        //
    }

    // POST            api/simulation .................... simulation.store
  public function store(Request $request)
    {

        //Consulte as ofertas de crédito

        $client = $request->input('client');
        $response = Http::post("https://dev.gosat.org/api/v1/simulacao/credito", [
          "cpf"=> $client
        ]);

        // Simule as ofertas de crédito

        $simulation = $response['instituicoes'];


        $offers =collect($simulation)->map(function($item) use ($client){
            $itemResults=[];
            foreach($item['modalidades'] as $subItem) {
                $responseOffer = Http::post("https://dev.gosat.org/api/v1/simulacao/oferta", [
                "cpf"=> $client,
                "instituicao_id" =>$item['id'],
                "codModalidade"=>  $subItem['cod']
                ]);
                $itemResults[$subItem['cod']]=['resultData' => $responseOffer->json() ];
            };

            return ['id' =>$item['id'],  'offer'=> $itemResults];
        });

        return $offers;








}

    // GET|HEAD        api/simulation/{simulation} ......... simulation.show 1.
    public function show(Simulation $simulation)
    {
        return response()->json([
            'simulation'=>$simulation
        ]);
    }

    // |HEAD        api/simulation/{simulation}/edit .... simulation.edit
    public function edit(Simulation $simulation)
    {

    }

    // PUT|PATCH       api/simulation/{simulation} ..... simulation.update
    public function update(Request $request, Simulation $simulation)
    {
        $request->validate([
            'client'=>'required',
        'simulationsCredit'=>'required',
        'simulationsOffer' =>'required'
        ]);

        try {

            $product->fill($request->post())->update();
            $product->save();
            return response()->json([
                'message'=>'Simulation Updated Successfully!!'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message'=>'Something goes wrong when updating a simulation!!!'
            ],500);
        }
    }

  //  DELETE          api/simulation/{simulation} ... simulation.destroy
    public function destroy(Simulation $simulation)
    {
        try {
            $simulation->delete();
            return response()->json([
                'message'=>'Simulation Deleted Successfully!!'
            ]);
        } catch (\Throwable $th) {
            \Log::error($th->getMessage());
            return response()->json([
                'message'=>'Something goes wrong while deleting a simulation!!'
            ]);
        }
    }
}
