<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\TaxSchemes;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\TaxSchemes\Pages\CreateTaxScheme;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\TaxSchemes\Pages\EditTaxScheme;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\TaxSchemes\Pages\ListTaxSchemes;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\TaxSchemes\Schemas\TaxSchemeForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\TaxSchemes\Tables\TaxSchemesTable;
use Modules\MasterData\Models\TaxScheme;

class TaxSchemeResource extends Resource
{
    protected static ?string $model = TaxScheme::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = MasterDataCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return TaxSchemeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TaxSchemesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTaxSchemes::route('/'),
            'create' => CreateTaxScheme::route('/create'),
            'edit' => EditTaxScheme::route('/{record}/edit'),
        ];
    }
}
