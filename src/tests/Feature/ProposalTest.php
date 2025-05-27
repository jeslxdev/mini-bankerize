<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProposalTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_proposal()
    {
        $response = $this->postJson('/api/proposal', [
            'cpf' => '12345678900',
            'nome' => 'Fulano de Tal',
            'data_nascimento' => '1990-01-01',
            'valor_emprestimo' => 1000.00,
            'chave_pix' => 'teste@teste.com'
        ]);

        if ($response->status() !== 201) {
            echo $response->getContent();
        }

        $response->assertStatus(201);

        $this->assertDatabaseHas('proposals', ['cpf' => '12345678900']);
    }
}
