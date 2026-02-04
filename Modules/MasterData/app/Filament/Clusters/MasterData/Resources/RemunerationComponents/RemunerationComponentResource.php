<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\RemunerationComponents;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;
use Modules\MasterData\Models\RemunerationComponent;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\RemunerationComponents\Pages\CreateRemunerationComponent;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\RemunerationComponents\Pages\EditRemunerationComponent;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\RemunerationComponents\Pages\ListRemunerationComponents;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\RemunerationComponents\Schemas\RemunerationComponentForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\RemunerationComponents\Tables\RemunerationComponentsTable;

class RemunerationComponentResource extends Resource
{
    protected static ?string $model = RemunerationComponent::class;

    protected static ?string $cluster = MasterDataCluster::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return RemunerationComponentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RemunerationComponentsTable::configure($table);
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
            'index' => ListRemunerationComponents::route('/'),
            'create' => CreateRemunerationComponent::route('/create'),
            'edit' => EditRemunerationComponent::route('/{record}/edit'),
        ];
    }
}
