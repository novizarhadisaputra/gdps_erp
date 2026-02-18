<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\CRM\Filament\Clusters\CRM\Resources\GeneralInformation\GeneralInformationResource;
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
            'closed_lost',
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
                    ->color(fn ($state): string => match ($state instanceof BackedEnum ? $state->value : $state) {
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
                            'start_date' => $record->start_date,
                            'end_date' => $record->end_date,
                        ];
                    }),
                Action::make('generateGIHeader')
                    ->label('Create General Info')
                    ->icon('heroicon-o-document-plus')
                    ->color('success')
                    ->visible(fn () => $this->getOwnerRecord()->salesPlan()->exists() && $this->getOwnerRecord()->generalInformations()->doesntExist())
                    ->action(function () {
                        $lead = $this->getOwnerRecord();
                        $record = $lead->salesPlan;

                        $lead->generalInformations()->create([
                            'customer_id' => $lead->customer_id,
                            'project_area_id' => $record->project_area_id,
                            'estimated_start_date' => $record->start_date,
                            'estimated_end_date' => $record->end_date,
                            'scope_of_work' => $lead->title,
                            'description' => $lead->description,
                            'sales_plan_id' => $record->id,
                            'status' => 'draft',
                        ]);

                        Notification::make()
                            ->title('General Information Created')
                            ->body('Data has been synced from Sales Plan.')
                            ->success()
                            ->send();

                        return redirect()->to(LeadResource::getUrl('general-informations', ['record' => $lead]));
                    }),
            ])
            ->recordActions([
                ViewAction::make()
                    ->schema(fn (Schema $schema) => SalesPlanForm::configure($schema)),
                EditAction::make()
                    ->schema(fn (Schema $schema) => SalesPlanForm::configure($schema)),
                Action::make('generateGI')
                    ->label('Generate GI')
                    ->icon('heroicon-o-document-plus')
                    ->color('success')
                    ->action(function ($record) {
                        $lead = $this->getOwnerRecord();

                        $lead->generalInformations()->create([
                            'customer_id' => $lead->customer_id,
                            'project_area_id' => $record->project_area_id,
                            'estimated_start_date' => $record->start_date,
                            'estimated_end_date' => $record->end_date,
                            'scope_of_work' => $lead->title,
                            'description' => $lead->description,
                            'sales_plan_id' => $record->id,
                            'status' => 'draft',
                        ]);

                        Notification::make()
                            ->title('General Information Created')
                            ->body('Data has been synced from Sales Plan.')
                            ->success()
                            ->send();

                        return redirect()->to(LeadResource::getUrl('general-informations', ['record' => $lead]));
                    }),
                DeleteAction::make(),
            ]);
    }
}
