<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Pages;

use BackedEnum;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\LeadResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\SalesPlan\SalesPlanResource;

class ManageSalesPlans extends ManageRelatedRecords
{
    protected static string $resource = LeadResource::class;

    public function getSubheading(): ?string
    {
        return 'Strategic sales planning and forecasting for this lead.';
    }

    protected static string $relationship = 'salesPlan';

    protected static ?string $relatedResource = SalesPlanResource::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPresentationChartLine;

    public static function canAccess(array $parameters = []): bool
    {
        $record = $parameters['record'] ?? null;

        if (! $record) {
            return false;
        }

        $status = $record->status instanceof BackedEnum ? $record->status->value : $record->status;

        // Accessible from Approach stage onwards
        return in_array($status, [
            'approach',
            'proposal',
            'negotiation',
            'contract',
            'won',
            'closed_lost',
        ]);
    }

    public function table(Table $table): Table
    {
        return SalesPlanResource::table($table)
            ->headerActions([
                CreateAction::make()
                    ->schema(fn (Schema $schema) => SalesPlanResource::form($schema))
                    ->visible(fn () => $this->getOwnerRecord()->salesPlan()->doesntExist()),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
