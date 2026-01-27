<?php

namespace App\Http\Controllers;

use App\Models\DocumentConfiguration;
use App\Models\Event;
use App\Support\EmailTemplateDefaults;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class EmailTemplateController extends Controller
{
    public function editEvent(Request $request, Event $event)
    {
        $user = $request->user();
        if (!$user || (!$user->isSuperAdmin() && $event->user_id !== $user->id)) {
            abort(403);
        }

        $eventDates = 'N/A';
        if ($event->start_date || $event->end_date) {
            $start = $event->start_date ? $event->start_date->format('d/m/Y') : null;
            $end = $event->end_date ? $event->end_date->format('d/m/Y') : null;
            $eventDates = $start && $end ? $start . ' - ' . $end : ($start ?: $end);
        }

        $logoUrl = $event->logo ? url(Storage::url($event->logo)) : '';
        $sampleData = [
            'participant_name' => 'Participante',
            'event_name' => $event->name,
            'event_logo_url' => $logoUrl,
            'event_type' => $event->type ?? '',
            'event_key' => $event->key ?? '',
            'event_dates' => $eventDates,
            'event_description' => $event->description ?? '',
            'document_name' => 'Constancia',
            'document_type' => '',
            'document_description' => '',
            'recipient_email' => '',
            'folio' => '',
            'uuid' => '',
            'verification_url' => '',
        ];

        return view('email_templates.editor', [
            'title' => 'Plantilla de correo (Evento)',
            'subtitle' => $event->name,
            'backUrl' => route('events.show', $event),
            'saveUrl' => route('events.email-template.update', $event),
            'emailSubject' => $event->email_subject
                ?? \App\Support\EmailTemplateRenderer::renderSubject(null, $sampleData),
            'templateMjml' => $event->email_template_mjml
                ?? EmailTemplateDefaults::defaultMjmlWithData($sampleData),
            'extraTokens' => [],
        ]);
    }

    public function updateEvent(Request $request, Event $event)
    {
        $user = $request->user();
        if (!$user || (!$user->isSuperAdmin() && $event->user_id !== $user->id)) {
            abort(403);
        }

        $validated = $request->validate([
            'email_subject' => ['nullable', 'string', 'max:255'],
            'email_template_html' => ['nullable', 'string'],
            'email_template_mjml' => ['nullable', 'string'],
        ]);

        $event->update([
            'email_subject' => $validated['email_subject'] ?: null,
            'email_template_html' => $validated['email_template_html'] ?: null,
            'email_template_mjml' => $validated['email_template_mjml'] ?: null,
        ]);

        return back()->with('success', 'Plantilla de correo guardada correctamente.');
    }

    public function editDocumentConfiguration(Request $request, DocumentConfiguration $documentConfiguration)
    {
        $user = $request->user();
        if (!$user || (!$user->isSuperAdmin() && $documentConfiguration->user_id !== $user->id)) {
            abort(403);
        }

        $event = $documentConfiguration->event;
        $eventName = $event?->name ?? 'Evento';
        $eventDates = 'N/A';
        if ($event && ($event->start_date || $event->end_date)) {
            $start = $event->start_date ? $event->start_date->format('d/m/Y') : null;
            $end = $event->end_date ? $event->end_date->format('d/m/Y') : null;
            $eventDates = $start && $end ? $start . ' - ' . $end : ($start ?: $end);
        }

        $logoUrl = $event && $event->logo ? url(Storage::url($event->logo)) : '';
        $sampleData = [
            'participant_name' => 'Participante',
            'event_name' => $eventName,
            'event_logo_url' => $logoUrl,
            'event_type' => $event->type ?? '',
            'event_key' => $event->key ?? '',
            'event_dates' => $eventDates,
            'event_description' => $event->description ?? '',
            'document_name' => $documentConfiguration->document_name,
            'document_type' => $documentConfiguration->document_type ?? '',
            'document_description' => $documentConfiguration->description ?? '',
            'recipient_email' => '',
            'folio' => '',
            'uuid' => '',
            'verification_url' => '',
        ];
        $sampleData = $sampleData + $this->extractSampleData($documentConfiguration);
        $extraTokens = $this->extractDynamicTokens($documentConfiguration);

        return view('email_templates.editor', [
            'title' => 'Plantilla de correo (Constancia)',
            'subtitle' => $documentConfiguration->document_name,
            'backUrl' => route('document-configurations.edit', $documentConfiguration),
            'saveUrl' => route('document-configurations.email-template.update', $documentConfiguration),
            'emailSubject' => $documentConfiguration->email_subject
                ?? \App\Support\EmailTemplateRenderer::renderSubject(null, $sampleData),
            'templateMjml' => $documentConfiguration->email_template_mjml
                ?? EmailTemplateDefaults::defaultMjmlWithData($sampleData),
            'extraTokens' => $extraTokens,
        ]);
    }

    public function updateDocumentConfiguration(Request $request, DocumentConfiguration $documentConfiguration)
    {
        $user = $request->user();
        if (!$user || (!$user->isSuperAdmin() && $documentConfiguration->user_id !== $user->id)) {
            abort(403);
        }

        $validated = $request->validate([
            'email_subject' => ['nullable', 'string', 'max:255'],
            'email_template_html' => ['nullable', 'string'],
            'email_template_mjml' => ['nullable', 'string'],
        ]);

        $documentConfiguration->update([
            'email_subject' => $validated['email_subject'] ?: null,
            'email_template_html' => $validated['email_template_html'] ?: null,
            'email_template_mjml' => $validated['email_template_mjml'] ?: null,
        ]);

        return back()->with('success', 'Plantilla de correo guardada correctamente.');
    }

    private function extractSampleData(DocumentConfiguration $documentConfiguration): array
    {
        $sampleData = $documentConfiguration->sample_data ?? [];
        if (is_string($sampleData)) {
            $sampleData = json_decode($sampleData, true) ?? [];
        }

        return is_array($sampleData) ? $sampleData : [];
    }

    private function extractDynamicTokens(DocumentConfiguration $documentConfiguration): array
    {
        $tokens = [];
        $textElements = $documentConfiguration->text_elements ?? [];
        if (is_string($textElements)) {
            $textElements = json_decode($textElements, true) ?? [];
        }

        if (is_array($textElements)) {
            foreach ($textElements as $element) {
                $text = is_array($element) ? ($element['text'] ?? '') : '';
                if ($text && preg_match_all('/\{(\w+)\}/', $text, $matches)) {
                    foreach ($matches[1] as $match) {
                        $tokens[] = $match;
                    }
                }
            }
        }

        $sampleData = $this->extractSampleData($documentConfiguration);
        foreach (array_keys($sampleData) as $key) {
            $tokens[] = $key;
        }

        $known = [
            'participant_name',
            'event_name',
            'event_logo_url',
            'event_type',
            'event_key',
            'event_dates',
            'event_description',
            'document_name',
            'document_type',
            'document_description',
            'recipient_email',
            'folio',
            'uuid',
            'verification_url',
            'email_message',
            'footer_note',
        ];

        $tokens = array_values(array_unique(array_diff($tokens, $known)));
        sort($tokens);

        return $tokens;
    }
}
