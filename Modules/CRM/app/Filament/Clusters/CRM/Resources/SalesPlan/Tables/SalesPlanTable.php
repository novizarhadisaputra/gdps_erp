<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\SalesPlan\Tables;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\CRM\Enums\ConfidenceLevel;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\LeadResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\SalesPlan\Schemas\SalesPlanForm;
use Modules\CRM\Models\SalesPlan;

class SalesPlanTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('lead.title')
                    ->label('Lead')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('ams.name')
                    ->label('AMS')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('revenueSegment.name')
                    ->label('Segment')
                    ->sortable(),
                TextColumn::make('productCluster.name')
                    ->label('Cluster')
                    ->sortable(),
                TextColumn::make('estimated_value')
                    ->label('Value')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('priority_level')
                    ->label('Priority')
                    ->badge()
                    ->color(fn ($state): string => match ($state instanceof \BackedEnum ? $state->value : (int) $state) {
                        1 => 'danger',
                        2 => 'warning',
                        3 => 'success',
                        default => 'secondary',
                    })
                    ->sortable(),
                TextColumn::make('confidence_level')
                    ->label('Confidence')
                    ->badge()
                    ->color(fn (ConfidenceLevel $state): string => match ($state) {
                        ConfidenceLevel::Optimistic => 'success',
                        ConfidenceLevel::Moderate => 'warning',
                        ConfidenceLevel::Pessimistic => 'danger',
                        default => 'secondary',
                    })
                    ->sortable(),
                TextColumn::make('start_date')
                    ->date()
                    ->sortable(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->schema(fn ($schema) => SalesPlanForm::configure($schema))
                    ->modalFooterActions([
                        Action::make('convertToGI')
                            ->label('Convert to GI')
                            ->icon('heroicon-o-document-plus')
                            ->color('success')
                            ->requiresConfirmation()
                            ->visible(fn (SalesPlan $record) => $record->lead?->generalInformations()->doesntExist())
                            ->action(function (SalesPlan $record) {
                                $lead = $record->lead;

                                if (! $lead) {
                                    Notification::make()
                                        ->title('Error')
                                        ->body('Lead not found for this Sales Plan.')
                                        ->danger()
                                        ->send();

                                    return;
                                }

                                $gi = $lead->generalInformations()->create([
                                    'customer_id' => $lead->customer_id,
                                    'project_area_id' => $record->project_area_id,
                                    'estimated_start_date' => $record->start_date,
                                    'estimated_end_date' => $record->end_date,
                                    'scope_of_work' => $lead->title,
                                    'description' => $lead->description,
                                    'sales_plan_id' => $record->id,
                                    'status' => 'draft',
                                ]);

                                foreach (($lead->customer?->contacts ?? []) as $contact) {
                                    $gi->pics()->create([
                                        'contact_role_id' => $contact['type'] ?? null,
                                        'name' => $contact['name'] ?? null,
                                        'phone' => $contact['phone'] ?? null,
                                        'email' => $contact['email'] ?? null,
                                    ]);
                                }

                                Notification::make()
                                    ->title('General Information Created')
                                    ->body('Data has been synced from Sales Plan.')
                                    ->success()
                                    ->send();

                                return redirect()->to(LeadResource::getUrl('general-informations', ['record' => $lead]));
                            }),
                    ]),
                // EditAction::make()
                //     ->schema(fn ($schema) => SalesPlanForm::configure($schema)),
                Action::make('convertToGI')
                    ->label('Convert to GI')
                    ->icon('heroicon-o-document-plus')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (SalesPlan $record) => $record->lead?->generalInformations()->doesntExist())
                    ->action(function (SalesPlan $record) {
                        $lead = $record->lead;

                        if (! $lead) {
                            Notification::make()
                                ->title('Error')
                                ->body('Lead not found for this Sales Plan.')
                                ->danger()
                                ->send();

                            return;
                        }

                        $gi = $lead->generalInformations()->create([
                            'customer_id' => $lead->customer_id,
                            'project_area_id' => $record->project_area_id,
                            'estimated_start_date' => $record->start_date,
                            'estimated_end_date' => $record->end_date,
                            'scope_of_work' => $lead->title,
                            'description' => $lead->description,
                            'sales_plan_id' => $record->id,
                            'status' => 'draft',
                        ]);

                        foreach (($lead->customer?->contacts ?? []) as $contact) {
                            $gi->pics()->create([
                                'contact_role_id' => $contact['type'] ?? null,
                                'name' => $contact['name'] ?? null,
                                'phone' => $contact['phone'] ?? null,
                                'email' => $contact['email'] ?? null,
                            ]);
                        }

                        Notification::make()
                            ->title('General Information Created')
                            ->body('Data has been synced from Sales Plan.')
                            ->success()
                            ->send();

                        return redirect()->to(LeadResource::getUrl('general-informations', ['record' => $lead]));
                    }),
            ]);
    }
}
