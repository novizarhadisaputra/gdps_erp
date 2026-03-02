<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$items = \Modules\MasterData\Models\Item::count();
$cats = \Modules\MasterData\Models\ItemCategory::count();
$uoms = \Modules\MasterData\Models\UnitOfMeasure::count();
$jobs = \Modules\MasterData\Models\JobPosition::count();
$assets = \Modules\MasterData\Models\AssetGroup::count();

echo "Items: $items\nCategories: $cats\nUOMs: $uoms\nJobs: $jobs\nAssets: $assets\n";
