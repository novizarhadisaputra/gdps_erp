<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\GeneralInformation\Tables;

use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\CRM\Enums\GeneralInformationStatus;
use Modules\CRM\Models\GeneralInformation;

class GeneralInformationTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('document_number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('scope_of_work')
                    ->limit(50)
                    ->searchable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('estimated_start_date')
                    ->date()
                    ->label('Start')
                    ->sortable(),
                TextColumn::make('estimated_end_date')
                    ->date()
                    ->label('End')
                    ->sortable(),
                IconColumn::make('tor')
                    ->label('ToR')
                    ->getStateUsing(fn ($record) => $record->hasMedia('tor'))
                    ->boolean()
                    ->url(function ($record) {
                        $media = $record->getFirstMedia('tor');
                        if (! $media) {
                            return null;
                        }

                        return $media->disk === 's3' ? $media->getTemporaryUrl(now()->addMinutes(30)) : $media->getUrl();
                    }, true),
                IconColumn::make('rfp')
                    ->label('RFP')
                    ->getStateUsing(fn ($record) => $record->hasMedia('rfp'))
                    ->boolean()
                    ->url(function ($record) {
                        $media = $record->getFirstMedia('rfp');
                        if (! $media) {
                            return null;
                        }

                        return $media->disk === 's3' ? $media->getTemporaryUrl(now()->addMinutes(30)) : $media->getUrl();
                    }, true),
                IconColumn::make('rfi')
                    ->label('RFI')
                    ->getStateUsing(fn ($record) => $record->hasMedia('rfi'))
                    ->boolean()
                    ->url(function ($record) {
                        $media = $record->getFirstMedia('rfi');
                        if (! $media) {
                            return null;
                        }

                        return $media->disk === 's3' ? $media->getTemporaryUrl(now()->addMinutes(30)) : $media->getUrl();
                    }, true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('pdf')
                    ->label('Export PDF')
                    ->color('gray')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function (GeneralInformation $record) {
                        $pdf = Pdf::loadView('crm::pdf.general_information', ['record' => $record]);

                        return response()->streamDownload(fn () => print ($pdf->output()), "general-information-{$record->document_number}.pdf");
                    }),
                ViewAction::make(),
                EditAction::make()
                    ->schema(fn (Schema $schema) => \Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\GeneralInformation\GeneralInformationResource::form($schema))
                    ->hidden(fn (GeneralInformation $record) => $record->isLocked()),
                DeleteAction::make()
                    ->hidden(fn (GeneralInformation $record) => $record->isLocked()),
                Action::make('Reject')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->requiresConfirmation()
                    ->modalHeading('Reject General Information')
                    ->modalDescription('Are you sure you want to reject this General Information? The status will return to Rejected and it can be edited again.')
                    ->action(function (GeneralInformation $record) {
                        $record->update(['status' => GeneralInformationStatus::Rejected]);

                        Notification::make()
                            ->title('General Information Rejected')
                            ->warning()
                            ->send();
                    })
                    ->visible(fn (GeneralInformation $record) => $record->status === GeneralInformationStatus::Submitted),
                Action::make('createPA')
                    ->label('Create PA')
                    ->icon('heroicon-o-presentation-chart-bar')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === GeneralInformationStatus::Approved)
                    ->requiresConfirmation()
                    ->modalHeading('Create Profitability Analysis')
                    ->modalDescription('Are you sure you want to create a Profitability Analysis (PA) based on this General Information?')
                    ->action(function ($record) {
                        $record->toProfitabilityAnalysis();

                        Notification::make()
                            ->title('Profitability Analysis Created')
                            ->success()
                            ->send();

                        return redirect()->to(\Modules\CRM\Filament\Clusters\CRM\Resources\Leads\LeadResource::getUrl('profitability-analyses', ['record' => $record->lead]));
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
