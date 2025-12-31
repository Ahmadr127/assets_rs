<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PrintFormatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * NOTE: Seeder database hanya berisi base values untuk configuration.
     * Actual sizing calculations are PROPORTIONAL dan di-generate oleh PrintFormat model:
     * - getProportionalQrSizeMm() - Calculate QR size based on label dimensions
     * - getProportionalFontNameSize() - Calculate font size for asset name
     * - getProportionalFontCodeSize() - Calculate font size for asset code
     * - getProportionalMarginMm() - Calculate proportional margins
     * - getTextAreaWidthMm() - Calculate available text area width
     */
    public function run(): void
    {
        $now = Carbon::now();

        $formats = [
            [
                'name' => 'Stiker Besar',
                'code' => '6x5',
                'width_cm' => 6.0,
                'height_cm' => 5.0,
                // Database values are now legacy (for reference)
                // Actual QR size calculated by: getProportionalQrSizeMm()
                'qr_size_px' => 295, // Legacy - not used
                'margin_mm' => 2,    // Base margin for proportional calculation
                'font_size_name' => 11, // Base font (not used - proportional calculated)
                'font_size_code' => 9,  // Base font (not used - proportional calculated)
                'is_active' => true,
                'is_default' => true,
                'sort_order' => 1,
                'description' => 'Ukuran standar untuk asset besar (6 x 5 cm) - Sizing proporsional otomatis',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Stiker Sedang',
                'code' => '5x3',
                'width_cm' => 5.0,
                'height_cm' => 3.0,
                'qr_size_px' => 248, // Legacy - not used
                'margin_mm' => 2,
                'font_size_name' => 9,
                'font_size_code' => 7,
                'is_active' => true,
                'is_default' => false,
                'sort_order' => 2,
                'description' => 'Ukuran untuk asset sedang (5 x 3 cm) - Sizing proporsional otomatis',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Stiker Kecil',
                'code' => '5x2.5',
                'width_cm' => 5.0,
                'height_cm' => 2.5,
                'qr_size_px' => 260, // Legacy - not used
                'margin_mm' => 1,
                'font_size_name' => 8,
                'font_size_code' => 6,
                'is_active' => true,
                'is_default' => false,
                'sort_order' => 3,
                'description' => 'Ukuran untuk asset kecil (5 x 2.5 cm) - Sizing proporsional otomatis',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Stiker Mini',
                'code' => '5x2',
                'width_cm' => 5.0,
                'height_cm' => 2.0,
                'qr_size_px' => 213, // Legacy - not used
                'margin_mm' => 1,
                'font_size_name' => 7,
                'font_size_code' => 6,
                'is_active' => true,
                'is_default' => false,
                'sort_order' => 4,
                'description' => 'Ukuran minimal untuk asset sangat kecil (5 x 2 cm) - Sizing proporsional otomatis',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('print_formats')->insert($formats);

        $this->command->info('Print formats seeded successfully!');
        $this->command->info('Available formats: 6x5 (default), 5x3, 5x2.5, 5x2');
        $this->command->info('NOTE: All sizing is PROPORTIONAL - calculated by PrintFormat model methods');
    }
}
