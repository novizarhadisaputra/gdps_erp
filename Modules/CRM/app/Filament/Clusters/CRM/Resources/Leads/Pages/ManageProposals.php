<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Pages;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Modules\CRM\Enums\LeadStatus;
use Modules\CRM\Enums\ProposalStatus;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\LeadResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Proposal\ProposalResource;
use Modules\CRM\Models\Proposal;

class ManageProposals extends ManageRelatedRecords
{
    protected static string $resource = LeadResource::class;

    public function getSubheading(): ?string
    {
        return 'Create and track project proposals for this lead.';
    }

    protected static string $relationship = 'proposals';

    protected static ?string $relatedResource = ProposalResource::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $title = 'Lead Proposals';

    public function form(Schema $schema): Schema
    {
        return ProposalResource::form($schema);
    }

    public function table(Table $table): Table
    {
        return ProposalResource::table($table)
            ->headerActions([
                Action::make('manualUpload')
                    ->label('Manual Upload (Reference)')
                    ->icon('heroicon-o-document-plus')
                    ->color('info')
                    ->modalWidth('xl')
                    ->schema([
                        FileUpload::make('file')
                            ->label('Proposal Document')
                            ->disk('local')
                            ->directory('temp-manual-uploads')
                            ->acceptedFileTypes([
                                'application/pdf',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'application/vnd.ms-excel',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                'application/msword',
                            ])
                            ->required()
                            ->helperText('Upload the signed or final proposal document.'),
                        TextInput::make('amount')
                            ->label('Proposal Amount')
                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                            ->prefix('IDR')
                            ->required()
                            ->default(fn () => $this->getOwnerRecord()->estimated_amount),
                        DatePicker::make('submission_date')
                            ->label('Submission Date')
                            ->default(now())
                            ->native(false)
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $lead = $this->getOwnerRecord();

                        $amount = is_numeric($data['amount'])
                            ? (float) $data['amount']
                            : (float) str_replace(['.', ','], ['', '.'], $data['amount']);

                        $proposal = Proposal::create([
                            'lead_id' => $lead->id,
                            'customer_id' => $lead->customer_id,
                            'work_scheme_id' => $lead->work_scheme_id,
                            'amount' => $amount,
                            'submission_date' => $data['submission_date'],
                            'status' => ProposalStatus::Draft,
                            'is_manual' => true,
                        ]);

                        if (isset($data['file'])) {
                            $file = is_array($data['file']) ? reset($data['file']) : $data['file'];
                            $filePath = Storage::disk('local')->path($file);
                            $proposal->addMedia($filePath)->toMediaCollection('final_proposal');
                        }

                        $lead->update(['status' => LeadStatus::Proposal]);

                        Notification::make()
                            ->title('Proposal created manually via upload')
                            ->success()
                            ->send();
                    })
                    ->successNotificationTitle('Manual Proposal created'),
            ]);
    }
}
