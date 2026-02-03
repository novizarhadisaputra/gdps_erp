<?php

namespace Modules\MasterData\Traits;

use App\Models\User;
use Illuminate\Support\Collection;
use Modules\MasterData\Services\SignatureService;

trait HasDigitalSignatures
{
    /**
     * Get the signatures for the model.
     */
    public function signatures(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(\Modules\MasterData\Models\Signature::class, 'signable');
    }

    /**
     * Get the signatures for the model.
     */
    public function getSignatures(): Collection
    {
        return $this->signatures;
    }

    /**
     * Add a signature to the model.
     */
    public function addSignature(User $user, string $type, string $qrCode): void
    {
        $this->signatures()->create([
            'user_id' => $user->id,
            'role' => $user->roles->first()?->name ?? 'User',
            'signature_type' => $type,
            'qr_code_path' => $qrCode,
            'ip_address' => request()->ip(),
            'signed_at' => now(),
        ]);
    }

    /**
     * Check if the model has been signed by a specific role/type.
     */
    /**
     * Check if the model has been signed by a specific role/type.
     * Updated to support checking if any of the given roles have signed (if array passed or loose check).
     */
    public function hasSignatureFrom(string|array $role): bool
    {
        if (is_array($role)) {
            return $this->signatures()->whereIn('role', $role)->exists();
        }

        return $this->signatures()->where('role', $role)->exists();
    }

    /**
     * Determine if all required signatures have been obtained.
     */
    public function isFullyApproved(): bool
    {
        $service = app(SignatureService::class);
        $required = $service->getRequiredApprovers($this);

        if ($required->isEmpty()) {
            return true;
        }

        foreach ($required as $rule) {
            // Updated logic to use isEligibleApprover-like check on existing signatures
            // We need to check if ANY existing signature satisfies this rule.

            // Get all signatures
            $signatures = $this->signatures;

            $ruleSatisfied = $signatures->contains(function ($signature) use ($rule) {
                // We need to check if the signer of this signature WAS eligible for this rule.
                // But we store 'role' in signature.
                // If rule is Role-based, check matching role.
                if ($rule->approver_type === 'Role') {
                    return in_array($signature->role, $rule->approver_role ?? []);
                }

                // If rule is User-based, check user_id
                if ($rule->approver_type === 'User') {
                    return in_array($signature->user_id, $rule->approver_user_id ?? []);
                }

                // If rule is Position/Unit, we assume role/user check covers valid signer identity at time of signing.
                // Or we need to re-verify user current attributes? Usually signature captures authority at moment.
                // But signature table only stores 'role'.
                // Ideally signature should store 'approver_type' or link to rule?
                // For simplicity, let's assume Role match is sufficient for Role type.
                // For User type, check user_id.

                return false;
            });

            if (! $ruleSatisfied) {
                return false;
            }
        }

        return true;
    }
}
