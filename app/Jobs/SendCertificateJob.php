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

            $folioNumber = $this->configuration->folio_start
                ? ($this->configuration->folio_start + $certificate->id - 1)
                : $certificate->id;
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
            Mail::to($this->recipientData['email'])->send(new CertificateMail(
                $pdfContent,
                ($this->configuration->document_name ?? 'constancia') . '.pdf',
                $this->recipientData['nombre_participante'] ?? 'Participante',
                $this->configuration->event->name ?? 'Evento',
                $this->configuration->document_name ?? 'Constancia',
                $this->configuration->event->logo ?? null,
                $this->configuration->email_message ?? null
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
