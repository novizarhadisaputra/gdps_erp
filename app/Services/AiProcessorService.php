<?php

namespace App\Services;

use App\Ai\Agents\CogsDataAgent;
use App\Ai\Agents\ProposalMetadataAgent;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;

class AiProcessorService
{
    public function processCogsData(string $filePath, array $existingContext = [], string $focus = 'all'): array
    {
        Log::info("AiProcessor: Processing COGS data from file: {$filePath}");

        if (! file_exists($filePath)) {
            Log::error("AiProcessor: File not found at path: {$filePath}");

            return ['manpower' => [], 'operational' => []];
        }
        $contextString = ! empty($existingContext)
            ? "\n\nReferensi Data Eksis (Gunakan ID jika cocok):\n".json_encode($existingContext)
            : '';

        $prompt = "Berikut adalah data mentah dari dokumen COGS. Data ini bisa berasal dari berbagai sheet (halaman) di Excel atau dokumen PDF. Tolong strukturkan data ini menjadi dua kategori: 'manpower' (tenaga kerja) dan 'operational' (biaya operasional/barang). 

        Aturan Khusus:
        1. FORMAT AGNOSTIC: Nama sheet atau header kolom bisa bervariasi. JANGAN terpaku pada satu nama saja. Jika Anda melihat baris yang memiliki deskripsi item/pekerjaan dan nilai biaya/harga, maka itu WAJIB diekstrak.
        2. SANGAT PENTING UTAMA: DOKUMEN INI ADALAH RAB / COSTING PENGADAAN BARANG ATAU JASA. ANDA WAJIB MENGEKSTRAK *SETIAP BARIS* YANG MEMILIKI NILAI BIAYA/HARGA TANPA TERKECUALI!
        3. JANGAN PERNAH menyimpulkan, meringkas, atau membuang baris apapun. Jika ada 100 baris, output JSON harus berisi 100 item. Semua jenis pengadaan barang, perlengkapan, consumable, sewa, overhead, gaji, transport, wajib masuk!
        4. PISAHKAN MENJADI DUA ARRAY ('manpower' dan 'operational'):
           - 'manpower' -> HANYA untuk Personil / SDM / Pekerja Manusia (Contoh: Security, Teknisi, Admin, Project Manager).
           - 'operational' -> UNTUK SEGALA JENIS BARANG ATAU BIAYA LAIN SELAIN MANUSIA. (Contoh: Perlengkapan, Seragam, Alat Berat, Server, Software, Transportasi, Makan Siang, ATK, Kendaraan, dll). JIKA BUKAN MANUSIA, MAKA ITU ADALAH 'operational'.";

        if ($focus === 'items') {
            $prompt .= "\n\n        FOKUS KHUSUS (ITEMS ONLY):
        - PRIORITASKAN BARANG, PERALATAN, MATERIAL, DAN JASA OPERASIONAL.
        - ABAIKAN/BUANG SEMUA HUMAN RESOURCES / PERSONIL / SDM (Gaji, Tunjangan, Gaji Pokok, dll).
        - ABAIKAN/BUANG SEMUA BIAYA FEE (Management Fee, Overhead Fee, Taxes/Pajak, Keuntungan, dll).
        - Output 'manpower' harus KOSONG [].";
        } elseif ($focus === 'manpower') {
            $prompt .= "\n\n        FOKUS KHUSUS (MANPOWER ONLY):
        - PRIORITASKAN PERSONIL / SDM / PEKERJA.
        - ABAIKAN SEMUA BARANG DAN PERALATAN.
        - Output 'operational' harus KOSONG [].";
        }

        $prompt .= "\n\n        5. UNTUK SEMUA ITEM (Baru maupun Lama):
           - Kami melampirkan data Master Referensi di bawah (items, job_positions, dst).
           - JIKA COCOK secara semantik: isi field 'matched_id' dengan ID dari referensi.
           - JIKA TIDAK ADA DI REFERENSI: Biarkan 'matched_id' berisi null. TETAP MASUKKAN BARANG TERSEBUT KE DALAM JSON.
        {$contextString}";

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $attachments = [];

        if (in_array($extension, ['xlsx', 'xls', 'csv'])) {
            $reader = IOFactory::createReaderForFile($filePath);
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($filePath);
            $spreadsheetContext = '';

            foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
                $sheetName = $worksheet->getTitle();
                $sheetContent = '';
                $rowCount = 0;

                foreach ($worksheet->getRowIterator() as $row) {
                    if ($rowCount >= 500) {
                        break;
                    } // Hard limit to prevent OOM

                    $cellIterator = $row->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(false);

                    $rowData = [];
                    $hasData = false;
                    foreach ($cellIterator as $cell) {
                        $value = $cell->getValue();
                        $rowData[] = $value === null ? '' : (string) $value;
                        if ($value !== null && $value !== '') {
                            $hasData = true;
                        }
                    }

                    if ($hasData) {
                        $sheetContent .= implode("\t", $rowData)."\n";
                        $rowCount++;
                    }
                }

                if ($rowCount > 0) {
                    $spreadsheetContext .= "\n### Sheet: {$sheetName}\n".$sheetContent;
                }
            }

            $prompt .= "\n\nData dari Excel:\n".$spreadsheetContext;
        } else {
            $attachments[] = \Laravel\Ai\Files\Document::fromPath($filePath);
        }

        return retry(3, function () use ($prompt, $attachments) {
            /** @var \Laravel\Ai\Responses\StructuredAgentResponse $response */
            $response = CogsDataAgent::make()->prompt($prompt, $attachments);

            return $response->toArray();
        }, 10000); // Retry 3 times with 10 seconds delay to respect rate limits
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

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $attachments = [];

        if (in_array($extension, ['xlsx', 'xls', 'csv'])) {
            $reader = IOFactory::createReaderForFile($filePath);
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($filePath);
            $spreadsheetContext = '';

            foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
                $sheetName = $worksheet->getTitle();
                $sheetContent = '';
                $rowCount = 0;

                foreach ($worksheet->getRowIterator() as $row) {
                    if ($rowCount >= 100) {
                        break;
                    } // Metadata usually at top

                    $cellIterator = $row->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(false);

                    $rowData = [];
                    $hasData = false;
                    foreach ($cellIterator as $cell) {
                        $value = $cell->getValue();
                        $rowData[] = $value === null ? '' : (string) $value;
                        if ($value !== null && $value !== '') {
                            $hasData = true;
                        }
                    }

                    if ($hasData) {
                        $sheetContent .= implode("\t", $rowData)."\n";
                        $rowCount++;
                    }
                }

                if ($rowCount > 0) {
                    $spreadsheetContext .= "\n### Sheet: {$sheetName}\n".$sheetContent;
                }
            }

            $prompt .= "\n\nData dari Excel:\n".$spreadsheetContext;
        } else {
            $attachments[] = \Laravel\Ai\Files\Document::fromPath($filePath);
        }

        return retry(3, function () use ($prompt, $attachments) {
            /** @var \Laravel\Ai\Responses\StructuredAgentResponse $response */
            $response = ProposalMetadataAgent::make()->prompt($prompt, $attachments);

            return $response->toArray();
        }, 10000);
    }
}
