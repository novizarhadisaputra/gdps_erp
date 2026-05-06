<?php

namespace Modules\Finance\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\CRM\Models\Customer;
use Modules\Finance\Models\AccountMapping;
use Modules\Finance\Models\ChartOfAccount;
use Modules\MasterData\Models\ProjectArea;

class AccountMappingSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            // Garuda Group
            ['name' => 'PT. AERO SYSTEM INDONESIA', 'ar' => '11015000', 'rev' => '31004000', 'accrual' => '11018700'],
            ['name' => 'PT. CITILINK INDONESIA', 'ar' => '11012060', 'rev' => '31005009', 'accrual' => '11018032'],
            ['name' => 'PT. GMF AEROASIA TBK', 'ar' => '11019050', 'rev' => '31009000', 'accrual' => '11018000'],
            ['name' => 'PT. SABRE TRAVEL NETWORK INDONESIA', 'ar' => '10014030', 'rev' => '30006006', 'accrual' => '10018905'],
            ['name' => 'PT. GARUDA INDONESIA-HOGF', 'ar' => '11019006', 'rev' => '30004101', 'accrual' => '11018006'],
            ['name' => 'PT. GARUDA INDONESIA-BKS0', 'ar' => '11010060', 'rev' => '31000060', 'accrual' => '11018060'],
            ['name' => 'PT. GARUDA INDONESIA-BTH0', 'ar' => '11010062', 'rev' => '31000062', 'accrual' => '11018062'],
            ['name' => 'PT. GARUDA INDONESIA-BTJ0', 'ar' => '11010064', 'rev' => '31000064', 'accrual' => '11018064'],
            ['name' => 'PT. GARUDA INDONESIA-DJB0', 'ar' => '11010066', 'rev' => '31000066', 'accrual' => '11018066'],
            ['name' => 'PT. GARUDA INDONESIA-KNO0', 'ar' => '11010076', 'rev' => '31000074', 'accrual' => '11018074'],
            ['name' => 'PT. GARUDA INDONESIA-PDG0', 'ar' => '11010078', 'rev' => '31000078', 'accrual' => '11018078'],
            ['name' => 'PT. GARUDA INDONESIA-PGK0', 'ar' => '11010080', 'rev' => '31000080', 'accrual' => '11018080'],
            ['name' => 'PT. GARUDA INDONESIA-PKU0', 'ar' => '11010082', 'rev' => '31000082', 'accrual' => '11018082'],
            ['name' => 'PT. GARUDA INDONESIA-PLM0', 'ar' => '11010084', 'rev' => '31000084', 'accrual' => '11018084'],
            ['name' => 'PT. GARUDA INDONESIA-TGK0', 'ar' => '11010088', 'rev' => '31000088', 'accrual' => '11018088'],
            ['name' => 'PT. GARUDA INDONESIA-TNJ0', 'ar' => '11010090', 'rev' => '31000090', 'accrual' => '11018090'],
            ['name' => 'PT. GARUDA INDONESIA-HODI', 'ar' => '11010012', 'rev' => '30004100', 'accrual' => '11018100'],
            ['name' => 'PT. GARUDA INDONESIA-CGK0', 'ar' => '11010124', 'rev' => '31000124', 'accrual' => '11018124'],
            ['name' => 'PT. GARUDA INDONESIA-DPS0', 'ar' => '11010188', 'rev' => '31000188', 'accrual' => '11018188'],
            ['name' => 'PT. GARUDA INDONESIA-JOG0', 'ar' => '11010190', 'rev' => '31000190', 'accrual' => '11018190'],
            ['name' => 'PT. GARUDA INDONESIA-KOE0', 'ar' => '11010192', 'rev' => '31000192', 'accrual' => '11018192'],
            ['name' => 'PT. GARUDA INDONESIA-LOP0', 'ar' => '11010194', 'rev' => '31000194', 'accrual' => '11018194'],
            ['name' => 'PT. GARUDA INDONESIA-MLG0', 'ar' => '11010196', 'rev' => '31000196', 'accrual' => '11018196'],
            ['name' => 'PT. GARUDA INDONESIA-SOC0', 'ar' => '11010198', 'rev' => '31000198', 'accrual' => '11018198'],
            ['name' => 'PT. GARUDA INDONESIA-SRG0', 'ar' => '11010200', 'rev' => '31000200', 'accrual' => '11018200'],
            ['name' => 'PT. GARUDA INDONESIA-SUB0', 'ar' => '11010202', 'rev' => '31000202', 'accrual' => '11018202'],
            ['name' => 'PT. GARUDA INDONESIA-AMQ0', 'ar' => '11010242', 'rev' => '31000242', 'accrual' => '11018242'],
            ['name' => 'PT. GARUDA INDONESIA-BDJ0', 'ar' => '11010244', 'rev' => '31000244', 'accrual' => '11018244'],
            ['name' => 'PT. GARUDA INDONESIA-BIK0', 'ar' => '11010248', 'rev' => '31000248', 'accrual' => '11018248'],
            ['name' => 'PT. GARUDA INDONESIA-BPN0', 'ar' => '11010250', 'rev' => '31000250', 'accrual' => '11018250'],
            ['name' => 'PT. GARUDA INDONESIA-DJJ0', 'ar' => '11010252', 'rev' => '31000252', 'accrual' => '11018252'],
            ['name' => 'PT. GARUDA INDONESIA-GTO0', 'ar' => '11010254', 'rev' => '31000254', 'accrual' => '11018254'],
            ['name' => 'PT. GARUDA INDONESIA-KDI0', 'ar' => '11010256', 'rev' => '31000256', 'accrual' => '11018256'],
            ['name' => 'PT. GARUDA INDONESIA-MDC0', 'ar' => '11010260', 'rev' => '31000260', 'accrual' => '11018260'],
            ['name' => 'PT. GARUDA INDONESIA-MKQ0', 'ar' => '11010264', 'rev' => '31000264', 'accrual' => '11018264'],
            ['name' => 'PT. GARUDA INDONESIA-PKY0', 'ar' => '11010270', 'rev' => '31000270', 'accrual' => '11018270'],
            ['name' => 'PT. GARUDA INDONESIA-PLW0', 'ar' => '11010272', 'rev' => '31000272', 'accrual' => '11018272'],
            ['name' => 'PT. GARUDA INDONESIA-PNK0', 'ar' => '11010274', 'rev' => '31000274', 'accrual' => '11018274'],
            ['name' => 'PT. GARUDA INDONESIA-SOQ0', 'ar' => '11010276', 'rev' => '31000276', 'accrual' => '11018276'],
            ['name' => 'PT. GARUDA INDONESIA-TIM0', 'ar' => '11010280', 'rev' => '31000280', 'accrual' => '11018280'],
            ['name' => 'PT. GARUDA INDONESIA-TTE0', 'ar' => '11010284', 'rev' => '31000284', 'accrual' => '11018284'],
            ['name' => 'PT. GARUDA INDONESIA-UPG0', 'ar' => '11010286', 'rev' => '31000286', 'accrual' => '11018286'],
            ['name' => 'PT. GARUDA INDONESIA-LBJ', 'ar' => '10010114', 'rev' => '31005025', 'accrual' => '11018288'],
            ['name' => 'PT. AEROFOOD INDONESIA-AC00', 'ar' => '11014000', 'rev' => '31003000', 'accrual' => '11018500'],
            ['name' => 'PT. AEROFOOD INDONESIA-AC01', 'ar' => '11014002', 'rev' => '31003002', 'accrual' => '11018502'],

            // Third Party & FMCG
            ['name' => 'PT KALISTA BIRU NUSANTARA', 'ar' => '10017200', 'rev' => '30015011', 'accrual' => '10018905'],
            ['name' => 'PT KALISTA SOTER HASTIA', 'ar' => '10017200', 'rev' => '30015011', 'accrual' => '10018905'],
            ['name' => 'PT. AERO GLOBE INDONESIA', 'ar' => '10017200', 'rev' => '30015011', 'accrual' => '10018905'],
            ['name' => 'PT. AEROJASA CARGO', 'ar' => '10017200', 'rev' => '30015011', 'accrual' => '10018905'],
            ['name' => 'PT. AEROWISATA', 'ar' => '10017200', 'rev' => '30015011', 'accrual' => '10018905'],
            ['name' => 'PT. AGRINAS JALADRI NUSANTARA', 'ar' => '10017200', 'rev' => '30015011', 'accrual' => '10018905'],
            ['name' => 'PT. ANEKA PETROINDO RAYA', 'ar' => '10017200', 'rev' => '30015011', 'accrual' => '10018905'],
            ['name' => 'PT. ANUGERAH BANGUN BERSAMA', 'ar' => '10017200', 'rev' => '30015011', 'accrual' => '10018905'],
            ['name' => 'PT. BAKER HUGHES INDONESIA', 'ar' => '10017200', 'rev' => '30015011', 'accrual' => '10018905'],
            ['name' => 'PT. BRAJA MUKTI CAKRA', 'ar' => '10017200', 'rev' => '30015011', 'accrual' => '10018905'],
            ['name' => 'PT. EKSPRES TRANSPORTASI ANTARBENUA', 'ar' => '10017200', 'rev' => '30015011', 'accrual' => '10018905'],
            ['name' => 'PT. JALAN-JALAN NUSANTARA', 'ar' => '10017200', 'rev' => '30015011', 'accrual' => '10018905'],
            ['name' => 'PT. KERETA CEPAT INDONESIA CHINA', 'ar' => '10017200', 'rev' => '30015011', 'accrual' => '10018905'],
            ['name' => 'PT. KOPNUSPOS', 'ar' => '10017200', 'rev' => '30015011', 'accrual' => '10018905'],
            ['name' => 'PT. KRAYON KONSULTAN INDO', 'ar' => '10017200', 'rev' => '30015011', 'accrual' => '10018905'],
            ['name' => 'PT. LINFOX LOGISTICS INDONESIA', 'ar' => '10017200', 'rev' => '30015011', 'accrual' => '10018905'],
            ['name' => 'PT. PLN INDONESIA POWER', 'ar' => '10017200', 'rev' => '30015011', 'accrual' => '10018905'],
            ['name' => 'PT. POS LOGISTIK INDONESIA', 'ar' => '10017200', 'rev' => '30015011', 'accrual' => '10018905'],
            ['name' => 'PT. SAIC INTERNASIONAL INDONESIA', 'ar' => '10017200', 'rev' => '30015011', 'accrual' => '10018905'],
            ['name' => 'PT. SGMW MOTOR INDONESIA', 'ar' => '10017200', 'rev' => '30015011', 'accrual' => '10018905'],
            ['name' => 'PT. SGMW SALES INDONESIA', 'ar' => '10017200', 'rev' => '30015011', 'accrual' => '10018905'],
            ['name' => 'PT. WIRA ADIRAJASA DIRGANTARA', 'ar' => '10017200', 'rev' => '30015011', 'accrual' => '10018905'],
            ['name' => 'PT.CITRA MULTI SERVICES', 'ar' => '10017200', 'rev' => '30015011', 'accrual' => '10018905'],
            ['name' => 'PT. AICE ICE CREAM JATIM INDUSTRY', 'ar' => '10017200', 'rev' => '30015011', 'accrual' => '10018905'],
            ['name' => 'PT. ACMIC ELECTRONIC', 'ar' => '10017200', 'rev' => '30015011', 'accrual' => '10018905'],
            ['name' => 'PT. BENTOEL DISTRIBUTOR UTAMA', 'ar' => '10017200', 'rev' => '30015011', 'accrual' => '10018905'],
            ['name' => 'PT. BENTOEL PRIMA', 'ar' => '10017200', 'rev' => '30015011', 'accrual' => '10018905'],
            ['name' => 'PT. GERBANG EDUKASI MAKMUR', 'ar' => '10017200', 'rev' => '30015011', 'accrual' => '10018905'],
            ['name' => 'PT. GRIFF PRIMA ABADI', 'ar' => '10017200', 'rev' => '30015011', 'accrual' => '10018905'],
            ['name' => 'PT. SMARTFREN TELECOM TBK', 'ar' => '10017200', 'rev' => '30015011', 'accrual' => '10018905'],
        ];

        $skipped = [];

        foreach ($data as $row) {
            $fullName = $row['name'];

            // Logic to extract base name and area code
            $baseCustomerName = $fullName;
            $areaCode = null;
            if (str_contains($fullName, '-')) {
                $parts = explode('-', $fullName);
                $baseCustomerName = trim($parts[0]);
                $areaCode = trim($parts[1]);
            }

            // Aggressive normalization for fuzzy matching
            $normalize = function ($name) {
                $name = str_replace(['PT.', 'PT', 'CV.', 'CV', 'TBK', 'Tbk'], '', $name);
                $name = preg_replace('/[^a-zA-Z0-9]/', '', $name);

                return strtolower($name);
            };

            $normalizedSearch = $normalize($baseCustomerName);

            // Find Customer by comparing normalized names
            $customer = Customer::all()->first(function ($c) use ($normalize, $normalizedSearch) {
                return $normalize($c->name) === $normalizedSearch;
            });

            // Fallback to loose ILIKE if normalization didn't find exact match
            if (! $customer) {
                $customer = Customer::where('name', 'ILIKE', "%{$normalizedSearch}%")->first();
            }

            if (! $customer) {
                // Final fallback: try cleaning common words and searching
                $cleanSearch = trim(str_replace(['PT.', 'PT', 'TBK', 'Tbk'], '', $baseCustomerName));
                $customer = Customer::where('name', 'ILIKE', "%{$cleanSearch}%")->first();
            }

            if (! $customer) {
                $skipped[] = $fullName;

                continue;
            }

            // Find Area by Code
            $area = null;
            if ($areaCode) {
                $area = ProjectArea::where('code', $areaCode)->first();
            }

            // Determine Target (Opsi 1: Prefer Area, Fallback to Customer)
            $mappableType = $area ? ProjectArea::class : Customer::class;
            $mappableId = $area ? $area->id : $customer->id;

            $this->createMapping($mappableType, $mappableId, 'receivable', $row['ar'], "Ref: {$fullName}");
            $this->createMapping($mappableType, $mappableId, 'revenue', $row['rev'], "Ref: {$fullName}");
            $this->createMapping($mappableType, $mappableId, 'accrual', $row['accrual'], "Ref: {$fullName}");
        }

        if (! empty($skipped)) {
            $this->command->warn('Skipped '.count($skipped).' rows because Customer was not found in CRM: '.implode(', ', $skipped));
        }
    }

    private function createMapping(string $type, string $id, string $mappingType, string $coaCode, string $note): void
    {
        $coa = ChartOfAccount::where('code', $coaCode)->first();

        if (! $coa) {
            return;
        }

        AccountMapping::updateOrCreate([
            'mappable_type' => $type,
            'mappable_id' => $id,
            'type' => $mappingType,
        ], [
            'chart_of_account_id' => $coa->id,
            'note' => $note,
        ]);
    }
}
