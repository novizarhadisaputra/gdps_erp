<?php

namespace Modules\MasterData\Services;

use App\Models\User;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Common\Version;
use chillerlan\QRCode\Output\QROutputInterface;
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
        // ... (existing code)

        return ApprovalRule::where('resource_type', $resourceType)
            ->where('is_active', true)
            ->get()
            ->filter(function ($rule) use ($model) {
                // If no criteria, it applies
                if (empty($rule->criteria_field) || empty($rule->operator)) {
                    return true;
                }

                $fieldValue = $model->{$rule->criteria_field};

                // Handle GeneralInformation special case (if criteria is sequence_number but logic implies existence)
                // But for now, standard comparison:
                return match ($rule->operator) {
                    '>' => $fieldValue > $rule->value,
                    '>=' => $fieldValue >= $rule->value,
                    '<' => $fieldValue < $rule->value,
                    '<=' => $fieldValue <= $rule->value,
                    '=' => $fieldValue == $rule->value,
                    'between' => $fieldValue >= $rule->value && $fieldValue <= $rule->max_value,
                    default => false,
                };
            })
            ->sortBy('order');
    }

    public function isEligibleApprover(ApprovalRule $rule, User $user): bool
    {
        if ($rule->approver_type === 'Role') {
            $userRoles = $user->roles->pluck('name')->toArray();
            $ruleRoles = $rule->approver_role ?? [];

            return ! empty(array_intersect($userRoles, $ruleRoles));
        }

        if ($rule->approver_type === 'User') {
            return in_array($user->id, $rule->approver_user_id ?? []);
        }

        if ($rule->approver_type === 'Position') {
            return in_array($user->position, $rule->approver_position ?? []);
        }

        if ($rule->approver_type === 'Unit') {
            // Check User's unit_id directly
            $userUnitId = $user->unit_id;
            if (! $userUnitId) {
                return false;
            }

            return in_array($userUnitId, $rule->approver_unit_id ?? []);
        }

        return false;
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
            'version' => Version::AUTO,
            'outputType' => QROutputInterface::GDIMAGE_PNG,
            'eccLevel' => EccLevel::L,
            'addQuietzone' => true,
            'imageBase64' => true,
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
        $recordClass = get_class($model); // Use FQCN for better resolution later

        // Simple data format, encoded symmetrically for brevity in QR
        $data = [
            'class' => $recordClass,
            'id' => $recordId,
            'user' => $user->id,
            'type' => $type,
            'time' => $timestamp,
        ];

        $token = base64_encode(json_encode($data));

        return url('/verify-signature/'.$token);
    }

    /**
     * Decode and verify signature data from a token.
     */
    public function decodeToken(string $token): ?array
    {
        try {
            $data = json_decode(base64_decode($token), true);

            if (! isset($data['class'], $data['id'], $data['user'])) {
                return null;
            }

            $model = $data['class']::findOrFail($data['id']);
            $user = User::findOrFail($data['user']);

            return [
                'model' => $model,
                'user' => $user,
                'type' => $data['type'] ?? 'approved',
                'time' => $data['time'] ?? null,
                'data' => $data,
            ];
        } catch (\Exception $e) {
            return null;
        }
    }
}
