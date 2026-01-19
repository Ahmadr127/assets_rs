<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Models\FixedAsset;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;

class QRCodeController extends Controller
{
    /**
     * Generate QR code for a given URL
     *
     * @param Request $request
     * @return Response
     */
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
        $size = (int) $request->input('size', 200);
        $format = $request->input('format', 'png');
        $margin = (int) $request->input('margin', 2);

        try {
            $cacheKey = 'qrcode_' . md5($url . $size . $format . $margin . 'v6');
            
            $qrData = Cache::remember($cacheKey, 3600, function () use ($url, $size, $format, $margin) {
                $builder = Builder::create()
                    ->writer($format === 'svg' ? new SvgWriter() : new PngWriter())
                    ->writerOptions([])
                    ->data($url)
                    ->encoding(new Encoding('UTF-8'))
                    ->errorCorrectionLevel(ErrorCorrectionLevel::High)
                    ->size($size)
                    ->margin($margin)
                    ->roundBlockSizeMode(RoundBlockSizeMode::Margin);

                if ($format === 'png') {
                    // Logo removal requested
                }

                $result = $builder->build();
                return [
                    'content' => $result->getString(),
                    'mime' => $result->getMimeType()
                ];
            });

            return response($qrData['content'])
                ->header('Content-Type', $qrData['mime'])
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
            $request->validate([
                'size' => 'nullable|integer|min:100|max:500',
                'format' => 'nullable|in:png,svg',
                'margin' => 'nullable|integer|min:0|max:10'
            ]);

            $size = (int) $request->input('size', 200);
            $format = $request->input('format', 'png');
            $margin = (int) $request->input('margin', 2);

            $url = route('asset.public.show', $fixedAsset);
            
            $builder = Builder::create()
                ->writer($format === 'svg' ? new SvgWriter() : new PngWriter())
                ->writerOptions([])
                ->data($url)
                ->encoding(new Encoding('UTF-8'))
                ->errorCorrectionLevel(ErrorCorrectionLevel::High)
                ->size($size)
                ->margin($margin)
                ->roundBlockSizeMode(RoundBlockSizeMode::Margin);

            if ($format === 'png') {
                // Logo removal requested
            }

            $qrCodeResult = $builder->build();

            $safeKode = preg_replace('/[^a-zA-Z0-9_-]/', '_', $fixedAsset->kode);
            $filename = 'asset_' . $safeKode . '_qrcode.' . $format;

            return response($qrCodeResult->getString())
                ->header('Content-Type', $qrCodeResult->getMimeType())
                ->header('Cache-Control', 'public, max-age=3600')
                ->header('Content-Disposition', 'inline; filename="' . $filename . '"');

        } catch (\Exception $e) {
            Log::error('Fixed Asset QR Code generation failed', [
                'asset_id' => $fixedAsset->id ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response('QR Code generation failed: ' . $e->getMessage(), 500)
                ->header('Content-Type', 'text/plain');
        }
    }



    /**
     * Generate printable QR code page for Fixed Asset
     * Menggunakan template terpisah untuk setiap ukuran
     *
     * @param FixedAsset $fixedAsset
     * @param Request $request
     * @return Response
     */
    public function printableAsset(FixedAsset $fixedAsset, Request $request)
    {
        $request->validate([
            'format' => 'nullable|exists:print_formats,code'
        ]);

        try {
            // Get print format from request or use default
            $formatCode = $request->input('format');
            
            if ($formatCode) {
                $printFormat = \App\Models\PrintFormat::where('code', $formatCode)
                    ->where('is_active', true)
                    ->first();
            }
            
            // Fallback to default if not found
            if (!isset($printFormat) || !$printFormat) {
                $printFormat = \App\Models\PrintFormat::getDefault();
            }

            // If still no format found, return error
            if (!$printFormat) {
                throw new \Exception('No print format available. Please run PrintFormatSeeder.');
            }

            // Generate QR code URL
            $url = route('asset.public.show', $fixedAsset);
            
            // Determine QR size based on format
            $qrSizes = [
                '6x5' => 295,   // 25mm at 300 DPI
                '5x3' => 248,   // 21mm at 300 DPI
                '5x2.5' => 260, // 22mm at 300 DPI
                '5x2' => 213,   // 18mm at 300 DPI
            ];
            
            $qrSizePx = $qrSizes[$printFormat->code] ?? 295;
            
            // Generate QR code PNG with Logo using Endroid
            $builder = Builder::create()
                ->writer(new PngWriter())
                ->writerOptions([])
                ->data($url)
                ->encoding(new Encoding('UTF-8'))
                ->errorCorrectionLevel(ErrorCorrectionLevel::High)
                ->size($qrSizePx)
                ->margin(0) // No margin for print, handled by CSS
                ->roundBlockSizeMode(RoundBlockSizeMode::Margin);



            $result = $builder->build();
            $qrCodeImage = $result->getDataUri();

            // Determine which template to use based on format code
            $templates = [
                '6x5' => 'qr-codes.print-6x5',
                '5x3' => 'qr-codes.print-5x3',
                '5x2.5' => 'qr-codes.print-5x25', // Filename tanpa titik
                '5x2' => 'qr-codes.print-5x2',
            ];
            
            $template = $templates[$printFormat->code] ?? 'qr-codes.print-6x5';

            $html = view($template, [
                'fixedAsset' => $fixedAsset,
                'qrCodeImage' => $qrCodeImage, // Pass Data URI instead of SVG
                'printFormat' => $printFormat,
                'url' => $url,
                'generatedAt' => now()->format('d/m/Y H:i')
            ])->render();

            return response($html)
                ->header('Content-Type', 'text/html')
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate');

        } catch (\Exception $e) {
            Log::error('Printable QR Code generation failed', [
                'asset_id' => $fixedAsset->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Failed to generate printable QR code: ' . $e->getMessage()
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

        $size = (int) $request->input('size', 400);
        $format = $request->input('format', 'svg');

        try {
            $url = route('asset.public.show', $fixedAsset);
            
            $builder = Builder::create()
                ->writer($format === 'svg' ? new SvgWriter() : new PngWriter())
                ->writerOptions([])
                ->data($url)
                ->encoding(new Encoding('UTF-8'))
                ->errorCorrectionLevel(ErrorCorrectionLevel::High)
                ->size($size)
                ->margin(2)
                ->roundBlockSizeMode(RoundBlockSizeMode::Margin);

            if ($format === 'png') {
                // Logo removal requested
            }

            $qrCodeResult = $builder->build();
            
            $safeKode = preg_replace('/[^a-zA-Z0-9_-]/', '_', $fixedAsset->kode);
            $filename = 'qrcode_' . $safeKode . '_' . now()->format('Ymd_His') . '.' . $format;

            return response($qrCodeResult->getString())
                ->header('Content-Type', $qrCodeResult->getMimeType())
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Content-Length', strlen($qrCodeResult->getString()))
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');

        } catch (\Exception $e) {
            Log::error('QR Code download failed', [
                'asset_id' => $fixedAsset->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Failed to download QR code: ' . $e->getMessage()
            ], 500);
        }
    }
}
