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

                // Aplicar mayúsculas si está configurado
                if (!empty($element['uppercase'])) {
                    $text = mb_strtoupper($text, 'UTF-8');
                }

                // Configurar fuente
                $fontFamily = $element['font_family'] ?? $this->default_font_family ?? 'Arial';
                $fontStyle = $element['font_style'] ?? $this->default_font_style ?? '';
                $fontSize = $element['font_size'] ?? $this->default_font_size ?? 12;

                $pdf->SetFont($fontFamily, $fontStyle, $fontSize);

                // Colores
                $textColorHex = $element['text_color'] ?? $this->default_text_color ?? '#000000';
                $textColor = $this->hexToRgb($textColorHex);
                $pdf->SetTextColor($textColor['r'], $textColor['g'], $textColor['b']);

                $fillColorHex = $element['fill_color'] ?? null;
                if (!empty($fillColorHex)) {
                    $fillColor = $this->hexToRgb($fillColorHex);
                    $pdf->SetFillColor($fillColor['r'], $fillColor['g'], $fillColor['b']);
                }

                // Auto Width Logic
                if (isset($element['auto_width_percent']) && floatval($element['auto_width_percent']) > 0) {
                    $widthPercent = floatval($element['auto_width_percent']);
                    if ($widthPercent > 100)
                        $widthPercent = 100;

                    $pageWidth = $pdf->GetPageWidth();
                    $textBoxWidth = round($pageWidth * ($widthPercent / 100), 2);
                    $sideMargin = round(($pageWidth - $textBoxWidth) / 2, 2);

                    $element['width'] = $textBoxWidth;
                    $element['x'] = $sideMargin;
                    $element['alignment'] = 'C';
                    $element['multicell'] = true;
                }

                $x = $element['x'] ?? 10;
                $y = $element['y'] ?? 10;
                $w = $element['width'] ?? 50;
                $h = $element['height'] ?? 10;
                $align = $element['alignment'] ?? 'L';
                $fill = !empty($element['fill']);
                $multicell = !empty($element['multicell']);

                $pdf->SetXY($x, $y);

                // Verificar si el texto contiene marcadores de formato (*, %, &)
                $hasFormatMarkers = (strpos($text, '*') !== false || strpos($text, '%') !== false || strpos($text, '&') !== false);

                if ($hasFormatMarkers) {
                    $this->drawFormattedText($pdf, $text, $element, $fontFamily, $fontSize, $textColorHex, $fillColorHex, $fill, $multicell);
                } else {
                    if ($multicell) {
                        $pdf->MultiCell($w, $h, iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $text), 0, $align, $fill);
                    } else {
                        $pdf->Cell($w, $h, iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $text), 0, 0, $align, $fill);
                    }
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

    /**
     * Dibujar texto con formato mixto (negrita, cursiva, mayúsculas)
     * Marcadores: *texto* = negrita, %texto% = cursiva, &texto& = mayúsculas
     */
    private function drawFormattedText($pdf, $text, $element, $baseFontFamily, $baseFontSize, $textColor, $fillColor, $fill, $useMultiCell = false)
    {
        // Parsear el texto y dividirlo en segmentos con formato
        $segments = $this->parseFormattedText($text);

        // Guardar posición inicial
        $startX = $pdf->GetX();
        $startY = $pdf->GetY();
        $currentX = $startX;
        $currentY = $startY;

        // Aplicar color de texto base
        if ($textColor && strlen($textColor) >= 7) {
            $r = hexdec(substr($textColor, 1, 2));
            $g = hexdec(substr($textColor, 3, 2));
            $b = hexdec(substr($textColor, 5, 2));
            $pdf->SetTextColor($r, $g, $b);
        } else {
            $pdf->SetTextColor(0, 0, 0);
        }

        // Aplicar color de relleno si es necesario
        if ($fill && $fillColor && strlen($fillColor) >= 7) {
            $r = hexdec(substr($fillColor, 1, 2));
            $g = hexdec(substr($fillColor, 3, 2));
            $b = hexdec(substr($fillColor, 5, 2));
            $pdf->SetFillColor($r, $g, $b);
        } else {
            $pdf->SetFillColor(255, 255, 255);
        }

        if ($useMultiCell) {
            // Modo MultiCell: dividir en líneas respetando el ancho y manteniendo formato
            $lines = $this->wrapFormattedText($pdf, $segments, $element['width'], $baseFontFamily, $baseFontSize);

            $alignment = isset($element['alignment']) ? $element['alignment'] : 'L';

            // Altura de línea configurada o calculada
            $defaultLineHeight = ($element['height'] ?? 0) > 0 ? $element['height'] : 0;
            $fontBasedHeight = round($baseFontSize * 0.45, 2); // aproximación en mm (12pt ≈ 5.4mm)
            if ($defaultLineHeight <= 0 || $defaultLineHeight < $fontBasedHeight) {
                $defaultLineHeight = $fontBasedHeight;
            }
            if ($defaultLineHeight <= 0) {
                $defaultLineHeight = max(5, round($baseFontSize * 0.45, 2));
            }

            if (isset($element['line_height']) && $element['line_height'] > 0) {
                $lineHeight = max($element['line_height'], $defaultLineHeight);
            } else {
                $lineHeight = $defaultLineHeight;
            }

            // Altura máxima disponible por caja (0 = sin límite)
            $boxHeightLimit = isset($element['max_height']) ? $element['max_height'] : 0;
            if ($boxHeightLimit > 0 && $boxHeightLimit < $lineHeight) {
                $boxHeightLimit = $lineHeight;
            }

            // Espaciado entre cajas adicionales
            $boxSpacing = isset($element['box_spacing']) ? $element['box_spacing'] : ($lineHeight * 0.25);

            $totalLines = count($lines);
            $maxLinesPerBox = $totalLines;
            if ($boxHeightLimit > 0) {
                $maxLinesPerBox = max(1, (int) floor($boxHeightLimit / $lineHeight));
            }

            $lineGroups = [];
            if ($maxLinesPerBox >= $totalLines) {
                $lineGroups[] = $lines;
            } else {
                for ($i = 0; $i < $totalLines; $i += $maxLinesPerBox) {
                    $lineGroups[] = array_slice($lines, $i, $maxLinesPerBox);
                }
            }

            $currentY = $startY;

            foreach ($lineGroups as $groupIndex => $groupLines) {
                if ($groupIndex > 0) {
                    $currentY += $boxSpacing;
                }

                foreach ($groupLines as $lineIndex => $line) {
                    $lineSegments = $line['segments'];
                    $lineWidth = $line['width'];

                    // Calcular posición X según alineación
                    if ($alignment === 'C') {
                        $currentX = $startX + ($element['width'] - $lineWidth) / 2;
                    } elseif ($alignment === 'R') {
                        $currentX = $startX + $element['width'] - $lineWidth;
                    } else {
                        $currentX = $startX;
                    }

                    $lineY = $currentY + ($lineIndex * $lineHeight);
                    $pdf->SetXY($currentX, $lineY);

                    foreach ($lineSegments as $segment) {
                        $fontStyle = $segment['style'];
                        $segmentText = $segment['text'];

                        if (function_exists('iconv')) {
                            $segmentText = iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $segmentText);
                        } else {
                            $segmentText = utf8_decode($segmentText);
                        }

                        $pdf->SetFont($baseFontFamily, $fontStyle, $baseFontSize);

                        $segmentWidth = $pdf->GetStringWidth($segmentText);

                        $currentPdfY = $pdf->GetY();
                        if (abs($currentPdfY - $lineY) > 0.01) {
                            $pdf->SetY($lineY);
                        }

                        $pdf->Cell($segmentWidth, $lineHeight, $segmentText, 0, 0, 'L', $fill);

                        $currentX += $segmentWidth;
                        $pdf->SetXY($currentX, $lineY);
                    }
                }

                $currentY += count($groupLines) * $lineHeight;
            }

            $pdf->SetXY($startX, $currentY);
        } else {
            // Modo Cell: una sola línea
            // Calcular ancho total del texto para alineación
            $totalWidth = 0;
            foreach ($segments as $segment) {
                $fontStyle = $segment['style'];
                $segmentText = $segment['text'];

                // Convertir a windows-1252
                if (function_exists('iconv')) {
                    $segmentText = iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $segmentText);
                } else {
                    $segmentText = utf8_decode($segmentText);
                }

                $pdf->SetFont($baseFontFamily, $fontStyle, $baseFontSize);
                $totalWidth += $pdf->GetStringWidth($segmentText);
            }

            // Ajustar posición inicial según alineación
            $alignment = isset($element['alignment']) ? $element['alignment'] : 'L';
            if ($alignment === 'C') {
                $currentX = $startX + ($element['width'] - $totalWidth) / 2;
            } elseif ($alignment === 'R') {
                $currentX = $startX + $element['width'] - $totalWidth;
            } else {
                $currentX = $startX;
            }

            // Dibujar cada segmento
            foreach ($segments as $segment) {
                $fontStyle = $segment['style'];
                $segmentText = $segment['text'];

                // Convertir a windows-1252
                if (function_exists('iconv')) {
                    $segmentText = iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $segmentText);
                } else {
                    $segmentText = utf8_decode($segmentText);
                }

                // Establecer fuente y estilo
                $pdf->SetFont($baseFontFamily, $fontStyle, $baseFontSize);

                // Calcular ancho del segmento
                $segmentWidth = $pdf->GetStringWidth($segmentText);

                // Dibujar el segmento
                $pdf->SetXY($currentX, $currentY);
                $pdf->Cell($segmentWidth, $element['height'], $segmentText, 0, 0, 'L', $fill);

                // Avanzar posición X
                $currentX += $segmentWidth;
            }

            // Mover a la siguiente línea después de todo el texto
            $pdf->SetXY($startX, $currentY + $element['height']);
        }
    }

    /**
     * Dividir texto formateado en líneas respetando el ancho máximo
     */
    private function wrapFormattedText($pdf, $segments, $maxWidth, $baseFontFamily, $baseFontSize)
    {
        $lines = [];
        $currentLine = [];
        $currentLineWidth = 0;

        foreach ($segments as $segment) {
            $fontStyle = $segment['style'];
            $segmentText = $segment['text'];

            // Manejar saltos de línea explícitos
            if (strpos($segmentText, "\n") !== false) {
                $parts = explode("\n", $segmentText);
                foreach ($parts as $index => $part) {
                    if ($index > 0) {
                        // Guardar línea actual y empezar nueva
                        if (!empty($currentLine)) {
                            $lines[] = [
                                'segments' => $currentLine,
                                'width' => $currentLineWidth
                            ];
                        }
                        $currentLine = [];
                        $currentLineWidth = 0;
                    }

                    if (!empty($part)) {
                        // Procesar la parte del texto
                        $this->addTextToLine($pdf, $part, $fontStyle, $maxWidth, $baseFontFamily, $baseFontSize, $currentLine, $currentLineWidth, $lines);
                    }
                }
            } else {
                // Procesar segmento sin saltos de línea
                $this->addTextToLine($pdf, $segmentText, $fontStyle, $maxWidth, $baseFontFamily, $baseFontSize, $currentLine, $currentLineWidth, $lines);
            }
        }

        // Agregar la última línea si no está vacía
        if (!empty($currentLine)) {
            $lines[] = [
                'segments' => $currentLine,
                'width' => $currentLineWidth
            ];
        }

        return $lines;
    }

    /**
     * Agregar texto a la línea actual, dividiendo en palabras si es necesario
     */
    private function addTextToLine($pdf, $text, $fontStyle, $maxWidth, $baseFontFamily, $baseFontSize, &$currentLine, &$currentLineWidth, &$lines)
    {
        // Convertir a windows-1252 para cálculos
        if (function_exists('iconv')) {
            $textConverted = iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $text);
        } else {
            $textConverted = utf8_decode($text);
        }

        $pdf->SetFont($baseFontFamily, $fontStyle, $baseFontSize);

        // Dividir el texto en palabras (incluyendo espacios)
        $words = preg_split('/(\s+)/u', $text, -1, PREG_SPLIT_DELIM_CAPTURE);

        foreach ($words as $word) {
            if (empty($word)) {
                continue;
            }

            // Convertir palabra para cálculos
            if (function_exists('iconv')) {
                $wordConverted = iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $word);
            } else {
                $wordConverted = utf8_decode($word);
            }

            $wordWidth = $pdf->GetStringWidth($wordConverted);

            // Si la palabra sola es más ancha que el máximo, hay que cortarla (caso extremo)
            if ($wordWidth > $maxWidth && !empty(trim($word))) {
                // Palabra muy larga, cortarla carácter por carácter
                $chars = preg_split('//u', $word, -1, PREG_SPLIT_NO_EMPTY);
                foreach ($chars as $char) {
                    if (function_exists('iconv')) {
                        $charConverted = iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $char);
                    } else {
                        $charConverted = utf8_decode($char);
                    }
                    $charWidth = $pdf->GetStringWidth($charConverted);

                    if ($currentLineWidth + $charWidth <= $maxWidth || empty($currentLine)) {
                        $currentLine[] = [
                            'text' => $char,
                            'style' => $fontStyle
                        ];
                        $currentLineWidth += $charWidth;
                    } else {
                        // Nueva línea
                        if (!empty($currentLine)) {
                            $lines[] = [
                                'segments' => $currentLine,
                                'width' => $currentLineWidth
                            ];
                        }
                        $currentLine = [
                            [
                                'text' => $char,
                                'style' => $fontStyle
                            ]
                        ];
                        $currentLineWidth = $charWidth;
                    }
                }
            } else {
                // Palabra normal
                if ($currentLineWidth + $wordWidth <= $maxWidth || empty($currentLine)) {
                    // Cabe en la línea actual
                    $currentLine[] = [
                        'text' => $word,
                        'style' => $fontStyle
                    ];
                    $currentLineWidth += $wordWidth;
                } else {
                    // No cabe, empezar nueva línea
                    if (!empty($currentLine)) {
                        $lines[] = [
                            'segments' => $currentLine,
                            'width' => $currentLineWidth
                        ];
                    }
                    $currentLine = [
                        [
                            'text' => $word,
                            'style' => $fontStyle
                        ]
                    ];
                    $currentLineWidth = $wordWidth;
                }
            }
        }
    }

    /**
     * Parsear texto con marcadores de formato y dividirlo en segmentos (Recursivo)
     * Marcadores: *texto* = negrita, %texto% = cursiva, &texto& = mayúsculas
     */
    private function parseFormattedText($text, $inheritedStyle = '')
    {
        $segments = [];
        $currentPos = 0;
        $textLength = mb_strlen($text, 'UTF-8');

        while ($currentPos < $textLength) {
            // Buscar el siguiente marcador usando funciones multibyte
            $nextAsterisk = mb_strpos($text, '*', $currentPos, 'UTF-8');
            $nextPercent = mb_strpos($text, '%', $currentPos, 'UTF-8');
            $nextAmpersand = mb_strpos($text, '&', $currentPos, 'UTF-8');

            // Encontrar el marcador más cercano
            $markers = [];
            if ($nextAsterisk !== false)
                $markers['*'] = $nextAsterisk;
            if ($nextPercent !== false)
                $markers['%'] = $nextPercent;
            if ($nextAmpersand !== false)
                $markers['&'] = $nextAmpersand;

            if (empty($markers)) {
                // No hay más marcadores, agregar el resto del texto
                $remainingText = mb_substr($text, $currentPos, null, 'UTF-8');
                if (!empty($remainingText)) {
                    $segments[] = [
                        'text' => $remainingText,
                        'style' => $inheritedStyle
                    ];
                }
                break;
            }

            $nextMarkerPos = min($markers);
            $markerChar = array_search($nextMarkerPos, $markers);

            // Agregar texto antes del marcador si existe
            if ($nextMarkerPos > $currentPos) {
                $beforeText = mb_substr($text, $currentPos, $nextMarkerPos - $currentPos, 'UTF-8');
                if (!empty($beforeText)) {
                    $segments[] = [
                        'text' => $beforeText,
                        'style' => $inheritedStyle
                    ];
                }
            }

            // Buscar el marcador de cierre
            $closeMarkerPos = mb_strpos($text, $markerChar, $nextMarkerPos + 1, 'UTF-8');

            if ($closeMarkerPos === false) {
                // No hay marcador de cierre, agregar el resto como texto normal
                $remainingText = mb_substr($text, $nextMarkerPos, null, 'UTF-8');
                if (!empty($remainingText)) {
                    $segments[] = [
                        'text' => $remainingText,
                        'style' => $inheritedStyle
                    ];
                }
                break;
            }

            // Extraer el texto entre marcadores
            $innerContent = mb_substr($text, $nextMarkerPos + 1, $closeMarkerPos - $nextMarkerPos - 1, 'UTF-8');

            // Determinar nuevo estilo y transformación
            $childStyle = $inheritedStyle;
            $isUppercase = false;

            if ($markerChar === '*') {
                if (strpos($childStyle, 'B') === false)
                    $childStyle .= 'B';
            } elseif ($markerChar === '%') {
                if (strpos($childStyle, 'I') === false)
                    $childStyle .= 'I';
            } elseif ($markerChar === '&') {
                $isUppercase = true;
            }

            // Llamada recursiva para procesar contenido anidado
            $innerSegments = $this->parseFormattedText($innerContent, $childStyle);

            // Aplicar transformación de mayúsculas si es necesario
            if ($isUppercase) {
                foreach ($innerSegments as &$seg) {
                    $seg['text'] = mb_strtoupper($seg['text'], 'UTF-8');
                }
            }

            // Fusionar segmentos
            $segments = array_merge($segments, $innerSegments);

            // Continuar después del marcador de cierre
            $currentPos = $closeMarkerPos + 1;
        }

        return $segments;
    }
}
