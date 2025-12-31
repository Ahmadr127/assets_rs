<?php

namespace App\Helpers;

class SvgHelper
{
    /**
     * Fix SVG size untuk print yang akurat
     * Menghapus width/height px dan mengganti dengan mm
     * 
     * @param string $svg - SVG string dari QR generator
     * @param int $px - Ukuran dalam pixel (dari database)
     * @param int $dpi - DPI printer (default 300)
     * @return string - SVG yang sudah dipatch
     */
    public static function fixSvgSize(string $svg, int $px, int $dpi = 300): string
    {
        // Konversi px ke mm menggunakan rumus: mm = px * 25.4 / DPI
        $mm = round(($px * 25.4) / $dpi, 2);
        
        // Parse SVG untuk mendapatkan viewBox
        $viewBox = self::extractViewBox($svg);
        
        // Jika tidak ada viewBox, buat dari width/height yang ada
        if (!$viewBox) {
            $viewBox = "0 0 {$px} {$px}";
        }
        
        // Hapus atribut width dan height yang ada (px atau apapun)
        $svg = preg_replace('/\s+width\s*=\s*["\'][^"\']*["\']/', '', $svg);
        $svg = preg_replace('/\s+height\s*=\s*["\'][^"\']*["\']/', '', $svg);
        
        // Hapus style inline yang mungkin ada
        $svg = preg_replace('/\s+style\s*=\s*["\'][^"\']*["\']/', '', $svg);
        
        // Inject viewBox jika belum ada
        if (!strpos($svg, 'viewBox')) {
            $svg = preg_replace('/<svg/', "<svg viewBox=\"{$viewBox}\"", $svg, 1);
        }
        
        // Inject width, height, dan style dengan ukuran mm
        $attributes = sprintf(
            'width="%smm" height="%smm" style="width:%smm;height:%smm;display:block;"',
            $mm, $mm, $mm, $mm
        );
        
        $svg = preg_replace('/<svg/', "<svg {$attributes}", $svg, 1);
        
        return $svg;
    }
    
    /**
     * Extract viewBox dari SVG
     * 
     * @param string $svg
     * @return string|null
     */
    private static function extractViewBox(string $svg): ?string
    {
        if (preg_match('/viewBox\s*=\s*["\']([^"\']+)["\']/', $svg, $matches)) {
            return $matches[1];
        }
        
        return null;
    }
    
    /**
     * Konversi px ke mm
     * 
     * @param int $px
     * @param int $dpi
     * @return float
     */
    public static function pxToMm(int $px, int $dpi = 300): float
    {
        return round(($px * 25.4) / $dpi, 2);
    }
    
    /**
     * Konversi mm ke px
     * 
     * @param float $mm
     * @param int $dpi
     * @return int
     */
    public static function mmToPx(float $mm, int $dpi = 300): int
    {
        return round(($mm * $dpi) / 25.4);
    }
}
