<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\Invoices\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Finance\Filament\Clusters\Finance\Resources\Invoices\InvoiceResource;
use Modules\Finance\Filament\Clusters\Finance\Resources\Invoices\Traits\HasInvoiceActions;

class EditInvoice extends EditRecord
{
    use HasInvoiceActions;

    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            $this->getExportPdfAction(),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (empty($data['payment_info'])) {
            $data['payment_info'] = [
                'account_name' => 'a.n. PT. Garuda Daya Pratama Sejahtera',
                'banks' => [
                    ['bank_name' => 'Bank Mandiri', 'account_number' => '155-00-1307311-2', 'currency' => 'IDR'],
                    ['bank_name' => 'BNI', 'account_number' => '7201812017', 'currency' => 'IDR'],
                ],
            ];
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // This is where you can further sanitize or mutate the payment_info before saving to the database
        // (as requested by the customizing-data-before-saving documentation)
        return $data;
    }
}
