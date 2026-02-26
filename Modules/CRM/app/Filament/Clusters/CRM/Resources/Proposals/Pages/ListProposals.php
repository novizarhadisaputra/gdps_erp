<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Proposals\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Schema;
use Modules\CRM\Filament\Clusters\CRM\Resources\Proposals\ProposalResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Proposals\Schemas\ProposalForm;

class ListProposals extends ListRecords
{
    protected static string $resource = ProposalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('importProposal')
                ->label('Create from Proposal File')
                ->icon('heroicon-m-arrow-up-tray')
                ->color('success')
                ->form([
                    \Filament\Forms\Components\FileUpload::make('proposal_file')
                        ->label('Proposal Document (PDF/Docx)')
                        ->required()
                        ->disk('s3')
                        ->directory('temp-imports'),
                ])
                ->action(function (array $data, \App\Services\AiProcessorService $aiService) {
                    $disk = \Illuminate\Support\Facades\Storage::disk('s3');
                    if (! $disk->exists($data['proposal_file'])) {
                        return;
                    }

                    // 1. Download to temp
                    $tempPath = tempnam(sys_get_temp_dir(), 'prop');
                    file_put_contents($tempPath, $disk->get($data['proposal_file']));

                    // 2. Extract with AI
                    // Note: In real laravel/ai, we'd pass the file content or path
                    $metadata = $aiService->extractProposalMetadata($tempPath);

                    // 3. Create Proposal
                    $importId = \Illuminate\Support\Str::uuid();
                    $proposal = \Modules\CRM\Models\Proposal::create([
                        'is_imported' => true,
                        'import_source_id' => $importId,
                        'proposal_number' => $metadata['proposal_number'] ?? 'PROP-'.uniqid(),
                        'amount' => $metadata['total_amount'] ?? 0,
                        'status' => 'draft',
                        // Other fields might need manual filling or AI extraction
                    ]);

                    // 4. Attach file
                    $proposal->addMedia($tempPath)
                        ->usingFileName(basename($data['proposal_file']))
                        ->toMediaCollection('final_proposal', 's3');

                    if (file_exists($tempPath)) {
                        unlink($tempPath);
                    }
                    $disk->delete($data['proposal_file']);

                    \Filament\Notifications\Notification::make()
                        ->success()
                        ->title('Proposal created from document')
                        ->send();

                    return redirect()->route('filament.admin.crm.resources.proposals.edit', $proposal);
                }),
            CreateAction::make()
                ->label('Create Manual')
                ->schema(fn (Schema $schema) => ProposalForm::configure($schema)),
        ];
    }
}
