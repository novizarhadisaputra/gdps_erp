<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\ProductClusters\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Modules\MasterData\Models\ProductCluster;

class ProductClusterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(static::schema());
    }

    public static function schema(): array
    {
        return [
            TextInput::make('code')
                ->required()
                ->unique(ProductCluster::class, 'code', ignoreRecord: true)
                ->placeholder('PC001'),
            TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->placeholder('Software Development'),
            Toggle::make('is_active')
                ->default(true)
                ->required(),
        ];
    }
}
