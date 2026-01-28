<?php

namespace Modules\Project\Services;

use Illuminate\Support\Facades\Http;
use Modules\CRM\Models\GeneralInformation;

class RiskRegisterService
{
    /**
     * Upload General Information to 3rd party risk register.
     */
    public function uploadGeneralInfo(GeneralInformation $info): array
    {
        // Mocking API call
        // $response = Http::post('https://api.thirdparty.com/risk-register/upload', [
        //     'customer_name' => $info->customer->name ?? 'New Customer',
        //     'pic_name' => $info->pic_customer_name,
        // ]);

        // return $response->json();

        return [
            'status' => 'success',
            'external_rr_id' => 'RR-'.uniqid(),
            'message' => 'Data uploaded successfully to Risk Register system.',
        ];
    }

    /**
     * Get risk register status from 3rd party system.
     */
    public function getRiskRegisterStatus(string $externalId): string
    {
        // Mocking status check
        // return Http::get("https://api.thirdparty.com/risk-register/status/{$externalId}")->json('status');

        return 'In Progress';
    }
}
