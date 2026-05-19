<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\Resources\ProfitabilityAnalysisRevision;

use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\ProfitabilityAnalysisResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\Resources\ProfitabilityAnalysisRevision\Pages\AuditProfitabilityAnalysisRevision;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\Resources\ProfitabilityAnalysisRevision\Pages\ViewProfitabilityAnalysisRevision;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\Resources\ProfitabilityAnalysisRevision\Schemas\ProfitabilityAnalysisRevisionForm;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\Resources\ProfitabilityAnalysisRevision\Tables\ProfitabilityAnalysisRevisionsTable;
use Modules\Finance\Models\ProfitabilityAnalysisRevision;

class ProfitabilityAnalysisRevisionResource extends Resource
{
    protected static ?string $model = ProfitabilityAnalysisRevision::class;

    protected static bool $isNested = true;

    protected static ?string $parentResource = ProfitabilityAnalysisResource::class;

    protected static ?string $slug = 'revisions';

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedClock;

    public static function getModelLabel(): string
    {
        return __('Profitability Analysis Revision');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Profitability Analysis Revisions');
    }

    public static function getNavigationLabel(): string
    {
        return __('Revision History');
    }

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
            'view' => ViewProfitabilityAnalysisRevision::route('/{record}'),
            'audit' => AuditProfitabilityAnalysisRevision::route('/{record}/audit'),
        ];
    }
}
