<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\Pages;

use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Resources\Pages\Concerns\InteractsWithParentRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\CostingTemplateResource;

class EditCostingTemplate extends EditRecord
{
    use InteractsWithParentRecord;

    protected static string $resource = CostingTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('pdf')
                ->label('Export PDF')
                ->color('gray')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->action(function () {
                    $record = $this->getRecord();
                    $pdf = Pdf::loadView('crm::pdf.costing_template', ['record' => $record]);
                    $name = Str::slug($record->name, '-');

                    return response()->streamDownload(fn () => print ($pdf->output()), "costing-template-{$name}.pdf");
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
