<?php

namespace App\Http\Controllers;

use App\Models\Simulation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * @OA\Info(
 *     title="Api Go Sat",
 *     version="1.0.0",
 *     description="API desenvolvida durante teste prático",
 *
 *     @OA\Contact(
 *        name="Maria Baeta",
 *        url="https://https://github.com/mariabaeta27/api-go-sat",
 *     ),
 * )
 */
class SimulationController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/simulation",
     *     summary="Endpoint que retorna as simulações que estão salvas no banco",
     *
     *     @OA\Response(response="200", description="Lista de simulações"),
     *     @OA\Response(response="400", description="Mensagem de erro: Bad request"),
     * )
     */
    public function getSimulations()
    {
        try {
            $dataBD = Simulation::all();

            $simulations = collect($dataBD->map(function ($item) {
                $item['simulations'] = json_decode($item['simulations'], true);

                return $item;
            }));

            return response()->json(count($simulations) == 0 ? 'Não há simulações salvas no banco!' : $simulations, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Não foi possível retornar as simulações!'], 400);
        }

    }

    /**
     * @OA\Post(
     *     path="/api/simulation",
     *     summary="Endpoint responsável por fazer consulta nas API disponibilizadas e gerar uma simulações a partir dos dados fornecidos.",
     *
     *     @OA\RequestBody(
     *         required=true,
     *         description="Dados para a simulação: client(cpf), amount(valor solicitado), installments(quantidade de parcelas)",
     *
     *         @OA\JsonContent(
     *             required={"client", "amount", "installments"},
     *
     *             @OA\Property(property="client", type="string", example="123.123.123-12"),
     *             @OA\Property(property="amount", type="integer", example=16000),
     *             @OA\Property(property="installments", type="integer", example=19),
     *         ),
     *     ),
     *
     *     @OA\Response(response="201", description="Retorno da simulação salvo no banco!"),
     *     @OA\Response(response="400", description="Mensagem de erro: Bad request"),
     * )
     */
    public function simulationQuery(Request $request)
    {
        try {
            $bodyContent = $request->json()->all();
            $urlBase = env('URL_SIMULATION');

            $client = $bodyContent['client'];
            $amount = $bodyContent['amount'];
            $installments = $bodyContent['installments'];

            $newSimulation = new Simulation();
            $credits = $this->getCredits($client, $urlBase);

            $offers = [];

            foreach ($credits as $item) {
                foreach ($item['modalidades'] as $subItem) {
                    $responseOffer = $this->getOffers($client, $item['id'], $subItem['cod'], $urlBase);

                    $result = $this->generateSimulations($responseOffer->json(), $amount, $installments, $subItem['nome'], $item['nome']);

                    if (($result) !== 0) {
                        $offers[] = $result;
                    }
                }

            }

            $messageError = ['Não foi possível realizar uma simulação, pois o valor ou a quantidade de parcelas para empréstimo inserido não estão dentro do intervalo permitido.'];

            $responseSimulation = count($offers) == 0 ? $messageError : collect($offers)->sortBy('valorApagar')->values()->all();

            $result = ['valorSolicitado' => $amount, 'qntParcelas' => $installments, 'simulacoes' => $responseSimulation];

            $newSimulation->client = $client;
            $newSimulation->valueRequested = $amount;
            $newSimulation->numberInstallments = $installments;
            $newSimulation->simulations = json_encode($responseSimulation);

            $newSimulation->save();

            return response()->json($result, 201);
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Não foi possível criar as simulações!'.$th], 400);
        }
    }

    private function getCredits($cpf, $url)
    {

        $response = Http::post($url.'/credito', [
            'cpf' => $cpf,
        ]);

        return $response->json()['instituicoes'];
    }

    private function getOffers($cpf, $id, $cod, $url)
    {

        $response = Http::post($url.'/oferta', [
            'cpf' => $cpf,
            'instituicao_id' => $id,
            'codModalidade' => $cod,
        ]);

        return $response;

    }

    private function generateSimulations($responseOffer, $amount, $installments, $modadelidade, $instituicao)
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
            return ['valorApagar' => ($amount * $interest * $installments) + $amount, 'taxaJuros' => $interest, 'modalidadeCredito' => $modadelidade, 'instituicaoFinanceira' => $instituicao];
        }

    }
}
