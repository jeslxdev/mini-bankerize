<?php

// Entidade de domínio FailedRequest.
// Representa uma requisição de autorização que falhou e pode ser reprocessada.
// Segue PSR-12 e expõe apenas os campos necessários para mass assignment e casts.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FailedRequest extends Model
{
    // Permite atribuição em massa dos campos de requisição
    protected $fillable = [
        'endpoint',
        'payload',
        'status',
        'response_code',
        'proposal_id',
        'attempts',
    ];

    // Cast para garantir que payload seja sempre array
    protected $casts = [
        'payload' => 'array',
    ];
}
