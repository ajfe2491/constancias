<?php

namespace App\Support;

use Illuminate\Support\Str;

class EmailTemplateRenderer
{
    public static function renderSubject(?string $subject, array $data): string
    {
        $subject = $subject ?: EmailTemplateDefaults::defaultSubject();
        return self::replacePlaceholders($subject, $data);
    }

    public static function renderHtml(
        string $templateHtml,
        array $data,
        ?string $emailMessage
    ): string {
        $emailMessageHtml = $emailMessage ? nl2br(e($emailMessage)) : '';
        $footerNote = 'Este correo fue enviado automáticamente. Por favor no responda a este mensaje, ya que esta bandeja no es monitoreada.';

        $html = self::replacePlaceholders($templateHtml, $data);
        $html = self::injectToken($html, '{{email_message}}', $emailMessageHtml);
        $html = self::injectToken($html, '{{footer_note}}', e($footerNote));

        return $html;
    }

    private static function replacePlaceholders(string $content, array $data): string
    {
        $map = [
            '{{participant_name}}' => e($data['participant_name'] ?? ''),
            '{{event_name}}' => e($data['event_name'] ?? ''),
            '{{event_logo_url}}' => e($data['event_logo_url'] ?? ''),
            '{{event_type}}' => e($data['event_type'] ?? ''),
            '{{event_key}}' => e($data['event_key'] ?? ''),
            '{{event_dates}}' => e($data['event_dates'] ?? ''),
            '{{event_description}}' => e($data['event_description'] ?? ''),
            '{{document_name}}' => e($data['document_name'] ?? ''),
            '{{document_type}}' => e($data['document_type'] ?? ''),
            '{{document_description}}' => e($data['document_description'] ?? ''),
            '{{recipient_email}}' => e($data['recipient_email'] ?? ''),
            '{{folio}}' => e($data['folio'] ?? ''),
            '{{uuid}}' => e($data['uuid'] ?? ''),
            '{{verification_url}}' => e($data['verification_url'] ?? ''),
        ];

        foreach ($data as $key => $value) {
            if ($key === 'email_message' || $key === 'footer_note') {
                continue;
            }
            if (!is_scalar($value) && $value !== null) {
                continue;
            }
            $token = '{{' . $key . '}}';
            if (!array_key_exists($token, $map)) {
                $map[$token] = e((string) $value);
            }
        }

        return str_replace(array_keys($map), array_values($map), $content);
    }

    private static function injectToken(string $content, string $token, string $value): string
    {
        if (Str::contains($content, $token)) {
            return str_replace($token, $value, $content);
        }

        if ($value === '') {
            return $content;
        }

        $injection = '<div style="margin-top:16px;">' . $value . '</div>';

        if (Str::contains($content, '</body>')) {
            return str_replace('</body>', $injection . '</body>', $content);
        }

        if (Str::contains($content, '</html>')) {
            return str_replace('</html>', $injection . '</html>', $content);
        }

        return $content . $injection;
    }
}
