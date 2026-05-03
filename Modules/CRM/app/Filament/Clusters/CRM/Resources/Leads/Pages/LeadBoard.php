<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Pages;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Modules\CRM\Enums\LeadStatus;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\LeadResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\GeneralInformation\GeneralInformationResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Schemas\LeadForm;
use Modules\CRM\Models\Lead;
use Relaticle\Flowforge\Board;
use Relaticle\Flowforge\BoardResourcePage;
use Relaticle\Flowforge\Column;

class LeadBoard extends BoardResourcePage
{
    protected static string $resource = LeadResource::class;

    public function getSubheading(): ?string
    {
        return 'Overview of all leads across different stages of the sales pipeline.';
    }

    protected static ?string $title = 'Sales Pipeline Board';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('list')
                ->label('List View')
                ->icon(Heroicon::OutlinedTableCells)
                ->url(LeadResource::getUrl('list')),
            CreateAction::make()
                ->label('New Lead')
                ->model(Lead::class)
                ->schema(fn (Schema $schema) => LeadForm::configure($schema)),
        ];
    }

    public function getEloquentQuery(): Builder
    {
        return static::getResource()::getEloquentQuery();
    }

    public function board(Board $board): Board
    {
        return $board
            ->query($this->getEloquentQuery())
            ->recordTitleAttribute('title')
            ->cardSchema(fn (Schema $schema) => $schema->components([
                Text::make(fn (Lead $record) => $record->customer->name ?? 'Unknown Customer')
                    ->weight(FontWeight::Bold),
                Text::make(fn (Lead $record) => 'IDR '.number_format($record->estimated_amount, 0, ',', '.'))
                    ->size('xs')
                    ->color('success'),
            ]))
            ->columnIdentifier('status')
            ->positionIdentifier('position')
            ->columns([
                Column::make('lead')->label('Lead')->color('gray'),
                Column::make('approach')->label('Approach')->color('info'),
                Column::make('proposal')->label('Proposal')->color('primary'),
                Column::make('negotiation')->label('Negotiation')->color('warning'),
                Column::make('contract')->label('Contract')->color('success'),
                Column::make('won')->label('Won')->color('success'),
                Column::make('closed_lost')->label('Closed Lost')->color('danger'),
                Column::make('cancelled')->label('Cancelled')->color('danger'),
                Column::make('postponed')->label('Postponed')->color('warning'),
            ])
            ->cardActions([
                ViewAction::make()->url(fn (Lead $record) => LeadResource::getUrl('view', ['record' => $record])),
                Action::make('salesPlan')
                    ->label('Setup Sales Plan')
                    ->visible(fn (Lead $record) => $record->status === LeadStatus::Approach && $record->salesPlan()->doesntExist())
                    ->icon(Heroicon::OutlinedPresentationChartLine)
                    ->color('info')
                    ->action(function (Lead $record) {
                        return redirect(LeadResource::getUrl('sales-plans', ['record' => $record]));
                    }),
                Action::make('setupGI')
                    ->label('Setup General Info')
                    ->visible(fn (Lead $record) => $record->status === LeadStatus::Approach && $record->salesPlan()->exists() && $record->generalInformations()->doesntExist())
                    ->icon(Heroicon::OutlinedClipboardDocumentList)
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Setup General Information')
                    ->modalDescription('Apakah Anda yakin ingin membuat data General Information (GI) berdasarkan Sales Plan ini?')
                    ->action(function (Lead $record) {
                        $record->salesPlan->toGeneralInformation();

                        Notification::make()
                            ->title('General Information Created')
                            ->body('Data has been synced from Sales Plan.')
                            ->success()
                            ->send();

                        return redirect()->to(GeneralInformationResource::getUrl());
                    }),
                EditAction::make()->url(fn (Lead $record) => LeadResource::getUrl('edit', ['record' => $record])),
                DeleteAction::make()->model(Lead::class),
            ]);
    }

    public function moveCard(string $cardId, string $targetColumnId, ?string $afterCardId = null, ?string $beforeCardId = null): void
    {
        $record = Lead::find($cardId);

        if (! $record) {
            return;
        }

        // Validation Logic
        $targetStatus = LeadStatus::from($targetColumnId);
        $currentStatus = $record->status;

        // 1. Hierarchy Validation (Prevent Moving Backwards)
        // Except for special states (weight 99) which can be accessed from anywhere
        if ($targetStatus->weight() < $currentStatus->weight() && $targetStatus->weight() !== 99) {
            Notification::make()
                ->title('Validation Failed')
                ->body("Cannot move lead back to {$targetStatus->getLabel()} from {$currentStatus->getLabel()}.")
                ->danger()
                ->send();

            return;
        }

        // 2. Prerequisite Validation
        $isValid = match ($targetColumnId) {
            'approach' => true,
            'proposal' => $record->profitabilityAnalyses()->exists() && $record->generalInformations()->exists(),
            'negotiation' => $record->proposals()->exists(),
            'contract' => $record->minutesOfAgreements()->where('status', \Modules\CRM\Enums\MoAStatus::Approved)->exists(),
            'won' => $record->purchaseOrders()->exists() || $record->workOrders()->exists() || $record->cooperationAgreements()->exists(),
            'closed_lost', 'cancelled', 'postponed' => true,
            default => true,
        };

        if (! $isValid) {
            $message = match ($targetColumnId) {
                'proposal' => 'Moving to Proposal requires both General Information and Profitability Analysis (PA).',
                'negotiation' => 'Please create a Proposal document first.',
                'contract' => 'Please approve Minutes of Agreement (MoA) first.',
                'won' => 'Please create at least one legal document (PO, SPK, or PKS) first.',
                default => 'Some requirements are missing for this stage.',
            };

            Notification::make()
                ->title('Validation Failed')
                ->body($message)
                ->danger()
                ->send();

            return;
        }

        parent::moveCard($cardId, $targetColumnId, $afterCardId, $beforeCardId);
    }
}
