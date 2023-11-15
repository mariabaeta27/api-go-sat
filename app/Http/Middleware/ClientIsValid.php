<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ClientIsValid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $client = preg_replace('/[^0-9]/', '', $request->input('client'));

        if ($client === '11111111111' || $client === '12312312312' || $client === '22222222222') {
            return $next($request);
        }

        return response()->json('Pedimos desculpas, mas CPF informado não foi encontrado!');
    }
}
