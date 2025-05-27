<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    // Nome do comando
    protected $signature = 'user:create-admin {email} {password} {name=Admin}';

    // Descrição do comando
    protected $description = 'Cria um usuário administrador para acessar o painel Filament';

    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');
        $name = $this->argument('name');

        if (User::where('email', $email)->exists()) {
            $this->error("Usuário com o e-mail {$email} já existe.");
            return 1;
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        $this->info("Usuário admin criado com sucesso!");
        $this->info("Email: {$email}");
        $this->info("Senha: {$password}");

        return 0;
    }
}
