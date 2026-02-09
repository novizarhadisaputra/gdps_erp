<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Pages;

use BackedEnum;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
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
                TextColumn::make('revenueSegment.name')
                    ->label('Segment'),
                TextColumn::make('industrialSector.name')
                    ->label('Sector'),
                TextColumn::make('estimated_value')
                    ->money('IDR')
                    ->label('Value'),
                TextColumn::make('confidence_level')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'optimistic' => 'success',
                        'moderate' => 'warning',
                        'pessimistic' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('start_date')
                    ->date()
                    ->label('Start'),
                TextColumn::make('end_date')
                    ->date()
                    ->label('End'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->schema(fn (Schema $schema) => SalesPlanForm::configure($schema))
                    ->visible(fn () => $this->getOwnerRecord()->salesPlan()->doesntExist())
                    ->fillForm(function () {
                        $record = $this->getOwnerRecord();

                        return [
                            'estimated_value' => $record->estimated_amount,
                            'confidence_level' => $record->confidence_level,
                            'revenue_segment_id' => $record->revenue_segment_id,
                            'product_cluster_id' => $record->product_cluster_id,
                            'project_type_id' => $record->project_type_id,
                            'service_line_id' => $record->service_line_id,
                            'industrial_sector_id' => $record->industrial_sector_id,
                            'project_area_id' => $record->project_area_id,
                        ];
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->schema(fn (Schema $schema) => SalesPlanForm::configure($schema)),
                DeleteAction::make(),
            ]);
    }
}
