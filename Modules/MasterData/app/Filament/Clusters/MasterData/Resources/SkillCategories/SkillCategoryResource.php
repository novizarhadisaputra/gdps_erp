<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\SkillCategories;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\SkillCategories\Pages\CreateSkillCategory;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\SkillCategories\Pages\EditSkillCategory;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\SkillCategories\Pages\ListSkillCategories;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\SkillCategories\Pages\ViewSkillCategory;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\SkillCategories\Schemas\SkillCategoryForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\SkillCategories\Tables\SkillCategoriesTable;
use Modules\MasterData\Models\SkillCategory;

class SkillCategoryResource extends Resource
{
    protected static ?string $model = SkillCategory::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static string|\UnitEnum|null $navigationGroup = 'HR & Organization';

    protected static ?int $navigationSort = 23;

    protected static ?string $cluster = MasterDataCluster::class;

    public static function form(Schema $schema): Schema
    {
        return SkillCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SkillCategoriesTable::configure($table);
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
            'index' => ListSkillCategories::route('/'),
            'create' => CreateSkillCategory::route('/create'),
            'view' => ViewSkillCategory::route('/{record}'),
            'edit' => EditSkillCategory::route('/{record}/edit'),
        ];
    }
}
