<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\DailyReports;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Support\Icons\Heroicon;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\ProjectResource;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\DailyReports\Pages\ManageDailyReportComments;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\DailyReports\Schemas\DailyReportForm;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\DailyReports\Tables\DailyReportsTable;
use Modules\Project\Models\DailyReport;

class DailyReportResource extends Resource
{
    protected static ?string $model = DailyReport::class;

    protected static bool $isNested = true;

    protected static ?string $parentResource = ProjectResource::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedDocumentText;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return DailyReportForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DailyReportsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'discussions' => ManageDailyReportComments::route('/{record}/discussions'),
        ];
    }
}
