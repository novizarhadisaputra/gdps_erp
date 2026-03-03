<?php

namespace Modules\CRM\Jobs;

use App\Models\User;
use App\Services\AiProcessorService;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\CRM\Models\ManpowerTemplate;
use Modules\CRM\Models\ManpowerTemplateItem;
use Modules\MasterData\Models\JobPosition;

class ImportManpowerAiJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // 5 minutes

    public $tries = 3;

    public function __construct(
        public string $filePath,
        public array $context,
        public string $templateId,
        public string $userId
    ) {}

    public function handle(AiProcessorService $aiService): void
    {
        ini_set('memory_limit', '2048M');

        $user = User::find($this->userId);
        $template = ManpowerTemplate::find($this->templateId);

        if (! $template || ! $user) {
            return;
        }

        Log::info("ImportManpowerAiJob: Handling job for template: {$this->templateId}");
        $lead = $template->lead;

        // Resolve Path via Media Library
        $media = $template->getFirstMedia('source_file');
        if (! $media) {
            Log::error("ImportManpowerAiJob: No media found for template: {$this->templateId}");
            Notification::make()
                ->title('Import Failed')
                ->body('No source file found for the template.')
                ->danger()
                ->sendToDatabase($user);

            return;
        }

        $localPath = null;
        try {
            if ($media->disk === 'local' || $media->disk === 'public') {
                $this->filePath = $media->getPath();
            } else {
                // Remote disk (S3, etc.), download to temporary local file
                $tempPath = tempnam(sys_get_temp_dir(), 'ai_import_');
                $extension = pathinfo($media->file_name, PATHINFO_EXTENSION);
                $this->filePath = $tempPath.($extension ? '.'.$extension : '');

                Log::info("ImportManpowerAiJob: Downloading remote file from disk: {$media->disk} to {$this->filePath}");
                file_put_contents($this->filePath, Storage::disk($media->disk)->get($media->getPath()));
                $localPath = $this->filePath; // Mark for cleanup
            }

            Log::info("ImportManpowerAiJob: Final resolved path: {$this->filePath}");

            if (! file_exists($this->filePath)) {
                throw new \Exception("File not found at resolved path: {$this->filePath}");
            }

            Log::info('ImportManpowerAiJob: Calling AiProcessorService...');
            $processedData = $aiService->processCogsData($this->filePath, $this->context, 'manpower');
            $manpowerData = $processedData['manpower'] ?? [];

            Log::info('ImportManpowerAiJob: AI returned '.count($manpowerData).' manpower items.');

            if (empty($manpowerData)) {
                Notification::make()
                    ->title('Manpower Import Failed')
                    ->body('AI could not identify any manpower data in the file.')
                    ->danger()
                    ->sendToDatabase($user);

                return;
            }

            $latestGi = $lead?->generalInformations()?->latest()?->first();

            $template->update([
                'name' => ($lead?->customer?->name ?? $lead?->title ?? 'Template').' - AI Auto Generated ('.now()->format('H:i').')',
                'description' => $latestGi?->scope_of_work ?? $template->description,
                'project_area_id' => $latestGi?->project_area_id ?? $lead?->project_area_id ?? $template->project_area_id,
            ]);

            foreach ($manpowerData as $item) {
                $jobPositionId = $item['matched_id'] ?? null;

                if ($jobPositionId && strtolower($jobPositionId) !== 'null') {
                    if (! JobPosition::where('id', $jobPositionId)->exists()) {
                        $jobPositionId = null;
                    }
                }

                if (! $jobPositionId || strtolower($jobPositionId) === 'null') {
                    $existing = JobPosition::where('name', 'ilike', $item['name'])->first();
                    if ($existing) {
                        $jobPositionId = $existing->id;
                    } else {
                        $newJob = JobPosition::create([
                            'name' => $item['name'],
                            'is_active' => true,
                            'is_labor_intensive' => true,
                            'risk_level' => 'Low',
                        ]);
                        $jobPositionId = $newJob->id;
                    }
                }

                ManpowerTemplateItem::create([
                    'manpower_template_id' => $template->id,
                    'job_position_id' => $jobPositionId,
                    'quantity' => $item['quantity'] ?? 1,
                    'basic_salary' => $item['basic_salary'] ?? 0,
                    'notes' => $item['notes'] ?? '',
                ]);
            }

            Log::info('ImportManpowerAiJob: Successfully processed all items.');

            Notification::make()
                ->title('Manpower Import Successful')
                ->body('AI has successfully processed and imported manpower data for lead: '.($lead?->title ?? 'N/A'))
                ->success()
                ->sendToDatabase($user);

        } catch (\Exception $e) {
            Notification::make()
                ->title('Manpower Import Error')
                ->body($e->getMessage())
                ->danger()
                ->sendToDatabase($user);
        } finally {
            if ($localPath && file_exists($localPath)) {
                Log::info("ImportManpowerAiJob: Cleaning up temporary local file: {$localPath}");
                unlink($localPath);
            }
        }
    }
}
