<?php

namespace Modules\CRM\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\CRM\Enums\ProposalStatus;
use Modules\CRM\Models\Proposal;
use Modules\MasterData\Enums\ApprovalSignatureType;

class PublicProposalController extends Controller
{
    public function show(Proposal $proposal)
    {
        $latestLog = $proposal->communicationLogs()->latest()->first();
        
        return view('crm::public.proposal.sign', compact('proposal', 'latestLog'));
    }

    public function sign(Request $request, Proposal $proposal)
    {
        $validated = $request->validate([
            'signer_name' => 'required|string|max:255',
            'signer_title' => 'required|string|max:255',
            'signature_data' => 'required|string', // Base64 signature
            'signed_proposal' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240', // Max 10MB
        ]);

        $proposal->addMediaFromBase64($validated['signature_data'])
            ->usingFileName("signature-{$proposal->id}-" . time() . ".png")
            ->toMediaCollection('digital_signature', 's3');

        // 2. Check current signatures count for Client (Public)
        $clientSignRole = 'Client (Public)';
        $existingSignatureCount = $proposal->signatures()->where('role', $clientSignRole)->count();
        $isExistingSigner = $proposal->signatures()
            ->where('role', $clientSignRole)
            ->where('signer_name', $validated['signer_name'])
            ->where('signer_title', $validated['signer_title'])
            ->exists();

        if (!$isExistingSigner && $existingSignatureCount >= 3) {
            return back()->withErrors(['message' => 'The maximum number of signatures for this proposal (3) has been reached.']);
        }

        // 3. Store the signed proposal file if uploaded
        if ($request->hasFile('signed_proposal')) {
            $proposal->addMediaFromRequest('signed_proposal')
                ->toMediaCollection('signed_proposal', 's3');
        }

        // 4. Update or create the signature audit record
        $proposal->signatures()->updateOrCreate(
            [
                'role' => $clientSignRole,
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

        // 5. Update proposal status
        $proposal->update([
            'status' => ProposalStatus::Approved,
        ]);

        // 6. Log the activity
        $proposal->communicationLogs()->create([
            'recipient_email' => $proposal->customer?->email,
            'subject' => 'Proposal Signed Publicly',
            'message' => "Proposal signed/updated by {$validated['signer_name']} ({$validated['signer_title']}) from IP {$request->ip()}.",
            'sent_at' => now(),
        ]);

        return view('crm::public.proposal.signed', compact('proposal'));
    }
}
