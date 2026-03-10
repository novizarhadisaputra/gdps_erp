<?php

namespace Modules\MasterData\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\CRM\Models\Customer;
use Modules\MasterData\Enums\LegalEntityType;
use Modules\MasterData\Models\BillingOption;
use Modules\MasterData\Models\IndustrialSector;
use Modules\MasterData\Models\Item;
use Modules\MasterData\Models\ItemCategory;
use Modules\MasterData\Models\PaymentTerm;
use Modules\MasterData\Models\ProductCluster;
use Modules\MasterData\Models\ProjectArea;
use Modules\MasterData\Models\ProjectType;
use Modules\MasterData\Models\RevenueSegment;
use Modules\MasterData\Models\SkillCategory;
use Modules\MasterData\Models\Tax;
use Modules\MasterData\Models\UnitOfMeasure;
use Modules\MasterData\Models\WorkScheme;

class MasterCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Revenue Segments
        $revenueSegments = [
            ['code' => 'GAG', 'name' => 'GA Group'],
            ['code' => 'GMF', 'name' => 'GMF'],
            ['code' => 'TP', 'name' => 'Third Parties'],
        ];
        foreach ($revenueSegments as $data) {
            RevenueSegment::updateOrCreate(['code' => $data['code']], $data);
        }

        // Industrial Sectors
        $industrialSectors = [
            ['code' => 'AVN', 'name' => 'Aviation'],
            ['code' => 'ENG', 'name' => 'Energy'],
            ['code' => 'FIN', 'name' => 'Finance'],
            ['code' => 'FMCG', 'name' => 'FMCG'],
            ['code' => 'RETL', 'name' => 'Retail'],
            ['code' => 'IT', 'name' => 'IT'],
            ['code' => 'LSC', 'name' => 'Logistics & Supply Chain'],
            ['code' => 'MFG', 'name' => 'Manufacturing'],
            ['code' => 'ONG', 'name' => 'Oil & Gas'],
            ['code' => 'PROP', 'name' => 'Property'],
            ['code' => 'TRAN', 'name' => 'Transportation'],
        ];
        foreach ($industrialSectors as $data) {
            IndustrialSector::updateOrCreate(['code' => $data['code']], $data);
        }

        // Skill Categories
        $skillCategories = [
            ['code' => 'LOW', 'name' => 'Low Skill'],
            ['code' => 'MED', 'name' => 'Medium Skill'],
            ['code' => 'HIGH', 'name' => 'High Skill'],
        ];
        foreach ($skillCategories as $data) {
            SkillCategory::updateOrCreate(['code' => $data['code']], $data);
        }

        // Work Schemes
        $workSchemes = [
            ['code' => '5HK', 'name' => '5 Hari Kerja'],
            ['code' => '6HK', 'name' => '6 Hari Kerja'],
            ['code' => '2SHFT', 'name' => '2 Shift'],
            ['code' => '3SHFT', 'name' => '3 Shift'],
            ['code' => 'REM', 'name' => 'Remote / Hybrid'],
            ['code' => 'ONQ', 'name' => 'On-Call'],
            ['code' => 'PT', 'name' => 'Part Time'],
            ['code' => 'SPSH', 'name' => 'Split Shift'],
            ['code' => 'FLXH', 'name' => 'Flexible Hour'],
            ['code' => 'RST', 'name' => 'Roster / Rotational'],
            ['code' => 'OT', 'name' => 'Others'],
        ];
        foreach ($workSchemes as $data) {
            WorkScheme::updateOrCreate(['code' => $data['code']], $data);
        }

        // Product Clusters
        $productClusters = [
            ['code' => 'BCA', 'name' => 'Beyond Care'],
            ['code' => 'BCL', 'name' => 'Beyond Clean'],
            ['code' => 'BFR', 'name' => 'Beyond Fresh'],
            ['code' => 'BSE', 'name' => 'Beyond Secure'],
            ['code' => 'BSK', 'name' => 'Beyond Sky'],
            ['code' => 'BFM', 'name' => 'Beyond Facility'],
            ['code' => 'SFA', 'name' => 'Smart Facilities'],
            ['code' => 'GTR', 'name' => 'GDPS Terrace'],
            ['code' => 'MGT', 'name' => 'GDPS Other'],
            ['code' => 'OTS', 'name' => 'Others_'],
        ];
        foreach ($productClusters as $data) {
            ProductCluster::updateOrCreate(['code' => $data['code']], $data);
        }

        // Taxes
        $taxes = [
            ['code' => 'P1', 'name' => 'PPh 21 ditanggung perusahaan/customer'],
            ['code' => 'P2', 'name' => 'PPh 21 ditanggung karyawan'],
            ['code' => 'P3', 'name' => 'PPh 21 Final - perusahaan/customer'],
            ['code' => 'OT', 'name' => 'Others'],
        ];
        foreach ($taxes as $data) {
            Tax::updateOrCreate(['code' => $data['code']], $data);
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
            ProjectArea::updateOrCreate(['name' => $data['name']], $data);
        }
        // Payment Terms
        $paymentTerms = [
            ['code' => 'TOP<30', 'name' => '<30 Hari Kalender', 'days' => 15],
            ['code' => 'TOP30', 'name' => '30 Hari Kalender', 'days' => 30],
            ['code' => 'TOP60', 'name' => '60 Hari Kalender', 'days' => 60],
            ['code' => 'TOP90', 'name' => '90 Hari Kalender', 'days' => 90],
        ];
        foreach ($paymentTerms as $data) {
            PaymentTerm::updateOrCreate(['code' => $data['code']], $data);
        }

        // Project Types
        $projectTypes = [
            ['code' => '01', 'name' => 'TAD/Headcount'],
            ['code' => '02', 'name' => 'Borongan'],
            ['code' => '03', 'name' => 'Mitra'],
            ['code' => '04', 'name' => 'Head Hunter'],
            ['code' => 'OT', 'name' => 'Others'],
        ];
        foreach ($projectTypes as $data) {
            ProjectType::updateOrCreate(['code' => $data['code']], $data);
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
            UnitOfMeasure::updateOrCreate(['code' => $data['code']], $data);
        }

        // Item Categories
        $categories = [
            ['code' => 'MATERIAL', 'name' => 'Material'],
            ['code' => 'EQ', 'name' => 'Equipment'],
        ];
        foreach ($categories as $data) {
            ItemCategory::updateOrCreate(['code' => $data['code']], $data);
        }

        // Items
        $items = [
            // Materials
            ['category' => 'Material', 'name' => 'Sabun Cuci Tangan', 'code' => 'MT-SBT', 'uom' => 'LTR'],
            ['category' => 'Material', 'name' => 'Cairan Pembersih Lantai', 'code' => 'MT-CPL', 'uom' => 'LTR'],
            ['category' => 'Material', 'name' => 'Tissue Roll', 'code' => 'MT-TSR', 'uom' => 'ROLL'],
        ];

        foreach ($items as $itemData) {
            $category = ItemCategory::where('name', $itemData['category'])->first();
            $uom = UnitOfMeasure::where('code', $itemData['uom'])->first();

            if ($category && $uom) {
                Item::updateOrCreate(
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
            BillingOption::updateOrCreate(['code' => $data['code']], $data);
        }

        // Customers (Kode Perusahaan)
        $customers = [
            ['code' => 'ABB', 'name' => 'Anugerah Bangun Bersama', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'ACE', 'name' => 'Anugrah Cita Era Food', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'ACS', 'name' => 'Aerofood Indonesia', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'AGI', 'name' => 'Aero Globe Indonesia', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'AIC', 'name' => 'Aice Indonesia', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'ASA', 'name' => 'Adhitama Sejahtera Alami', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'ASY', 'name' => 'Aero System Indonesia', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'AWS', 'name' => 'Aerowisata', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'BAT', 'name' => 'British American Tobacco', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'BHI', 'name' => 'Baker Hughes Indonesia', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'BMC', 'name' => 'Braja Mukti Cakra', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'BPR', 'name' => 'Bentoel Prima', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'BTO', 'name' => 'Bintang Toedjoe', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'CKT', 'name' => 'Ciptaloka Karsa Teknologi', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'CSF', 'name' => 'Caturnusa Sejahtera Finance', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'DLA', 'name' => 'Deltomed Laboratories', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'DPP', 'name' => 'Dutagaruda Piranti Prima', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'DSJ', 'name' => 'Distribusi Sentra Jaya', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'GFO', 'name' => 'Gagafood', 'legal_entity_type' => null],
            ['code' => 'GGC', 'name' => 'GDPS Goldcare', 'legal_entity_type' => null],
            ['code' => 'GIA', 'name' => 'Garuda Indonesia', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'GMF', 'name' => 'GMF Aeroasia, Tbk.', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'GOT', 'name' => 'GDPS Other', 'legal_entity_type' => null],
            ['code' => 'GPS', 'name' => 'Gita Prima Selaras', 'legal_entity_type' => null],
            ['code' => 'GRP', 'name' => 'Gunung Raja Paksi', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'GTI', 'name' => 'Grab Teknologi Indonesia', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'GDP', 'name' => 'GDPS', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'IKN', 'name' => 'Inklusi Keuangan Nusantara', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'JAE', 'name' => 'Jas Aero Engineering Services', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'JAS', 'name' => 'Jasa Angkasa Semesta', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'JCI', 'name' => 'Japfa Comfeed Indonesia, Tbk.', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'JTA', 'name' => 'Jakarana Tama', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'KOP', 'name' => 'Kopnuspos', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'KPH', 'name' => 'Kreanova Pharmaret', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'KTI', 'name' => 'Kudo Teknologi Indonesia', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'LLI', 'name' => 'Linfox Logistics Indonesia', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'MNN', 'name' => 'Maha Nagari Nusantara', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'MNP', 'name' => 'Madusari Nusaperdana', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'MSA', 'name' => 'Mitra Semen Asia', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'PFI', 'name' => 'Pet Food Indoprima', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'PNT', 'name' => 'Panca Nusa Travelindo', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'QGA', 'name' => 'Citilink Indonesia', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'RIN', 'name' => 'Rhipe International', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'SAN', 'name' => 'Sanad A Mubadala Company', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'SGF', 'name' => 'So Good Food Manufacturing', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'SMU', 'name' => 'Sayap Mas Utama', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'STE', 'name' => 'Smartfren Telecom Tbk', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'SUI', 'name' => 'Sepeda Untuk Indonesia', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'TIN', 'name' => 'Traveloka Indonesia', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'TTR', 'name' => 'Trinusa Travelindo', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'UID', 'name' => 'Universitas Indonesia', 'legal_entity_type' => null],
            ['code' => 'VKA', 'name' => 'Virama Karya', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'YMA', 'name' => 'Yayasan Marala', 'legal_entity_type' => LegalEntityType::Yayasan],
            ['code' => 'ZIL', 'name' => 'Zespri International Limited', 'legal_entity_type' => null],
            ['code' => 'MDI', 'name' => 'Mengniu Dairy Indonesia (Yoyic)', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'FJA', 'name' => 'Fiji Airways', 'legal_entity_type' => null],
            ['code' => 'SSI', 'name' => 'SGMW Sales Indonesia', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'SMI', 'name' => 'SGMW Motor Indonesia', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'KCC', 'name' => 'Kereta Cepat Indonesia China (KCIC)', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'CKI', 'name' => 'Krayon Konsultan Indo', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'RSH', 'name' => 'RS Haji', 'legal_entity_type' => null],
            ['code' => 'SZI', 'name' => 'Shenglu Zhangmei International', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'JJN', 'name' => 'Jalan-Jalan Nusantara', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'SIM', 'name' => 'Sentosa Indo Mulya', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'AUF', 'name' => 'Andalan Utama Foodindo', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'PRS', 'name' => 'Perumnas', 'legal_entity_type' => null],
            ['code' => 'STN', 'name' => 'Sabre Travel Network Indonesia', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'ZIN', 'name' => 'Zalora Indonesia', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'BDU', 'name' => 'Bentoel Distributor Utama', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'SII', 'name' => 'SAIC Internasional Indonesia', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'AJI', 'name' => 'AICE Ice Cream Jatim Industry', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'KBN', 'name' => 'Kementerian BUMN', 'legal_entity_type' => null],
            ['code' => 'RXI', 'name' => 'RUI XUE INTERNATIONAL', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'FMP', 'name' => 'Fashion Marketplace', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'GPA', 'name' => 'Gapura Angkasa', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'AFI', 'name' => 'Alpen Food Industry', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'JPC', 'name' => 'Jakarta Praise Community Church', 'legal_entity_type' => null],
            ['code' => 'KII', 'name' => 'Kerajaan Ice Indonesia', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'GEM', 'name' => 'GERBANG EDUKASI MAKMUR', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'PLI', 'name' => 'Pos Logistik Indonesia', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'MTE', 'name' => 'MENTARI TIMUR EKSPEDISI', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'PAJ', 'name' => 'Pratama Artha Jaya', 'legal_entity_type' => LegalEntityType::CV],
            ['code' => 'AJC', 'name' => 'Aerojasa Cargo', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'AIM', 'name' => 'Andalas Indah Makmur', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'KNA', 'name' => 'Kalista Nusa Armada', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'KSH', 'name' => 'Kalista Soter Hastia', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'BEC', 'name' => 'Bumilangit Entertainment Corpora', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'PIP', 'name' => 'PLN Indonesia Power', 'legal_entity_type' => null],
            ['code' => 'MBS', 'name' => 'Mega Boga Sagara', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'SUB', 'name' => 'Senang Untuk Berbagi', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'KBI', 'name' => 'Kalista Biru Nusantara', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'YIC', 'name' => 'Yammi Ice Cream', 'legal_entity_type' => LegalEntityType::CV],
            ['code' => 'PFT', 'name' => 'Profit Foods Tranding', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'SEI', 'name' => 'Sicepat Ekspres Indonesia', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'HBI', 'name' => 'Hiboo Baby Indonesia', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'PAL', 'name' => 'PNG Air Limited', 'legal_entity_type' => null],
            ['code' => 'AEC', 'name' => 'ACMIC ELECTRONIC', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'BFI', 'name' => 'Belfood Indonesia', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'GFP', 'name' => 'GRIFF PRIMA ABADI', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'KLI', 'name' => 'KOHLER INDONESIA', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'KMI', 'name' => 'KOHLER MANUFACTURING INDONESIA', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'APR', 'name' => 'Aneka Petroindo Raya', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'GMI', 'name' => 'Gading Murni', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'CBI', 'name' => 'CEMERLANG BERJAYA INDONESIA', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'AJN', 'name' => 'Agrinas Jaladri Nusantara', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'SSM', 'name' => 'Strada Satya Makmur', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'TAP', 'name' => 'TALENT ANGELS PTE LTD', 'legal_entity_type' => null],
            ['code' => 'ETA', 'name' => 'Ekspres Transportasi Antarbenua', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'WAD', 'name' => 'Wira Adirajasa Dirgantara', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'ATF', 'name' => 'ARTHA UTAMA FOODINDO', 'legal_entity_type' => LegalEntityType::PT],
            ['code' => 'KMG', 'name' => 'Keumseong LLC', 'legal_entity_type' => null],
        ];

        foreach ($customers as $data) {
            Customer::updateOrCreate(['code' => $data['code']], $data);
        }
    }
}
