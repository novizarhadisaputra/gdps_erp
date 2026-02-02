<?php

namespace Modules\CRM\Filament\Resources\Leads\Pages;

use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\CRM\Enums\ProposalStatus;
use Modules\CRM\Filament\Resources\Leads\LeadResource;

class ManageProposals extends ManageRelatedRecords
{
    protected static string $resource = LeadResource::class;

    protected static string $relationship = 'proposals';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $title = 'Proposals';

    public static function canAccess(array $parameters = []): bool
    {
        $record = $parameters['record'] ?? null;

        if (! $record) {
            return false;
        }

        // Handle Enum casting
        $status = $record->status instanceof BackedEnum ? $record->status->value : $record->status;

        return in_array($status, [
            'approach',
            'proposal',
            'negotiation',
            'won',
            'closed_lost',
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('proposal_number')
                    ->required()
                    ->maxLength(255),
                Select::make('status')
                    ->options(ProposalStatus::class)
                    ->required()
                    ->default('draft'),
                TextInput::make('amount')
                    ->numeric()
                    ->prefix('IDR'),
                DatePicker::make('submission_date'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('proposal_number')
            ->columns([
                TextColumn::make('proposal_number'),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('amount')
                    ->money('IDR'),
                TextColumn::make('submission_date')
                    ->date(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Actions\CreateAction::make()
                    ->fillForm(function (): array {
                        $record = $this->getOwnerRecord();

                        return [
                            'customer_id' => $record->customer_id,
                            'amount' => $record->estimated_amount,
                        ];
                    })
                    ->mutateDataUsing(function (array $data): array {
                        $data['customer_id'] = $this->getOwnerRecord()->customer_id;

                        return $data;
                    }),
            ])
            ->recordActions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
                Actions\Action::make('pdf')
                    ->label('Export PDF')
                    ->color('gray')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function (\Modules\CRM\Models\Proposal $record) {
                        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('crm::pdf.proposal', ['record' => $record]);

                        return response()->streamDownload(fn () => print ($pdf->output()), "proposal-{$record->proposal_number}.pdf");
                    }),
            ])
            ->groupedBulkActions([
                Actions\DeleteBulkAction::make(),
            ]);
    }
}
