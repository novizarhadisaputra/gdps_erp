<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Pages;

use BackedEnum;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\LeadResource;
use Modules\Project\Filament\Clusters\Project\Resources\ProjectInformations\Schemas\ProjectInformationForm;

class ManageProjectInformations extends ManageRelatedRecords
{
    protected static string $resource = LeadResource::class;

    protected static string $relationship = 'projectInformations';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInformationCircle;

    protected static ?string $title = 'Project Information';

    public static function canAccess(array $parameters = []): bool
    {
        $record = $parameters['record'] ?? null;

        if (! $record) {
            return false;
        }

        // Handle Enum casting
        $status = $record->status instanceof BackedEnum ? $record->status->value : $record->status;

        return $status === 'won';
    }

    public function form(Schema $schema): Schema
    {
        return ProjectInformationForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('document_number')
            ->columns([
                TextColumn::make('document_number'),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('project.name')
                    ->label('Project'),
                TextColumn::make('start_date')->date(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->fillForm(function (): array {
                        $record = $this->getOwnerRecord();
                        $pa = $record->profitabilityAnalyses()->where('status', 'approved')->latest()->first();
                        $salesPlan = $record->salesPlan;

                        return [
                            'description' => $record->description,
                            'direct_cost' => $pa?->direct_cost,
                            'revenue_per_month' => $pa?->revenue_per_month,
                            'management_fee_per_month' => $pa?->management_fee,
                            'start_date' => $pa?->generalInformation?->estimated_start_date ?? $salesPlan?->start_date,
                            'end_date' => $pa?->generalInformation?->estimated_end_date ?? $salesPlan?->end_date,
                            'project_type_id' => $pa?->generalInformation?->project_type_id ?? $salesPlan?->project_type_id,
                        ];
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->groupedBulkActions([
                DeleteBulkAction::make(),
            ]);
    }
}
