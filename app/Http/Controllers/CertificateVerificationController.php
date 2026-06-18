<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CertificateVerificationController extends Controller
{
    public function show(string $uuid): View
    {
        $certificate = Certificate::with(['documentConfiguration.event'])
            ->where('uuid', $uuid)
            ->firstOrFail();

        return view('certificates.verify', [
            'certificate' => $certificate,
        ]);
    }

    public function preview(Request $request, string $uuid)
    {
        $startTime = microtime(true);
        
        $certificate = Certificate::with(['documentConfiguration.event'])
            ->where('uuid', $uuid)
            ->firstOrFail();
            
        $configUpdatedAt = $certificate->documentConfiguration?->updated_at?->timestamp ?? 0;
        $format = $request->query('format', 'png');

        // 1. Intentar servir desde caché incluyendo el timestamp del diseño
        if ($format === 'png' || $format === 'jpg') {
            $previewPath = "previews/{$uuid}_{$configUpdatedAt}.{$format}";
            if (Storage::disk('local')->exists($previewPath)) {
                return response(Storage::disk('local')->get($previewPath), 200, [
                    'Content-Type' => "image/{$format}",
                    'Cache-Control' => 'public, max-age=86400',
                ]);
            }
        }

        // 2. Si no hay caché, procesar
        $t1 = microtime(true);
        $data = $certificate->recipient_data ?? [];
        $data['folio'] = $certificate->folio_number ?? $data['folio'] ?? null;

        if ($certificate->qr_path) {
            $data['qr_path'] = Storage::disk('public')->path($certificate->qr_path);
        }

        $pdf = $certificate->documentConfiguration->generatePDF($data);
        $pdfContent = $pdf->Output('S');
        $t2 = microtime(true);

        if ($request->query('format') === 'png' || $request->query('format') === 'jpg') {
            $format = $request->query('format', 'png');
            
            if (!class_exists(\Imagick::class)) {
                // Fallback GD...
                $img = imagecreatetruecolor(800, 400);
                $bg = imagecolorallocate($img, 245, 245, 245);
                imagefill($img, 0, 0, $bg);
                imagestring($img, 5, 160, 180, 'Imagick no disponible', imagecolorallocate($img, 100, 100, 100));
                ob_start();
                imagepng($img);
                return response(ob_get_clean(), 200, ['Content-Type' => 'image/png']);
            }

            $tmpPdf = storage_path('app/tmp/v_' . $uuid . '.pdf');
            if (!is_dir(dirname($tmpPdf))) mkdir(dirname($tmpPdf), 0755, true);
            file_put_contents($tmpPdf, $pdfContent);

            try {
                $imagick = new \Imagick();
                // Reducir resolución para mayor velocidad en previsualización
                $imagick->setResolution(72, 72);
                $imagick->readImage($tmpPdf . '[0]');
                $imagick->setImageBackgroundColor('white');
                $imagick = $imagick->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
                $imagick->setImageFormat($format);
                
                // Usar un filtro más rápido para el redimensionamiento
                $imagick->resizeImage(800, 0, \Imagick::FILTER_TRIANGLE, 1);
                
                if ($format === 'jpg') {
                    $imagick->setImageCompressionQuality(75);
                }
                
                $blob = $imagick->getImageBlob();
                Storage::disk('local')->put("previews/{$uuid}_{$configUpdatedAt}.{$format}", $blob);

                $imagick->clear();
                $imagick->destroy();
                
                $t3 = microtime(true);
                $total = round(($t3 - $startTime) * 1000);
                $pdfTime = round(($t2 - $t1) * 1000);
                $imgTime = round(($t3 - $t2) * 1000);
                
                \Log::info("PREVIEW: UUID {$uuid} | Total: {$total}ms | PDF: {$pdfTime}ms | IMG: {$imgTime}ms");
            } catch (\Exception $e) {
                \Log::error("ERROR PREVIEW: " . $e->getMessage());
                throw $e;
            } finally {
                @unlink($tmpPdf);
            }

            return response($blob, 200, ['Content-Type' => "image/{$format}"]);
        }

        $disposition = $request->boolean('download') ? 'attachment' : 'inline';

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => $disposition . '; filename="constancia.pdf"'
        ]);
    }
}
