<?php

namespace Modules\MasterData\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\MasterData\Models\AppSetting;

class AppSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'group' => 'custom',
                'key' => 'revenue_segment_third_party',
                'payload' => [
                    'debit_account' => '10018905',
                    'debit_posting_key' => '40',
                    'credit_account' => '30015011',
                    'credit_posting_key' => '50',
                    'description' => 'Default GL Accounts for Third Party Revenue Segments',
                ],
                'is_active' => true,
            ],
            [
                'group' => 'seo',
                'key' => 'site_metadata',
                'payload' => [
                    'title' => 'GDPS ERP',
                    'description' => 'Integrated Enterprise Resource Planning System for GDPS',
                    'keywords' => 'erp, gdps, logistics, enterprise resource planning',
                    'author' => 'GDPS IT Team',
                    'og_image' => '/assets/img/og-image.png',
                ],
                'is_active' => true,
            ],
            [
                'group' => 'finance',
                'key' => 'global_financial_parameters',
                'payload' => [
                    'corp_tax_rate' => 22.00,
                    'interest_rate' => 1.50,
                    'management_fee_rate' => 15.00,
                    'vat_rate' => 12.00,
                    'description' => 'Global financial parameters for profitability analysis and invoicing',
                ],
                'is_active' => true,
            ],
        ];

        foreach ($settings as $setting) {
            AppSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
