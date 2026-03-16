<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\WorkCompletionReports\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Modules\Project\Enums\WorkCompletionStatus;

class WorkCompletionReportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('report_number')
                    ->label('Report Number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('project.code')
                    ->label('Project Code')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('document_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('period')
                    ->label('Service Period')
                    ->getStateUsing(fn ($record) => "{$record->service_period_start->format('d/m/Y')} - {$record->service_period_end->format('d/m/Y')}")
                    ->toggleable(),
                TextColumn::make('status')
                    ->badge(),
            ])
            ->filters([
                SelectFilter::make('project_id')
                    ->relationship('project', 'code'),
                SelectFilter::make('status')
                    ->options(WorkCompletionStatus::class),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                \Filament\Actions\Action::make('sendEmail')
                    ->label('Send Email')
                    ->icon('heroicon-o-paper-airplane')
                    ->requiresConfirmation()
                    ->action(function (\Modules\Project\Models\WorkCompletionReport $record) {
                        try {
                            $signatureUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
                                'work_completion_reports.public.sign',
                                now()->addDays(7),
                                ['report' => $record->id]
                            );

                            $messageBody = "Please review and sign Work Completion Report (BAPP) #{$record->report_number} by clicking the link below:<br><br>";
                            $messageBody .= "<a href='{$signatureUrl}' style='display: inline-block; padding: 10px 20px; background: #2563eb; color: white; text-decoration: none; border-radius: 5px;'>Sign BAPP Online</a>";

                            $response = \Illuminate\Support\Facades\Http::withHeaders([
                                'content-type' => 'application/json',
                                'x-requester-app' => 'GDPS-ERP',
                            ])->post('https://machine.garudapratama.com/api/v1/email/send', [
                                'to' => [$record->customer?->email],
                                'subject' => "Work Completion Report (BAPP) - {$record->report_number}",
                                'body' => $messageBody,
                            ]);

                            if (! $response->successful()) {
                                throw new \Exception('External API Error: '.$response->status());
                            }

                            $record->update(['status' => WorkCompletionStatus::Sent]);

                            \Filament\Notifications\Notification::make()
                                ->title('Email Sent')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()
                                ->title('Failed to Send Email')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
