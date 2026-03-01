<?php

namespace App\Services;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Ai;

use function Laravel\Ai\agent;

class AiProcessorService
{
    /**
     * Parse COGS data from a raw array (from Excel) using AI for fuzzy matching and structuring.
     */
    public function processCogsData(array $rawData, array $existingContext = []): array
    {
        // Clean up raw data to reduce token usage
        $cleanData = array_filter(array_map(function ($row) {
            return array_filter($row, fn ($cell) => $cell !== null && $cell !== '');
        }, $rawData), fn ($row) => ! empty($row));

        $contextString = ! empty($existingContext)
            ? "\n\nReferensi Data Eksis (Gunakan ID jika cocok):\n".json_encode($existingContext)
            : '';

        $prompt = "Berikut adalah data mentah dari Excel COGS. Tolong strukturkan data ini menjadi dua kategori: 'manpower' (tenaga kerja) dan 'operational' (biaya operasional/barang). 

        Aturan Khusus:
        1. Untuk 'manpower', identifikasi jabatan, jumlah orang, dan gaji pokok.
        2. Untuk 'operational', identifikasi apakah item tersebut adalah ASET (butuh depresiasi) atau EXPENSE rutin.
        3. Tentukan kategori operasional seperti: Tools, Material, IT, Konsumsi, Transport, dll.
        4. COCOKKAN nama dengan data referensi yang diberikan (items, job_positions, item_categories, units_of_measure). Kembalikan 'matched_id' dari referensi items/job_positions jika ada yang cocok. 
        5. Untuk 'operational', juga kembalikan 'matched_category_id' dan 'matched_unit_id' berdasarkan referensi jika ada yang cocok. Jika tidak ada kecocokan, kembalikan null (bukan string 'null').
        6. GUNAKAN konteks regional dan durasi proyek untuk memberikan hasil yang lebih akurat jika diperlukan.
        {$contextString}

        Data Mentah:
        ".json_encode(array_values($cleanData));

        return retry(3, function () use ($prompt) {
            return agent(
                schema: fn (JsonSchema $schema) => [
                    'manpower' => $schema->array()->items(
                        $schema->object([
                            'name' => $schema->string()->description('Nama Jabatan')->required(),
                            'matched_id' => $schema->string()->description('ID dari data referensi jika cocok'),
                            'quantity' => $schema->number()->description('Jumlah Personel')->required(),
                            'basic_salary' => $schema->number()->description('Gaji Pokok per Bulan')->required(),
                            'duration_months' => $schema->number()->description('Durasi Kerja (bulan)'),
                            'notes' => $schema->string(),
                        ])
                    ),
                    'operational' => $schema->array()->items(
                        $schema->object([
                            'name' => $schema->string()->description('Nama Barang/Jasa')->required(),
                            'matched_id' => $schema->string()->description('ID dari data referensi jika cocok'),
                            'matched_category_id' => $schema->string()->description('ID dari data referensi kategori (item_categories)'),
                            'matched_unit_id' => $schema->string()->description('ID dari data referensi satuan (units_of_measure)'),
                            'category' => $schema->string()->description('Kategori (Tools, Material, IT, etc.)'),
                            'quantity' => $schema->number()->required(),
                            'unit' => $schema->string()->description('Satuan (Pcs, Set, Lot, dll)'),
                            'unit_price' => $schema->number()->description('Harga Satuan')->required(),
                            'duration_months' => $schema->number()->description('Durasi Penggunaan (bulan)'),
                            'is_asset' => $schema->boolean()->description('Apakah ini barang investasi/aset?')->required(),
                            'useful_life_years' => $schema->number()->description('Masa Pakai (tahun) jika aset'),
                            'depreciation_months' => $schema->number()->description('Bulan Depresiasi (jika aset)'),
                        ])
                    ),
                ]
            )->prompt($prompt)->toArray();
        }, 3000); // Retry 3 times with 3 seconds delay
    }

    /**
     * Extract metadata from a proposal document.
     */
    public function extractProposalMetadata(string $filePath): array
    {
        $prompt = 'Tolong ekstrak informasi penting dari dokumen proposal ini. Saya butuh: 
        1. proposal_number
        2. total_amount (nominal saja)
        3. customer_name
        4. submission_date (format YYYY-MM-DD)';

        return retry(3, function () use ($prompt, $filePath) {
            $response = agent(
                schema: fn (JsonSchema $schema) => [
                    'proposal_number' => $schema->string(),
                    'total_amount' => $schema->number(),
                    'customer_name' => $schema->string(),
                    'submission_date' => $schema->string()->format('date'),
                ]
            )->prompt($prompt, [
                \Laravel\Ai\Files\Document::fromPath($filePath),
            ]);

            return $response->toArray();
        }, 3000);
    }
}
