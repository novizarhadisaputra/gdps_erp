<?php

namespace Modules\Project\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\CRM\Http\Requests\SignDocumentRequest;
use Modules\MasterData\Enums\ApprovalSignatureType;
use Modules\Project\Enums\WorkCompletionStatus;
use Modules\Project\Models\WorkCompletionReport;

class PublicWorkCompletionController extends Controller
{
    public function show(WorkCompletionReport $report)
    {
        return view('project::public.work-completion.sign', compact('report'));
    }

    public function sign(SignDocumentRequest $request, WorkCompletionReport $report)
    {
        $validated = $request->validated();

        if (! empty($validated['signature_data'])) {
            $report->addMediaFromBase64($validated['signature_data'])
                ->usingFileName("signature-bapp-{$report->id}-".time().'.png')
                ->toMediaCollection('digital_signature', 's3');
        }

        if ($request->hasFile('signed_document')) {
            $report->addMediaFromRequest('signed_document')
                ->toMediaCollection('completion_documents', 's3');
        }

        $report->signatures()->updateOrCreate(
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

        $report->update(['status' => WorkCompletionStatus::Signed]);

        return view('project::public.work-completion.signed', compact('report'));
    }
}
