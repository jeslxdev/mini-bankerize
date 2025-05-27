<?php

namespace Tests\Unit\Services;

use App\Models\Proposal;
use App\Services\ProposalService;
use App\Ports\HttpClientPort;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class ProposalServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_proposal_successful_flow()
    {
        $mockHttp = Mockery::mock(HttpClientPort::class);
        $mockHttp->shouldReceive('authorize')->once()->andReturn(true);
        $mockHttp->shouldReceive('notify')->once()->andReturn(true);

        $service = new ProposalService($mockHttp);

        $data = [
            'cpf' => '12345678901',
            'nome' => 'João Teste',
            'data_nascimento' => '2000-01-01',
            'valor_emprestimo' => 1000.00,
            'chave_pix' => 'joao@pix.com',
        ];

        $proposal = $service->createProposal($data);

        $this->assertInstanceOf(Proposal::class, $proposal);
        $this->assertEquals('12345678901', $proposal->cpf);
    }

    public function test_create_proposal_authorize_fails()
    {
        $mockHttp = Mockery::mock(HttpClientPort::class);
        $mockHttp->shouldReceive('authorize')->once()->andThrow(new \Exception('Erro na autorização'));
        $mockHttp->shouldNotReceive('notify');

        $service = new ProposalService($mockHttp);

        $data = [
            'cpf' => '12345678901',
            'nome' => 'João Teste',
            'data_nascimento' => '2000-01-01',
            'valor_emprestimo' => 1000.00,
            'chave_pix' => 'joao@pix.com',
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Erro na autorização');
        $service->createProposal($data);
    }

    public function test_create_proposal_notify_fails()
    {
        $mockHttp = Mockery::mock(HttpClientPort::class);
        $mockHttp->shouldReceive('authorize')->once()->andReturn(true);
        $mockHttp->shouldReceive('notify')->once()->andThrow(new \Exception('Erro na notificação'));

        $service = new ProposalService($mockHttp);

        $data = [
            'cpf' => '12345678901',
            'nome' => 'João Teste',
            'data_nascimento' => '2000-01-01',
            'valor_emprestimo' => 1000.00,
            'chave_pix' => 'joao@pix.com',
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Erro na notificação');
        $service->createProposal($data);
    }
}
