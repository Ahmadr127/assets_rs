<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>QR Code - {{ $fixedAsset->nama_fixed_asset }}</title>

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: sans-serif; background: white; }

        /* Screen Preview */
        .container { max-width: 400px; margin: 20px auto; padding: 10px; }
        .instructions { background: #e3f2fd; border-left: 3px solid #2196f3; padding: 10px; margin-bottom: 15px; }
        .instructions h3 { font-size: 12px; margin-bottom: 5px; color: #1976d2; }
        .instructions p { font-size: 10px; color: #333; }

        .label-stiker {
            border: 2px solid #000;
            padding: 4px;
            background: white;
            max-width: 250px;
        }

        .label-content {
            display: flex;
            gap: 4px;
            align-items: stretch;
        }

        .qr-code {
            border: 1px solid #000;
            padding: 2px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
        }

        .qr-code svg {
            width: 75px;
            height: 75px;
        }

        .asset-info {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .logo-section {
            text-align: center;
            max-height: 12px;
            overflow: hidden;
        }

        .logo-section img {
            max-width: 100%;
            max-height: 12px;
        }

        .nama-barang {
            font-size: 7px;
            font-weight: bold;
            text-align: center;
            line-height: 1;
            word-wrap: break-word;
            display: flex;
            align-items: center;
            justify-content: center;
            flex: 1;
        }

        .kode-manual {
            font-size: 5px;
            font-weight: bold;
            text-align: center;
            padding: 1px;
            border: 1px solid #000;
            font-family: monospace;
        }

        /* PRINT VERSION 5×2 cm (50×20 mm) */
        @media print {
            @page {
                size: 50mm 20mm;
                margin: 0;
            }

            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            html, body {
                width: 50mm;
                height: 20mm;
                margin: 0;
                padding: 0;
                overflow: hidden;
            }

            .container {
                width: 50mm;
                height: 20mm;
                margin: 0;
                padding: 0;
            }

            .no-print { display: none !important; }

            .label-stiker {
                width: 48mm;
                height: 18mm;
                margin: 1mm;
                padding: 0.5mm;
                border: 1px solid #000;
                display: block;
                box-sizing: border-box;
            }

            .label-content {
                display: flex;
                align-items: center;
                gap: 0.5mm;
                height: 100%;
            }

            /* QR 16mm (SQUARE PERFECT) */
            .qr-code {
                width: 16mm;
                height: 16mm;
                padding: 0.5mm;
                display: flex;
                align-items: center;
                justify-content: center;
                border: 1px solid #000;
                box-sizing: border-box;
                flex-shrink: 0;
            }

            .qr-code svg {
                width: 15mm !important;
                height: 15mm !important;
                max-width: 15mm !important;
                max-height: 15mm !important;
            }

            .asset-info {
                flex: 1;
                min-width: 0;
                display: flex;
                flex-direction: column;
                justify-content: center;
                gap: 0.2mm;
                overflow: hidden;
                height: 16mm;
            }

            .logo-section {
                max-height: 3mm;
                flex-shrink: 0;
                width: 100%;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .logo-section img {
                max-width: 100%;
                max-height: 3mm;
                height: auto;
                display: block;
            }

            .nama-barang {
                font-size: 6pt;
                font-weight: bold;
                text-align: center;
                line-height: 0.85;
                word-wrap: break-word;
                overflow-wrap: break-word;
                overflow: hidden;
                width: 100%;
                flex: 1;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .kode-manual {
                font-size: 8pt;
                font-weight: bold;
                text-align: center;
                padding: 0.3mm 0.5mm;
                border: 1px solid #000;
                font-family: monospace;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                flex-shrink: 0;
                width: 100%;
                box-sizing: border-box;
            }
            
            /* Auto-scaling untuk asset number panjang */
            .kode-manual.medium { font-size: 7pt; }
            .kode-manual.long { font-size: 6pt; line-height: 1.1; white-space: normal; word-break: break-all; }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="instructions no-print">
            <h3>QR Code Label - Format 5x2 cm</h3>
            <p>Scan QR untuk melihat detail asset. Klik Print.</p>
        </div>

        <div class="label-stiker">
            <div class="label-content">

                <!-- QR CODE -->
                <div class="qr-code">
                    {!! $qrCodeSvg !!}
                </div>

                <!-- DETAIL KANAN -->
                <div class="asset-info">
                    <div class="logo-section">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo" onerror="this.style.display='none'">
                    </div>

                    <div class="nama-barang">
                        {{ $fixedAsset->nama_fixed_asset }}
                    </div>

                    @php
                        $assetNumber = $fixedAsset->asset_number ?? '-';
                        $length = strlen($assetNumber);
                        $sizeClass = $length > 25 ? 'long' : ($length > 15 ? 'medium' : '');
                    @endphp
                    <div class="kode-manual {{ $sizeClass }}">
                        {{ $assetNumber }}
                    </div>
                </div>

            </div>
        </div>

        <!-- PREVIEW INFO -->
        <div class="no-print" style="margin-top: 20px; font-size: 11px; background: #f8f9fa; padding: 10px; border-radius: 5px;">
            <p><strong>Format:</strong> 5×2 cm (50×20 mm)</p>
            <p><strong>QR:</strong> 18×18 mm</p>
            <p><strong>ID Asset:</strong> #{{ $fixedAsset->id }}</p>
            <p><strong>Dibuat:</strong> {{ $generatedAt }}</p>
        </div>
    </div>

    <script>
        window.addEventListener('load', function() {
            if (window.location.search.includes('autoprint=1')) {
                setTimeout(() => window.print(), 500);
            }
        });
    </script>

</body>
</html>
