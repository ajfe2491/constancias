<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentConfiguration extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'document_type',
        'document_name',
        'description',
        'is_active',
        'page_orientation',
        'page_size',
        'page_unit',
        'background_image',
        'background_x',
        'background_y',
        'background_width',
        'background_height',
        'show_qr',
        'qr_x',
        'qr_y',
        'qr_width',
        'qr_height',
        'text_elements',
        'default_font_family',
        'default_font_size',
        'default_font_style',
        'default_text_color',
        'default_fill_color',
        'margin_top',
        'margin_bottom',
        'margin_left',
        'margin_right',
        'enable_live_preview',
        'preview_delay',
        'sample_data',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    protected $casts = [
        'is_active' => 'boolean',
        'show_qr' => 'boolean',
        'enable_live_preview' => 'boolean',
        'text_elements' => 'array',
        'sample_data' => 'array',
        'background_x' => 'decimal:2',
        'background_y' => 'decimal:2',
        'background_width' => 'decimal:2',
        'background_height' => 'decimal:2',
        'qr_x' => 'decimal:2',
        'qr_y' => 'decimal:2',
        'qr_width' => 'decimal:2',
        'qr_height' => 'decimal:2',
        'margin_top' => 'decimal:2',
        'margin_bottom' => 'decimal:2',
        'margin_left' => 'decimal:2',
        'margin_right' => 'decimal:2',
    ];

    /**
     * Generar PDF usando la configuración
     */
    public function generatePDF($data = [])
    {
        // Datos del sistema (Hardcoded por ahora, idealmente vendrían de una configuración)
        $systemData = [
            'congress_name' => 'Congreso de Constancias',
            'congress_year' => date('Y'),
            'congress_location' => 'Ciudad de México',
        ];
        $data = array_merge($systemData, $data);

        $unit = $this->page_unit ?: 'mm';
        if (!in_array($unit, ['mm', 'pt', 'in'])) {
            $unit = 'mm';
        }
        $orientation = $this->page_orientation ?: 'P';
        $size = $this->page_size ?: 'Letter';

        $pdf = new \FPDF($orientation, $unit, $size);

        // Márgenes
        $left = is_numeric($this->margin_left) ? floatval($this->margin_left) : 0;
        $top = is_numeric($this->margin_top) ? floatval($this->margin_top) : 0;
        $right = is_numeric($this->margin_right) ? floatval($this->margin_right) : 0;
        $bottom = is_numeric($this->margin_bottom) ? floatval($this->margin_bottom) : 0;

        $pdf->SetMargins($left, $top, $right);
        $pdf->SetAutoPageBreak(true, $bottom);

        $pdf->AddPage();
        $pdf->SetTextColor(0, 0, 0);

        // Imagen de Fondo
        if ($this->background_image && floatval($this->background_width) > 0 && floatval($this->background_height) > 0) {
            $imagePath = $this->resolvePublicOrStoragePath($this->background_image);
            if ($imagePath && file_exists($imagePath)) {
                $pdf->Image($imagePath, $this->background_x, $this->background_y, $this->background_width, $this->background_height);
            }
        }

        // QR (Placeholder)
        if ($this->show_qr && isset($data['qr_path']) && file_exists($data['qr_path'])) {
            $pdf->Image($data['qr_path'], $this->qr_x, $this->qr_y, $this->qr_width, $this->qr_height);
        }

        // Elementos de Texto
        if ($this->text_elements) {
            foreach ($this->text_elements as $element) {
                $text = $this->replacePlaceholders($element['text'], $data);

                // Convertir saltos de línea
                $text = str_replace(['\\n', '\\r'], ["\n", "\r"], $text);

                // Configurar fuente
                $fontFamily = $element['font_family'] ?? $this->default_font_family ?? 'Arial';
                $fontStyle = $element['font_style'] ?? $this->default_font_style ?? '';
                $fontSize = $element['font_size'] ?? $this->default_font_size ?? 12;

                $pdf->SetFont($fontFamily, $fontStyle, $fontSize);

                // Colores
                $textColor = $this->hexToRgb($element['text_color'] ?? $this->default_text_color ?? '#000000');
                $pdf->SetTextColor($textColor['r'], $textColor['g'], $textColor['b']);

                if (!empty($element['fill_color'])) {
                    $fillColor = $this->hexToRgb($element['fill_color']);
                    $pdf->SetFillColor($fillColor['r'], $fillColor['g'], $fillColor['b']);
                }

                $x = $element['x'] ?? 10;
                $y = $element['y'] ?? 10;
                $w = $element['width'] ?? 50;
                $h = $element['height'] ?? 10;
                $align = $element['alignment'] ?? 'L';
                $fill = !empty($element['fill']);
                $multicell = !empty($element['multicell']);

                $pdf->SetXY($x, $y);

                if ($multicell) {
                    $pdf->MultiCell($w, $h, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $text), 0, $align, $fill);
                } else {
                    $pdf->Cell($w, $h, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $text), 0, 0, $align, $fill);
                }
            }
        }

        return $pdf;
    }

    protected function resolvePublicOrStoragePath($path)
    {
        // Si es ruta absoluta
        if (file_exists($path))
            return $path;

        // Si está en storage/app/public
        $storagePath = storage_path('app/public/' . str_replace('storage/', '', $path));
        if (file_exists($storagePath))
            return $storagePath;

        // Si está en public
        $publicPath = public_path($path);
        if (file_exists($publicPath))
            return $publicPath;

        return null;
    }

    protected function replacePlaceholders($text, $data)
    {
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $text = str_replace('{' . $key . '}', $value, $text);
            }
        }
        return $text;
    }

    protected function hexToRgb($hex)
    {
        $hex = str_replace('#', '', $hex);
        if (strlen($hex) == 3) {
            $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }
        return ['r' => $r, 'g' => $g, 'b' => $b];
    }
}
