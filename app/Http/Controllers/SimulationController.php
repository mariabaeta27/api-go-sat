<?php

namespace App\Http\Controllers;

use App\Models\Simulation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SimulationController extends Controller
{
    // POST            api/simulation .................... simulation.store
    public function store(Request $request)
    {
        $bodyContent = $request->json()->all();

        $client = $bodyContent['client'];
        $amount = $bodyContent['amount'];
        $installments = $bodyContent['installments'];

        $newSimulation = new Simulation();
        $credits = $this->getCredits($client);

        $institutions = $credits['instituicoes'];

        $offers = collect(collect($institutions)->map(function ($item) use ($client, $amount, $installments) {
            $itemResults = [];
            foreach ($item['modalidades'] as $subItem) {
                $responseOffer = $this->getOffers($client, $item['id'], $subItem['cod']);

                $result = $this->getSimulations($responseOffer->json(), $amount, $installments);

                if ($result !== 0) {
                    $itemResults[] = (object) ['modalidadeCredito' => $subItem['nome'],  $result];
                }
            }

            if ($itemResults !== []) {
                return (object) ['instituicaoFinanceira' => $item['nome'],   $itemResults];
            }
        }))->filter(function ($offer) {
            return $offer !== null;
        })->sortBy(function ($offer) {
            return reset($offer);
        })->values();

        $messageError = 'O valor  ou a quantidade de parcelas para empréstimo inserido não estão dentro do intervalo permitido.';

        $result = (object) ['valorSolicitado' => $amount, 'qntParcelas' => $installments, 'simulação' => empty($offers) ? $messageError : $offers];

        return $result;

        //Salva os dados no banco
        // $newSimulation->client = $client;

        // $newSimulation->save();

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

    private function getSimulations($responseOffer, $amount, $installments)
    {

        $minInstallment = $responseOffer['QntParcelaMin'];
        $maxInstallment = $responseOffer['QntParcelaMax'];
        $minValue = $responseOffer['valorMin'];
        $maxValue = $responseOffer['valorMax'];
        $interest = $responseOffer['jurosMes'];

        if ($amount < $minValue || $amount > $maxValue) {
            return 0;
        } elseif ($installments < $minInstallment || $installments > $maxInstallment) {
            return 0;
        } else {
            return (object) ['valorApagar' => $amount * $interest * $installments, 'taxaJuros' => $interest];
        }

    }
}
