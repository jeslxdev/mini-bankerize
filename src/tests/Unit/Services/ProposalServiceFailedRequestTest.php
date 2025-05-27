<?php

namespace Tests\Unit\Services;

use App\Models\FailedRequest;
use App\Models\Proposal;
use App\Services\ProposalService;
use App\Ports\HttpClientPort;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class ProposalServiceFailedRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_failed_request_is_registered_on_authorize_error()
    {
        $mockHttp = Mockery::mock(HttpClientPort::class);
        $mockHttp->shouldReceive('authorize')->once()->andReturnUsing(function($data) {
            FailedRequest::create([
                'endpoint' => 'https://util.devi.tools/api/v2/authorize',
                'payload' => $data,
                'status' => 'pending',
                'response_code' => 403,
                'attempts' => 1,
            ]);
            throw new \Exception('Erro na autorização');
        });
        $mockHttp->shouldNotReceive('notify');

        $service = new ProposalService($mockHttp);

        $data = [
            'cpf' => '12345678901',
            'nome' => 'João Teste',
            'data_nascimento' => '2000-01-01',
            'valor_emprestimo' => 1000.00,
            'chave_pix' => 'joao@pix.com',
        ];

        try {
            $service->createProposal($data);
        } catch (\Exception $e) {
            // continue
        }

        $this->assertDatabaseHas('failed_requests', [
            'endpoint' => 'https://util.devi.tools/api/v2/authorize',
            'status' => 'pending',
            'response_code' => 403,
        ]);
    }

    public function test_failed_request_is_registered_on_notify_error()
    {
        $mockHttp = Mockery::mock(HttpClientPort::class);
        $mockHttp->shouldReceive('authorize')->once()->andReturn(true);
        $mockHttp->shouldReceive('notify')->once()->andReturnUsing(function($data) {
            FailedRequest::create([
                'endpoint' => 'https://util.devi.tools/api/v1/notify',
                'payload' => $data,
                'status' => 'pending',
                'response_code' => 403,
                'attempts' => 1,
            ]);
            throw new \Exception('Erro na notificação');
        });

        $service = new ProposalService($mockHttp);

        $data = [
            'cpf' => '12345678901',
            'nome' => 'João Teste',
            'data_nascimento' => '2000-01-01',
            'valor_emprestimo' => 1000.00,
            'chave_pix' => 'joao@pix.com',
        ];

        try {
            $service->createProposal($data);
        } catch (\Exception $e) {
            // continue
        }

        $this->assertDatabaseHas('failed_requests', [
            'endpoint' => 'https://util.devi.tools/api/v1/notify',
            'status' => 'pending',
            'response_code' => 403,
        ]);
    }
}
