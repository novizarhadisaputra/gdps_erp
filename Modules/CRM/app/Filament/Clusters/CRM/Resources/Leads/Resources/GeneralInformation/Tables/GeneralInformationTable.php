<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\GeneralInformation\Tables;

use BackedEnum;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\CRM\Models\GeneralInformation;
use Modules\MasterData\Services\SignatureService;

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
                    ->badge()
                    ->color(fn ($state): string => match ($state instanceof BackedEnum ? $state->value : $state) {
                        'draft' => 'gray',
                        'submitted' => 'info',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
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
                ViewAction::make()
                    ->modalFooterActions([
                        Action::make('Sign')
                            ->label('Digital Signature')
                            ->color('primary')
                            ->icon('heroicon-o-pencil-square')
                            ->schema([
                                TextInput::make('pin')
                                    ->label('Signature PIN')
                                    ->password()
                                    ->required()
                                    ->helperText('Enter your digital signature PIN.'),
                            ])
                            ->action(function (GeneralInformation $record, array $data) {
                                $service = app(SignatureService::class);

                                if (! $service->verifyPin(auth()->user(), $data['pin'])) {
                                    Notification::make()
                                        ->title('Incorrect PIN')
                                        ->danger()
                                        ->send();

                                    return;
                                }

                                $required = $service->getRequiredApprovers($record);
                                $matchingRule = $required->first(fn ($rule) => $service->isEligibleApprover($rule, auth()->user()));

                                if (! $matchingRule) {
                                    Notification::make()
                                        ->title('Access Denied')
                                        ->body('You do not have the authority to sign this document based on the current approval rules.')
                                        ->warning()
                                        ->send();

                                    return;
                                }

                                if ($record->hasSignatureFrom($matchingRule->approver_role ?? $matchingRule->approver_type)) {
                                    Notification::make()
                                        ->title('Already Signed')
                                        ->body('This document has already been signed by the appropriate role.')
                                        ->warning()
                                        ->send();

                                    return;
                                }

                                $qrData = $service->createSignatureData(auth()->user(), $record, $matchingRule->signature_type);
                                $record->addSignature(auth()->user(), $matchingRule->signature_type);

                                Notification::make()
                                    ->title('Document Successfully Signed')
                                    ->success()
                                    ->send();

                                if ($record->isFullyApproved()) {
                                    $record->update(['status' => 'approved']);
                                }
                            })
                            ->visible(fn (GeneralInformation $record) => in_array($record->status, ['submitted', 'draft'])),

                        Action::make('Submit')
                            ->color('info')
                            ->icon('heroicon-o-paper-airplane')
                            ->requiresConfirmation()
                            ->action(fn (GeneralInformation $record) => $record->update(['status' => 'submitted']))
                            ->visible(fn (GeneralInformation $record) => $record->status === 'draft'),
                    ]),
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
                        $record->update(['status' => 'rejected']);

                        Notification::make()
                            ->title('General Information Rejected')
                            ->warning()
                            ->send();
                    })
                    ->visible(fn (GeneralInformation $record) => $record->status === 'submitted'),
                Action::make('createPA')
                    ->label('Create PA')
                    ->icon('heroicon-o-presentation-chart-bar')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'approved')
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
