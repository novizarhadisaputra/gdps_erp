<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Modules\Finance\Classes\ProjectGenerationService;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\ProjectResource;
use Modules\Project\Models\Project;

class ConvertToProjectAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'convertToProject';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Generate Project')
            ->icon('heroicon-o-briefcase')
            ->color('success')
            ->requiresConfirmation()
            // Ensure step-by-step: Must be won, no project exists, AND an approved PA exists.
            ->visible(fn ($record) => $record->status->value === 'won'
                && ! Project::where('lead_id', $record->id)->exists()
                && $record->profitabilityAnalyses()->where('status', 'approved')->exists()
            )
            ->schema([
                TextInput::make('summary')
                    ->label('Summary')
                    ->default(fn ($record) => "You are about to generate a Project from the latest approved Profitability Analysis for '{$record->title}'.")
                    ->disabled()
                    ->dehydrated(false)
                    ->columnSpanFull(),
                TextInput::make('project_name_override')
                    ->label('Project Name (Optional)')
                    ->placeholder(fn ($record) => $record->title),
            ])
            ->action(function (array $data, $record) {
                // $record is the Lead
                // Find latest approved PA
                $pa = $record->profitabilityAnalyses()->where('status', 'approved')->latest('created_at')->first();
                if (! $pa) {
                    Notification::make()->title('Operation failed: No Approved Profitability Analysis found.')->danger()->send();

                    return;
                }

                // Generate Project using the centralized service
                $service = app(ProjectGenerationService::class);
                $project = $service->generateFromPA($pa);

                if (! empty($data['project_name_override'])) {
                    $project->update(['name' => $data['project_name_override']]);
                }

                Notification::make()
                    ->title('Project Generated Successfully')
                    ->body("Project Code: {$project->number}")
                    ->success()
                    ->send();

                $this->redirect(ProjectResource::getUrl('edit', ['record' => $project]));
            });
    }
}
