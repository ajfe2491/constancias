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
                return response()->json([
                    'message' => 'Imagick no está disponible en el servidor.'
                ], 501);
            }

            $tmpDir = storage_path('app/tmp');
            if (!is_dir($tmpDir)) {
                mkdir($tmpDir, 0755, true);
            }

            $tmpPdf = $tmpDir . '/verify_' . $certificate->id . '_' . uniqid() . '.pdf';
            file_put_contents($tmpPdf, $pdfContent);

            $imagick = new \Imagick();
            $imagick->setResolution(150, 150);
            $imagick->readImage($tmpPdf . '[0]');
            $imagick->setImageBackgroundColor('white');
            $imagick = $imagick->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
            $imagick->setImageFormat('png');
            $png = $imagick->getImageBlob();
            $imagick->clear();
            $imagick->destroy();
            @unlink($tmpPdf);

            return response($png, 200, [
                'Content-Type' => 'image/png',
                'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            ]);
        }

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="constancia.pdf"'
        ]);
    }
}
