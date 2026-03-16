<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders\Resources\Amendment\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Modules\CRM\Enums\SalesOrderAmendmentStatus;

class AmendmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('amendment_number')
                    ->required()
                    ->maxLength(255)
                    ->disabled()
                    ->dehydrated(false),
                Textarea::make('reason')
                    ->required()
                    ->columnSpanFull()
                    ->disabled()
                    ->dehydrated(false),
                Select::make('status')
                    ->options(SalesOrderAmendmentStatus::class)
                    ->required()
                    ->disabled()
                    ->dehydrated(false),
                KeyValue::make('snapshot')
                    ->label('Data Snapshot')
                    ->columnSpanFull()
                    ->disabled()
                    ->dehydrated(false),
            ]);
    }
}
