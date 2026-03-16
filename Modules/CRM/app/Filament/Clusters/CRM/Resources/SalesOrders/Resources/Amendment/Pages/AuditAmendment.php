<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders\Resources\Amendment\Pages;

use Filament\Resources\Pages\Concerns\InteractsWithParentRecord;
use Filament\Resources\Pages\Page;
use Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders\Resources\Amendment\AmendmentResource;

class AuditAmendment extends Page
{
    use InteractsWithParentRecord;

    protected static string $resource = AmendmentResource::class;

    protected string $view = 'crm::filament.pages.audit-discussion';

    protected static ?string $title = 'Audit Discussion';

    protected static ?string $navigationLabel = 'Audit Discussion';

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';
}
