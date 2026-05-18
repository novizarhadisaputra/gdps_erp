<?php

namespace Modules\MasterData\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\MasterData\Models\TaxObject;

class TaxObjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $taxObjects = [
            [
                'code' => '21-100-01',
                'name' => 'Pegawai Tetap',
                'is_taxable' => true,
                'is_default' => true,
                'is_active' => true,
                'description' => 'Penerima penghasilan bagi Pegawai Tetap (Penerima Upah bulanan/TER).',
            ],
            [
                'code' => '21-100-02',
                'name' => 'Penerima Pensiun Berkala',
                'is_taxable' => true,
                'is_default' => false,
                'is_active' => true,
                'description' => 'Penerima uang pensiun berkala.',
            ],
            [
                'code' => '21-100-03',
                'name' => 'Pegawai Tidak Tetap / Lepas',
                'is_taxable' => true,
                'is_default' => false,
                'is_active' => true,
                'description' => 'Pegawai Tidak Tetap atau Tenaga Kerja Lepas.',
            ],
            [
                'code' => '21-100-07',
                'name' => 'Bukan Pegawai - Berkesinambungan',
                'is_taxable' => true,
                'is_default' => false,
                'is_active' => true,
                'description' => 'Bukan Pegawai yang Menerima Imbalan yang Bersifat Berkesinambungan.',
            ],
            [
                'code' => '21-100-08',
                'name' => 'Bukan Pegawai - Tidak Berkesinambungan',
                'is_taxable' => true,
                'is_default' => false,
                'is_active' => true,
                'description' => 'Bukan Pegawai yang Menerima Imbalan yang Tidak Bersifat Berkesinambungan.',
            ],
            [
                'code' => '21-100-04',
                'name' => 'Distributor Multi Level Marketing (MLM)',
                'is_taxable' => true,
                'is_default' => false,
                'is_active' => true,
                'description' => 'Imbalan kepada distributor Multi Level Marketing (MLM) atau penjualan langsung.',
            ],
            [
                'code' => '21-100-05',
                'name' => 'Agen Asuransi',
                'is_taxable' => true,
                'is_default' => false,
                'is_active' => true,
                'description' => 'Imbalan/komisi kepada agen asuransi.',
            ],
            [
                'code' => '21-100-06',
                'name' => 'Petugas Penjaja Barang Dagangan',
                'is_taxable' => true,
                'is_default' => false,
                'is_active' => true,
                'description' => 'Imbalan kepada petugas penjaja barang dagangan.',
            ],
            [
                'code' => '21-100-09',
                'name' => 'Peserta Kegiatan',
                'is_taxable' => true,
                'is_default' => false,
                'is_active' => true,
                'description' => 'Peserta kegiatan yang menerima upah/imbalan.',
            ],
            [
                'code' => '21-100-11',
                'name' => 'Mantan Pegawai',
                'is_taxable' => true,
                'is_default' => false,
                'is_active' => true,
                'description' => 'Mantan pegawai yang menerima jasa produksi, tantiem, gratifikasi, bonus atau imbalan sejenis.',
            ],
            [
                'code' => '21-100-12',
                'name' => 'Peserta Program Pensiun',
                'is_taxable' => true,
                'is_default' => false,
                'is_active' => true,
                'description' => 'Uang Manfaat Pensiun atau sejenisnya yang diambil sebagian oleh peserta program pensiun yang masih berstatus pegawai.',
            ],
            [
                'code' => 'NON-TAXABLE',
                'name' => 'Bukan Objek Pajak (Non-Taxable)',
                'is_taxable' => false,
                'is_default' => false,
                'is_active' => true,
                'description' => 'Penghasilan/remunerasi yang tidak dikenakan pajak (PPh 21 dikecualikan).',
            ],
        ];

        foreach ($taxObjects as $taxObject) {
            TaxObject::updateOrCreate(['code' => $taxObject['code']], $taxObject);
        }
    }
}
