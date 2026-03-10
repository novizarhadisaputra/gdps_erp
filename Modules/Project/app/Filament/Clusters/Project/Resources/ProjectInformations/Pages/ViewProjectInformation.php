<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\ProjectInformations\Pages;

use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Modules\Project\Filament\Clusters\Project\Resources\ProjectInformations\ProjectInformationResource;

class ViewProjectInformation extends ViewRecord
{
    protected static string $resource = ProjectInformationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportPdf')
                ->label('Export PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('danger')
                ->action(function ($record) {
                    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
                        'project::pdf.project-information',
                        [
                            'record' => $record,
                            'isExport' => true,
                            'isPdf' => true,
                        ]
                    )->setPaper('a4', 'portrait');

                    $filename = 'project_info_'.($record->project?->project_code ?? $record->id).'.pdf';
                    $filename = str_replace(['/', '\\'], '_', $filename);

                    return response()->streamDownload(function () use ($pdf) {
                        echo $pdf->output();
                    }, $filename, [
                        'Content-Type' => 'application/pdf',
                    ]);
                }),
            \Filament\Actions\EditAction::make(),
        ];
    }
}
