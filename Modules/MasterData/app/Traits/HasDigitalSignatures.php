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
        return $this->signatures()->get();
    }

    /**
     * Add a signature to the model.
     */
    public function addSignature(User $user, string $type, ?string $role = null): void
    {
        $this->signatures()->create([
            'user_id' => $user->id,
            'role' => $role ?? $user->roles->first()?->name ?? 'User',
            'signature_type' => $type,
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
     * Check if a specific approval rule has been satisfied by existing signatures.
     */
    public function isRuleSatisfied(\Modules\MasterData\Models\ApprovalRule $rule): bool
    {
        $signatures = $this->signatures()
            ->where('signature_type', $rule->signature_type)
            ->get();

        return $signatures->contains(function ($signature) use ($rule) {
            if ($rule->approver_type === 'Role') {
                return in_array($signature->role, $rule->approver_role ?? []);
            }

            if ($rule->approver_type === 'User') {
                return in_array($signature->user_id, $rule->approver_user_id ?? []);
            }

            // For simplicity, we assume signature capture at time of signing satisfies rule requirements.
            return false;
        });
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
            if (! $this->isRuleSatisfied($rule)) {
                return false;
            }
        }

        return true;
    }
}
