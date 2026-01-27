<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use App\Support\EmailTemplateRenderer;

class CertificateMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public $pdfContent,
        public $filename,
        public $participantName,
        public $eventName,
        public $documentName,
        public $logo = null,
        public $emailMessage = null,
        public $emailTemplateHtml = null,
        public $emailSubject = null,
        public array $templateData = []
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->emailSubject
            ? EmailTemplateRenderer::renderSubject($this->emailSubject, $this->templateData)
            : $this->documentName . ' ' . $this->eventName;

        return new Envelope(
            from: new \Illuminate\Mail\Mailables\Address(config('mail.from.address'), $this->eventName),
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        if ($this->emailTemplateHtml) {
            $html = EmailTemplateRenderer::renderHtml(
                $this->emailTemplateHtml,
                $this->templateData,
                $this->emailMessage
            );

            return new Content(
                htmlString: $html,
            );
        }

        return new Content(
            markdown: 'emails.certificate',
            with: [
                'name' => $this->participantName,
                'event' => $this->eventName,
                'logo' => $this->logo,
                'emailMessage' => $this->emailMessage,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->pdfContent, $this->filename)
                ->withMime('application/pdf'),
        ];
    }
}
