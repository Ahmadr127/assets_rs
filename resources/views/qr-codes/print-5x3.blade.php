<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>QR Code - {{ $fixedAsset->nama_fixed_asset }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: sans-serif; background: white; }
        
        /* Screen preview */
        .container { max-width: 400px; margin: 20px auto; padding: 10px; }
        .instructions { background: #e3f2fd; border-left: 3px solid #2196f3; padding: 10px; margin-bottom: 15px; }
        .instructions h3 { font-size: 12px; margin-bottom: 5px; color: #1976d2; }
        .instructions p { font-size: 10px; color: #333; }
        
        .label-stiker { border: 2px solid #000; padding: 6px; background: white; max-width: 300px; }
        .label-content { display: flex; gap: 6px; align-items: stretch; }
        .qr-code { flex-shrink: 0; border: 1px solid #000; padding: 3px; background: white; display: flex; align-items: center; justify-content: center; }
        .qr-code img {
            width: 100px;
            height: 100px;
        }
        
        .asset-info { flex: 1; display: flex; flex-direction: column; justify-content: center; }
        .nama-barang { font-size: 9px; font-weight: bold; text-align: center; line-height: 1.2; word-wrap: break-word; flex: 1; display: flex; align-items: center; justify-content: center; }
        .kode-manual { font-size: 7px; font-weight: bold; text-align: center; padding: 2px; border: 1px solid #000; font-family: monospace; }
        
        /* PRINT STYLES - 5x3 cm */
        @media print {
            @page { size: 50mm 30mm; margin: 0; }
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            html, body { width: 50mm; height: 30mm; margin: 0; padding: 0; overflow: hidden; }
            .container { margin: 0; padding: 0; width: 50mm; height: 30mm; display: block; }
            .no-print { display: none !important; }
            
            .label-stiker {
                border: 1px solid #000;
                width: 48mm;
                height: 28mm;
                margin: 1mm;
                padding: 1.5mm;
                background: white;
                display: block;
                box-sizing: border-box;
            }
            
            .label-content {
                display: flex;
                align-items: center;
                gap: 1.5mm;
                width: 100%;
                height: 100%;
            }
            
            /* QR Code - KOTAK SEMPURNA */
            .qr-code {
                flex-shrink: 0;
                border: 1px solid #000;
                padding: 0.5mm;
                width: 24mm;
                height: 24mm;
                display: flex;
                align-items: center;
                justify-content: center;
                box-sizing: border-box;
            }
            
            .qr-code img {
                width: 23mm !important;
                height: 23mm !important;
                max-width: 23mm !important;
                max-height: 23mm !important;
                display: block;
            }
            
            .asset-info {
                flex: 1;
                min-width: 0;
                display: flex;
                flex-direction: column;
                justify-content: center;
                gap: 0.3mm;
                overflow: hidden;
                height: 24mm;
            }
            
            .nama-barang {
                font-size: 7pt;
                font-weight: bold;
                text-align: center;
                line-height: 0.95;
                word-wrap: break-word;
                overflow-wrap: break-word;
                flex: 1;
                display: flex;
                align-items: center;
                justify-content: center;
                overflow: hidden;
                width: 100%;
            }
            
            .kode-manual {
                font-size: 10pt;
                font-weight: bold;
                text-align: center;
                padding: 0.4mm 0.5mm;
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
            .kode-manual.medium { font-size: 8.5pt; }
            .kode-manual.long { font-size: 7pt; line-height: 1.1; white-space: normal; word-break: break-all; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="instructions no-print">
            <h3>QR Code Label - Format 5x3 cm</h3>
            <p>Scan QR Code untuk melihat detail asset. Klik Print untuk mencetak.</p>
        </div>
        
        <div class="label-stiker">
            <div class="label-content">
                <div class="qr-code">
                    <img src="{{ $qrCodeImage }}" alt="QR Code">
                </div>
                
                <div class="asset-info">
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
        
        <div class="no-print" style="margin-top: 20px; padding: 10px; background: #f8f9fa; border-radius: 5px; font-size: 11px;">
            <p><strong>Format:</strong> 5×3 cm (50mm × 30mm)</p>
            <p><strong>QR Size:</strong> 21mm × 21mm</p>
            <p><strong>ID Asset:</strong> #{{ $fixedAsset->id }}</p>
            <p><strong>Dibuat:</strong> {{ $generatedAt }}</p>
        </div>
    </div>
    
    <script>
        window.addEventListener('load', function() {
            if (window.location.search.includes('autoprint=1')) {
                setTimeout(function() {
                    window.print();
                }, 500);
            }
        });
    </script>
</body>
</html>
