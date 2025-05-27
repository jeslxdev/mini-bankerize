# MiniBankerize

> Sistema de Propostas de Empréstimo com API RESTful, painel administrativo Filament e arquitetura Hexagonal (Ports and Adapters).

> Projeto desenvolvido seguindo princípios SOLID, PSR-12 e arquitetura Hexagonal.

> Laravel Framework 12.15.0

> PHP 8.3.21 (cli) (built: May  6 2025 15:56:17) (NTS Visual C++ 2019 x64)

## Como clonar o projeto

```powershell
git clone https://github.com/seu-usuario/mini-bankerize.git
cd mini-bankerize/src
```

## Como instalar e rodar o projeto

1. **Suba os containers Docker:**
   ```powershell
   docker-compose up -d
   ```

2. **Instale as dependências PHP:**
   ```powershell
   docker exec -it bankerize_app composer install
   ```

3. **Rode as migrations:**
   ```powershell
   docker exec -it bankerize_app php artisan migrate:fresh
   ```

4. **Crie um usuário admin para o painel Filament:**
   ```powershell
   docker exec -it bankerize_app php artisan user:create-admin admin@teste.com senha123 "João Admin"
   ```

5. **Acesse o painel Filament:**
   - URL: http://localhost:8080/admin
   - Usuário: `admin@teste.com`
   - Senha: `senha123`

## Testando a API com múltiplas requisições (PowerShell)

Execute o comando abaixo no PowerShell para enviar 10 propostas diferentes:

```powershell
for ($i = 1; $i -le 10; $i++) {
    $cpf = "123123123" + "{0:D3}" -f $i
    $nome = "Fulano $i"
    $data_nascimento = "2024-10-10"
    $valor = [math]::Round((Get-Random -Minimum 1000 -Maximum 10000), 2)
    $chave_pix = "pix$i@example.com"

    $body = @{
        cpf = $cpf
        nome = $nome
        data_nascimento = $data_nascimento
        valor_emprestimo = $valor
        chave_pix = $chave_pix
    } | ConvertTo-Json

    Invoke-RestMethod -Uri 'http://127.0.0.1:8080/api/proposal' -Method Post -Body $body -ContentType 'application/json'
}
```

## Comandos úteis

- Rodar as migrations:
  ```powershell
  php artisan migrate:fresh
  ```
- Rodar os testes unit:
```powershell
php artisan test
```
- Criar usuário admin Filament:
  ```powershell
  php artisan user:create-admin admin@teste.com senha123 "João Admin"
  ```

---