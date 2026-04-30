<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class SystemSettingService
{
    protected $filePath = 'settings.json';

    protected $defaultEventTypes = [
        'Congreso',
        'Curso',
        'Taller',
        'Seminario',
        'Simposio',
        'Conferencia',
        'Diplomado',
        'Foro',
        'Panel',
        'Otro',
    ];

    protected $defaultDocumentTypes = [
        'constancia' => 'Constancia',
        'gafete' => 'Gafete',
        'carta' => 'Carta',
    ];

    public function getEventTypes(): array
    {
        $settings = $this->getSettings();
        return $settings['event_types'] ?? $this->defaultEventTypes;
    }

    public function getDocumentTypes(): array
    {
        $settings = $this->getSettings();
        return $settings['document_types'] ?? $this->defaultDocumentTypes;
    }

    public function addEventType(string $type): void
    {
        $settings = $this->getSettings();
        $types = $settings['event_types'] ?? $this->defaultEventTypes;

        if (!in_array($type, $types)) {
            $types[] = $type;
            sort($types);
            $settings['event_types'] = $types;
            $this->saveSettings($settings);
        }
    }

    public function removeEventType(string $type): void
    {
        $settings = $this->getSettings();
        $types = $settings['event_types'] ?? $this->defaultEventTypes;

        $settings['event_types'] = array_values(array_filter($types, fn($t) => $t !== $type));
        $this->saveSettings($settings);
    }

    public function addDocumentType(string $key, string $label): void
    {
        $settings = $this->getSettings();
        $types = $settings['document_types'] ?? $this->defaultDocumentTypes;

        if (!array_key_exists($key, $types)) {
            $types[$key] = $label;
            $settings['document_types'] = $types;
            $this->saveSettings($settings);
        }
    }

    public function removeDocumentType(string $key): void
    {
        $settings = $this->getSettings();
        $types = $settings['document_types'] ?? $this->defaultDocumentTypes;

        if (array_key_exists($key, $types)) {
            unset($types[$key]);
            $settings['document_types'] = $types;
            $this->saveSettings($settings);
        }
    }

    protected function getSettings(): array
    {
        if (Storage::exists($this->filePath)) {
            return json_decode(Storage::get($this->filePath), true) ?? [];
        }
        return [
            'event_types' => $this->defaultEventTypes,
            'document_types' => $this->defaultDocumentTypes,
        ];
    }

    protected function saveSettings(array $settings): void
    {
        Storage::put($this->filePath, json_encode($settings, JSON_PRETTY_PRINT));
    }
}
