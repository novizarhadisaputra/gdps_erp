<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\Resources\ProfitabilityAnalysisRevision\Pages;

use Filament\Resources\Pages\Concerns\InteractsWithParentRecord;
use Filament\Resources\Pages\Page;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\Resources\ProfitabilityAnalysisRevision\ProfitabilityAnalysisRevisionResource;

class AuditProfitabilityAnalysisRevision extends Page
{
    use InteractsWithParentRecord;

    protected static string $resource = ProfitabilityAnalysisRevisionResource::class;

    protected string $view = 'crm::filament.pages.audit-discussion';

    protected static ?string $title = 'Audit Discussion';

    protected static ?string $navigationLabel = 'Audit Discussion';

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';
}
