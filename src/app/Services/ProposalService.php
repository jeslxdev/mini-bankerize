<?php

// Serviço de domínio responsável por toda a lógica de proposta.
// Aplica SOLID (SRP, DIP), utiliza porta HttpClientPort (Hexagonal) para dependências externas.
// Segue PSR-12 para organização e legibilidade.

namespace App\Services;

use App\Models\Proposal;
use App\Ports\HttpClientPort;

class ProposalService
{
    // Injeta a porta de comunicação HTTP (adapter externo)
    public function __construct(private HttpClientPort $httpClient) {}

    /**
     * Cria uma proposta, sempre retorna mensagem Pix (regra de negócio).
     * Se a autorização falhar, registra FailedRequest e status_autorizacao = 1.
     * Se sucesso, cria Proposal e notifica.
     *
     * @param array $data
     * @return array|Proposal
     */
    public function createProposal(array $data)
    {
        $result = $this->httpClient->authorize($data);  

        if (!$result) {
            $proposal = Proposal::firstOrCreate(
                [
                    'cpf' => $data['cpf'],
                ],
                array_merge($data, ['status_autorizacao' => 1])
            );
            if ($proposal->status_autorizacao != 1) {
                $proposal->update(['status_autorizacao' => 1]);
            }
            \App\Models\FailedRequest::create([
                'endpoint' => 'https://util.devi.tools/api/v2/authorize',
                'payload' => $data,
                'status' => 'pending',
                'response_code' => 403,
                'attempts' => 1,
                'proposal_id' => $proposal->id,
            ]);
            return [
                'pix_message' => 'Pix enviado para chave-pix ' . ($data['chave_pix'] ?? '') . '. valor do pix R$ ' . number_format($data['valor_emprestimo'] ?? 0, 2, ',', '.')
            ];
        }

        $proposal = Proposal::create($data);
        try {
            $this->httpClient->notify($proposal->toArray());
        } catch (\Throwable $e) {
            throw $e;
        }
        return $proposal;
    }
}
