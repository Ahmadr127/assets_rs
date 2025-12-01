<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>QR Code - {{ $fixedAsset->nama_fixed_asset }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Arial', sans-serif; background: white; color: #000; line-height: 1.2; }

        .container { max-width: 400px; margin: 20px auto; padding: 0; }

        /* Label Stiker Style */
        .label-stiker {
            border: 2px solid #000;
            padding: 10px;
            background: white;
            display: inline-block;
            width: 100%;
            max-width: 350px;
            box-sizing: border-box;
        }

        .label-content { display: flex; align-items: stretch; gap: 10px; }

        /* QR Code di Kiri (layar) */
        .qr-code {
            flex-shrink: 0;
            border: 1px solid #000;
            padding: 5px;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .qr-code svg {
            display: block;
            width: auto;
            height: auto;
            max-width: 100%;
            max-height: 100%;
        }

        /* Info di Kanan */
        .asset-info { 
            flex: 1; 
            display: flex; 
            flex-direction: column; 
            justify-content: space-between; 
            padding: 5px 0;
            min-width: 0;
            overflow: hidden;
        }
        .logo-section { text-align: center; margin-bottom: 8px; overflow: hidden; max-height: 30px; }
        .logo-section img { max-width: 100%; height: auto; max-height: 30px; object-fit: contain; }
        .nama-barang {
            font-size: {{ $printFormat->font_size_name }}px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 8px;
            line-height: 1.2;
            word-wrap: break-word;
            word-break: break-word;
            overflow-wrap: break-word;
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .kode-manual {
            font-size: {{ $printFormat->font_size_code }}px;
            font-weight: bold;
            text-align: center;
            padding: 3px 5px;
            border: 1px solid #000;
            background: white;
            font-family: 'Courier New', monospace;
        }

        .instructions { background: #e3f2fd; border-left: 3px solid #2196f3; padding: 10px; margin-bottom: 15px; }
        .instructions h3 { font-size: 12px; margin-bottom: 5px; color: #1976d2; }
        .instructions p { font-size: 10px; color: #333; }

        /* PRINT STYLES */
        @media print {
            @page {
                size: {{ $printFormat->getWidthMm() }}mm {{ $printFormat->getHeightMm() }}mm;
                margin: 0;
            }

            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }

            html, body {
                width: {{ $printFormat->getWidthMm() }}mm;
                height: {{ $printFormat->getHeightMm() }}mm;
                margin: 0 !important;
                padding: 0 !important;
                overflow: hidden;
            }

            .container {
                margin: 0;
                padding: 0;
                width: {{ $printFormat->getWidthMm() }}mm;
                height: {{ $printFormat->getHeightMm() }}mm;
                max-width: {{ $printFormat->getWidthMm() }}mm;
                max-height: {{ $printFormat->getHeightMm() }}mm;
                display: block;
                box-sizing: border-box;
            }

            .no-print { display: none !important; }

            .label-stiker {
                border: 1px solid #000;
                width: 100%;
                height: 100%;
                padding: {{ $printFormat->getLabelPaddingMm() }}mm;
                background: white;
                display: block;
                box-sizing: border-box;
                page-break-inside: avoid !important;
            }

            .label-content {
                display: flex;
                align-items: stretch;
                gap: {{ $printFormat->getContentGapMm() }}mm;
                width: 100%;
                height: 100%;
            }

            /* Kalkulasi ukuran QR dan box secara akurat (menggunakan DPI printer) */
            @php
                // DPI printer yang kita targetkan
                $printerDpi = 300;

                // QR size proporsional dari model
                $qrSizeMm = $printFormat->getProportionalQrSizeMm();
                
                // Padding di sekitar QR
                $qrPaddingMm = $printFormat->getProportionalMarginMm();

                // Total box dimension
                $qrBoxMm = round($qrSizeMm + ($qrPaddingMm * 2), 2);

                // Gap antara QR dan text
                $gapMm = $printFormat->getContentGapMm();

                // Padding label
                $labelPaddingMm = $printFormat->getLabelPaddingMm();

                // Logo max height: adaptif berdasarkan tinggi label
                $labelHeightMm = $printFormat->getHeightMm();
                if ($labelHeightMm <= 20) {
                    $logoMaxMm = 1.5; // Very small
                } elseif ($labelHeightMm <= 30) {
                    $logoMaxMm = 2; // Medium
                } else {
                    $logoMaxMm = 3; // Large
                }

                // Font sizes proporsional
                $fontNameSize = $printFormat->getProportionalFontNameSize();
                $fontCodeSize = $printFormat->getProportionalFontCodeSize();
                
                // Calculated text area width for verification
                $textAreaWidth = $printFormat->getTextAreaWidthMm();
            @endphp

            .qr-code {
                flex-shrink: 0;
                border: 1px solid #000;
                padding: {{ $qrPaddingMm }}mm;
                background: white;
                display: flex;
                align-items: center;
                justify-content: center;
                width: {{ $qrBoxMm }}mm;
                height: {{ $qrBoxMm }}mm;
                box-sizing: border-box;
            }

            .qr-code svg {
                width: {{ $qrSizeMm }}mm !important;
                height: {{ $qrSizeMm }}mm !important;
                max-width: {{ $qrSizeMm }}mm !important;
                max-height: {{ $qrSizeMm }}mm !important;
                display: block;
            }

            .asset-info {
                flex: 1;
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                min-width: 0;
                overflow: hidden;
                gap: 1mm;
            }

            .logo-section {
                text-align: center;
                margin-bottom: 0;
                max-height: {{ $logoMaxMm }}mm;
                overflow: hidden;
                flex-shrink: 0;
            }

            .logo-section img {
                max-width: 100%;
                max-height: {{ $logoMaxMm }}mm;
                height: auto;
            }

            .nama-barang {
                font-size: {{ $fontNameSize }}pt;
                font-weight: bold;
                text-align: center;
                line-height: 1;
                word-wrap: break-word;
                overflow-wrap: break-word;
                word-break: break-word;
                hyphens: auto;
                flex: 1;
                display: flex;
                align-items: center;
                justify-content: center;
                overflow: hidden;
                min-height: 0;
                padding: 0 0.5mm;
            }

            .kode-manual {
                font-size: {{ $fontCodeSize }}pt;
                font-weight: bold;
                text-align: center;
                padding: 0.5mm 1mm;
                border: 1px solid #000;
                background: white;
                font-family: 'Courier New', monospace;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                flex-shrink: 0;
            }
        }

        /* Mobile responsive for preview */
        @media (max-width: 768px) {
            .container { max-width: 100%; padding: 10px; }
            .label-stiker { max-width: 100%; }
        }

    </style>
</head>
<body>
    <div class="container">
        <!-- Instructions -->
        <div class="instructions no-print">
            <h3>Cara Menggunakan QR Code Label</h3>
            <p>Scan QR Code ini menggunakan aplikasi kamera atau QR scanner untuk melihat detail lengkap asset. Klik Print untuk mencetak label.</p>
            <p><strong>Format Print:</strong> {{ $printFormat->name }} ({{ $printFormat->width_cm }} x {{ $printFormat->height_cm }} cm)</p>
        </div>

        <!-- Label Stiker -->
        <div class="label-stiker">
            <div class="label-content">
                <!-- QR Code di Kiri -->
                <div class="qr-code">
                    {!! $qrCodeSvg !!}
                </div>

                <!-- Info di Kanan -->
                <div class="asset-info">
                    <!-- Logo -->
                    <div class="logo-section">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo" onerror="this.style.display='none'">
                    </div>

                    <!-- Nama Barang -->
                    <div class="nama-barang">
                        {{ $fixedAsset->nama_fixed_asset }}
                    </div>

                    <!-- Kode Manual -->
                    <div class="kode-manual">
                        {{ $fixedAsset->kode_manual ?? $fixedAsset->kode ?? '-' }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Info Tambahan (no-print) -->
        <div class="no-print" style="margin-top: 20px; padding: 10px; background: #f8f9fa; border-radius: 5px; font-size: 11px;">
            <p><strong>ID Asset:</strong> #{{ $fixedAsset->id }}</p>
            <p><strong>Kode:</strong> {{ $fixedAsset->kode ?? '-' }}</p>
            <p><strong>Kode Manual:</strong> {{ $fixedAsset->kode_manual ?? '-' }}</p>
            <p><strong>Dibuat pada:</strong> {{ $generatedAt }}</p>
        </div>
    </div>

    <script>
        @php
            // Kalkulasi ukuran QR dalam mm untuk JavaScript fallback
            $printerDpi = 300;
            $qrSizeMm = $printFormat->getProportionalQrSizeMm();
        @endphp

        // FALLBACK: Force SVG size sebelum print
        // Ini memastikan browser tidak override ukuran mm yang sudah di-set
        function enforceSvgSize() {
            const qrSizeMm = {{ $qrSizeMm }};
            const svgElements = document.querySelectorAll('.qr-code svg');
            
            svgElements.forEach(function(svg) {
                // Set atribut width/height dalam mm
                svg.setAttribute('width', qrSizeMm + 'mm');
                svg.setAttribute('height', qrSizeMm + 'mm');
                
                // Force inline style (prioritas tertinggi)
                svg.style.width = qrSizeMm + 'mm';
                svg.style.height = qrSizeMm + 'mm';
                svg.style.maxWidth = qrSizeMm + 'mm';
                svg.style.maxHeight = qrSizeMm + 'mm';
                svg.style.display = 'block';
                
                // Pastikan viewBox ada (untuk scalability)
                if (!svg.getAttribute('viewBox')) {
                    const width = svg.getAttribute('data-width') || 100;
                    const height = svg.getAttribute('data-height') || 100;
                    svg.setAttribute('viewBox', `0 0 ${width} ${height}`);
                }
            });
            
            console.log('SVG size enforced:', qrSizeMm + 'mm');
        }

        // Enforce saat load
        window.addEventListener('load', function() {
            enforceSvgSize();
            
            // Auto print when opened in new window (autoprint=1)
            if (window.location.search.includes('autoprint=1')) {
                setTimeout(function() {
                    // Enforce lagi sebelum print (untuk memastikan)
                    enforceSvgSize();
                    window.print();
                }, 500);
            }
        });

        // Enforce sebelum print dialog (untuk manual print)
        window.addEventListener('beforeprint', function() {
            enforceSvgSize();
        });
    </script>

</body>
</html>
