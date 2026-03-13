<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\Bapps;

use Modules\Project\Models\Bapp;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Project\Filament\Clusters\Project\ProjectCluster;
use Modules\Project\Filament\Clusters\Project\Resources\Bapps\Pages\CreateBapp;
use Modules\Project\Filament\Clusters\Project\Resources\Bapps\Pages\EditBapp;
use Modules\Project\Filament\Clusters\Project\Resources\Bapps\Pages\ListBapps;
use Modules\Project\Filament\Clusters\Project\Resources\Bapps\Schemas\BappForm;
use Modules\Project\Filament\Clusters\Project\Resources\Bapps\Tables\BappsTable;

class BappResource extends Resource
{
    protected static ?string $model = Bapp::class;

    protected static ?string $singularLabel = 'Work Handover';

    protected static ?string $pluralLabel = 'Work Handovers';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = ProjectCluster::class;

    public static function form(Schema $schema): Schema
    {
        return BappForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BappsTable::configure($table);
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
            'index' => ListBapps::route('/'),
            'create' => CreateBapp::route('/create'),
            'edit' => EditBapp::route('/{record}/edit'),
        ];
    }
}
