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
            line-height: 1.4;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        
        .header h1 {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .header p {
            font-size: 14px;
            color: #666;
        }
        
        .qr-section {
            display: flex;
            align-items: flex-start;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .qr-code {
            flex-shrink: 0;
            text-align: center;
            border: 3px solid #333;
            padding: 20px;
            background: white;
        }
        
        .qr-code svg {
            display: block;
        }
        
        .asset-info {
            flex: 1;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .info-item {
            border-bottom: 1px solid #eee;
            padding-bottom: 8px;
        }
        
        .info-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            font-weight: bold;
            margin-bottom: 3px;
        }
        
        .info-value {
            font-size: 14px;
            font-weight: 500;
        }
        
        .url-section {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .url-label {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .url-value {
            font-size: 12px;
            font-family: 'Courier New', monospace;
            word-break: break-all;
            background: white;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 3px;
        }
        
        .footer {
            text-align: center;
            font-size: 11px;
            color: #666;
            border-top: 1px solid #eee;
            padding-top: 15px;
        }
        
        .instructions {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .instructions h3 {
            font-size: 14px;
            margin-bottom: 8px;
            color: #1976d2;
        }
        
        .instructions p {
            font-size: 12px;
            color: #333;
        }
        
        /* Print styles */
        @media print {
            body {
                margin: 0;
                padding: 10px;
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
            
            <div class="asset-info">
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Kode Asset</div>
                        <div class="info-value">{{ $fixedAsset->kode }}</div>
                    </div>
                    
                    @if($fixedAsset->kode_manual)
                    <div class="info-item">
                        <div class="info-label">Kode Manual</div>
                        <div class="info-value">{{ $fixedAsset->kode_manual }}</div>
                    </div>
                    @endif
                    
                    <div class="info-item">
                        <div class="info-label">Nama Asset</div>
                        <div class="info-value">{{ $fixedAsset->nama_fixed_asset }}</div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Tipe</div>
                        <div class="info-value">{{ optional($fixedAsset->typeRef)->name ?? $fixedAsset->tipe_fixed_asset ?? '-' }}</div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Lokasi</div>
                        <div class="info-value">{{ optional($fixedAsset->location)->name ?? $fixedAsset->lokasi ?? '-' }}</div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">PIC</div>
                        <div class="info-value">{{ $fixedAsset->pic }}</div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Status</div>
                        <div class="info-value">{{ optional($fixedAsset->statusRef)->name ?? $fixedAsset->status_display ?? '-' }}</div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Kondisi</div>
                        <div class="info-value">{{ optional($fixedAsset->conditionRef)->name ?? $fixedAsset->condition_display ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- URL Section -->
        <div class="url-section">
            <div class="url-label">URL Detail Asset:</div>
            <div class="url-value">{{ $url }}</div>
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
