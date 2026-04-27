<?php

namespace Modules\CRM\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Log;
use Modules\CRM\Enums\GeneralInformationStatus;
use Modules\CRM\Http\Requests\RiskRegisterWebhookRequest;
use Modules\CRM\Models\GeneralInformation;

class RiskRegisterWebhookController extends Controller
{
    use ApiResponse;

    /**
     * Handle the incoming webhook from Risk Register system.
     * Expected Payload:
     * {
     *   "rr_submission_id": "RR-SUB-12345",
     *   "rr_document_number": "RR-2024-001",
     *   "status": "APPROVED",
     *   "general_information_id": "uuid-of-gi-record"
     * }
     * Header: X-RR-Signature (Optional/Recommended for shared secret validation)
     */
    public function handle(RiskRegisterWebhookRequest $request)
    {
        // Validation is handled by RiskRegisterWebhookRequest

        $validated = $request->validated();
        $gi = GeneralInformation::findOrFail($validated['general_information_id']);

        // 2. Update Risk Register details
        $statusMap = [
            'APPROVED' => GeneralInformationStatus::Approved->value,
            'REJECTED' => GeneralInformationStatus::Rejected->value,
            'IN_PROGRESS' => 'in_progress', // Maintain specific custom RR statuses if needed, or use Enum if exists
            'SUBMITTED' => GeneralInformationStatus::Submitted->value,
        ];

        $gi->update([
            'rr_submission_id' => $validated['rr_submission_id'],
            'rr_document_number' => $validated['rr_document_number'],
            'rr_document_path' => $validated['rr_document_path'] ?? null,
            'rr_status' => $statusMap[$validated['status']] ?? 'draft',
            'rr_payload' => $request->all(),
        ]);

        Log::info("Risk Register Webhook processed for GI: {$gi->number}, Status: {$gi->rr_status}");

        // 3. Trigger Strict Approval Check
        // If RR is approved, check if we can fully approve the GI (signatures must also be ready)
        if ($gi->rr_status === GeneralInformationStatus::Approved->value && $gi->isFullyApproved()) {
            $gi->update(['status' => GeneralInformationStatus::Approved]);
            Log::info("General Information {$gi->number} fully APPROVED via Webhook.");
        }

        return $this->success([
            'current_status' => $gi->status,
            'rr_status' => $gi->rr_status,
        ], 'Webhook processed successfully');
    }
}
