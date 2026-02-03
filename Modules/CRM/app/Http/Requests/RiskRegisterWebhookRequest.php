<?php

namespace Modules\CRM\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RiskRegisterWebhookRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Signature validation logic can be moved here or kept in middleware/controller
        // For now, let's keep it simple or implement the signature check if header is present
        $secret = config('services.risk_register.webhook_secret');
        if ($secret) {
            $signature = $this->header('X-RR-Signature');

            return $signature === $secret;
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'general_information_id' => 'required|uuid|exists:general_informations,id',
            'rr_submission_id' => 'required|string',
            'rr_document_number' => 'required|string',
            'status' => 'required|string|in:APPROVED,REJECTED,IN_PROGRESS,SUBMITTED',
        ];
    }
}
