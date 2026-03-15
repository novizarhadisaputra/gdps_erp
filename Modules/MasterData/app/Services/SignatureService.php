<?php

namespace Modules\MasterData\Services;

use App\Models\User;
use chillerlan\QRCode\Common\EccLevel;
use Modules\MasterData\Notifications\ApprovalRequiredNotification;
use chillerlan\QRCode\Common\Version;
use chillerlan\QRCode\Output\QROutputInterface;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Modules\MasterData\Enums\ApprovalSignatureType;
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
                // 1. Evaluate legacy single condition (if present)
                if (! empty($rule->criteria_field) && ! empty($rule->operator)) {
                    $fieldValue = $model->{$rule->criteria_field};
                    $satisfied = match ($rule->operator) {
                        '>' => $fieldValue > $rule->value,
                        '>=' => $fieldValue >= $rule->value,
                        '<' => $fieldValue < $rule->value,
                        '<=' => $fieldValue <= $rule->value,
                        '=' => $fieldValue == $rule->value,
                        'in' => in_array($fieldValue, array_map('trim', explode(',', $rule->value))),
                        'between' => $fieldValue >= $rule->value && $fieldValue <= $rule->max_value,
                        default => false,
                    };

                    if (! $satisfied) {
                        return false;
                    }
                }

                // 2. Evaluate new multi-conditions (if present)
                if (! empty($rule->conditions) && is_array($rule->conditions)) {
                    foreach ($rule->conditions as $condition) {
                        $field = $condition['field'] ?? null;
                        $operator = $condition['operator'] ?? null;
                        $value = $condition['value'] ?? null;
                        $max = $condition['max_value'] ?? null;

                        if (! $field || ! $operator) {
                            continue;
                        }

                        $fieldValue = $model->{$field};
                        $conditionSatisfied = match ($operator) {
                            '>' => $fieldValue > $value,
                            '>=' => $fieldValue >= $value,
                            '<' => $fieldValue < $value,
                            '<=' => $fieldValue <= $value,
                            '=' => $fieldValue == $value,
                            'in' => in_array($fieldValue, array_map('trim', explode(',', $value))),
                            'between' => $fieldValue >= $value && $fieldValue <= $max,
                            default => false,
                        };

                        if (! $conditionSatisfied) {
                            return false;
                        }
                    }
                }

                return true;
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
     * Get all users eligible for an approval rule.
     */
    public function getEligibleUsers(ApprovalRule $rule): \Illuminate\Support\Collection
    {
        $query = User::query();

        if ($rule->approver_type === 'Role') {
            $query->role($rule->approver_role ?? []);
        } elseif ($rule->approver_type === 'User') {
            $query->whereIn('id', $rule->approver_user_id ?? []);
        } elseif ($rule->approver_type === 'Position') {
            $query->whereIn('position', $rule->approver_position ?? []);
        } elseif ($rule->approver_type === 'Unit') {
            $query->whereIn('unit_id', $rule->approver_unit_id ?? []);
        } else {
            return collect();
        }

        return $query->get();
    }

    /**
     * Notify the next group of approvers for a model.
     */
    public function notifyNextApprovers(Model $model): void
    {
        $required = $this->getRequiredApprovers($model);
        
        // Find the first rule that is NOT yet satisfied
        $nextRule = $required->first(fn ($rule) => ! $model->isRuleSatisfied($rule));

        if ($nextRule) {
            $eligibleUsers = $this->getEligibleUsers($nextRule);
            $url = $this->getResourceUrl($model);
            $message = "A " . class_basename($model) . " requires your approval.";

            foreach ($eligibleUsers as $user) {
                $user->notify(new ApprovalRequiredNotification($model, $message, $url));
            }
        }
    }

    /**
     * Get the Filament resource URL for a model.
     */
    protected function getResourceUrl(Model $model): string
    {
        $class = get_class($model);
        
        $resource = match ($class) {
            \Modules\CRM\Models\Proposal::class => \Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Proposal\ProposalResource::class,
            \Modules\Finance\Models\ProfitabilityAnalysis::class => \Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\ProfitabilityAnalysisResource::class,
            \Modules\CRM\Models\Contract::class => \Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Contract\ContractResource::class,
            \Modules\CRM\Models\MinutesOfAgreement::class => \Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\MinutesOfAgreement\MinutesOfAgreementResource::class,
            \Modules\CRM\Models\GeneralInformation::class => \Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\GeneralInformation\GeneralInformationResource::class,
            \Modules\Project\Models\ProjectInformation::class => \Modules\Project\Filament\Clusters\Project\Resources\ProjectInformations\ProjectInformationResource::class,
            \Modules\Project\Models\WorkCompletionReport::class => \Modules\Project\Filament\Clusters\Project\Resources\WorkCompletionReports\WorkCompletionReportResource::class,
            default => null,
        };

        if ($resource) {
            $parameters = ['record' => $model->getKey()];
            
            // Check if it's a nested resource (like Proposal)
            if (isset($model->lead_id)) {
                $parameters['lead'] = $model->lead_id;
            }

            return $resource::getUrl('view', $parameters);
        }

        return url('/');
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
    public function createSignatureData(?User $user, Model $model, string $type, ?string $guestName = null): string
    {
        $timestamp = now()->toIso8601String();
        $recordId = $model->getKey();
        $recordClass = get_class($model);

        $data = [
            'class' => $recordClass,
            'id' => $recordId,
            'user' => $user?->id,
            'guest_name' => $guestName,
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

            if (! isset($data['class'], $data['id'])) {
                return null;
            }

            $model = $data['class']::findOrFail($data['id']);
            $user = isset($data['user']) ? User::find($data['user']) : null;
            $guestName = $data['guest_name'] ?? null;

            return [
                'model' => $model,
                'user' => $user,
                'guest_name' => $guestName,
                'type' => $data['type'] ?? ApprovalSignatureType::Approver->value,
                'time' => $data['time'] ?? null,
                'data' => $data,
            ];
        } catch (\Exception $e) {
            return null;
        }
    }
}
