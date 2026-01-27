<?php

namespace App\Support;

class EmailTemplateDefaults
{
    public static function defaultSubject(): string
    {
        return '{{document_name}} - {{event_name}}';
    }

    public static function defaultMjml(): string
    {
        return <<<'MJML'
<mjml>
  <mj-head>
    <mj-title>{{document_name}} - {{event_name}}</mj-title>
    <mj-preview>Constancia emitida</mj-preview>
    <mj-attributes>
      <mj-text font-family="Inter, Arial, sans-serif" color="#111827" />
      <mj-button background-color="#2563eb" color="#ffffff" border-radius="8px" />
    </mj-attributes>
  </mj-head>
  <mj-body background-color="#eef2f7">
    <mj-section padding="16px 16px 0">
      <mj-column>
        <mj-section background-color="#ffffff" border-radius="16px" padding="14px 18px">
          <mj-column width="28%">
            <mj-image src="{{event_logo_url}}" width="84px" alt="Logo del evento" padding="0" />
          </mj-column>
          <mj-column width="72%">
            <mj-text font-size="22px" font-weight="900" color="#7c3aed" letter-spacing="-1px">
              SIICE
            </mj-text>
            <mj-divider border-color="#e2e8f0" border-width="2px" padding="6px 0" />
            <mj-text font-size="9px" color="#94a3b8" letter-spacing="2px" line-height="14px">
              SISTEMA INTEGRAL DE IDENTIFICACION<br />
              Y CERTIFICACION
            </mj-text>
          </mj-column>
        </mj-section>
      </mj-column>
    </mj-section>

    <mj-section padding="0 16px">
      <mj-column>
        <mj-section background-color="#0f172a" border-radius="16px" padding="22px">
          <mj-column>
            <mj-text font-size="12px" color="#cbd5f5" letter-spacing="1px">
              DOCUMENTO DIGITAL
            </mj-text>
            <mj-text font-size="26px" font-weight="bold" color="#ffffff">
              {{document_name}}
            </mj-text>
            <mj-text font-size="14px" color="#e2e8f0">
              Evento: {{event_name}}
            </mj-text>
          </mj-column>
        </mj-section>
      </mj-column>
    </mj-section>

    <mj-section padding="0 16px">
      <mj-column>
        <mj-section background-color="#ffffff" border-radius="16px" padding="22px">
          <mj-column>
            <mj-text font-size="16px" color="#0f172a" font-weight="bold">
              Hola {{participant_name}}
            </mj-text>
            <mj-text font-size="14px" color="#334155">
              Adjuntamos tu documento en PDF. Guarda este correo como respaldo y usa el boton de verificacion cuando lo necesites.
            </mj-text>
            <mj-text font-size="14px" color="#334155">
              {{email_message}}
            </mj-text>
          </mj-column>
        </mj-section>
      </mj-column>
    </mj-section>

    <mj-section padding="0 16px">
      <mj-column>
        <mj-section background-color="#ffffff" border-radius="16px" padding="18px 22px">
          <mj-column width="50%">
            <mj-text font-size="12px" color="#64748b">
              <strong>Tipo de evento:</strong> {{event_type}}
            </mj-text>
            <mj-text font-size="12px" color="#64748b">
              <strong>Clave:</strong> {{event_key}}
            </mj-text>
            <mj-text font-size="12px" color="#64748b">
              <strong>Fechas:</strong> {{event_dates}}
            </mj-text>
          </mj-column>
          <mj-column width="50%">
            <mj-text font-size="12px" color="#64748b">
              <strong>Documento:</strong> {{document_name}}
            </mj-text>
            <mj-text font-size="12px" color="#64748b">
              <strong>Tipo:</strong> {{document_type}}
            </mj-text>
            <mj-text font-size="12px" color="#64748b">
              <strong>Folio:</strong> {{folio}}
            </mj-text>
          </mj-column>
          <mj-column>
            <mj-text font-size="12px" color="#64748b">
              {{event_description}}
            </mj-text>
            <mj-text font-size="12px" color="#64748b">
              {{document_description}}
            </mj-text>
            <mj-button href="{{verification_url}}">
              Verificar constancia
            </mj-button>
          </mj-column>
        </mj-section>
      </mj-column>
    </mj-section>

    <mj-section padding="0 16px 20px">
      <mj-column>
        <mj-section background-color="#f8fafc" border-radius="16px" padding="14px 20px">
          <mj-column>
            <mj-text font-size="11px" color="#64748b">
              {{footer_note}}
            </mj-text>
          </mj-column>
        </mj-section>
      </mj-column>
    </mj-section>
  </mj-body>
</mjml>
MJML;
    }

    public static function defaultMjmlWithData(array $data): string
    {
        $mjml = self::defaultMjml();

        $replacements = [
            '{{participant_name}}' => $data['participant_name'] ?? 'Participante',
            '{{event_name}}' => $data['event_name'] ?? 'Evento',
            '{{event_type}}' => $data['event_type'] ?? '',
            '{{event_key}}' => $data['event_key'] ?? '',
            '{{event_dates}}' => $data['event_dates'] ?? '',
            '{{event_description}}' => $data['event_description'] ?? '',
            '{{event_logo_url}}' => $data['event_logo_url'] ?? '',
            '{{document_name}}' => $data['document_name'] ?? 'Constancia',
            '{{document_type}}' => $data['document_type'] ?? '',
            '{{document_description}}' => $data['document_description'] ?? '',
            '{{recipient_email}}' => $data['recipient_email'] ?? '',
            '{{folio}}' => $data['folio'] ?? '',
            '{{uuid}}' => $data['uuid'] ?? '',
            '{{verification_url}}' => $data['verification_url'] ?? '',
            // Keep email_message as token so it is always injected later.
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $mjml);
    }
}
