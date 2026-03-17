<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders\Resources\Amendment;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders\SalesOrderResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders\Resources\Amendment\Pages\AuditAmendment;
use Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders\Resources\Amendment\Pages\ViewAmendment;
use Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders\Resources\Amendment\Schemas\AmendmentForm;
use Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders\Resources\Amendment\Tables\AmendmentsTable;
use Modules\CRM\Models\SalesOrderAmendment;
use Filament\Resources\Pages\Page;

class AmendmentResource extends Resource
{
    protected static ?string $model = SalesOrderAmendment::class;

    protected static bool $isNested = true;

    protected static ?string $parentResource = SalesOrderResource::class;

    protected static ?string $navigationLabel = 'Amendments';

    protected static ?string $slug = 'amendments';

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedClock;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            ViewAmendment::class,
            AuditAmendment::class,
        ]);
    }

    public static function form(Schema $schema): Schema
    {
        return AmendmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AmendmentsTable::configure($table);
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
            'view' => ViewAmendment::route('/{record}'),
            'audit' => AuditAmendment::route('/{record}/audit'),
        ];
    }
}
