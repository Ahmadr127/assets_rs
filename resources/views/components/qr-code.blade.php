@props(['fixedAsset', 'size' => 120])

@php
    try {
        $url = route('fixed-assets.show', $fixedAsset);
        $qrCodeSvg = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
            ->size($size)
            ->margin(1)
            ->generate($url);
    } catch (\Exception $e) {
        $qrCodeSvg = '<div class="text-red-500 text-xs p-2">QR Code Error</div>';
    }
@endphp

<div class="text-center">
    <!-- QR Code Display -->
    <div class="flex justify-center mb-2">
        <div class="inline-block p-1 bg-white border border-gray-200 rounded">
            {!! $qrCodeSvg !!}
        </div>
    </div>
    
    <!-- Title -->
    <p class="text-xs text-gray-600 mb-2 truncate">{{ $fixedAsset->nama_fixed_asset }}</p>
    
    <!-- Action Buttons -->
    <div class="flex justify-center space-x-1">
        <!-- Print Button -->
        <a href="{{ route('qr.asset.print', $fixedAsset) }}?autoprint=1" 
           target="_blank"
           class="inline-flex items-center px-2 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700 transition"
           title="Print QR Code">
            <i class="fas fa-print mr-1"></i>
            Print
        </a>
        
        <!-- Download Button -->
        <button onclick="downloadQRCode({{ json_encode(route('qr.asset.download', ['fixedAsset' => $fixedAsset, 'size' => 400, 'format' => 'svg'])) }}, {{ json_encode('qrcode_' . $fixedAsset->kode . '.svg') }})"
                class="inline-flex items-center px-2 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700 transition"
                title="Download QR Code">
            <i class="fas fa-download mr-1"></i>
            Download
        </button>
        
        <!-- Share Button -->
        <button onclick="shareAsset({{ json_encode(route('fixed-assets.show', $fixedAsset)) }}, {{ json_encode($fixedAsset->nama_fixed_asset) }})" 
                class="inline-flex items-center px-2 py-1 bg-purple-600 text-white text-xs rounded hover:bg-purple-700 transition"
                title="Share Asset">
            <i class="fas fa-share-alt mr-1"></i>
            Share
        </button>
        
        <!-- Copy URL Button -->
        <button onclick="copyToClipboard({{ json_encode(route('fixed-assets.show', $fixedAsset)) }})" 
                class="inline-flex items-center px-2 py-1 bg-gray-600 text-white text-xs rounded hover:bg-gray-700 transition"
                title="Copy URL">
            <i class="fas fa-copy mr-1"></i>
            Copy
        </button>
    </div>
</div>

@push('scripts')
<script>
function downloadQRCode(url, filename) {
    try {
        // Method 1: Try fetch API for better error handling
        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.blob();
            })
            .then(blob => {
                // Create blob URL
                const blobUrl = window.URL.createObjectURL(blob);
                
                // Create temporary anchor element
                const link = document.createElement('a');
                link.href = blobUrl;
                link.download = filename;
                link.style.display = 'none';
                
                // Append, click, and cleanup
                document.body.appendChild(link);
                link.click();
                
                // Clean up
                setTimeout(() => {
                    document.body.removeChild(link);
                    window.URL.revokeObjectURL(blobUrl);
                }, 100);
                
                // Show success message
                if (typeof showToast === 'function') {
                    showToast('QR Code berhasil diunduh!');
                }
            })
            .catch(err => {
                console.error('Download error:', err);
                // Fallback to direct link method
                window.location.href = url;
            });
    } catch (err) {
        console.error('Download error:', err);
        // Final fallback
        window.location.href = url;
    }
}

function shareAsset(url, title) {
    if (navigator.share) {
        navigator.share({
            title: title,
            text: 'Lihat detail asset: ' + title,
            url: url
        }).catch(err => console.log('Error sharing:', err));
    } else {
        // Fallback: copy to clipboard
        copyToClipboard(url);
        showToast('URL berhasil disalin ke clipboard!');
    }
}

function copyToClipboard(text) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(() => {
            showToast('URL berhasil disalin!');
        }).catch(err => {
            console.error('Failed to copy: ', err);
            fallbackCopyTextToClipboard(text);
        });
    } else {
        fallbackCopyTextToClipboard(text);
    }
}

function fallbackCopyTextToClipboard(text) {
    const textArea = document.createElement("textarea");
    textArea.value = text;
    textArea.style.top = "0";
    textArea.style.left = "0";
    textArea.style.position = "fixed";
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    
    try {
        const successful = document.execCommand('copy');
        if (successful) {
            showToast('URL berhasil disalin!');
        }
    } catch (err) {
        console.error('Fallback: Oops, unable to copy', err);
    }
    
    document.body.removeChild(textArea);
}

function showToast(message) {
    // Ensure DOM is ready
    if (!document.body) {
        console.warn('Toast: document.body not ready');
        return;
    }
    
    // Create toast notification
    const toast = document.createElement('div');
    toast.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded shadow-lg z-50 text-sm';
    toast.textContent = message;
    
    try {
        document.body.appendChild(toast);
        
        // Remove toast after 3 seconds
        setTimeout(() => {
            if (toast && toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 3000);
    } catch (err) {
        console.error('Toast error:', err);
    }
}
</script>
@endpush
