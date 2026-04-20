<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
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

    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedBanknotes;

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
                ActionGroup::make([
                    Action::make('manualUpload')
                        ->label('Manual Upload (Reference)')
                        ->icon(Heroicon::OutlinedDocumentPlus)
                        ->color('info')
                        ->schema([
                            FileUpload::make('file')
                                ->disk('s3')
                                ->visibility('private')
                                ->required()
                                ->columnSpanFull()
                                ->helperText('Upload the original costing document as a valid data reference.'),
                        ])
                        ->action(function (array $data) {
                            $lead = $this->getOwnerRecord();
                            $latestGi = $lead->generalInformations()->latest('created_at')->first();

                            $record = CostingTemplate::create([
                                'lead_id' => $lead->id,
                                'name' => ($lead->customer?->name ?? 'Lead') . ' Costing',
                                'description' => $latestGi?->scope_of_work ?? ($lead->customer?->name ?? 'Lead') . ' Lead',
                                'pic_id' => $lead->pic_costing_id ?? auth()->id(),
                                'total_monthly_cost' => 0,
                                'total_amount' => 0,
                            ]);

                            if ($filePath = $data['file'] ?? null) {
                                try {
                                    // Robustly handle string or array from Filament
                                    $path = is_array($filePath) ? (array_key_first($filePath) ?: reset($filePath)) : $filePath;

                                    if ($path) {
                                        \Illuminate\Support\Facades\Log::info('Attaching CostingTemplate media from S3', [
                                            'template_id' => $record->id,
                                            'path' => $path,
                                        ]);

                                        $record->addMediaFromDisk($path, 's3')
                                            ->toMediaCollection('source_file');

                                        \Illuminate\Support\Facades\Log::info('Successfully attached CostingTemplate media.');
                                    }
                                } catch (\Exception $e) {
                                    \Illuminate\Support\Facades\Log::error('MEDIA_ATTACHMENT_ERROR: CostingTemplate', [
                                        'template_id' => $record->id,
                                        'error' => $e->getMessage(),
                                        'trace' => $e->getTraceAsString(),
                                    ]);
                                }
                            }

                            $this->redirect(CostingTemplateResource::getUrl('view', ['lead' => $lead->id, 'record' => $record->id]));
                        })
                        ->successNotificationTitle('Manual Tools & Equipment Costing created'),
                    CreateAction::make()
                        ->schema(fn(Schema $schema) => CostingTemplateResource::form($schema))
                        ->fillForm(fn() => CostingTemplateForm::getAutoFillData($this->getOwnerRecord()))
                        ->after(fn(CostingTemplate $record) => $this->redirect(CostingTemplateResource::getUrl('view', ['lead' => $record->lead_id, 'record' => $record->id]))),
                ])
                ->label('Options')
                ->icon(Heroicon::OutlinedEllipsisVertical)
                ->color('primary')
                ->button(),
            ]);
    }
}
