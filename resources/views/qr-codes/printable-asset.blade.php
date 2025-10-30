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
            color: #333;
            line-height: 1.3;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 15px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        
        .header h1 {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 3px;
        }
        
        .header p {
            font-size: 12px;
            color: #666;
        }
        
        .qr-section {
            display: flex;
            align-items: flex-start;
            gap: 20px;
            margin-bottom: 15px;
        }
        
        .qr-code {
            flex-shrink: 0;
            text-align: center;
            border: 2px solid #333;
            padding: 10px;
            background: white;
        }
        
        .qr-code svg {
            display: block;
            max-width: 200px;
            max-height: 200px;
            width: 100%;
            height: auto;
        }
        
        .asset-info {
            flex: 1;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }
        
        .info-item {
            border-bottom: 1px solid #eee;
            padding-bottom: 4px;
        }
        
        .info-label {
            font-size: 10px;
            color: #666;
            text-transform: uppercase;
            font-weight: bold;
            margin-bottom: 2px;
        }
        
        .info-value {
            font-size: 11px;
            font-weight: 500;
        }
        
        .url-section {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 3px;
            padding: 10px;
            margin-bottom: 12px;
        }
        
        .url-label {
            font-size: 10px;
            color: #666;
            margin-bottom: 3px;
            font-weight: bold;
        }
        
        .url-value {
            font-size: 10px;
            font-family: 'Courier New', monospace;
            word-break: break-all;
            background: white;
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 2px;
        }
        
        .footer {
            text-align: center;
            font-size: 9px;
            color: #666;
            border-top: 1px solid #eee;
            padding-top: 8px;
        }
        
        .instructions {
            background: #e3f2fd;
            border-left: 3px solid #2196f3;
            padding: 10px;
            margin-bottom: 12px;
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
        
        /* Print styles */
        @media print {
            @page {
                size: A4;
                margin: 10mm;
            }
            
            body {
                margin: 0;
                padding: 0;
            }
            
            .container {
                max-width: none;
                margin: 0;
                padding: 0;
            }
            
            .no-print {
                display: none !important;
            }
            
            .qr-section {
                page-break-inside: avoid;
            }
            
            .header {
                margin-bottom: 10px;
                padding-bottom: 8px;
            }
            
            .qr-section {
                margin-bottom: 10px;
                gap: 15px;
            }
            
            .qr-code {
                padding: 8px;
            }
            
            .qr-code svg {
                max-width: 180px;
                max-height: 180px;
            }
            
            .info-grid {
                gap: 5px;
            }
            
            .info-item {
                padding-bottom: 3px;
            }
            
            .url-section {
                padding: 6px;
                margin-bottom: 8px;
            }
            
            .footer {
                padding-top: 5px;
                font-size: 8px;
            }
            
            .instructions {
                padding: 8px;
                margin-bottom: 8px;
            }
        }
        
        /* Mobile responsive */
        @media (max-width: 768px) {
            .qr-section {
                flex-direction: column;
                align-items: center;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>QR Code Asset</h1>
            <p>{{ $fixedAsset->nama_fixed_asset }}</p>
        </div>
        
        <!-- Instructions -->
        <div class="instructions no-print">
            <h3>Cara Menggunakan QR Code</h3>
            <p>Scan QR Code ini menggunakan aplikasi kamera atau QR scanner untuk melihat detail lengkap asset secara online.</p>
        </div>
        
        <!-- QR Code and Asset Info -->
        <div class="qr-section">
            <div class="qr-code">
                {!! $qrCodeSvg !!}
            </div>        
        </div>
        <!-- Footer -->
        <div class="footer">
            <p>
                <strong>Asset Management System</strong><br>
                QR Code dibuat pada: {{ $generatedAt }}<br>
                ID Asset: #{{ $fixedAsset->id }}
            </p>
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
