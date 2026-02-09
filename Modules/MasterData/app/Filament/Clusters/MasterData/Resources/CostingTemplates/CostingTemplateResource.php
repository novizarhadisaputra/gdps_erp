<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\CostingTemplates;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\CostingTemplates\Schemas\CostingTemplateForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\CostingTemplates\Tables\CostingTemplatesTable;
use Modules\MasterData\Models\CostingTemplate;

class CostingTemplateResource extends Resource
{
    protected static ?string $cluster = MasterDataCluster::class;

    protected static ?string $model = CostingTemplate::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calculator';

    protected static ?int $navigationSort = 121;

    protected static string|\UnitEnum|null $navigationGroup = 'Finance & Accounting';

    protected static ?string $navigationLabel = 'Item Templates';

    public static function form(Schema $schema): Schema
    {
        return CostingTemplateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CostingTemplatesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCostingTemplates::route('/'),
        ];
    }
}
