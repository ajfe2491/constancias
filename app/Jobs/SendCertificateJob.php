<?php

namespace App\Jobs;

use App\Mail\CertificateMail;
use App\Models\ConstancyGeneralHistory;
use App\Models\DocumentConfiguration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
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
