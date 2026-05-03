<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ManpowerTemplate\Pages;

use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\Concerns\InteractsWithParentRecord;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ManpowerTemplate\ManpowerTemplateResource;

class ViewManpowerTemplate extends ViewRecord
{
    use InteractsWithParentRecord;

    protected static string $resource = ManpowerTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('pdf')
                ->label('Export PDF')
                ->color('gray')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->action(function () {
                    $record = $this->getRecord();
                    $costSimulation = $record->getCostSimulation();
                    $pdf = Pdf::loadView('crm::pdf.manpower_template', [
                        'record' => $record,
                        'costSimulation' => $costSimulation,
                    ]);
                    $name = Str::slug($record->name, '-');
                    $fileName = "{$name}.pdf";

                    return response()->streamDownload(fn () => print ($pdf->output()), $fileName);
                }),
            EditAction::make(),
            DeleteAction::make(),
        ];
    }
}
