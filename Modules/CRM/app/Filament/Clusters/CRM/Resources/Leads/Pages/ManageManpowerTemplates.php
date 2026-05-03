<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\LeadResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ManpowerTemplate\ManpowerTemplateResource;
use Modules\CRM\Models\ManpowerTemplate;

class ManageManpowerTemplates extends ManageRelatedRecords
{
    protected static string $resource = LeadResource::class;

    public function getSubheading(): ?string
    {
        return 'Define manpower requirements for this lead.';
    }

    protected static string $relationship = 'manpowerTemplates';

    protected static ?string $relatedResource = ManpowerTemplateResource::class;

    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $title = 'Manpower Costing';

    protected static ?string $navigationLabel = 'Manpower Costing';

    public function form(Schema $schema): Schema
    {
        return ManpowerTemplateResource::form($schema);
    }

    public function table(Table $table): Table
    {
        return ManpowerTemplateResource::table($table)
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
                                ->helperText('Upload the original document as a valid data reference.'),
                        ])
                        ->action(function (array $data) {
                            $lead = $this->getOwnerRecord();
                            $latestGi = $lead->generalInformations()->latest('created_at')->first();

                            $record = ManpowerTemplate::create([
                                'lead_id' => $lead->id,
                                'name' => ($lead->customer?->name ?? 'Lead').' Manpower',
                                'description' => $latestGi?->scope_of_work,
                                'project_area_id' => $latestGi?->project_area_id ?? $lead->project_area_id,
                                'work_scheme_id' => $latestGi?->work_scheme_id ?? $lead->work_scheme_id,
                                'is_active' => true,
                            ]);

                            if ($filePath = $data['file'] ?? null) {
                                try {
                                    // Robustly handle string or array from Filament
                                    $path = is_array($filePath) ? (array_key_first($filePath) ?: reset($filePath)) : $filePath;

                                    if ($path) {
                                        \Illuminate\Support\Facades\Log::info('Attaching ManpowerTemplate media from S3', [
                                            'template_id' => $record->id,
                                            'path' => $path,
                                        ]);

                                        $record->addMediaFromDisk($path, 's3')
                                            ->toMediaCollection('source_file');

                                        \Illuminate\Support\Facades\Log::info('Successfully attached ManpowerTemplate media.');
                                    }
                                } catch (\Exception $e) {
                                    \Illuminate\Support\Facades\Log::error('MEDIA_ATTACHMENT_ERROR: ManpowerTemplate', [
                                        'template_id' => $record->id,
                                        'error' => $e->getMessage(),
                                        'trace' => $e->getTraceAsString(),
                                    ]);
                                }
                            }

                            $this->redirect(ManpowerTemplateResource::getUrl('view', ['lead' => $lead->id, 'record' => $record->id]));
                        })
                        ->successNotificationTitle('Manual Manpower Costing created'),
                    CreateAction::make()
                        ->after(fn (ManpowerTemplate $record) => $this->redirect(ManpowerTemplateResource::getUrl('view', ['lead' => $record->lead_id, 'record' => $record->id]))),
                ])
                    ->label('Options')
                    ->icon(Heroicon::OutlinedEllipsisVertical)
                    ->color('primary')
                    ->button(),
            ]);
    }
}
