<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Pages;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\LeadResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\CostingTemplateResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\Schemas\CostingTemplateForm;
use Modules\CRM\Models\CostingTemplate;

class ManageCostingTemplates extends ManageRelatedRecords
{
    protected static string $resource = LeadResource::class;

    public function getSubheading(): ?string
    {
        return 'Configure and manage costing templates for this lead.';
    }

    protected static string $relationship = 'costingTemplates';

    protected static ?string $relatedResource = CostingTemplateResource::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?string $title = 'Tools & Equipment Costing';

    protected static ?string $navigationLabel = 'Tools & Equipment Costing';

    public function form(Schema $schema): Schema
    {
        return CostingTemplateResource::form($schema);
    }

    public function table(Table $table): Table
    {
        return CostingTemplateResource::table($table)
            ->headerActions([
                Action::make('manualUpload')
                    ->label('Manual Upload (Reference)')
                    ->icon('heroicon-o-document-plus')
                    ->color('info')
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('file')
                            ->collection('source_file')
                            ->disk('s3')
                            ->visibility('private')
                            ->required()
                            ->helperText('Upload the original document as a valid data reference.'),
                    ])
                    ->action(function (array $data) {
                        $lead = $this->getOwnerRecord();
                        $latestGi = $lead->generalInformations()->latest('created_at')->first();

                        $record = CostingTemplate::create([
                            'lead_id' => $lead->id,
                            'name' => ($lead->customer?->name ?? 'Lead').' Tools & Equipment',
                            'description' => $latestGi?->scope_of_work,
                            'pic_id' => $lead->pic_costing_id ?? auth()->id(),
                        ]);

                        if (isset($data['file'])) {
                            $record->addMediaFromDisk($data['file'], 's3')->toMediaCollection('source_file');
                        }

                        $this->redirect(CostingTemplateResource::getUrl('view', ['lead' => $lead->id, 'record' => $record->id]));
                    })
                    ->successNotificationTitle('Manual Tools & Equipment Costing created'),
                CreateAction::make()
                    ->schema(fn (Schema $schema) => CostingTemplateResource::form($schema))
                    ->fillForm(fn () => CostingTemplateForm::getAutoFillData($this->getOwnerRecord()))
                    ->after(fn (CostingTemplate $record) => $this->redirect(CostingTemplateResource::getUrl('view', ['lead' => $record->lead_id, 'record' => $record->id]))),
            ]);
    }
}
