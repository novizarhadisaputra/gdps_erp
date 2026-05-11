<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\WorkCompletionReports;

use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Project\Filament\Clusters\Project\ProjectCluster;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\WorkCompletionReports\Schemas\WorkCompletionReportForm;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\WorkCompletionReports\Schemas\WorkCompletionReportInfolist;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\WorkCompletionReports\Tables\WorkCompletionReportsTable;
use Modules\Project\Filament\Clusters\Project\Resources\WorkCompletionReports\Pages\CreateWorkCompletionReport;
use Modules\Project\Filament\Clusters\Project\Resources\WorkCompletionReports\Pages\EditWorkCompletionReport;
use Modules\Project\Filament\Clusters\Project\Resources\WorkCompletionReports\Pages\ListWorkCompletionReports;
use Modules\Project\Filament\Clusters\Project\Resources\WorkCompletionReports\Pages\SendWorkCompletionReport;
use Modules\Project\Filament\Clusters\Project\Resources\WorkCompletionReports\Pages\ViewWorkCompletionReport;
use Modules\Project\Models\WorkCompletionReport;

class WorkCompletionReportResource extends Resource
{
    protected static ?string $model = WorkCompletionReport::class;

    protected static ?string $cluster = ProjectCluster::class;

    protected static ?string $modelLabel = 'BAPP';

    protected static ?string $pluralModelLabel = 'BAPP';

    protected static ?string $navigationLabel = 'BAPP';

    protected static ?string $slug = 'bapp';

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedDocumentCheck;

    protected static ?int $navigationSort = 2;

    public static function getSubNavigationPosition(): SubNavigationPosition
    {
        return SubNavigationPosition::Start;
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            Pages\ViewWorkCompletionReport::class,
            Pages\EditWorkCompletionReport::class,
            Pages\GenerateFinancialDocuments::class,
            Pages\SendWorkCompletionReport::class,
            Pages\ManageWorkCompletionReportComments::class,
        ]);
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
            'generate-financial-documents' => Pages\GenerateFinancialDocuments::route('/{record}/generate-financial-documents'),
            'discussions' => Pages\ManageWorkCompletionReportComments::route('/{record}/discussions'),
        ];
    }
}
