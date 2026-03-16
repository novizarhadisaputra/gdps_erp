<?php

namespace Modules\CRM\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\CRM\Enums\SalesOrderStatus;
use Modules\CRM\Http\Requests\SignDocumentRequest;
use Modules\CRM\Models\SalesOrder;
use Modules\MasterData\Enums\ApprovalSignatureType;

class PublicSalesOrderController extends Controller
{
    public function show(SalesOrder $salesOrder)
    {
        return view('crm::public.sales-order.sign', compact('salesOrder'));
    }

    public function sign(SignDocumentRequest $request, SalesOrder $salesOrder)
    {
        $validated = $request->validated();

        if (! empty($validated['signature_data'])) {
            $salesOrder->addMediaFromBase64($validated['signature_data'])
                ->usingFileName("signature-so-{$salesOrder->id}-".time().'.png')
                ->toMediaCollection('digital_signature', 's3');
        }

        if ($request->hasFile('signed_document')) {
            $salesOrder->addMediaFromRequest('signed_document')
                ->toMediaCollection('signed_so', 's3');
        }

        $salesOrder->signatures()->updateOrCreate(
            [
                'role' => 'Customer (Public)',
                'signer_name' => $validated['signer_name'],
                'signer_title' => $validated['signer_title'],
            ],
            [
                'signature_type' => ApprovalSignatureType::Approver->value,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'signed_at' => now(),
            ]
        );

        $salesOrder->update(['status' => SalesOrderStatus::Approved]);

        return view('crm::public.sales-order.signed', compact('salesOrder'));
    }
}
