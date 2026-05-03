<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Resources\ProfitabilityAnalysisRevision\Pages;

use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Resources\ProfitabilityAnalysisRevision\ProfitabilityAnalysisRevisionResource;

class AuditProfitabilityAnalysisRevision extends Page
{
    use InteractsWithRecord;

    protected static string $resource = ProfitabilityAnalysisRevisionResource::class;

    protected string $view = 'finance::filament.pages.audit-discussion';

    protected static ?string $title = 'Audit Discussion';

    protected static ?string $navigationLabel = 'Audit Discussion';

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }
}
