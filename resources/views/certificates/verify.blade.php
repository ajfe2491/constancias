<x-guest-layout>
    <div class="space-y-6">
        <header class="text-center space-y-2">
            <h1 class="text-2xl font-bold tracking-tight">Verificación de constancia</h1>
            <p class="text-sm opacity-70">Resultado oficial de autenticidad del documento.</p>
        </header>

        <div class="alert alert-success shadow">
            <span>Constancia válida</span>
        </div>

        <div class="bg-base-200 rounded-xl p-5 space-y-6">
            <div class="flex flex-col sm:flex-row gap-4 items-center sm:items-start">
                <div class="w-24 h-24 bg-base-100 rounded-xl border border-base-300 flex items-center justify-center">
                    @if ($certificate->documentConfiguration?->event?->logo)
                        <img src="{{ Storage::url($certificate->documentConfiguration->event->logo) }}"
                            alt="Logo del evento" class="max-h-20 w-auto object-contain" />
                    @else
                        <span class="text-xs opacity-60">Sin logo</span>
                    @endif
                </div>
                <div class="flex-1 text-center sm:text-left space-y-1">
                    <div class="text-xs uppercase tracking-wider opacity-60">Documento</div>
                    <div class="text-lg font-semibold">
                        {{ $certificate->documentConfiguration?->document_name ?? 'Documento' }}
                    </div>
                    <div class="text-sm opacity-70">
                        {{ $certificate->documentConfiguration?->document_type ?? 'Constancia' }}
                    </div>
                    @if ($certificate->documentConfiguration?->description)
                        <div class="text-sm opacity-80">
                            {{ $certificate->documentConfiguration->description }}
                        </div>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="opacity-60">Folio</p>
                    <p class="font-semibold">{{ $certificate->folio ?? 'Sin folio' }}</p>
                </div>
                <div>
                    <p class="opacity-60">Evento</p>
                    <p class="font-semibold">{{ $certificate->documentConfiguration?->event?->name ?? 'Evento' }}</p>
                </div>
                <div>
                    <p class="opacity-60">Fecha de emisión</p>
                    <p class="font-semibold">{{ $certificate->created_at?->format('Y-m-d H:i') }}</p>
                </div>
                <div>
                    <p class="opacity-60">Participante</p>
                    <p class="font-semibold">{{ $certificate->recipient_name ?? 'No disponible' }}</p>
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
                    ->except(['email'])
                    ->filter(fn($value) => $value !== null && $value !== '');
            @endphp
            @if ($data->isNotEmpty())
                <div class="border-t border-base-300 pt-4">
                    <div class="text-xs uppercase tracking-wider opacity-60 mb-2">Datos relevantes</div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
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

        <div class="text-xs opacity-60 text-center">
            ID de verificación: {{ $certificate->uuid }}
        </div>
    </div>
</x-guest-layout>
