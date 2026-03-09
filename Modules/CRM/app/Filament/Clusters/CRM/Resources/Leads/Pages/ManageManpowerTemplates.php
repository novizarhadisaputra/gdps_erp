<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Pages;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
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

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

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
                Action::make('manualUpload')
                    ->label('Manual Upload (Reference)')
                    ->icon('heroicon-o-document-plus')
                    ->color('info')
                    ->schema([
                        FileUpload::make('file')
                            ->disk('local')
                            ->directory('temp-manual-uploads')
                            ->acceptedFileTypes([
                                'application/pdf',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'application/vnd.ms-excel',
                            ])
                            ->required()
                            ->helperText('Upload the original document as a valid data reference.'),
                    ])
                    ->action(function (array $data) {
                        $lead = $this->getOwnerRecord();
                        $latestGi = $lead->generalInformations()->latest()->first();

                        $record = ManpowerTemplate::create([
                            'lead_id' => $lead->id,
                            'name' => 'Manpower Costing from '.now()->format('Y-m-d H:i'),
                            'description' => $latestGi?->scope_of_work,
                            'project_area_id' => $latestGi?->project_area_id ?? $lead->project_area_id,
                            'work_scheme_id' => $latestGi?->work_scheme_id ?? $lead->work_scheme_id,
                            'contract_type_id' => $latestGi?->contract_type_id,
                            'is_active' => true,
                        ]);

                        if (isset($data['file'])) {
                            $filePath = Storage::disk('local')->path($data['file']);
                            $record->addMedia($filePath)->toMediaCollection('source_file');
                        }

                        $this->redirect(ManpowerTemplateResource::getUrl('edit', ['lead' => $lead->id, 'record' => $record->id]));
                    })
                    ->successNotificationTitle('Manual Manpower Costing created'),
                CreateAction::make(),
            ]);
    }
}
