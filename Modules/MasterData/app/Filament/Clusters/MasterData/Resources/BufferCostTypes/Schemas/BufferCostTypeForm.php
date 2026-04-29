<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\BufferCostTypes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BufferCostTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Buffer Cost Definition')
                    ->description('Define types of buffer costs used for contingency and risk management.')
                    ->schema([
                        TextInput::make('name')
                            ->label('Buffer Cost Name')
                            ->placeholder('e.g. Project Buffer, Operational Risk')
                            ->required()
                            ->maxLength(255)
                            ->helperText('The descriptive name of the buffer cost type.'),
                        TextInput::make('code')
                            ->label('Buffer Cost Code')
                            ->placeholder('e.g. BUF-PROJ, BUF-OPS')
                            ->required()
                            ->unique(\Modules\MasterData\Models\BufferCostType::class, 'code', ignoreRecord: true)
                            ->helperText('Unique short code for this buffer cost.'),
                    ])->columns(2),

                Section::make('Status & Defaults')
                    ->description('Manage the availability and default status of this buffer cost type.')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Active Status')
                            ->default(true)
                            ->helperText('Determines if this buffer cost type can be applied to new projects.'),
                        Toggle::make('is_default')
                            ->label('Default Buffer')
                            ->default(false)
                            ->helperText('Sets this as the pre-selected option for new buffer assignments.'),
                    ])->columns(2),
            ]);
    }
}
