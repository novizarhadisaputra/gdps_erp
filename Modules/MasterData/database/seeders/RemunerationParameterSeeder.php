<?php

namespace Modules\MasterData\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\MasterData\Models\BenefitType;
use Modules\MasterData\Models\BpjsBasisType;
use Modules\MasterData\Models\BufferCostType;
use Modules\MasterData\Models\ContractType;
use Modules\MasterData\Models\FixedAllowance;
use Modules\MasterData\Models\NonFixedAllowance;
use Modules\MasterData\Models\PartnerFeeType;
use Modules\MasterData\Models\PtkpConfig;
use Modules\MasterData\Models\TaxScheme;
use Modules\MasterData\Models\ThrBasisType;

class RemunerationParameterSeeder extends Seeder
{
    public function run(): void
    {
        // Kol A: Tipe Kontrak
        $contractTypes = [
            ['code' => 'PKWT', 'name' => 'PKWT (Pegawai Tidak Tetap)'],
            ['code' => 'PKWTT', 'name' => 'PKWTT (Pegawai Tetap)'],
            ['code' => 'MITRA', 'name' => 'Bukan Pegawai (Mitra, Tenaga Ahli, Freelancer)'],
        ];
        foreach ($contractTypes as $data) {
            ContractType::updateOrCreate(['code' => $data['code']], $data);
        }

        // Kol B: Tunjangan Tetap (Fixed Allowance)
        $fixedAllowances = [
            ['name' => 'Tunjangan Jabatan', 'is_bpjs_base' => true, 'is_taxable' => true],
            ['name' => 'Tunjangan Fungsional', 'is_bpjs_base' => true, 'is_taxable' => true],
            ['name' => 'Tunjangan Komunikasi', 'is_bpjs_base' => true, 'is_taxable' => true],
            ['name' => 'Tunjangan Transportasi (Tetap)', 'is_bpjs_base' => true, 'is_taxable' => true],
            ['name' => 'Tunjangan Makan (Tetap)', 'is_bpjs_base' => true, 'is_taxable' => true],
            ['name' => 'Tunjangan Lembur (Tetap)', 'is_bpjs_base' => true, 'is_taxable' => true],
            ['name' => 'Tunjangan Shift (Tetap)', 'is_bpjs_base' => true, 'is_taxable' => true],
            ['name' => 'Tunjangan Sertifikasi/Keahlian', 'is_bpjs_base' => true, 'is_taxable' => true],
        ];
        foreach ($fixedAllowances as $data) {
            FixedAllowance::updateOrCreate(['name' => $data['name']], array_merge(['default_amount' => 0, 'is_active' => true], $data));
        }

        // Kol C: Tunjangan Tidak Tetap (Non-Fixed Allowance)
        $nonFixedAllowances = [
            ['name' => 'Tunjangan Kehadiran', 'is_taxable' => true, 'calculation_basis' => 'flat'],
            ['name' => 'Tunjangan Transportasi (Tidak Tetap)', 'is_taxable' => true, 'calculation_basis' => 'per_day'],
            ['name' => 'Tunjangan Makan (Tidak Tetap)', 'is_taxable' => true, 'calculation_basis' => 'per_day'],
            ['name' => 'Tunjangan Lembur (Tidak Tetap)', 'is_taxable' => true, 'calculation_basis' => 'per_hour'],
            ['name' => 'Tunjangan Shift (Tidak Tetap)', 'is_taxable' => true, 'calculation_basis' => 'flat'],
            ['name' => 'Extra Voeding', 'is_taxable' => true, 'calculation_basis' => 'flat'],
            ['name' => 'Insentif Hari Raya', 'is_taxable' => true, 'calculation_basis' => 'flat'],
            ['name' => 'Penghasilan Bulanan', 'is_taxable' => true, 'calculation_basis' => 'flat'],
        ];
        foreach ($nonFixedAllowances as $data) {
            NonFixedAllowance::updateOrCreate(['name' => $data['name']], array_merge(['default_amount' => 0, 'is_active' => true], $data));
        }

        // Kol D: Fee/Imbalan Jasa Mitra
        $partnerFeeTypes = [
            ['name' => 'Service Fee Utama', 'calculation_basis' => 'flat', 'is_taxable' => false],
            ['name' => 'Retainer Fee', 'calculation_basis' => 'flat', 'is_taxable' => false],
            ['name' => 'Fee per Output/Unit', 'calculation_basis' => 'per_output', 'is_taxable' => false],
            ['name' => 'Fee per Transaksi', 'calculation_basis' => 'per_output', 'is_taxable' => false],
            ['name' => 'Fee per Project', 'calculation_basis' => 'flat', 'is_taxable' => false],
            ['name' => 'Success Fee', 'calculation_basis' => 'percentage', 'is_taxable' => false],
            ['name' => 'Fee per Jam', 'calculation_basis' => 'per_hour', 'is_taxable' => false],
            ['name' => 'Peak Hour Fee', 'calculation_basis' => 'per_hour', 'is_taxable' => false],
            ['name' => 'Operational Allowance', 'calculation_basis' => 'flat', 'is_taxable' => false],
            ['name' => 'Reimbursement', 'calculation_basis' => 'flat', 'is_taxable' => false],
            ['name' => 'Equipment/Tool Fee', 'calculation_basis' => 'flat', 'is_taxable' => false],
            ['name' => 'Mobility/Area Coverage Fee', 'calculation_basis' => 'flat', 'is_taxable' => false],
            ['name' => 'Quality/SLA Incentive', 'calculation_basis' => 'flat', 'is_taxable' => false],
            ['name' => 'Fee per Hari', 'calculation_basis' => 'per_day', 'is_taxable' => false],
            ['name' => 'Pulsa', 'calculation_basis' => 'flat', 'is_taxable' => false],
            ['name' => 'Sistem', 'calculation_basis' => 'flat', 'is_taxable' => false],
            ['name' => 'Service Kendaraan', 'calculation_basis' => 'flat', 'is_taxable' => false],
        ];
        foreach ($partnerFeeTypes as $data) {
            PartnerFeeType::updateOrCreate(['name' => $data['name']], array_merge(['is_active' => true], $data));
        }

        // Kol F: Tipe Benefit
        $benefitTypes = [
            ['name' => 'THR – Monthly Accrual Billing', 'accrual_method' => 'monthly'],
            ['name' => 'THR – One-Time Billing', 'accrual_method' => 'one_time'],
            ['name' => 'Kompensasi Akhir Kontrak (Uang Pisah)', 'accrual_method' => 'one_time'],
            ['name' => 'Extra Voeding (Barang)', 'accrual_method' => 'monthly'],
            ['name' => 'Bonus', 'accrual_method' => 'one_time'],
        ];
        foreach ($benefitTypes as $data) {
            BenefitType::updateOrCreate(['name' => $data['name']], array_merge(['is_active' => true], $data));
        }

        // Kol G: Biaya Buffer/Inval/MP
        $bufferCostTypes = [
            ['name' => 'Biaya MP Pengganti (Cuti/Sakit)'],
            ['name' => 'Biaya MP Pengganti (Isolasi Mandiri)'],
        ];
        foreach ($bufferCostTypes as $data) {
            BufferCostType::updateOrCreate(['name' => $data['name']], array_merge(['is_active' => true], $data));
        }

        // Kol I: Basis Pengali THR & Pesangon
        $thrBasisTypes = [
            ['name' => 'Adjusted Gaji Pokok', 'formula_code' => 'gaji_pokok'],
            ['name' => 'Adjusted Gaji Pokok + Tunjangan Tetap', 'formula_code' => 'gaji_plus_tetap'],
            ['name' => 'Adjusted Gaji Pokok + Tunjangan Tetap + Sebagian Tunjangan Tidak Tetap (Makan & Transpor)', 'formula_code' => 'gaji_plus_tetap_plus_sebagian'],
        ];
        foreach ($thrBasisTypes as $data) {
            ThrBasisType::updateOrCreate(['formula_code' => $data['formula_code']], array_merge(['is_active' => true], $data));
        }

        // Kol K: Basis Pengali BPJS
        $bpjsBasisTypes = [
            ['name' => 'Adjusted Gaji Pokok', 'formula_code' => 'gaji_pokok'],
            ['name' => 'Adjusted Gaji Pokok + Tunjangan Tetap', 'formula_code' => 'gaji_plus_tunjangan_tetap'],
        ];
        foreach ($bpjsBasisTypes as $data) {
            BpjsBasisType::updateOrCreate(['formula_code' => $data['formula_code']], array_merge(['is_active' => true], $data));
        }

        // Kol Q: Tipe PPh21
        $taxSchemes = [
            ['name' => 'Skema 1 – TER Bulanan (Pegawai Tetap)', 'scheme_code' => 'skema_1', 'notes' => 'Tarif Efektif Rata-rata bulanan untuk pegawai tetap (PPh 21 Pasal 21)'],
            ['name' => 'Skema 2a – Tidak Tetap Harian', 'scheme_code' => 'skema_2a', 'notes' => 'Pegawai tidak tetap dibayar harian'],
            ['name' => 'Skema 2b – Tidak Tetap Bulanan', 'scheme_code' => 'skema_2b', 'notes' => 'Pegawai tidak tetap dibayar bulanan'],
            ['name' => 'Skema 2c – Tidak Tetap per Project', 'scheme_code' => 'skema_2c', 'notes' => 'Pegawai tidak tetap dibayar per project'],
            ['name' => 'Skema 2d – Tidak Tetap Lainnya', 'scheme_code' => 'skema_2d', 'notes' => 'Kategori lainnya untuk pegawai tidak tetap'],
            ['name' => 'Skema 3 – Fix Percentage', 'scheme_code' => 'skema_3', 'notes' => 'PPh 21 dengan tarif tetap (fix %)'],
            ['name' => 'Skema 4 – Bersama (Gross-Up)', 'scheme_code' => 'skema_4', 'notes' => 'PPh 21 ditanggung bersama, gross-up'],
            ['name' => 'Skema 5 – Karyawan (Net)', 'scheme_code' => 'skema_5', 'notes' => 'PPh 21 ditanggung karyawan (net)'],
        ];
        foreach ($taxSchemes as $data) {
            TaxScheme::updateOrCreate(['scheme_code' => $data['scheme_code']], array_merge(['is_active' => true], $data));
        }

        // Kol R: PTKP
        $ptkpConfigs = [
            ['code' => 'TK/0', 'name' => 'Tidak Kawin, 0 Tanggungan', 'tax_category' => 'A', 'annual_amount' => 54000000],
            ['code' => 'TK/1', 'name' => 'Tidak Kawin, 1 Tanggungan', 'tax_category' => 'A', 'annual_amount' => 58500000],
            ['code' => 'TK/2', 'name' => 'Tidak Kawin, 2 Tanggungan', 'tax_category' => 'B', 'annual_amount' => 63000000],
            ['code' => 'TK/3', 'name' => 'Tidak Kawin, 3 Tanggungan', 'tax_category' => 'B', 'annual_amount' => 67500000],
            ['code' => 'K/0', 'name' => 'Kawin, 0 Tanggungan', 'tax_category' => 'A', 'annual_amount' => 58500000],
            ['code' => 'K/1', 'name' => 'Kawin, 1 Tanggungan', 'tax_category' => 'B', 'annual_amount' => 63000000],
            ['code' => 'K/2', 'name' => 'Kawin, 2 Tanggungan', 'tax_category' => 'B', 'annual_amount' => 67500000],
            ['code' => 'K/3', 'name' => 'Kawin, 3 Tanggungan', 'tax_category' => 'C', 'annual_amount' => 72000000],
        ];
        foreach ($ptkpConfigs as $data) {
            PtkpConfig::updateOrCreate(['code' => $data['code']], array_merge(['is_active' => true], $data));
        }
    }
}
