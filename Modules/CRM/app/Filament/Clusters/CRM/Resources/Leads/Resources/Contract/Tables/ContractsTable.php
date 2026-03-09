<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Contract\Tables;

use App\Filament\Pages\EditProfile;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\CRM\Enums\ContractStatus;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Contract\Schemas\ContractForm;
use Modules\CRM\Models\Contract;
use Modules\MasterData\Services\SignatureService;

class ContractsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('contract_number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('proposal.proposal_number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('expiry_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('reminder_status')
                    ->formatStateUsing(fn ($state): string => str_replace('_', ' ', $state instanceof \BackedEnum ? $state->value : (string) $state)),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('pdf')
                    ->label('Export PDF')
                    ->color('gray')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function (Contract $record) {
                        $pdf = Pdf::loadView('crm::pdf.contract', ['record' => $record]);

                        return response()->streamDownload(fn () => print ($pdf->output()), "contract-{$record->contract_number}.pdf");
                    }),
                ViewAction::make(),
                EditAction::make()
                    ->schema(fn (Schema $schema) => ContractForm::configure($schema)),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
