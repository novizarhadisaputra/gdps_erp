<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Resources\ProfitabilityAnalysisRevision;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\ProfitabilityAnalysisResource;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Resources\ProfitabilityAnalysisRevision\Pages\AuditProfitabilityAnalysisRevision;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Resources\ProfitabilityAnalysisRevision\Pages\ListProfitabilityAnalysisRevisions;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Resources\ProfitabilityAnalysisRevision\Pages\ViewProfitabilityAnalysisRevision;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Resources\ProfitabilityAnalysisRevision\Schemas\ProfitabilityAnalysisRevisionForm;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Resources\ProfitabilityAnalysisRevision\Tables\ProfitabilityAnalysisRevisionsTable;
use Modules\Finance\Models\ProfitabilityAnalysisRevision;
use Filament\Resources\Pages\Page;

class ProfitabilityAnalysisRevisionResource extends Resource
{
    protected static ?string $model = ProfitabilityAnalysisRevision::class;

    protected static bool $isNested = true;

    protected static ?string $parentResource = ProfitabilityAnalysisResource::class;

    protected static ?string $navigationLabel = 'Revision History';

    protected static ?string $slug = 'revisions';

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedClock;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            ViewProfitabilityAnalysisRevision::class,
            AuditProfitabilityAnalysisRevision::class,
        ]);
    }

    public static function form(Schema $schema): Schema
    {
        return ProfitabilityAnalysisRevisionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProfitabilityAnalysisRevisionsTable::configure($table);
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
            'index' => ListProfitabilityAnalysisRevisions::route('/'),
            'view' => ViewProfitabilityAnalysisRevision::route('/{record}'),
            'audit' => AuditProfitabilityAnalysisRevision::route('/{record}/audit'),
        ];
    }
}
