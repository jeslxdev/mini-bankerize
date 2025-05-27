<?php

// Entidade de domínio Proposal.
// Representa uma proposta de empréstimo no sistema.
// Segue PSR-12 e expõe apenas os campos necessários para mass assignment.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proposal extends Model
{
    // Permite atribuição em massa dos campos de proposta
    protected $fillable = [
        'cpf',
        'nome',
        'data_nascimento',
        'valor_emprestimo',
        'chave_pix',
        'status_autorizacao',
    ];
}
