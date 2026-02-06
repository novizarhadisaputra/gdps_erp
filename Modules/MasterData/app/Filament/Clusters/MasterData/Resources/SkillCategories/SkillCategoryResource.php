<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\SkillCategories;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\SkillCategories\Pages\ListSkillCategories;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\SkillCategories\Schemas\SkillCategoryForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\SkillCategories\Tables\SkillCategoriesTable;
use Modules\MasterData\Models\SkillCategory;

class SkillCategoryResource extends Resource
{
    protected static ?string $model = SkillCategory::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';

    protected static string|\UnitEnum|null $navigationGroup = 'Sales Master';

    protected static ?int $navigationSort = 4;

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
        ];
    }
}
