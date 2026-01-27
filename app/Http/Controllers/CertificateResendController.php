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
        $user = $request->user();
        if (!$user || (!$user->isSuperAdmin() && !$certificate->canBeViewedBy($user))) {
            abort(403);
        }

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

        $event = $certificate->documentConfiguration->event;
        $eventDates = 'N/A';
        if ($event && ($event->start_date || $event->end_date)) {
            $start = $event->start_date ? $event->start_date->format('d/m/Y') : null;
            $end = $event->end_date ? $event->end_date->format('d/m/Y') : null;
            $eventDates = $start && $end ? $start . ' - ' . $end : ($start ?: $end);
        }
        $templateHtml = $certificate->documentConfiguration->email_template_html
            ?: ($event->email_template_html ?? null);
        $templateSubject = $certificate->documentConfiguration->email_subject
            ?: ($event->email_subject ?? null);
        $eventLogoUrl = $event && $event->logo ? url(Storage::url($event->logo)) : '';
        $templateData = [
            'participant_name' => $recipientData['nombre_participante'] ?? 'Participante',
            'event_name' => $event->name ?? 'Evento',
            'event_logo_url' => $eventLogoUrl,
            'event_type' => $event->type ?? '',
            'event_key' => $event->key ?? '',
            'event_dates' => $eventDates,
            'event_description' => $event->description ?? '',
            'document_name' => $certificate->documentConfiguration->document_name ?? 'Constancia',
            'document_type' => $certificate->documentConfiguration->document_type ?? '',
            'document_description' => $certificate->documentConfiguration->description ?? '',
            'recipient_email' => $validated['email'],
            'folio' => $certificate->folio ?? '',
            'uuid' => $certificate->uuid,
            'verification_url' => route('certificates.verify', $certificate->uuid),
        ];
        $templateData = $templateData + $recipientData;

        Mail::to($validated['email'])->send(new CertificateMail(
            $pdfContent,
            ($certificate->documentConfiguration->document_name ?? 'constancia') . '.pdf',
            $recipientData['nombre_participante'] ?? 'Participante',
            $event->name ?? 'Evento',
            $certificate->documentConfiguration->document_name ?? 'Constancia',
            $event->logo ?? null,
            $certificate->documentConfiguration->email_message ?? null,
            $templateHtml,
            $templateSubject,
            $templateData
        ));

        $certificate->update([
            'recipient_email' => $validated['email'],
            'recipient_data' => $recipientData,
        ]);

        return back()->with('success', 'Constancia reenviada correctamente.');
    }
}
