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
use Illuminate\Database\Eloquent\Builder;
use Modules\CRM\Enums\ContractStatus;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\LeadResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Schemas\LeadForm;
use Modules\CRM\Models\Lead;
use Relaticle\Flowforge\Board;
use Relaticle\Flowforge\BoardResourcePage;
use Relaticle\Flowforge\Column;

class LeadBoard extends BoardResourcePage
{
    protected static string $resource = LeadResource::class;

    protected static ?string $title = 'Sales Pipeline Board';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('list')
                ->label('List View')
                ->icon('heroicon-o-table-cells')
                ->url(LeadResource::getUrl('list')),
            CreateAction::make()
                ->label('New Lead')
                ->form(fn (Schema $schema) => LeadForm::configure($schema)),
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
                Text::make(fn (Lead $record) => 'Prob: '.$record->probability.'%')
                    ->size('xs')
                    ->color('gray'),
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
                Column::make('won')->label('Won')->color('success'),
                Column::make('closed_lost')->label('Closed Lost')->color('danger'),
                Column::make('cancelled')->label('Cancelled')->color('danger'),
                Column::make('postponed')->label('Postponed')->color('warning'),
            ])
            ->cardActions([
                ViewAction::make()->url(fn (Lead $record) => LeadResource::getUrl('view', ['record' => $record])),
                Action::make('salesPlan')
                    ->label('Setup Sales Plan')
                    ->icon('heroicon-o-presentation-chart-line')
                    ->color('info')
                    ->url(fn (Lead $record) => LeadResource::getUrl('sales-plans', ['record' => $record])),
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
        $isValid = match ($targetColumnId) {
            'approach' => true, // Initial approach can be done
            'proposal' => $record->profitabilityAnalyses()->exists() && $record->generalInformations()->exists(),
            'negotiation' => $record->proposals()->exists(),
            'won' => $record->contracts()->where('status', ContractStatus::Active)->exists(),
            'closed_lost', 'cancelled', 'postponed' => true, // Can always move to terminal/pause states
            default => true,
        };

        if (! $isValid) {
            $message = match ($targetColumnId) {
                'proposal' => 'Moving to Proposal requires both General Information and Profitability Analysis (PA).',
                'negotiation' => 'Please create a Proposal document first.',
                'won' => 'Please create and activate a Contract first.',
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
