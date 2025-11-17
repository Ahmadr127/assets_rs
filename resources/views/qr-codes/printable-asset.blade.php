<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code - {{ $fixedAsset->nama_fixed_asset }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            background: white;
            color: #000;
            line-height: 1.2;
        }
        
        .container {
            max-width: 400px;
            margin: 20px auto;
            padding: 0;
        }
        
        /* Label Stiker Style - seperti di gambar */
        .label-stiker {
            border: 2px solid #000;
            padding: 10px;
            background: white;
            display: inline-block;
            width: 100%;
            max-width: 350px;
        }
        
        .label-content {
            display: flex;
            align-items: stretch;
            gap: 10px;
        }
        
        /* QR Code di Kiri */
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
            width: 120px;
            height: 120px;
        }
        
        /* Info di Kanan */
        .asset-info {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 5px 0;
        }
        
        .logo-section {
            text-align: center;
            margin-bottom: 8px;
        }
        
        .logo-section img {
            max-width: 100%;
            height: auto;
            max-height: 35px;
        }
        
        .nama-barang {
            font-size: 11px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 8px;
            line-height: 1.3;
            word-wrap: break-word;
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .kode-manual {
            font-size: 10px;
            font-weight: bold;
            text-align: center;
            padding: 3px 5px;
            border: 1px solid #000;
            background: white;
            font-family: 'Courier New', monospace;
        }
        
        /* Instructions (no-print) */
        .instructions {
            background: #e3f2fd;
            border-left: 3px solid #2196f3;
            padding: 10px;
            margin-bottom: 15px;
        }
        
        .instructions h3 {
            font-size: 12px;
            margin-bottom: 5px;
            color: #1976d2;
        }
        
        .instructions p {
            font-size: 10px;
            color: #333;
        }
        
        /* Print styles - Custom Label Size */
        @media print {
            @page {
                size: 100mm 60mm;
                margin: 0;
            }
            
            html, body {
                width: 100mm;
                height: 60mm;
                margin: 0;
                padding: 0;
            }
            
            .container {
                margin: 0;
                padding: 5mm;
                width: 100%;
                height: 100%;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .no-print {
                display: none !important;
            }
            
            .label-stiker {
                page-break-inside: avoid;
                border: 2px solid #000;
                max-width: 90mm;
                width: 100%;
            }
            
            .qr-code {
                border: 1px solid #000;
            }
            
            .qr-code svg {
                width: 110px;
                height: 110px;
            }
            
            .kode-manual {
                border: 1px solid #000;
            }
        }
        
        /* Print styles alternative - A4 with multiple labels */
        @media print {
            /* Uncomment this and comment above for A4 multiple labels
            @page {
                size: A4;
                margin: 10mm;
            }
            
            body {
                margin: 0;
                padding: 0;
            }
            
            .container {
                margin: 0;
                padding: 0;
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 10mm;
            }
            */
        }
        
        /* Mobile responsive */
        @media (max-width: 768px) {
            .container {
                max-width: 100%;
                padding: 10px;
            }
            
            .label-stiker {
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Instructions -->
        <div class="instructions no-print">
            <h3>Cara Menggunakan QR Code Label</h3>
            <p>Scan QR Code ini menggunakan aplikasi kamera atau QR scanner untuk melihat detail lengkap asset. Klik Print untuk mencetak label.</p>
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
        // Auto print when opened in new window
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
