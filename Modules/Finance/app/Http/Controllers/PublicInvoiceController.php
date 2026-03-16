<?php

namespace Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\CRM\Http\Requests\SignDocumentRequest;
use Modules\Finance\Enums\InvoiceStatus;
use Modules\Finance\Models\Invoice;
use Modules\MasterData\Enums\ApprovalSignatureType;

class PublicInvoiceController extends Controller
{
    public function show(Invoice $invoice)
    {
        return view('finance::public.invoice.sign', compact('invoice'));
    }

    public function sign(SignDocumentRequest $request, Invoice $invoice)
    {
        $validated = $request->validated();

        if (! empty($validated['signature_data'])) {
            $invoice->addMediaFromBase64($validated['signature_data'])
                ->usingFileName("signature-inv-{$invoice->id}-".time().'.png')
                ->toMediaCollection('digital_signature', 's3');
        }

        if ($request->hasFile('signed_document')) {
            $invoice->addMediaFromRequest('signed_document')
                ->toMediaCollection('signed_invoice', 's3');
        }

        $invoice->signatures()->updateOrCreate(
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

        $invoice->update(['status' => InvoiceStatus::Sent]);

        return view('finance::public.invoice.signed', compact('invoice'));
    }
}
