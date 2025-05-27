<?php

// Adapter HTTP responsável por comunicação externa (porta de saída).
// Implementa HttpClientPort (Hexagonal), isolando dependências externas do domínio.
// Segue PSR-12 e aplica SRP (cada método tem uma responsabilidade).

namespace App\Adapters;

use Illuminate\Support\Facades\Http;
use App\Models\FailedRequest;
use App\Models\Proposal;
use App\Ports\HttpClientPort;

class HttpClientAdapter implements HttpClientPort
{
    /**
     * Envia requisição POST e retorna o response do endpoint externo.
     * O retry é responsabilidade do Filament.
     *
     * @param string $endpoint
     * @param array $data
     * @param Proposal|null $proposal
     * @return \Illuminate\Http\Client\Response
     */
    public function postWithRetry(string $endpoint, array $data, ?Proposal $proposal = null)
    {
        $response = Http::get($endpoint, $data);
        return $response;
    }

    /**
     * Realiza autorização via endpoint externo.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        $response = Http::withOptions([
            'verify' => false,
        ])->get('https://util.devi.tools/api/v2/authorize');
        return $response->successful();
    }

    /**
     * Notifica endpoint externo sobre proposta aprovada.
     *
     * @return bool
     */
    public function notify(): bool
    {
        $response = Http::withOptions([
            'verify' => false,
        ])->get('https://util.devi.tools/api/v1/notify');
        return $response->successful();
    }
}
