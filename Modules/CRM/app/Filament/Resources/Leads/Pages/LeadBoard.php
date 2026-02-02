<?php

namespace Modules\CRM\Filament\Resources\Leads\Pages;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Modules\CRM\Filament\Resources\Leads\LeadResource;
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
                ->url(LeadResource::getUrl('index')),
            CreateAction::make()
                ->label('New Lead')
                ->url(LeadResource::getUrl('create')),
        ];
    }

    public function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
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
            ])
            ->cardActions([
                ViewAction::make()->url(fn (Lead $record) => LeadResource::getUrl('view', ['record' => $record])),
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
            'approach' => $record->generalInformations()->exists(),
            'proposal' => $record->proposals()->exists(),
            'negotiation' => $record->profitabilityAnalyses()->exists(),
            'won' => $record->contracts()->where('status', \Modules\CRM\Enums\ContractStatus::Active)->exists(),
            default => true,
        };

        if (! $isValid) {
            $message = match ($targetColumnId) {
                'approach' => 'Please create General Information first.',
                'proposal' => 'Please create a Proposal document first.',
                'negotiation' => 'Please create a Profitability Analysis first.',
                'won' => 'Please create and activate a Contract first.',
                default => 'Invalid move.',
            };

            \Filament\Notifications\Notification::make()
                ->title('Validation Failed')
                ->body($message)
                ->danger()
                ->send();

            return;
        }

        parent::moveCard($cardId, $targetColumnId, $afterCardId, $beforeCardId);
    }
}
