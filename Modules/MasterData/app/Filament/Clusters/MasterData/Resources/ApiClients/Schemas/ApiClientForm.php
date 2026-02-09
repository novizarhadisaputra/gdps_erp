<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\ApiClients\Schemas;

use App\Models\ApiClient;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ApiClientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Client Identity')
                    ->description('External system identification details.')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g. HR System'),

                        TextInput::make('client_id')
                            ->label('Client ID')
                            ->default(fn () => 'client_'.Str::random(16))
                            ->readonly()
                            ->required()
                            ->unique(ApiClient::class, 'client_id', ignoreRecord: true),

                        TextInput::make('client_secret')
                            ->label('Client Secret')
                            ->password()
                            ->required()
                            ->visibleOn('create')
                            ->default(fn () => Str::random(32))
                            ->helperText('Copy this secret now. It will not be shown again.')
                            ->hintAction(
                                Action::make('copy')
                                    ->icon('heroicon-m-clipboard')
                                    ->action(function ($state, $component) {
                                        // This is handled via JS usually or just UI side
                                    })
                            ),

                        Toggle::make('is_active')
                            ->label('Active Status')
                            ->default(true)
                            ->required(),
                    ]),
            ]);
    }
}
