<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\BenefitTypes;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BenefitTypes\Pages\CreateBenefitType;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BenefitTypes\Pages\EditBenefitType;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BenefitTypes\Pages\ListBenefitTypes;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BenefitTypes\Pages\ViewBenefitType;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BenefitTypes\Schemas\BenefitTypeForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BenefitTypes\Tables\BenefitTypesTable;
use Modules\MasterData\Models\BenefitType;

class BenefitTypeResource extends Resource
{
    protected static ?string $model = BenefitType::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = MasterDataCluster::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Payroll & Benefits';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return BenefitTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BenefitTypesTable::configure($table);
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
            'index' => ListBenefitTypes::route('/'),
            'create' => CreateBenefitType::route('/create'),
            'view' => ViewBenefitType::route('/{record}'),
            'edit' => EditBenefitType::route('/{record}/edit'),
        ];
    }
}
