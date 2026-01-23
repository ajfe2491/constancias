<x-guest-wide-layout>
    <div class="space-y-8 max-w-6xl mx-auto mt-6 pb-6">
        <div class="verify-row">
            <div class="verify-col-main border border-base-300 bg-base-100 rounded-xl p-4 min-w-0">
                <div class="text-xs uppercase tracking-wider opacity-60 mb-2">Vista previa</div>
                <img src="{{ route('certificates.preview', $certificate->uuid) }}?format=png"
                    alt="Vista previa de la constancia"
                    class="w-full h-auto rounded border border-base-200" />
                    <div class="text-xs opacity-60 text-center pt-4">
            ID de verificación: {{ $certificate->uuid }}
        </div>
            </div>

            <div class="verify-col-side bg-base-200 rounded-xl p-5 space-y-6 md:sticky md:top-6 self-start min-w-0">
                <div class="space-y-2">
                    <h2 class="text-lg font-bold tracking-tight">Verificación de constancia</h2>
                    <p class="text-sm opacity-70">Resultado oficial de autenticidad del documento.</p>
                    <div class="badge badge-success badge-outline">Constancia válida</div>
                </div>
                <div class="flex items-center gap-4">
                    <div class="w-20 h-20 bg-base-100 rounded-xl border border-base-300 flex items-center justify-center">
                        @if ($certificate->documentConfiguration?->event?->logo)
                            <img src="{{ Storage::url($certificate->documentConfiguration->event->logo) }}"
                                alt="Logo del evento" class="max-h-16 w-auto object-contain" />
                        @else
                            <span class="text-xs opacity-60">Sin logo</span>
                        @endif
                    </div>
                    <div class="space-y-1">
                        <div class="text-xs uppercase tracking-wider opacity-60">Documento</div>
                        <div class="text-lg font-semibold">
                            {{ $certificate->documentConfiguration?->document_name ?? 'Documento' }}
                        </div>
                        <div class="text-sm opacity-70">
                            {{ $certificate->documentConfiguration?->document_type ?? 'Constancia' }}
                        </div>
                    </div>
                </div>
                @if ($certificate->documentConfiguration?->description)
                    <div class="text-sm opacity-80">
                        {{ $certificate->documentConfiguration->description }}
                    </div>
                @endif

                <div class="grid grid-cols-1 gap-4 text-sm">
                    <div>
                        <p class="opacity-60">Folio</p>
                        <p class="font-semibold">{{ $certificate->folio ?? 'Sin folio' }}</p>
                    </div>
                    <div>
                        <p class="opacity-60">Evento</p>
                        <p class="font-semibold">{{ $certificate->documentConfiguration?->event?->name ?? 'Evento' }}</p>
                    </div>
                    <div>
                        <p class="opacity-60">Fechas del evento</p>
                        <p class="font-semibold">
                            @if ($certificate->documentConfiguration?->event?->start_date)
                                {{ $certificate->documentConfiguration->event->start_date->format('d/m/Y') }}
                                @if ($certificate->documentConfiguration?->event?->end_date)
                                    - {{ $certificate->documentConfiguration->event->end_date->format('d/m/Y') }}
                                @endif
                            @else
                                N/A
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="opacity-60">Fecha de emisión</p>
                        <p class="font-semibold">{{ $certificate->created_at?->format('Y-m-d H:i') }}</p>
                    </div>
                    <div>
                        <p class="opacity-60">Correo</p>
                        <p class="font-semibold">{{ $certificate->recipient_email ?? 'No disponible' }}</p>
                    </div>
                    @if ($certificate->documentConfiguration?->event?->description)
                        <div>
                            <p class="opacity-60">Descripción del evento</p>
                            <p class="font-semibold">{{ $certificate->documentConfiguration->event->description }}</p>
                        </div>
                    @endif
                </div>

                @php
                    $data = collect($certificate->recipient_data ?? [])
                        ->except(['email', 'folio', 'folio_number', 'qr_path'])
                        ->filter(fn($value) => $value !== null && $value !== '');
                @endphp
                @if ($data->isNotEmpty())
                    <div class="border-t border-base-300 pt-4">
                        <div class="text-xs uppercase tracking-wider opacity-60 mb-2">Datos relevantes</div>
                        <div class="grid grid-cols-1 gap-3 text-sm">
                            @foreach ($data as $key => $value)
                                <div>
                                    <p class="opacity-60">{{ str_replace('_', ' ', ucfirst($key)) }}</p>
                                    <p class="font-semibold break-words">{{ $value }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
       
    </div>
    <style>
        .verify-row {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
        }
        .verify-col-main,
        .verify-col-side {
            flex: 0 0 100%;
            max-width: 100%;
        }
        @media (min-width: 992px) {
            .verify-row {
                flex-wrap: nowrap;
            }
            .verify-col-main {
                flex: 0 0 calc(70% - 0.75rem);
                max-width: calc(70% - 0.75rem);
            }
            .verify-col-side {
                flex: 0 0 calc(30% - 0.75rem);
                max-width: calc(30% - 0.75rem);
            }
        }
    </style>
</x-guest-wide-layout>
