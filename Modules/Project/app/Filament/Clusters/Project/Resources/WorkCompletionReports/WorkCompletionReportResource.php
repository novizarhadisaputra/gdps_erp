<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\WorkCompletionReports;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Project\Filament\Clusters\Project\ProjectCluster;
use Modules\Project\Filament\Clusters\Project\Resources\WorkCompletionReports\Pages\CreateWorkCompletionReport;
use Modules\Project\Filament\Clusters\Project\Resources\WorkCompletionReports\Pages\EditWorkCompletionReport;
use Modules\Project\Filament\Clusters\Project\Resources\WorkCompletionReports\Pages\ListWorkCompletionReports;
use Modules\Project\Filament\Clusters\Project\Resources\WorkCompletionReports\Schemas\WorkCompletionReportForm;
use Modules\Project\Filament\Clusters\Project\Resources\WorkCompletionReports\Tables\WorkCompletionReportsTable;
use Modules\Project\Models\WorkCompletionReport;

class WorkCompletionReportResource extends Resource
{
    protected static ?string $model = WorkCompletionReport::class;

    protected static ?string $singularLabel = 'Work Completion Report';

    protected static ?string $pluralLabel = 'Work Completion Reports';

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = ProjectCluster::class;

    public static function form(Schema $schema): Schema
    {
        return WorkCompletionReportForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WorkCompletionReportsTable::configure($table);
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
            'index' => ListWorkCompletionReports::route('/'),
            'create' => CreateWorkCompletionReport::route('/create'),
            'edit' => EditWorkCompletionReport::route('/{record}/edit'),
        ];
    }
}
