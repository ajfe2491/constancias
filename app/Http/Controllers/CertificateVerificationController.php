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
        
        // 1. Intentar servir desde caché inmediatamente (lo más rápido)
        if ($request->query('format') === 'png') {
            $previewPath = "previews/{$uuid}.png";
            if (Storage::disk('local')->exists($previewPath)) {
                $png = Storage::disk('local')->get($previewPath);
                return response($png, 200, [
                    'Content-Type' => 'image/png',
                    'Cache-Control' => 'public, max-age=3600',
                ]);
            }
        }

        // 2. Si no hay caché, procesar (lento)
        $certificate = Certificate::with(['documentConfiguration.event'])
            ->where('uuid', $uuid)
            ->firstOrFail();

        $data = $certificate->recipient_data ?? [];
        $data['folio'] = $certificate->folio_number ?? $data['folio'] ?? null;

        if ($certificate->qr_path) {
            $data['qr_path'] = Storage::disk('public')->path($certificate->qr_path);
        }

        $pdf = $certificate->documentConfiguration->generatePDF($data);
        $pdfContent = $pdf->Output('S');

        if ($request->query('format') === 'png') {
            if (!class_exists(\Imagick::class)) {
                return response()->json(['message' => 'Imagick no disponible'], 501);
            }

            $tmpPdf = storage_path('app/tmp/v_' . $uuid . '.pdf');
            if (!is_dir(dirname($tmpPdf))) mkdir(dirname($tmpPdf), 0755, true);
            file_put_contents($tmpPdf, $pdfContent);

            try {
                $imagick = new \Imagick();
                // Resolución baja para rasterización rápida
                $imagick->setResolution(72, 72);
                $imagick->readImage($tmpPdf . '[0]');
                $imagick->setImageBackgroundColor('white');
                $imagick = $imagick->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
                $imagick->setImageFormat('png');
                $imagick->resizeImage(800, 0, \Imagick::FILTER_LANCZOS, 1);
                
                $png = $imagick->getImageBlob();
                Storage::disk('local')->put("previews/{$uuid}.png", $png);

                $imagick->clear();
                $imagick->destroy();
                
                $totalTime = round((microtime(true) - $startTime) * 1000);
                \Log::error("PREVIEW GENERADO: UUID {$uuid} en {$totalTime}ms");
            } catch (\Exception $e) {
                \Log::error("ERROR PREVIEW: " . $e->getMessage());
                throw $e;
            } finally {
                @unlink($tmpPdf);
            }

            return response($png, 200, ['Content-Type' => 'image/png']);
        }

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="constancia.pdf"'
        ]);
    }
}
