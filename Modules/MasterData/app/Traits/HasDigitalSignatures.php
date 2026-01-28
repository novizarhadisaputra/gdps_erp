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
    public function getSignatures(): Collection
    {
        return collect($this->signatures ?: []);
    }

    /**
     * Add a signature to the model.
     */
    public function addSignature(User $user, string $type, string $qrCode): void
    {
        $signatures = $this->getSignatures();

        $signatures->push([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_role' => $user->roles->first()?->name,
            'type' => $type,
            'qr_code' => $qrCode,
            'signed_at' => now()->toIso8601String(),
        ]);

        $this->update(['signatures' => $signatures->toArray()]);
    }

    /**
     * Check if the model has been signed by a specific role/type.
     */
    public function hasSignatureFrom(string $role): bool
    {
        return $this->getSignatures()->contains('user_role', $role);
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
