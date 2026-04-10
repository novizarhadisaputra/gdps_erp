<?php

namespace Modules\MasterData\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\MasterData\Models\ProductCluster;

class ProductClusterLogoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mapping = [
            'BCA' => 'Beyond Care.png',
            'BCL' => 'Beyond Clean.png',
            'BFR' => 'Beyond Fresh.png',
            'BSE' => 'Beyond Secure.png',
            'BSK' => 'Beyond Sky.png',
            'BFM' => 'Beyond Facility.png',
        ];

        foreach ($mapping as $code => $filename) {
            /** @var ProductCluster|null $cluster */
            $cluster = ProductCluster::where('code', $code)->first();

            if ($cluster) {
                $path = public_path('images/product_clusters/'.$filename);

                if (file_exists($path)) {
                    // Ensure the media collection is cleared before adding to avoid duplicates
                    $cluster->clearMediaCollection('logo');
                    $cluster->addMedia($path)
                        ->preservingOriginal()
                        ->usingFileName(\Illuminate\Support\Str::slug(pathinfo($filename, PATHINFO_FILENAME)).'.'.pathinfo($filename, PATHINFO_EXTENSION))
                        ->storingConversionsOnDisk('s3')
                        ->addCustomHeaders([
                            'ContentType' => 'image/png',
                        ])
                        ->withCustomProperties([
                            'visibility' => 'private',
                        ])
                        ->toMediaCollection('logo', 's3');
                }
            }
        }
    }
}
