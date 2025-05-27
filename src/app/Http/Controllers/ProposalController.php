<?php

// Controller responsável por receber requisições HTTP relacionadas a propostas.
// Aplica SRP (cada método tem uma responsabilidade), injeta ProposalService (DIP, Hexagonal).
// Segue PSR-12 e valida entrada conforme regras de negócio.

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ProposalService;

class ProposalController extends Controller
{
    // Injeta o serviço de domínio ProposalService
    public function __construct(private ProposalService $service) {}

    /**
     * Endpoint para criar uma nova proposta.
     * Sempre retorna mensagem Pix, independente do resultado da autorização.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'cpf' => 'required|string',
            'nome' => 'required|string',
            'data_nascimento' => 'required|date',
            'valor_emprestimo' => 'required|numeric',
            'chave_pix' => 'required|string',
        ]);

        $this->service->createProposal($validated);

        return response()->json([
            'pix_message' => 'Pix enviado para chave-pix ' . ($validated['chave_pix'] ?? '') . '. valor do pix R$ ' . number_format($validated['valor_emprestimo'] ?? 0, 2, ',', '.')
        ], 201);
    }
}
