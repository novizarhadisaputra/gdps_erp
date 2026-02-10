<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\ApiClients\Schemas;

use App\Models\ApiClient;
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
                            ->placeholder('e.g. HR System')
                            ->helperText('A descriptive name for identifying this external system or integration.'),

                        TextInput::make('client_id')
                            ->label('Client ID')
                            ->default(fn () => 'client_'.Str::random(16))
                            ->readonly()
                            ->required()
                            ->unique(ApiClient::class, 'client_id', ignoreRecord: true)
                            ->helperText('The unique identifier for this client. This is automatically generated.'),

                        TextInput::make('client_secret')
                            ->label('Client Secret')
                            ->password()
                            ->required()
                            ->copyable()
                            ->visibleOn('create')
                            ->default(fn () => Str::random(32))
                            ->helperText('Copy this secret now. It will not be shown again.'),
                        Toggle::make('is_active')
                            ->label('Active Status')
                            ->default(true)
                            ->required()
                            ->helperText('Enable or disable this client\'s access to the API.'),
                    ]),
            ]);
    }
}
