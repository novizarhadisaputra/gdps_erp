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
use Filament\Tables\Table;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\LeadResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\SalesPlan\SalesPlanResource;

class ManageSalesPlans extends ManageRelatedRecords
{
    protected static string $resource = LeadResource::class;

    protected static string $relationship = 'salesPlan';

    protected static ?string $relatedResource = SalesPlanResource::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPresentationChartLine;

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
        return SalesPlanResource::table($table)
            ->headerActions([
                CreateAction::make()
                    ->schema(fn (Schema $schema) => SalesPlanResource::form($schema))
                    ->visible(fn () => $this->getOwnerRecord()->salesPlan()->doesntExist()),
                Action::make('generateGIHeader')
                    ->label('Create General Info')
                    ->icon('heroicon-o-document-plus')
                    ->color('success')
                    ->visible(function () {
                        $lead = $this->getOwnerRecord();
                        $salesPlan = $lead->salesPlan;

                        return $salesPlan &&
                            $lead->generalInformations()->doesntExist() &&
                            ! empty($salesPlan->revenue_distribution_planning);
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Create General Information')
                    ->modalDescription('Apakah Anda yakin ingin membuat data General Information (GI) berdasarkan Sales Plan ini?')
                    ->action(function () {
                        $lead = $this->getOwnerRecord();
                        $lead->salesPlan->toGeneralInformation();

                        Notification::make()
                            ->title('General Information Created')
                            ->body('Data has been synced from Sales Plan.')
                            ->success()
                            ->send();

                        return redirect()->to(LeadResource::getUrl('general-informations', ['record' => $lead]));
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('generateGI')
                    ->label('Generate GI')
                    ->icon('heroicon-o-document-plus')
                    ->color('success')
                    ->visible(fn ($record) => ! empty($record->revenue_distribution_planning))
                    ->requiresConfirmation()
                    ->modalHeading('Generate General Information')
                    ->modalDescription('Apakah Anda yakin ingin membuat data General Information (GI) berdasarkan Sales Plan ini?')
                    ->action(function ($record) {
                        $record->toGeneralInformation();

                        Notification::make()
                            ->title('General Information Created')
                            ->body('Data has been synced from Sales Plan.')
                            ->success()
                            ->send();

                        return redirect()->to(LeadResource::getUrl('general-informations', ['record' => $this->getOwnerRecord()]));
                    }),
                DeleteAction::make(),
            ]);
    }
}
