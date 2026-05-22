<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class JogoController extends Controller
{
    private $tentativasMaximas = 6;

    public function iniciarJogo()
    {
        $palavras = file(storage_path('app/dicionario.txt'), FILE_IGNORE_NEW_LINES);

        $palavraSecreta = $palavras[array_rand($palavras)];

        $idJogo = Str::uuid()->toString();

        cache()->put($idJogo, [
            'palavra' => $palavraSecreta,
            'tentativas' => 0
        ], now()->addHours(1));

        return response()->json([
            'idJogo' => $idJogo,
            'tamanhoPalavra' => 5,
            'tentativasMaximas' => $this->tentativasMaximas
        ]);
    }

    public function validarTentativa(Request $request)
    {
        $request->validate([
            'idJogo' => 'required',
            'palavra' => 'required|string|size:5'
        ]);

        $jogo = cache()->get($request->idJogo);

        if (!$jogo) {
            return response()->json([
                'erro' => 'Jogo não encontrado'
            ], 404);
        }

        $palavra = strtolower($request->palavra);

        $dicionario = file(storage_path('app/dicionario.txt'), FILE_IGNORE_NEW_LINES);

        if (!in_array($palavra, $dicionario)) {

            return response()->json([
                'resultado' => [],
                'venceu' => false,
                'tentativasRestantes' => 6 - $jogo['tentativas'],
                'palavraValida' => false
            ]);
        }

        $palavraSecreta = $jogo['palavra'];

        $resultado = [];

        for ($i = 0; $i < 5; $i++) {

            if ($palavra[$i] == $palavraSecreta[$i]) {

                $status = 'correta';

            } elseif (str_contains($palavraSecreta, $palavra[$i])) {

                $status = 'presente';

            } else {

                $status = 'ausente';
            }

            $resultado[] = [
                'letra' => $palavra[$i],
                'status' => $status
            ];
        }

        $jogo['tentativas']++;

        cache()->put($request->idJogo, $jogo, now()->addHours(1));

        return response()->json([
            'resultado' => $resultado,
            'venceu' => $palavra == $palavraSecreta,
            'tentativasRestantes' => 6 - $jogo['tentativas'],
            'palavraValida' => true
        ]);
    }
}