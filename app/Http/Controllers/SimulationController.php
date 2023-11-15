<?php

namespace App\Http\Controllers;

use App\Models\Simulation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

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
        $client = $request->input('client');
        $simulationDB = DB::select('SELECT * from simulations where client = ?', [$client]);

        // return $simulationDB;

        if (empty($simulation)) {
            $newSimulation = new Simulation();
            $credits = $this->getCredits($client);

            $institutions = $credits['instituicoes'];

            $offers = collect($institutions)->map(function ($item) use ($client) {
                $itemResults = [];
                foreach ($item['modalidades'] as $subItem) {
                    $responseOffer = $this->getOffers($client, $item['id'], $subItem['cod']);

                    $itemResults[$subItem['cod']] = $responseOffer->json();
                }

                return ['id' => $item['id'],  'offers' => $itemResults];
            });

            // return $credits;

            //Salva os dados no banco
            $newSimulation->client = $client;
            $newSimulation->simulationsCredits = json_encode($credits);
            $newSimulation->simulationsOffers = json_encode($offers);

            $newSimulation->save();

            return response()->json('Dados salvos com sucesso!');

        } else {
            return response()->json('Os dados já estão salvos!');
        }

    }

    private function getCredits($cpf)
    {
        $response = Http::post('https://dev.gosat.org/api/v1/simulacao/credito', [
            'cpf' => $cpf,
        ]);

        return $response->json();
    }

    private function getOffers($cpf, $id, $cod)
    {

        $response = Http::post('https://dev.gosat.org/api/v1/simulacao/oferta', [
            'cpf' => $cpf,
            'instituicao_id' => $id,
            'codModalidade' => $cod,
        ]);

        return $response;

    }

    // GET|HEAD        api/simulation/{simulation} ......... simulation.show 1.
    public function show(Simulation $simulation)
    {
        return response()->json([
            'simulation' => $simulation,
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
            'client' => 'required',
            'simulationsCredit' => 'required',
            'simulationsOffer' => 'required',
        ]);

        try {

            $product->fill($request->post())->update();
            $product->save();

            return response()->json([
                'message' => 'Simulation Updated Successfully!!',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Something goes wrong when updating a simulation!!!',
            ], 500);
        }
    }

    //  DELETE          api/simulation/{simulation} ... simulation.destroy
    public function destroy(Simulation $simulation)
    {
        try {
            $simulation->delete();

            return response()->json([
                'message' => 'Simulation Deleted Successfully!!',
            ]);
        } catch (\Throwable $th) {
            \Log::error($th->getMessage());

            return response()->json([
                'message' => 'Something goes wrong while deleting a simulation!!',
            ]);
        }
    }
}
