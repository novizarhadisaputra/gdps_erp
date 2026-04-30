<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\WorkSchemes;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\WorkSchemes\Pages\CreateWorkScheme;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\WorkSchemes\Pages\EditWorkScheme;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\WorkSchemes\Pages\ListWorkSchemes;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\WorkSchemes\Pages\ViewWorkScheme;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\WorkSchemes\Schemas\WorkSchemeForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\WorkSchemes\Tables\WorkSchemesTable;
use Modules\MasterData\Models\WorkScheme;

class WorkSchemeResource extends Resource
{
    protected static ?string $cluster = MasterDataCluster::class;

    protected static ?string $model = WorkScheme::class;

    protected static ?int $navigationSort = 43;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|\UnitEnum|null $navigationGroup = 'HR & Organization';

    public static function form(Schema $schema): Schema
    {
        return WorkSchemeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WorkSchemesTable::configure($table);
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
            'index' => ListWorkSchemes::route('/'),
            'create' => CreateWorkScheme::route('/create'),
            'view' => ViewWorkScheme::route('/{record}'),
            'edit' => EditWorkScheme::route('/{record}/edit'),
        ];
    }
}
