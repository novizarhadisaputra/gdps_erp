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
                Section::make(__('Buffer Cost Definition'))
                    ->description(__('Define types of buffer costs used for contingency and risk management.'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('Buffer Cost Name'))
                            ->placeholder(__('e.g. Project Buffer, Operational Risk'))
                            ->required()
                            ->maxLength(255)
                            ->helperText(__('The descriptive name of the buffer cost type.')),
                        TextInput::make('code')
                            ->label(__('Buffer Cost Code'))
                            ->placeholder(__('e.g. BUF-PROJ, BUF-OPS'))
                            ->required()
                            ->unique(\Modules\MasterData\Models\BufferCostType::class, 'code', ignoreRecord: true)
                            ->helperText(__('Unique short code for this buffer cost.')),
                    ])->columns(2),

                Section::make(__('Status & Defaults'))
                    ->description(__('Manage the availability and default status of this buffer cost type.'))
                    ->schema([
                        Toggle::make('is_active')
                            ->label(__('Active Status'))
                            ->default(true)
                            ->helperText(__('Determines if this buffer cost type can be applied to new projects.')),
                        Toggle::make('is_default')
                            ->label(__('Default Buffer'))
                            ->default(false)
                            ->helperText(__('Sets this as the pre-selected option for new buffer assignments.')),
                    ])->columns(2),
            ]);
    }
}
