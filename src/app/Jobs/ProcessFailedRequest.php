<?php

// Job responsável por reprocessar requisições de autorização que falharam.
// Utiliza o padrão Ports and Adapters para acessar Proposal (domínio) e HTTP (infraestrutura).
// Aplica princípios SOLID: responsabilidade única (reprocessar uma requisição), injeção de dependência (FailedRequest).

namespace App\Jobs;

use App\Models\FailedRequest;
use App\Models\Proposal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Http;

class ProcessFailedRequest implements ShouldQueue
{
    use Queueable;

    // Recebe a entidade de domínio FailedRequest (porta de entrada)
    public function __construct(public FailedRequest $failedRequest) {}

    /**
     * Executa o reprocessamento da requisição.
     * Atualiza o status da FailedRequest e da Proposal conforme o resultado.
     * Cria a Proposal se não existir, garantindo idempotência.
     */
    public function handle(): void
    {
        $this->failedRequest->increment('attempts');
        $response = Http::post($this->failedRequest->endpoint, $this->failedRequest->payload);

        $this->failedRequest->response_code = $response->status();

        if ($response->status() === 201) {
            $this->failedRequest->status = 'completed';
            $payload = is_array($this->failedRequest->payload)
                ? $this->failedRequest->payload
                : json_decode($this->failedRequest->payload, true);
            if (isset($payload['cpf'])) {
                $proposal = Proposal::where('cpf', $payload['cpf'])->first();
                if ($proposal) {
                    $proposal->update(['status_autorizacao' => 0]);
                } else {
                    Proposal::create(array_merge($payload, ['status_autorizacao' => 0]));
                }
            }
        } elseif ($this->failedRequest->attempts >= 5) {
            $this->failedRequest->status = 'failed';
        } else {
            $this->failedRequest->status = 'retrying';
            dispatch(new self($this->failedRequest))->delay(now()->addSeconds(30));
        }

        $this->failedRequest->save();
    }
}

