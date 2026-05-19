<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ManpowerTemplate\Pages;

use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\Concerns\InteractsWithParentRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ManpowerTemplate\ManpowerTemplateResource;

class EditManpowerTemplate extends EditRecord
{
    use InteractsWithParentRecord;

    protected static string $resource = ManpowerTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make(__('pdf'))
                ->label(__('Export PDF'))
                ->color('gray')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->action(function () {
                    $record = $this->getRecord();
                    $costSimulation = $record->getCostSimulation();
                    $pdf = Pdf::loadView('crm::pdf.manpower_template', [
                        'record' => $record,
                        'costSimulation' => $costSimulation,
                    ]);
                    $name = str_replace(['/', '\\'], '-', $this->record->name);
                    $leadName = \Illuminate\Support\Str::slug($this->record->lead?->company_name ?? $this->record->lead?->title ?? 'Unknown-Lead', '-');
                    $fileName = "Manpower_{$name}_{$leadName}.pdf";

                    return response()->streamDownload(fn () => print ($pdf->output()), $fileName);
                }),
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
