<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\ProfitabilityAnalysisResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\Resources\ProfitabilityAnalysisMonthly\ProfitabilityAnalysisMonthlyResource;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Resources\ProfitabilityAnalysisMonthly\Tables\ProfitabilityAnalysisMonthliesTable;

class ManageProfitabilityAnalysisMonthlies extends ManageRelatedRecords
{
    protected static string $resource = ProfitabilityAnalysisResource::class;

    protected static string $relationship = 'monthlies';

    protected static ?string $relatedResource = ProfitabilityAnalysisMonthlyResource::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?string $title = 'Monthly Performance Records';

    public static function getNavigationLabel(): string
    {
        return 'Monthly Performance';
    }

    public function table(Table $table): Table
    {
        return ProfitabilityAnalysisMonthliesTable::configure($table)
            ->headerActions([
                CreateAction::make()
                    ->label(__('Add Monthly Record')),
            ]);
    }

    public function getBreadcrumbs(): array
    {
        $resource = static::getResource();
        $record = $this->getOwnerRecord();
        $lead = $record->lead;

        $leadResource = \Modules\CRM\Filament\Clusters\CRM\Resources\Leads\LeadResource::class;

        return [
            $leadResource::getUrl() => $leadResource::getBreadcrumb(),
            $leadResource::getUrl('view', ['record' => $lead]) => $lead?->title ?? 'Lead',
            $resource::getUrl('index', ['lead' => $lead]) => $resource::getBreadcrumb(),
            $resource::getUrl('view', ['lead' => $lead, 'record' => $record]) => $record?->number ?? 'PA',
            '#' => 'Monthly Performance',
        ];
    }
}
