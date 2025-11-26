<?php

namespace Database\Seeders;

use App\Models\DocumentConfiguration;
use Illuminate\Database\Seeder;

class DefaultDocumentConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verificar si ya existe una configuración por defecto
        $existing = DocumentConfiguration::whereNull('event_id')
            ->where('document_name', 'Constancia General')
            ->first();

        if ($existing) {
            $this->command->info('La configuración por defecto ya existe. Actualizando...');
            $config = $existing;
        } else {
            $this->command->info('Creando configuración por defecto...');
            $config = new DocumentConfiguration();
        }

        // Aplicar configuración de constancia_general del proyecto multidisciplinario
        $config->fill([
            'document_name' => 'Constancia General',
            'document_type' => 'constancia_general',
            'description' => 'Constancia general de participación en el congreso',
            'is_active' => true,
            'page_orientation' => 'L',
            'page_size' => 'Letter',
            'page_unit' => 'mm',
            'background_image' => 'backgrounds/constancia2.jpg',
            'background_x' => 1,
            'background_y' => 1,
            'background_width' => 277.5,
            'background_height' => 214,
            'show_qr' => true,
            'qr_x' => 210,
            'qr_y' => 158,
            'qr_width' => 30,
            'qr_height' => 30,
            'text_elements' => [
                [
                    'name' => 'nombre_participante',
                    'text' => '{nombre_participante}',
                    'x' => 105,
                    'y' => 95,
                    'width' => 80,
                    'height' => 13,
                    'font_family' => 'Arial',
                    'font_size' => 20,
                    'font_style' => 'B',
                    'alignment' => 'C',
                    'fill' => false
                ],
                [
                    'name' => 'texto_participante',
                    'text' => '{texto_participante}',
                    'x' => 41.6,
                    'y' => 110,
                    'width' => 194.3,
                    'height' => 45,
                    'font_family' => 'Arial',
                    'font_size' => 12,
                    'font_style' => '',
                    'alignment' => 'C',
                    'fill' => false,
                    'multicell' => true
                ]
            ],
            'sample_data' => [
                'nombre_participante' => 'Juan Carlos Pérez García',
                'texto_participante' => 'por su destacada participación en el VII CONGRESO MULTIDISCIPLINARIO realizado los días 24 y 25 de octubre de 2024'
            ],
            'default_font_family' => 'Arial',
            'default_font_size' => 12,
            'default_font_style' => '',
            'default_text_color' => '#000000'
        ]);

        $config->save();

        $this->command->info('✓ Configuración por defecto aplicada exitosamente.');
        $this->command->info('  ID: ' . $config->id);
        $this->command->info('  Nombre: ' . $config->document_name);
    }
}
