<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Pages;

use BackedEnum;
use Filament\Actions;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\LeadResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Schemas\SalesPlanForm;

class ManageSalesPlans extends ManageRelatedRecords
{
    protected static string $resource = LeadResource::class;

    protected static string $relationship = 'salesPlan';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPresentationChartLine;

    protected static ?string $title = 'Sales Plan (Approach)';

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
            'won',
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('revenueSegment.name')
                    ->label('Segment'),
                Tables\Columns\TextColumn::make('industrialSector.name')
                    ->label('Sector'),
                Tables\Columns\TextColumn::make('estimated_value')
                    ->money('IDR')
                    ->label('Value'),
                Tables\Columns\TextColumn::make('confidence_level')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'optimistic' => 'success',
                        'moderate' => 'warning',
                        'pessimistic' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('start_date')
                    ->date()
                    ->label('Start'),
                Tables\Columns\TextColumn::make('end_date')
                    ->date()
                    ->label('End'),
            ])
            ->headerActions([
                Actions\CreateAction::make()
                    ->schema(fn (Schema $schema) => SalesPlanForm::configure($schema))
                    ->visible(fn () => $this->getOwnerRecord()->salesPlan()->doesntExist())
                    ->fillForm(fn () => [
                        'estimated_value' => $this->getOwnerRecord()->estimated_amount,
                    ]),
            ])
            ->recordActions([
                Actions\EditAction::make()
                    ->schema(fn (Schema $schema) => SalesPlanForm::configure($schema)),
                Actions\DeleteAction::make(),
            ]);
    }
}
