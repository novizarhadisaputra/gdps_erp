<?php

namespace Modules\CRM\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SignDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'signer_name' => 'required|string|max:255',
            'signer_title' => 'required|string|max:255',
            'sender_email' => 'required|email|max:255',
            'signature_data' => 'nullable|string',
            'signed_document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ];
    }

    public function messages(): array
    {
        return [
            'signer_name.required' => 'Full name of signer is required.',
            'signer_title.required' => 'Job title / position is required.',
            'sender_email.required' => 'Your email address is required.',
            'signed_document.required' => 'A signed document file (PDF or image) is required.',
        ];
    }
}
