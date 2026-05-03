<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\Pages;

use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\Concerns\InteractsWithParentRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Livewire\Attributes\On;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\CostingTemplateResource;

class EditCostingTemplate extends EditRecord
{
    use InteractsWithParentRecord;

    protected static string $resource = CostingTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            Action::make('pdf')
                ->label('Export PDF')
                ->color('gray')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->action(function () {
                    $record = $this->getRecord();
                    $pdf = Pdf::loadView('crm::pdf.costing_template', ['record' => $record]);
                    $name = str_replace(['/', '\\'], '-', $this->record->name);
                    $leadName = \Illuminate\Support\Str::slug($this->record->lead?->company_name ?? $this->record->lead?->title ?? 'Unknown-Lead', '-');
                    $fileName = "Costing_{$name}_{$leadName}.pdf";

                    return response()->streamDownload(fn () => print ($pdf->output()), $fileName);
                }),
            \Filament\Actions\DeleteAction::make(),
        ];
    }

    #[On('costing-items-updated')]
    public function refreshTotals(): void
    {
        $record = $this->getRecord();
        $record->refresh();

        // Update specific calculated fields in the form data without clobbering other fields (like name)
        $this->data['total_amount'] = $record->total_amount;
        $this->data['total_monthly_cost'] = $record->total_monthly_cost;
        $this->data['margin_percentage'] = $record->margin_percentage;
    }
}
