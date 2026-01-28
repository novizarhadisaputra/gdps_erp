<?php

namespace Modules\MasterData\Services;

use App\Models\User;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Modules\MasterData\Models\ApprovalRule;

class SignatureService
{
    /**
     * Evaluate a model against approval rules to determine required approvers.
     */
    public function getRequiredApprovers(Model $model): \Illuminate\Support\Collection
    {
        $resourceType = get_class($model);

        return ApprovalRule::where('resource_type', $resourceType)
            ->where('is_active', true)
            ->get()
            ->filter(function ($rule) use ($model) {
                $fieldValue = $model->{$rule->criteria_field};

                return match ($rule->operator) {
                    '>' => $fieldValue > $rule->value,
                    '>=' => $fieldValue >= $rule->value,
                    '<' => $fieldValue < $rule->value,
                    '<=' => $fieldValue <= $rule->value,
                    '=' => $fieldValue == $rule->value,
                    default => false,
                };
            })
            ->sortBy('order');
    }

    /**
     * Verify if the user's signature PIN is correct.
     */
    public function verifyPin(User $user, string $pin): bool
    {
        if (! $user->signature_pin) {
            return false;
        }

        return Hash::check($pin, $user->signature_pin);
    }

    /**
     * Generate a QR Code for the signature data.
     */
    public function generateQRCode(string $data): string
    {
        $options = new QROptions([
            'version' => 5,
            'outputType' => QRCode::OUTPUT_MARKUP_SVG,
            'eccLevel' => QRCode::ECC_L,
        ]);

        return (new QRCode($options))->render($data);
    }

    /**
     * Create a secure data string for the signature QR Code.
     */
    public function createSignatureData(User $user, Model $model, string $type): string
    {
        $timestamp = now()->toIso8601String();
        $recordId = $model->getKey();
        $recordClass = class_basename($model);

        // Simple data format, in production this should be a signed JWT or a verification URL
        return json_encode([
            'signed_by' => $user->name,
            'role' => $user->roles->first()?->name ?? 'User',
            'type' => $type,
            'document' => "{$recordClass} #{$recordId}",
            'date' => $timestamp,
            'verify' => url('/verify-signature/'.base64_encode("{$recordClass}:{$recordId}:{$user->id}:{$timestamp}")),
        ]);
    }
}
