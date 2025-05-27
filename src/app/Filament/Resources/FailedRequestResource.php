<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FailedRequestResource\Pages;
use App\Models\FailedRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;

class FailedRequestResource extends Resource
{
    protected static ?string $model = FailedRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-circle';
    protected static ?string $navigationGroup = 'Admin';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('endpoint')->required()->default('https://util.devi.tools/api/v2/authorize'),
                Forms\Components\Textarea::make('payload')
                    ->json()
                    ->rows(5)
                    ->default(json_encode([
                        'cpf' => '12312312312',
                        'nome' => 'Fulano de Tal',
                        'data_nascimento' => '2024-10-10',
                        'valor_emprestimo' => 1000.00,
                        'chave_pix' => 'teste@teste.com',
                    ], JSON_PRETTY_PRINT)),
                Forms\Components\TextInput::make('status')->required()->default('pending'),
                Forms\Components\TextInput::make('response_code')->numeric()->default(403),
                Forms\Components\TextInput::make('attempts')->numeric()->default(1),
                Forms\Components\TextInput::make('proposal_id')
                    ->numeric()
                    ->nullable()
                    ->default(null)
                    ->helperText('Deixe em branco ou preencha apenas com um ID de proposta existente.'),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('endpoint')->limit(20),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make(name: 'response_code'),
                Tables\Columns\TextColumn::make(name: 'attempts'),
                Tables\Columns\TextColumn::make(name: 'created_at')->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'retrying' => 'Retrying',
                        'failed' => 'Failed',
                        'completed' => 'Completed',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('Reenviar')
                    ->color('primary')
                    ->action(function (FailedRequest $record) {
                        $record->status = 'retrying';
                        $record->attempts = ($record->attempts ?? 0) + 1;
                        $record->save();

                        $payload = is_array($record->payload)
                            ? $record->payload
                            : json_decode($record->payload, true);

                        // Usa o HttpClientAdapter para fazer o GET no authorize
                        $httpClient = app(\App\Adapters\HttpClientAdapter::class);
                        $success = $httpClient->authorize($payload);

                        $record->response_code = $success ? 200 : 403;
                        if ($success) {
                            $record->status = 'completed';
                            $payload = is_array($record->payload)
                                ? $record->payload
                                : json_decode($record->payload, true);
                            if (isset($payload['cpf'])) {
                                $proposal = \App\Models\Proposal::where('cpf', $payload['cpf'])->first();
                                if ($proposal) {
                                    $proposal->update(['status_autorizacao' => 0]);
                                } else {
                                    \App\Models\Proposal::create(array_merge($payload, ['status_autorizacao' => 0]));
                                }
                            }
                            \Filament\Notifications\Notification::make()
                                ->title('Pix efetuada na chave pix: ' . ($payload['chave_pix'] ?? ''))
                                ->body('Valor do pix: R$ ' . number_format($payload['valor_emprestimo'] ?? 0, 2, ',', '.'))
                                ->success()
                                ->send();
                        } else {
                            $record->status = 'failed';
                        }
                        $record->save();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('Reenviar selecionados')
                    ->action(function ($records) {
                        foreach ($records as $record) {
                            dispatch(new \App\Jobs\ProcessFailedRequest($record));
                        }
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFailedRequests::route('/'),
        ];
    }
}
