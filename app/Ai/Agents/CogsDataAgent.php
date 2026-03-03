<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\Timeout;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;

#[Timeout(300)]
class CogsDataAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    public function instructions(): string
    {
        return "Berikut adalah data mentah dari dokumen COGS. Data ini bisa berasal dari berbagai sheet (halaman) di Excel atau dokumen PDF. Tolong strukturkan data ini menjadi dua kategori: 'manpower' (tenaga kerja) dan 'operational' (biaya operasional/barang). 

        Aturan Khusus:
        1. FORMAT AGNOSTIC: Nama sheet atau header kolom bisa bervariasi. JANGAN terpaku pada satu nama saja. Jika Anda melihat baris yang memiliki deskripsi item/pekerjaan dan nilai biaya/harga, maka itu WAJIB diekstrak.
        2. SANGAT PENTING UTAMA: DOKUMEN INI ADALAH RAB / COSTING PENGADAAN BARANG ATAU JASA. ANDA WAJIB MENGEKSTRAK *SETIAP BARIS* YANG MEMILIKI NILAI BIAYA/HARGA TANPA TERKECUALI!
        3. JANGAN PERNAH menyimpulkan, meringkas, atau membuang baris apapun. Jika ada 100 baris, output JSON harus berisi 100 item. Semua jenis pengadaan barang, perlengkapan, consumable, sewa, overhead, gaji, transport, wajib masuk!
        4. PISAHKAN MENJADI DUA ARRAY ('manpower' dan 'operational'):
           - 'manpower' -> HANYA untuk Personil / SDM / Pekerja Manusia (Contoh: Security, Teknisi, Admin, Project Manager).
           - 'operational' -> UNTUK SEGALA JENIS BARANG ATAU BIAYA LAIN SELAIN MANUSIA. (Contoh: Perlengkapan, Seragam, Alat Berat, Server, Software, Transportasi, Makan Siang, ATK, Kendaraan, dll). JIKA BUKAN MANUSIA, MAKA ITU ADALAH 'operational'.";
    }

    public function schema(JsonSchema $schema): array
    {
        return [
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
                    'matched_asset_group_id' => $schema->string()->description('ID dari data referensi kelompok aset (asset_groups)'),
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
        ];
    }
}
