<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrintFormat extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'width_cm',
        'height_cm',
        'qr_size_px',
        'margin_mm',
        'font_size_name',
        'font_size_code',
        'is_active',
        'is_default',
        'sort_order',
        'description',
    ];

    protected $casts = [
        'width_cm' => 'decimal:2',
        'height_cm' => 'decimal:2',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    /**
     * Get width in millimeters
     */
    public function getWidthMm(): float
    {
        return $this->width_cm * 10;
    }

    /**
     * Get height in millimeters
     */
    public function getHeightMm(): float
    {
        return $this->height_cm * 10;
    }

    /**
     * Get the default print format
     */
    public static function getDefault()
    {
        return self::where('is_default', true)
            ->where('is_active', true)
            ->first() ?? self::where('is_active', true)->first();
    }

    /**
     * Get all active formats ordered by sort_order
     */
    public static function getActive()
    {
        return self::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /**
     * Scope for active formats
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get display name with dimensions
     */
    public function getDisplayNameAttribute(): string
    {
        return "{$this->name} ({$this->width_cm} x {$this->height_cm} cm)";
    }

    /**
     * Calculate proportional QR size based on label dimensions
     * QR should take appropriate % of label width based on format
     */
    public function getProportionalQrSizeMm(): float
    {
        $labelWidthMm = $this->getWidthMm();
        $labelHeightMm = $this->getHeightMm();
        
        // For small labels, use more aggressive sizing
        // For large labels, use conservative sizing
        if ($labelHeightMm <= 25) {
            // Small labels: QR = 65% of height (maximize QR, minimize margins)
            $qrSizeMm = $labelHeightMm * 0.65;
        } else {
            // Large labels: QR = 70% of height
            $qrSizeMm = $labelHeightMm * 0.70;
        }
        
        return round($qrSizeMm, 2);
    }

    /**
     * Calculate proportional font sizes based on label dimensions
     * Uses adaptive scaling for better readability
     */
    public function getProportionalFontNameSize(): float
    {
        $labelHeightMm = $this->getHeightMm();
        
        // Adaptive font scaling based on label height
        if ($labelHeightMm <= 20) {
            // Very small labels (5x2cm): 6pt
            $fontSize = 6;
        } elseif ($labelHeightMm <= 25) {
            // Small labels (5x2.5cm): 7pt
            $fontSize = 7;
        } elseif ($labelHeightMm <= 30) {
            // Medium labels (5x3cm): 8pt
            $fontSize = 8;
        } else {
            // Large labels (6x5cm): 10pt
            $fontSize = 10;
        }
        
        return (float) $fontSize;
    }

    public function getProportionalFontCodeSize(): float
    {
        // Code size = 80% of name size (closer to name size for readability)
        $nameSize = $this->getProportionalFontNameSize();
        return round($nameSize * 0.8, 1);
    }

    /**
     * Calculate proportional margins based on label dimensions
     */
    public function getProportionalMarginMm(): float
    {
        $labelHeightMm = $this->getHeightMm();
        
        // Smaller labels = smaller margins
        if ($labelHeightMm <= 20) {
            return 0.5; // Very small labels: minimal margin
        } elseif ($labelHeightMm <= 30) {
            return 1; // Small-medium labels
        } else {
            return 1.5; // Large labels
        }
    }

    /**
     * Get padding for label container
     */
    public function getLabelPaddingMm(): float
    {
        $labelHeightMm = $this->getHeightMm();
        
        if ($labelHeightMm <= 20) {
            return 1; // Very small: 1mm padding
        } elseif ($labelHeightMm <= 30) {
            return 1.5; // Small-medium: 1.5mm
        } else {
            return 2; // Large: 2mm
        }
    }
    
    /**
     * Get gap between QR and text area
     */
    public function getContentGapMm(): float
    {
        $labelHeightMm = $this->getHeightMm();
        
        if ($labelHeightMm <= 20) {
            return 1; // Very small: 1mm gap
        } elseif ($labelHeightMm <= 30) {
            return 1.5; // Small-medium: 1.5mm
        } else {
            return 2; // Large: 2mm
        }
    }

    /**
     * Get total width available for text (after QR and margins)
     */
    public function getTextAreaWidthMm(): float
    {
        $labelWidthMm = $this->getWidthMm();
        $paddingMm = $this->getLabelPaddingMm();
        $qrSizeMm = $this->getProportionalQrSizeMm();
        $qrPaddingMm = $this->getProportionalMarginMm();
        $gapMm = $this->getContentGapMm();
        
        // Total QR box width = QR + padding on both sides
        $qrBoxMm = $qrSizeMm + ($qrPaddingMm * 2);
        
        // Text width = Label - (label padding × 2) - QR box - gap
        $textWidth = $labelWidthMm - ($paddingMm * 2) - $qrBoxMm - $gapMm;
        
        return max(5, $textWidth); // Minimum 5mm for text
    }
}
