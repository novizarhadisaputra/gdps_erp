<?php

namespace Modules\MasterData\Filament\Resources\WorkSchemes;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;
use Modules\MasterData\Filament\Resources\WorkSchemes\Pages\ListWorkSchemes;
use Modules\MasterData\Filament\Resources\WorkSchemes\Schemas\WorkSchemeForm;
use Modules\MasterData\Filament\Resources\WorkSchemes\Tables\WorkSchemesTable;
use Modules\MasterData\Models\WorkScheme;

class WorkSchemeResource extends Resource
{
    protected static ?string $cluster = MasterDataCluster::class;

    protected static ?string $model = WorkScheme::class;

    protected static ?int $navigationSort = 4;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static string|\UnitEnum|null $navigationGroup = 'Project Structure';

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
        ];
    }
}
