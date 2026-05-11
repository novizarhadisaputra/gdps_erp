<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\WorkCompletionReports;

use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\ProjectResource;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\WorkCompletionReports\Pages\CreateWorkCompletionReport;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\WorkCompletionReports\Pages\EditWorkCompletionReport;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\WorkCompletionReports\Pages\ListWorkCompletionReports;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\WorkCompletionReports\Pages\ManageWorkCompletionReportComments;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\WorkCompletionReports\Pages\SendWorkCompletionReport;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\WorkCompletionReports\Pages\ViewWorkCompletionReport;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\WorkCompletionReports\Schemas\WorkCompletionReportForm;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\WorkCompletionReports\Schemas\WorkCompletionReportInfolist;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\WorkCompletionReports\Tables\WorkCompletionReportsTable;
use Modules\Project\Models\WorkCompletionReport;

class WorkCompletionReportResource extends Resource
{
    protected static ?string $model = WorkCompletionReport::class;

    protected static bool $isNested = true;

    protected static ?string $parentResource = ProjectResource::class;

    public static function can(string $action, ?\Illuminate\Database\Eloquent\Model $record = null): bool
    {
        return true;
    }

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function getSubNavigationPosition(): SubNavigationPosition
    {
        return SubNavigationPosition::Start;
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            ViewWorkCompletionReport::class,
            EditWorkCompletionReport::class,
            SendWorkCompletionReport::class,
            ManageWorkCompletionReportComments::class,
        ]);
    }

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

    public static function infolist(Schema $schema): Schema
    {
        return WorkCompletionReportInfolist::configure($schema);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWorkCompletionReports::route('/'),
            'create' => CreateWorkCompletionReport::route('/create'),
            'view' => ViewWorkCompletionReport::route('/{record}'),
            'edit' => EditWorkCompletionReport::route('/{record}/edit'),
            'send' => SendWorkCompletionReport::route('/{record}/send'),
            'discussions' => ManageWorkCompletionReportComments::route('/{record}/discussions'),
            'generate-financial-documents' => \Modules\Project\Filament\Clusters\Project\Resources\WorkCompletionReports\Pages\GenerateFinancialDocuments::route('/{record}/generate-financial-documents'),
        ];
    }
}
