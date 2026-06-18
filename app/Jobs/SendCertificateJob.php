<?php

namespace App\Jobs;

use App\Mail\CertificateMail;
use App\Models\Certificate;
use App\Models\ConstancyGeneralHistory;
use App\Models\DocumentConfiguration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendCertificateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public DocumentConfiguration $configuration,
        public array $recipientData,
        public int $historyId
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $certificate = Certificate::create([
                'uuid' => (string) Str::uuid(),
                'recipient_name' => $this->recipientData['nombre_participante'] ?? null,
                'recipient_email' => $this->recipientData['email'] ?? null,
                'recipient_data' => $this->recipientData,
                'document_configuration_id' => $this->configuration->id,
                'event_id' => $this->configuration->event_id,
                'history_id' => $this->historyId,
            ]);

            // Folio Calculation: Scope by DocumentConfiguration and Event
            $lastCertificate = Certificate::where('document_configuration_id', $this->configuration->id)
                ->where('event_id', $this->configuration->event_id)
                ->where('id', '!=', $certificate->id)
                ->latest('id')
                ->first();

            $configFolioStart = $this->configuration->folio_start ?? 1;

            if ($lastCertificate && $lastCertificate->folio_number) {
                // Respect folio_start as a minimum value, allowing "jumps" if configured
                $folioNumber = max($lastCertificate->folio_number + 1, $configFolioStart);
            } else {
                $folioNumber = $configFolioStart;
            }

            $formattedFolio = $this->configuration->formatFolio($folioNumber);
            $certificate->update([
                'folio_number' => $folioNumber,
                'folio' => $formattedFolio,
            ]);

            $this->recipientData['folio'] = $folioNumber;

            if ($this->configuration->show_qr) {
                $verificationUrl = route('certificates.verify', $certificate->uuid);
                $qrImage = (new \chillerlan\QRCode\QRCode(new \chillerlan\QRCode\QROptions([
                    'outputType' => \chillerlan\QRCode\QRCode::OUTPUT_IMAGE_PNG,
                    'eccLevel' => \chillerlan\QRCode\QRCode::ECC_L,
                    'scale' => 5,
                    'imageBase64' => false,
                ])))->render($verificationUrl);

                $qrRelativePath = 'qrs/' . $certificate->uuid . '.png';
                Storage::disk('public')->put($qrRelativePath, $qrImage);

                $certificate->update([
                    'qr_path' => $qrRelativePath,
                ]);

                $this->recipientData['qr_path'] = storage_path('app/public/' . $qrRelativePath);
                ConstancyGeneralHistory::where('id', $this->historyId)->increment('qrs_generados');
            }

            // Generate PDF
            // The generatePDF method expects an array of data to replace placeholders
            $pdf = $this->configuration->generatePDF($this->recipientData);
            $pdfContent = $pdf->Output('', 'S');

            // Send Email
            $event = $this->configuration->event;
            $eventDates = 'N/A';
            if ($event && ($event->start_date || $event->end_date)) {
                $start = $event->start_date ? $event->start_date->format('d/m/Y') : null;
                $end = $event->end_date ? $event->end_date->format('d/m/Y') : null;
                $eventDates = $start && $end ? $start . ' - ' . $end : ($start ?: $end);
            }
            $templateHtml = $this->configuration->email_template_html
                ?: ($event->email_template_html ?? null);
            $templateSubject = $this->configuration->email_subject
                ?: ($event->email_subject ?? null);
            $eventLogoUrl = $event && $event->logo ? url(Storage::url($event->logo)) : '';
            $templateData = [
                'participant_name' => $this->recipientData['nombre_participante'] ?? 'Participante',
                'event_name' => $event->name ?? 'Evento',
                'event_logo_url' => $eventLogoUrl,
                'event_type' => $event->type ?? '',
                'event_key' => $event->key ?? '',
                'event_dates' => $eventDates,
                'event_description' => $event->description ?? '',
                'document_name' => $this->configuration->document_name ?? 'Constancia',
                'document_type' => $this->configuration->document_type ?? '',
                'document_description' => $this->configuration->description ?? '',
                'recipient_email' => $this->recipientData['email'] ?? '',
                'folio' => $certificate->folio ?? '',
                'uuid' => $certificate->uuid,
                'verification_url' => route('certificates.verify', $certificate->uuid),
            ];
            $templateData = $templateData + $this->recipientData;

            Mail::to($this->recipientData['email'])->send(new CertificateMail(
                $pdfContent,
                ($this->configuration->document_name ?? 'constancia') . '.pdf',
                $this->recipientData['nombre_participante'] ?? 'Participante',
                $event->name ?? 'Evento',
                $this->configuration->document_name ?? 'Constancia',
                $event->logo ?? null,
                $this->configuration->email_message ?? null,
                $templateHtml,
                $templateSubject,
                $templateData
            ));

            // Update History (Success)
            ConstancyGeneralHistory::where('id', $this->historyId)->increment('procesados_exitosos');

        } catch (\Exception $e) {
            Log::error('Error sending certificate: ' . $e->getMessage());

            // Update History (Failure)
            $history = ConstancyGeneralHistory::find($this->historyId);
            if ($history) {
                $history->increment('procesados_fallidos');

                // Append error to JSON column
                $errors = $history->errores ?? [];
                $errors[] = [
                    'email' => $this->recipientData['email'] ?? 'unknown',
                    'error' => $e->getMessage(),
                    'time' => now()->toDateTimeString()
                ];
                $history->update(['errores' => $errors]);
            }
        }
    }
}
