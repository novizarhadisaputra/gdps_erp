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
    public function hasSignatureFrom(string $role): bool
    {
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
            if (! $this->hasSignatureFrom($rule->approver_role)) {
                return false;
            }
        }

        return true;
    }
}
