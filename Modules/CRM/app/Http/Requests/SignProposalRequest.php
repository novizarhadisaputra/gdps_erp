<?php

namespace Modules\CRM\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SignProposalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Public route — anyone with a valid signed URL may submit.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'signer_name' => 'required|string|max:255',
            'signer_title' => 'required|string|max:255',
            'sender_email' => 'required|email|max:255',
            'signature_data' => 'nullable|string',
            'signed_proposal' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'signer_name.required' => 'Full name of signer is required.',
            'signer_title.required' => 'Job title / position is required.',
            'sender_email.required' => 'Your email address is required.',
            'sender_email.email' => 'Please enter a valid email address.',
            'signature_data.string' => 'Invalid signature format.',
            'signed_proposal.required' => 'A signed proposal file (PDF or image) is required.',
            'signed_proposal.file' => 'Signed proposal must be a file.',
            'signed_proposal.mimes' => 'Signed proposal must be a PDF, JPG, or PNG.',
            'signed_proposal.max' => 'Signed proposal file must not exceed 10MB.',
        ];
    }
}
