<?php

namespace Modules\MasterData\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\MasterData\Models\AssetGroup;
use Modules\MasterData\Models\Item;
use Modules\MasterData\Models\ItemCategory;
use Modules\MasterData\Models\Unit;
use Modules\MasterData\Models\UnitOfMeasure;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // For simplicity, we use the first found unit as default for these global items
        $defaultUnit = Unit::first() ?? Unit::create([
            'name' => 'Internal Unit',
            'code' => 'INT',
            'external_id' => '0',
        ]);

        $itemsData = [
            ['Name' => 'Floor Polisher', 'Unit' => 'Unit', 'Price' => 17000000, 'Category' => 'Tools/Equipment', 'AssetGroup' => 'Kelompok 1'],
            ['Name' => 'Cong-R Dust merek Johnson', 'Unit' => 'Galon', 'Price' => 390000, 'Category' => 'Tools/Equipment', 'AssetGroup' => 'Kelompok 1'],
            ['Name' => 'Forward Cleaner merek Johnson', 'Unit' => 'Galon', 'Price' => 320000, 'Category' => 'Tools/Equipment', 'AssetGroup' => 'Kelompok 1'],
            ['Name' => 'Trusol merek Johnson', 'Unit' => 'Galon', 'Price' => 350000, 'Category' => 'Tools/Equipment', 'AssetGroup' => 'Kelompok 1'],
            ['Name' => 'Break Up Fat merek Johnson', 'Unit' => 'Galon', 'Price' => 450000, 'Category' => 'Tools/Equipment', 'AssetGroup' => 'Kelompok 1'],
            ['Name' => 'Hand Soap merek Johnson', 'Unit' => 'Galon', 'Price' => 190000, 'Category' => 'Tools/Equipment', 'AssetGroup' => 'Kelompok 1'],
            ['Name' => 'New Complete merek Johnson', 'Unit' => 'Galon', 'Price' => 650000, 'Category' => 'Tools/Equipment', 'AssetGroup' => 'Kelompok 1'],
            ['Name' => 'Glance Glass Cleaner merek Johnson', 'Unit' => 'Galon', 'Price' => 180000, 'Category' => 'Tools/Equipment', 'AssetGroup' => 'Kelompok 1'],
            ['Name' => 'Hand Soap merek Best', 'Unit' => 'Pail', 'Price' => 310000, 'Category' => 'Tools/Equipment', 'AssetGroup' => 'Kelompok 1'],
            ['Name' => 'Floor Cleaner merek Best', 'Unit' => 'Pail', 'Price' => 204800, 'Category' => 'Tools/Equipment', 'AssetGroup' => 'Kelompok 1'],
            ['Name' => 'Bowl Getter merek Best', 'Unit' => 'Pail', 'Price' => 224000, 'Category' => 'Tools/Equipment', 'AssetGroup' => 'Kelompok 1'],
            ['Name' => 'Glass Cleaner merek Best', 'Unit' => 'Pail', 'Price' => 112000, 'Category' => 'Tools/Equipment', 'AssetGroup' => 'Kelompok 1'],
            ['Name' => 'Grease Oil merek Best', 'Unit' => 'Pail', 'Price' => 182000, 'Category' => 'Tools/Equipment', 'AssetGroup' => 'Kelompok 1'],
            ['Name' => 'Strip Powder merek Best', 'Unit' => 'Kg', 'Price' => 25000, 'Category' => 'Tools/Equipment', 'AssetGroup' => 'Kelompok 1'],
            ['Name' => 'Carpet Shampoo merek Best', 'Unit' => 'Pail', 'Price' => 245000, 'Category' => 'Tools/Equipment', 'AssetGroup' => 'Kelompok 1'],
            ['Name' => 'Laptop', 'Unit' => 'Unit', 'Price' => 7000000, 'Category' => 'Tools/Equipment', 'AssetGroup' => 'Kelompok 1'],
            ['Name' => 'Printer', 'Unit' => 'Unit', 'Price' => 2500000, 'Category' => 'Tools/Equipment', 'AssetGroup' => 'Kelompok 1'],
            ['Name' => 'ATK & Kertas', 'Unit' => 'Set', 'Price' => 250000, 'Category' => 'Tools/Equipment', 'AssetGroup' => 'Kelompok 1'],
            ['Name' => 'Petty Cash', 'Unit' => 'Bln', 'Price' => 500000, 'Category' => 'Operating Expense', 'AssetGroup' => null],
            ['Name' => 'Biaya Koordinasi Wilayah', 'Unit' => 'Event', 'Price' => 500000, 'Category' => 'Operating Expense', 'AssetGroup' => null],
            ['Name' => 'Sapu Lidi Tanpa Tangkai', 'Unit' => 'Pcs', 'Price' => 7000, 'Category' => 'Tools', 'AssetGroup' => 'Kelompok 1'],
            ['Name' => 'Sarung Tangan Bahan', 'Unit' => 'Psg', 'Price' => 8200, 'Category' => 'Tools', 'AssetGroup' => 'Kelompok 1'],
            ['Name' => 'Gunting Galah/ Gergaji Galah', 'Unit' => 'Btl', 'Price' => 278000, 'Category' => 'Tools', 'AssetGroup' => 'Kelompok 1'],
            ['Name' => 'Oli 2 Tack (oli samping)', 'Unit' => 'Btl', 'Price' => 42500, 'Category' => 'Material', 'AssetGroup' => null],
            ['Name' => 'Busi Potong Rumput Gendong NGK', 'Unit' => 'Pcs', 'Price' => 75000, 'Category' => 'Material', 'AssetGroup' => null],
            ['Name' => 'Batu Asahan', 'Unit' => 'Pcs', 'Price' => 45000, 'Category' => 'Tools', 'AssetGroup' => 'Kelompok 1'],
            ['Name' => 'Gunting Dahan / Akar', 'Unit' => 'Pcs', 'Price' => 37500, 'Category' => 'Tools', 'AssetGroup' => 'Kelompok 1'],
            ['Name' => 'Gunting Tanaman / Teplon', 'Unit' => 'Pcs', 'Price' => 265000, 'Category' => 'Tools', 'AssetGroup' => 'Kelompok 1'],
            ['Name' => 'Jas hujan Model Ponco', 'Unit' => 'Stell', 'Price' => 305000, 'Category' => 'Tools', 'AssetGroup' => 'Kelompok 1'],
            ['Name' => 'Kape Gagang Kayu', 'Unit' => 'pcs', 'Price' => 19000, 'Category' => 'Tools', 'AssetGroup' => 'Kelompok 1'],
            ['Name' => 'Kuas 4"', 'Unit' => 'pcs', 'Price' => 17500, 'Category' => 'Tools', 'AssetGroup' => 'Kelompok 1'],
            ['Name' => 'Mangkok Coil Stater Mesin Potong Rumput Gendong TASCO', 'Unit' => 'pcs', 'Price' => 97000, 'Category' => 'Material', 'AssetGroup' => null],
            ['Name' => 'Sepatu Bot AP Hijau', 'Unit' => 'Psg', 'Price' => 125000, 'Category' => 'Tools', 'AssetGroup' => 'Kelompok 1'],
            ['Name' => 'Selling Flexible Mesin Potong Rumput Gendong TASCO 328 @ 80cm', 'Unit' => 'Pcs', 'Price' => 65000, 'Category' => 'Material', 'AssetGroup' => null],
            ['Name' => 'Kampas Ganda Mesin Potong Rumput Gendong TASCO', 'Unit' => 'Pcs', 'Price' => 75000, 'Category' => 'Material', 'AssetGroup' => null],
            ['Name' => 'Mata Pisau Mesin Potong Rumput Gendong TASCO 328 18"', 'Unit' => 'Pcs', 'Price' => 150000, 'Category' => 'Material', 'AssetGroup' => null],
            ['Name' => 'Mur 17" Mesin Potong Rumput Gendong', 'Unit' => 'Pcs', 'Price' => 15000, 'Category' => 'Material', 'AssetGroup' => null],
            ['Name' => 'Kaca Mata Safety', 'Unit' => 'Pcs', 'Price' => 23000, 'Category' => 'Tools', 'AssetGroup' => 'Kelompok 1'],
            ['Name' => 'Topi Camping Bahan', 'Unit' => 'Pcs', 'Price' => 70000, 'Category' => 'Tools', 'AssetGroup' => 'Kelompok 1'],
            ['Name' => 'Tali Senar untuk potong rumput 84 Meter', 'Unit' => 'roll', 'Price' => 115000, 'Category' => 'Material', 'AssetGroup' => null],
            ['Name' => 'Gatul Tanah', 'Unit' => 'Pcs', 'Price' => 48000, 'Category' => 'Tools', 'AssetGroup' => 'Kelompok 1'],
            ['Name' => 'Gergaji', 'Unit' => 'Pcs', 'Price' => 80000, 'Category' => 'Tools', 'AssetGroup' => 'Kelompok 1'],
            ['Name' => 'Garpu Taman Besar', 'Unit' => 'Pcs', 'Price' => 140000, 'Category' => 'Tools', 'AssetGroup' => 'Kelompok 1'],
            ['Name' => 'Golok', 'Unit' => 'Pcs', 'Price' => 125000, 'Category' => 'Tools', 'AssetGroup' => 'Kelompok 1'],
            ['Name' => 'Sprayer Gendong', 'Unit' => 'Unit', 'Price' => 245000, 'Category' => 'Tools', 'AssetGroup' => 'Kelompok 1'],
            ['Name' => 'Bensin', 'Unit' => 'Ltr', 'Price' => 15000, 'Category' => 'Material', 'AssetGroup' => null],
            ['Name' => 'Selang Berserat 1/2 inch 100 meter', 'Unit' => 'Roll', 'Price' => 950000, 'Category' => 'Tools', 'AssetGroup' => 'Kelompok 1'],
            ['Name' => 'OGITS IFM', 'Unit' => 'System', 'Price' => 75000000, 'Category' => 'Software/System', 'AssetGroup' => 'Kelompok 1'],
            ['Name' => 'OGITS ISM', 'Unit' => 'System', 'Price' => 35000000, 'Category' => 'Software/System', 'AssetGroup' => 'Kelompok 1'],
            ['Name' => 'Visitor Management System', 'Unit' => 'per gedung', 'Price' => 25000000, 'Category' => 'Software/System', 'AssetGroup' => 'Kelompok 1'],
            ['Name' => 'Room Booking System', 'Unit' => 'per gedung', 'Price' => 30000000, 'Category' => 'Software/System', 'AssetGroup' => 'Kelompok 1'],
            ['Name' => 'RAM 12 GB + Core 8 + Storage 2 TB', 'Unit' => 'Server', 'Price' => 25000000, 'Category' => 'Hardware', 'AssetGroup' => 'Kelompok 1'],
            ['Name' => 'Water Meter (incl. instalasi)', 'Unit' => 'per titik', 'Price' => 20670000, 'Category' => 'Hardware', 'AssetGroup' => 'Kelompok 1'],
            ['Name' => 'Power Meter + CT sensor (incl. instalasi)', 'Unit' => 'per titik', 'Price' => 12350000, 'Category' => 'Hardware', 'AssetGroup' => 'Kelompok 1'],
            ['Name' => 'Gateway', 'Unit' => 'Unit', 'Price' => 14820000, 'Category' => 'Hardware', 'AssetGroup' => 'Kelompok 1'],
            ['Name' => 'Instalasi Gateway IOT', 'Unit' => 'Service', 'Price' => 5785000, 'Category' => 'Setup Cost', 'AssetGroup' => null],
            ['Name' => 'Motion Sensor', 'Unit' => 'Unit', 'Price' => 682500, 'Category' => 'Hardware', 'AssetGroup' => 'Kelompok 1'],
            ['Name' => 'Instalasi Sensor', 'Unit' => 'Unit', 'Price' => 500000, 'Category' => 'Setup Cost', 'AssetGroup' => null],
        ];

        // Cache asset groups and categories to avoid repetitive queries
        $assetGroups = AssetGroup::all()->keyBy('name');
        $itemCategories = [];
        $uoms = [];

        foreach ($itemsData as $data) {
            // Ensure Category exists
            $categoryName = $data['Category'];
            if (! isset($itemCategories[$categoryName])) {
                $itemCategories[$categoryName] = ItemCategory::updateOrCreate(
                    ['name' => $categoryName],
                    [
                        'unit_id' => $defaultUnit->id,
                        'code' => Str::upper(Str::slug($categoryName, '')),
                    ]
                );
            }

            // Ensure UoM exists
            $uomNameRaw = $data['Unit'];
            $uomName = Str::title($uomNameRaw);
            $uomCode = Str::upper(Str::limit($uomName, 3, ''));

            // Standardize some codes
            if ($uomName === 'Pcs') {
                $uomCode = 'PCS';
            }

            if (! isset($uoms[$uomCode])) {
                $uoms[$uomCode] = UnitOfMeasure::where('code', $uomCode)
                    ->orWhere('name', $uomName)
                    ->first()
                    ?? UnitOfMeasure::create([
                        'name' => $uomName,
                        'unit_id' => $defaultUnit->id,
                        'code' => $uomCode,
                    ]);
            }
            $uom = $uoms[$uomCode];

            // Find Asset Group ID
            $assetGroupId = null;
            $match = null;
            if ($data['AssetGroup']) {
                // Try matching partially or exactly
                $match = $assetGroups->first(fn ($ag) => str_contains($ag->name, (string) $data['AssetGroup']));
                $assetGroupId = $match?->id;
            }

            // Create/Update Item
            Item::updateOrCreate(
                ['name' => $data['Name']],
                [
                    'unit_id' => $defaultUnit->id,
                    'item_category_id' => $itemCategories[$categoryName]->id,
                    'unit_of_measure_id' => $uom->id,
                    'asset_group_id' => $assetGroupId,
                    'price' => $data['Price'],
                    'depreciation_months' => $match?->useful_life_years ? ($match->useful_life_years * 12) : null,
                    'is_active' => true,
                    'code' => 'ITM-'.Str::upper(Str::random(6)),
                ]
            );
        }
    }
}
