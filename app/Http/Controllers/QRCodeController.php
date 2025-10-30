<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Models\FixedAsset;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class QRCodeController extends Controller
{
    /**
     * Generate QR code for a given URL
     *
     * @param Request $request
     * @return Response
     */
    public function generate(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
            'size' => 'nullable|integer|min:100|max:500',
            'format' => 'nullable|in:png,svg',
            'margin' => 'nullable|integer|min:0|max:10'
        ]);

        $url = $request->input('url');
        $size = $request->input('size', 200);
        $format = $request->input('format', 'png');
        $margin = $request->input('margin', 2);

        try {
            // Create cache key based on parameters
            $cacheKey = 'qrcode_' . md5($url . $size . $format . $margin);
            
            // Cache QR code for 1 hour
            $qrCode = Cache::remember($cacheKey, 3600, function () use ($url, $size, $format, $margin) {
                if ($format === 'svg') {
                    return QrCode::format('svg')
                        ->size($size)
                        ->margin($margin)
                        ->generate($url);
                } else {
                    return QrCode::format('png')
                        ->size($size)
                        ->margin($margin)
                        ->generate($url);
                }
            });

            $contentType = $format === 'svg' ? 'image/svg+xml' : 'image/png';

            return response($qrCode)
                ->header('Content-Type', $contentType)
                ->header('Cache-Control', 'public, max-age=3600')
                ->header('Content-Disposition', 'inline; filename="qrcode.' . $format . '"');

        } catch (\Exception $e) {
            Log::error('QR Code generation failed', [
                'url' => $url,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to generate QR code'
            ], 500);
        }
    }

    /**
     * Generate QR code specifically for Fixed Asset
     *
     * @param FixedAsset $fixedAsset
     * @param Request $request
     * @return Response
     */
    public function fixedAsset(FixedAsset $fixedAsset, Request $request)
    {
        try {
            // Validate input parameters
            $request->validate([
                'size' => 'nullable|integer|min:100|max:500',
                'format' => 'nullable|in:png,svg',
                'margin' => 'nullable|integer|min:0|max:10'
            ]);

            $size = $request->input('size', 200);
            $format = $request->input('format', 'png');
            $margin = $request->input('margin', 2);

            // Generate the URL for the asset (public route for QR scanning)
            $url = route('asset.public.show', $fixedAsset);
            
            // Generate QR code
            $qrCode = QrCode::format($format)
                ->size($size)
                ->margin($margin)
                ->errorCorrection('M')
                ->generate($url);

            $contentType = $format === 'svg' ? 'image/svg+xml' : 'image/png';
            
            // Sanitize filename
            $safeKode = preg_replace('/[^a-zA-Z0-9_-]/', '_', $fixedAsset->kode);
            $filename = 'asset_' . $safeKode . '_qrcode.' . $format;

            return response()->make($qrCode, 200, [
                'Content-Type' => $contentType,
                'Cache-Control' => 'public, max-age=3600',
                'Content-Disposition' => 'inline; filename="' . $filename . '"'
            ]);

        } catch (\Exception $e) {
            Log::error('Fixed Asset QR Code generation failed', [
                'asset_id' => $fixedAsset->id ?? 'unknown',
                'asset_code' => $fixedAsset->kode ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Return a simple error response for debugging
            return response('QR Code generation failed: ' . $e->getMessage(), 500)
                ->header('Content-Type', 'text/plain');
        }
    }

    /**
     * Generate printable QR code page for Fixed Asset
     *
     * @param FixedAsset $fixedAsset
     * @param Request $request
     * @return Response
     */
    public function printableAsset(FixedAsset $fixedAsset, Request $request)
    {
        $request->validate([
            'size' => 'nullable|integer|min:200|max:400'
        ]);

        $size = $request->input('size', 200);
        
        try {
            $url = route('asset.public.show', $fixedAsset);
            $qrCodeSvg = QrCode::format('svg')
                ->size($size)
                ->margin(2)
                ->errorCorrection('M')
                ->generate($url);

            $html = view('qr-codes.printable-asset', [
                'fixedAsset' => $fixedAsset,
                'qrCodeSvg' => $qrCodeSvg,
                'url' => $url,
                'generatedAt' => now()->format('d/m/Y H:i')
            ])->render();

            return response($html)
                ->header('Content-Type', 'text/html')
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate');

        } catch (\Exception $e) {
            Log::error('Printable QR Code generation failed', [
                'asset_id' => $fixedAsset->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to generate printable QR code'
            ], 500);
        }
    }

    /**
     * Download QR code as file
     *
     * @param FixedAsset $fixedAsset
     * @param Request $request
     * @return Response|\Illuminate\Http\JsonResponse
     */
    public function download(FixedAsset $fixedAsset, Request $request)
    {
        $request->validate([
            'size' => 'nullable|integer|min:100|max:1000',
            'format' => 'nullable|in:png,svg'
        ]);

        $size = $request->input('size', 400);
        $format = $request->input('format', 'svg'); // Default to SVG (no imagick needed)

        try {
            $url = route('asset.public.show', $fixedAsset);
            
            // Always use SVG format as it doesn't require imagick extension
            // SVG is also better quality and smaller file size
            $qrCode = QrCode::format('svg')
                ->size($size)
                ->margin(2)
                ->errorCorrection('H')
                ->generate($url);
            
            $contentType = 'image/svg+xml';
            $actualFormat = 'svg';

            // Sanitize filename
            $safeKode = preg_replace('/[^a-zA-Z0-9_-]/', '_', $fixedAsset->kode);
            $filename = 'qrcode_' . $safeKode . '_' . now()->format('Ymd_His') . '.' . $actualFormat;

            return response()->make($qrCode, 200, [
                'Content-Type' => $contentType,
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Content-Length' => strlen($qrCode),
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ]);

        } catch (\Exception $e) {
            Log::error('QR Code download failed', [
                'asset_id' => $fixedAsset->id,
                'asset_code' => $fixedAsset->kode ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Failed to download QR code: ' . $e->getMessage()
            ], 500);
        }
    }
}
