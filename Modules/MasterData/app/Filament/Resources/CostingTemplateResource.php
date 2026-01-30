<?php

namespace Modules\MasterData\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Resources\CostingTemplates\Pages;
use Modules\MasterData\Filament\Resources\CostingTemplates\Schemas\CostingTemplateForm;
use Modules\MasterData\Filament\Resources\CostingTemplates\Tables\CostingTemplatesTable;
use Modules\MasterData\Models\CostingTemplate;

class CostingTemplateResource extends Resource
{
    protected static ?string $model = CostingTemplate::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-calculator';

    protected static string | \UnitEnum | null $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Costing Templates';

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
            'create' => Pages\CreateCostingTemplate::route('/create'),
            'edit' => Pages\EditCostingTemplate::route('/{record}/edit'),
        ];
    }
}
