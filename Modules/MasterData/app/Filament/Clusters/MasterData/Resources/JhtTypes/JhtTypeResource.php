<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\JhtTypes;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JhtTypes\Pages\CreateJhtType;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JhtTypes\Pages\EditJhtType;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JhtTypes\Pages\ListJhtTypes;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JhtTypes\Schemas\JhtTypeForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JhtTypes\Tables\JhtTypesTable;
use Modules\MasterData\Models\JhtType;

class JhtTypeResource extends Resource
{
    protected static ?string $model = JhtType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = MasterDataCluster::class;

    public static function form(Schema $schema): Schema
    {
        return JhtTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return JhtTypesTable::configure($table);
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
            'index' => ListJhtTypes::route('/'),
            'create' => CreateJhtType::route('/create'),
            'edit' => EditJhtType::route('/{record}/edit'),
        ];
    }
}
