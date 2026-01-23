<?php

namespace App\Http\Controllers;

use App\Mail\CertificateMail;
use App\Models\Certificate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class CertificateResendController extends Controller
{
    public function store(Request $request, Certificate $certificate): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'data' => ['nullable', 'array'],
            'data.*' => ['nullable', 'string'],
        ]);

        $certificate->loadMissing('documentConfiguration.event');

        $recipientData = $certificate->recipient_data ?? [];
        $recipientData = array_merge($recipientData, $validated['data'] ?? []);
        $recipientData['email'] = $validated['email'];
        if ($certificate->folio_number) {
            $recipientData['folio'] = $certificate->folio_number;
        }
        if ($certificate->qr_path) {
            $recipientData['qr_path'] = Storage::disk('public')->path($certificate->qr_path);
        }

        $pdf = $certificate->documentConfiguration->generatePDF($recipientData);
        $pdfContent = $pdf->Output('', 'S');

        Mail::to($validated['email'])->send(new CertificateMail(
            $pdfContent,
            ($certificate->documentConfiguration->document_name ?? 'constancia') . '.pdf',
            $recipientData['nombre_participante'] ?? 'Participante',
            $certificate->documentConfiguration->event->name ?? 'Evento',
            $certificate->documentConfiguration->document_name ?? 'Constancia',
            $certificate->documentConfiguration->event->logo ?? null,
            $certificate->documentConfiguration->email_message ?? null
        ));

        $certificate->update([
            'recipient_email' => $validated['email'],
            'recipient_data' => $recipientData,
        ]);

        return back()->with('success', 'Constancia reenviada correctamente.');
    }
}
