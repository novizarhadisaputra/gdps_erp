<?php

namespace Modules\MasterData\Database\Seeders;

use Illuminate\Database\Seeder;

class MasterCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Work Schemes
        $workSchemes = [
            ['code' => '01', 'name' => 'TAD/Headcount'],
            ['code' => '02', 'name' => 'Borongan'],
            ['code' => '03', 'name' => 'Mitra'],
            ['code' => '04', 'name' => 'Head Hunter'],
            ['code' => 'OT', 'name' => 'Others'],
        ];
        foreach ($workSchemes as $data) {
            \Modules\MasterData\Models\WorkScheme::updateOrCreate(['code' => $data['code']], $data);
        }

        // Product Clusters
        $productClusters = [
            ['code' => 'BCA', 'name' => 'Product Cluster Beyond Care'],
            ['code' => 'BCL', 'name' => 'Product Cluster Beyond Clean'],
            ['code' => 'BFR', 'name' => 'Product Cluster Beyond Fresh'],
            ['code' => 'BSE', 'name' => 'Product Cluster Beyond Secure'],
            ['code' => 'BSK', 'name' => 'Product Cluster Beyond Sky'],
            ['code' => 'SFA', 'name' => 'Product Cluster Smart Facilities'],
            ['code' => 'GTR', 'name' => 'Product Cluster GDPS Terrace'],
            ['code' => 'MGT', 'name' => 'Product Cluster GDPS Other'],
            ['code' => 'OTS', 'name' => 'Product Cluster Others_'],
        ];
        foreach ($productClusters as $data) {
            \Modules\MasterData\Models\ProductCluster::updateOrCreate(['code' => $data['code']], $data);
        }

        // Taxes
        $taxes = [
            ['code' => 'P1', 'name' => 'PPh 21 ditanggung perusahaan/customer'],
            ['code' => 'P2', 'name' => 'PPh 21 ditanggung karyawan'],
            ['code' => 'P3', 'name' => 'PPh 21 Final - perusahaan/customer'],
            ['code' => 'OT', 'name' => 'Others'],
        ];
        foreach ($taxes as $data) {
            \Modules\MasterData\Models\Tax::updateOrCreate(['code' => $data['code']], $data);
        }

        // Project Areas
        $areas = [
            ['code' => null, 'name' => 'Kalimantan Timur/Samarinda'],
            ['code' => 'AMQ', 'name' => 'Maluku'],
            ['code' => 'BDG', 'name' => 'Bandung Barat'],
            ['code' => 'BDJ', 'name' => 'Kalimantan Selatan/Banjar'],
            ['code' => 'KBD', 'name' => 'Kab. Bandung'],
            ['code' => 'BEK', 'name' => 'Bekasi'],
            ['code' => 'BJO', 'name' => 'Bojonegoro'],
            ['code' => 'BKL', 'name' => 'Bangkalan'],
            ['code' => 'BLO', 'name' => 'Blora'],
            ['code' => 'BNR', 'name' => 'Banjarnegara'],
            ['code' => 'BNY', 'name' => 'Banyumas'],
            ['code' => 'BOG', 'name' => 'Bogor'],
            ['code' => 'BPN', 'name' => 'Balikpapan'],
            ['code' => 'BRE', 'name' => 'Brebes'],
            ['code' => 'BTG', 'name' => 'Batang'],
            ['code' => 'BTH', 'name' => 'Batam'],
            ['code' => 'BTJ', 'name' => 'Aceh'],
            ['code' => 'BTL', 'name' => 'Blitar'],
            ['code' => 'BWI', 'name' => 'Banyuwangi'],
            ['code' => 'BYL', 'name' => 'Boyolali'],
            ['code' => 'CGK', 'name' => 'Cengkareng'],
            ['code' => 'CIA', 'name' => 'Cianjur, Sukabumi'],
            ['code' => 'CKR', 'name' => 'Cikarang'],
            ['code' => 'CLA', 'name' => 'Cilacap'],
            ['code' => 'CRB', 'name' => 'Cirebon'],
            ['code' => 'DEM', 'name' => 'Demak'],
            ['code' => 'DEP', 'name' => 'Depok'],
            ['code' => 'DJB', 'name' => 'Jambi'],
            ['code' => 'DJJ', 'name' => 'Papua'],
            ['code' => 'DOM', 'name' => 'Papua Barat'],
            ['code' => 'DPS', 'name' => 'Denpasar'],
            ['code' => 'FKS', 'name' => 'Bengkulu'],
            ['code' => 'GAT', 'name' => 'Garut'],
            ['code' => 'GND', 'name' => 'Gorontalo'],
            ['code' => 'GRB', 'name' => 'Grobogan'],
            ['code' => 'GRG', 'name' => 'Grogol'],
            ['code' => 'GRS', 'name' => 'Gresik'],
            ['code' => 'GUM', 'name' => 'Maluku Utara'],
            ['code' => 'JKR', 'name' => 'Jabodetabekar'],
            ['code' => 'HLP', 'name' => 'Halim Perdana Kusuma'],
            ['code' => 'HOF', 'name' => 'Head Office'],
            ['code' => 'IDN', 'name' => 'Indramayu'],
            ['code' => 'JBA', 'name' => 'Jakarta Barat'],
            ['code' => 'JBG', 'name' => 'Jombang'],
            ['code' => 'JBR', 'name' => 'Jember'],
            ['code' => 'JPR', 'name' => 'Jepara'],
            ['code' => 'JSE', 'name' => 'Jakarta Selatan'],
            ['code' => 'JTI', 'name' => 'Jakarta Timur'],
            ['code' => 'JUT', 'name' => 'Jakarta Utara'],
            ['code' => 'KBM', 'name' => 'Kebumen'],
            ['code' => 'KDI', 'name' => 'Sulawesi Tenggara/Kendari'],
            ['code' => 'KDL', 'name' => 'Kendal'],
            ['code' => 'KDR', 'name' => 'Kediri'],
            ['code' => 'KDS', 'name' => 'Kudus'],
            ['code' => 'KLT', 'name' => 'Klaten'],
            ['code' => 'KNG', 'name' => 'Kuningan'],
            ['code' => 'KNO', 'name' => 'Kualanamu'],
            ['code' => 'KRW', 'name' => 'Karawang'],
            ['code' => 'KRY', 'name' => 'Karanganyar'],
            ['code' => 'LMJ', 'name' => 'Lumajang'],
            ['code' => 'LMN', 'name' => 'Lamongan'],
            ['code' => 'LOP', 'name' => 'Lombok'],
            ['code' => 'MAJ', 'name' => 'Majalengka'],
            ['code' => 'MDC', 'name' => 'Sulawesi Utara/Manado'],
            ['code' => 'MDN', 'name' => 'Madiun'],
            ['code' => 'MGL', 'name' => 'Magelang'],
            ['code' => 'MJK', 'name' => 'Mojokerto'],
            ['code' => 'MJU', 'name' => 'Sulawesi Tengah'],
            ['code' => 'MLG', 'name' => 'Malang'],
            ['code' => 'MTN', 'name' => 'Magetan'],
            ['code' => 'NGJ', 'name' => 'Nganjuk'],
            ['code' => 'NGW', 'name' => 'Ngawi'],
            ['code' => 'PAR', 'name' => 'Pare-pare'],
            ['code' => 'PAS', 'name' => 'Pasuruan'],
            ['code' => 'PAT', 'name' => 'Pati'],
            ['code' => 'PBL', 'name' => 'Probolinggo'],
            ['code' => 'PCT', 'name' => 'Pacitan'],
            ['code' => 'PDG', 'name' => 'Sumatera Barat'],
            ['code' => 'SUM', 'name' => 'Sumatera'],
            ['code' => 'PKL', 'name' => 'Pekalongan'],
            ['code' => 'PKU', 'name' => 'Riau/Pekanbaru, Duri/Taluk Kuantan'],
            ['code' => 'PKY', 'name' => 'Kalimantan Tengah/Palangkaraya'],
            ['code' => 'PLG', 'name' => 'Purbalingga'],
            ['code' => 'PLM', 'name' => 'Palembang, Prabumulih'],
            ['code' => 'PMK', 'name' => 'Pamekasan'],
            ['code' => 'PML', 'name' => 'Pemalang'],
            ['code' => 'PML', 'name' => 'Pemalang'],
            ['code' => 'PND', 'name' => 'Pangandaran'],
            ['code' => 'PNK', 'name' => 'Kalimantan Barat/Pontianak'],
            ['code' => 'PON', 'name' => 'Ponorogo'],
            ['code' => 'PRW', 'name' => 'Purworejo'],
            ['code' => 'PWT', 'name' => 'Purwakarta'],
            ['code' => 'RBM', 'name' => 'Rembang'],
            ['code' => 'RE1', 'name' => 'Region 1'],
            ['code' => 'RE2', 'name' => 'Region 2'],
            ['code' => 'RE3', 'name' => 'Region 3'],
            ['code' => 'RE4', 'name' => 'Region 4'],
            ['code' => 'RE5', 'name' => 'Region 5'],
            ['code' => 'SBG', 'name' => 'Subang'],
            ['code' => 'SID', 'name' => 'Sidoarjo'],
            ['code' => 'SIT', 'name' => 'Situbondo'],
            ['code' => 'SMD', 'name' => 'Sumedang'],
            ['code' => 'SMN', 'name' => 'Sumenep'],
            ['code' => 'SMP', 'name' => 'Sampang'],
            ['code' => 'SOC', 'name' => 'Solo'],
            ['code' => 'SRA', 'name' => 'Sragen'],
            ['code' => 'SRG', 'name' => 'Semarang'],
            ['code' => 'SUB', 'name' => 'Surabaya'],
            ['code' => 'TAS', 'name' => 'Tasikmalaya'],
            ['code' => 'TGG', 'name' => 'Sulawesi Barat'],
            ['code' => 'JTM', 'name' => 'Jawa Timur'],
            ['code' => 'TGL', 'name' => 'Tegal'],
            ['code' => 'TJQ', 'name' => 'Bangka Belitung/ Tanjung Pandan'],
            ['code' => 'TKG', 'name' => 'Lampung'],
            ['code' => 'TMG', 'name' => 'Temanggung'],
            ['code' => 'TNJ', 'name' => 'Tanjung Pinang, Kepri'],
            ['code' => 'TRK', 'name' => 'Kalimantan Utara/Tarakan'],
            ['code' => 'UPG', 'name' => 'Sulawesi Selatan/Ujung Pandang'],
            ['code' => 'WNG', 'name' => 'Wonogiri'],
            ['code' => 'WSO', 'name' => 'Wonosobo'],
            ['code' => 'YIA', 'name' => 'Yogyakarta'],
            ['code' => 'ZOD', 'name' => 'Gudang Zoodia'],
            ['code' => 'ALL', 'name' => 'All Area'],
            ['code' => 'CGO', 'name' => 'Cargo Warehouse'],
            ['code' => 'TE3', 'name' => 'Terminal 3'],
            ['code' => 'TE1', 'name' => 'Terminal 1'],
            ['code' => 'TE2', 'name' => 'Terminal 2'],
            ['code' => 'GIT', 'name' => 'GITC'],
            ['code' => 'KSI', 'name' => 'Kebon Sirih'],
            ['code' => 'GSA', 'name' => 'Gunung Sahari'],
            ['code' => 'KMO', 'name' => 'Kemayoran'],
            ['code' => 'BIN', 'name' => 'Bintaro'],
            ['code' => 'CIK', 'name' => 'Cikokol'],
            ['code' => 'TNG', 'name' => 'Tangerang'],
            ['code' => 'JPU', 'name' => 'Jakarta Pusat'],
            ['code' => 'BOF', 'name' => 'Back of Factory'],
            ['code' => 'WDC', 'name' => 'West DC'],
            ['code' => 'MPA', 'name' => 'Menara Palma'],
            ['code' => 'SIL', 'name' => 'Siliwangi'],
            ['code' => 'TBB', 'name' => 'TBB'],
            ['code' => 'JTH', 'name' => 'Jawa Tengah'],
            ['code' => 'TGS', 'name' => 'Tangerang Selatan'],
            ['code' => 'GSK', 'name' => 'Gersik'],
            ['code' => 'JDB', 'name' => 'Jabodetabek'],
            ['code' => 'BBR', 'name' => 'Bintaro,Bogor'],
            ['code' => 'SLP', 'name' => 'Slipi'],
            ['code' => 'JBT', 'name' => 'Jawa Barat'],
            ['code' => 'TBT', 'name' => 'Tebet'],
            ['code' => 'KSL', 'name' => 'Kalimantan,Sulawesi'],
            ['code' => 'BDO', 'name' => 'Bandung'],
            ['code' => 'JKT', 'name' => 'Jakarta'],
            ['code' => 'SGT', 'name' => 'Sumbagut'],
            ['code' => 'MED', 'name' => 'Medan'],
            ['code' => 'IKN', 'name' => 'Ibu Kota Nusantara'],
            ['code' => 'CKD', 'name' => 'Cikande'],
            ['code' => 'FJI', 'name' => 'Fiji'],
            ['code' => 'TLL', 'name' => 'Tegalluar'],
            ['code' => 'BNJ', 'name' => 'Binjai'],
            ['code' => 'SIN', 'name' => 'Singapura'],
            ['code' => 'ROK', 'name' => 'Korea Selatan'],
        ];
        foreach ($areas as $data) {
            \Modules\MasterData\Models\ProjectArea::updateOrCreate(['name' => $data['name']], $data);
        }
        // Payment Terms
        $paymentTerms = [
            ['code' => 'TOP<30', 'name' => '<30 Hari Kalender'],
            ['code' => 'TOP30', 'name' => '30 Hari Kalender'],
            ['code' => 'TOP60', 'name' => '60 Hari Kalender'],
            ['code' => 'TOP90', 'name' => '90 Hari Kalender'],
        ];
        foreach ($paymentTerms as $data) {
            \Modules\MasterData\Models\PaymentTerm::updateOrCreate(['code' => $data['code']], $data);
        }

        // Project Types
        $projectTypes = [
            ['code' => 'HC', 'name' => 'Headcount'],
            ['code' => 'BRG', 'name' => 'Borongan'],
            ['code' => 'OTH', 'name' => 'Others'],
        ];
        foreach ($projectTypes as $data) {
            \Modules\MasterData\Models\ProjectType::updateOrCreate(['code' => $data['code']], $data);
        }

        // Units of Measure
        $uoms = [
            ['code' => 'PRS', 'name' => 'Person'],
            ['code' => 'LOT', 'name' => 'Lot'],
            ['code' => 'PCS', 'name' => 'Pieces'],
            ['code' => 'SET', 'name' => 'Set'],
            ['code' => 'BOX', 'name' => 'Box'],
            ['code' => 'LTR', 'name' => 'Liter'],
            ['code' => 'ROLL', 'name' => 'Roll'],
        ];
        foreach ($uoms as $data) {
            \Modules\MasterData\Models\UnitOfMeasure::updateOrCreate(['code' => $data['code']], $data);
        }

        // Item Categories
        $categories = [
            ['code' => 'MT', 'name' => 'Material'],
            ['code' => 'EQ', 'name' => 'Equipment'],
        ];
        foreach ($categories as $data) {
            \Modules\MasterData\Models\ItemCategory::updateOrCreate(['code' => $data['code']], $data);
        }

        // Items
        $items = [
            // Materials
            ['category' => 'Material', 'name' => 'Sabun Cuci Tangan', 'code' => 'MT-SBT', 'uom' => 'LTR'],
            ['category' => 'Material', 'name' => 'Cairan Pembersih Lantai', 'code' => 'MT-CPL', 'uom' => 'LTR'],
            ['category' => 'Material', 'name' => 'Tissue Roll', 'code' => 'MT-TSR', 'uom' => 'ROLL'],
        ];

        foreach ($items as $itemData) {
            $category = \Modules\MasterData\Models\ItemCategory::where('name', $itemData['category'])->first();
            $uom = \Modules\MasterData\Models\UnitOfMeasure::where('code', $itemData['uom'])->first();

            if ($category && $uom) {
                \Modules\MasterData\Models\Item::updateOrCreate(
                    ['code' => $itemData['code']],
                    [
                        'item_category_id' => $category->id,
                        'unit_of_measure_id' => $uom->id,
                        'name' => $itemData['name'],
                        'is_active' => true,
                    ]
                );
            }
        }

        // Billing Options
        $billingOptions = [
            ['code' => 'BILL', 'name' => 'Ditagihkan'],
            ['code' => 'NB_CMP', 'name' => 'Tidak Ditagihkan (Dibebankan oleh Perusahaan)'],
            ['code' => 'NB_TAD', 'name' => 'Tidak Ditagihkan (Dibebankan ke TAD)'],
            ['code' => 'RMB', 'name' => 'Reimburse'],
            ['code' => 'RMB_FEE', 'name' => 'Reimburse + Management Fee'],
            ['code' => 'NB', 'name' => 'Tidak Ditagihkan'],
            ['code' => 'OTH_BAPP', 'name' => 'Other BAPP'],
        ];
        foreach ($billingOptions as $data) {
            \Modules\MasterData\Models\BillingOption::updateOrCreate(['code' => $data['code']], $data);
        }
    }
}
