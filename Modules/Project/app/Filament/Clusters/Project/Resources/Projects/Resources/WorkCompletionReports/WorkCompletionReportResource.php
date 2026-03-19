<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\WorkCompletionReports;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\ProjectResource;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\WorkCompletionReports\Pages\CreateWorkCompletionReport;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\WorkCompletionReports\Pages\EditWorkCompletionReport;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\WorkCompletionReports\Pages\ListWorkCompletionReports;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\WorkCompletionReports\Pages\ManageWorkCompletionReportComments;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\WorkCompletionReports\Schemas\WorkCompletionReportForm;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\WorkCompletionReports\Tables\WorkCompletionReportsTable;
use Modules\Project\Models\WorkCompletionReport;

class WorkCompletionReportResource extends Resource
{
    protected static ?string $model = WorkCompletionReport::class;

    protected static bool $isNested = true;

    protected static ?string $parentResource = ProjectResource::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return WorkCompletionReportForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WorkCompletionReportsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWorkCompletionReports::route('/'),
            'create' => CreateWorkCompletionReport::route('/create'),
            'edit' => EditWorkCompletionReport::route('/{record}/edit'),
            'discussions' => ManageWorkCompletionReportComments::route('/{record}/discussions'),
        ];
    }
}
