<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Pages;

use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables\Table;
use Filament\Support\Icons\Heroicon;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\ProfitabilityAnalysisResource;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Resources\ProfitabilityAnalysisMonthly\ProfitabilityAnalysisMonthlyResource;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Resources\ProfitabilityAnalysisMonthly\Tables\ProfitabilityAnalysisMonthliesTable;

class ManageProfitabilityAnalysisMonthlies extends ManageRelatedRecords
{
    protected static string $resource = ProfitabilityAnalysisResource::class;

    protected static string $relationship = 'monthlies';

    protected static ?string $relatedResource = ProfitabilityAnalysisMonthlyResource::class;

    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

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
                    ->label('Add Monthly Record'),
            ]);
    }
}
