<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-base-content leading-tight">{{ $title }}</h2>
                <p class="text-sm opacity-70">{{ $subtitle }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ $backUrl }}" class="btn btn-ghost btn-sm">Volver</a>
                <button type="button" id="save-template" class="btn btn-primary btn-sm">Guardar plantilla</button>
            </div>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4 email-template-shell">
        <form id="email-template-form" method="POST" action="{{ $saveUrl }}">
            @csrf
            @method('PUT')
            <input type="hidden" name="email_subject" id="email_subject">
            <input type="hidden" name="email_template_html" id="email_template_html">
            <input type="hidden" name="email_template_mjml" id="email_template_mjml">
        </form>

        <div class="email-template-layout">
            <div class="flex-1 min-w-0">
                <div class="card bg-base-100 shadow-sm border border-base-200">
                    <div class="card-body p-0">
                        <div id="gjs" style="height: calc(100vh - 260px); min-height: 560px;"></div>
                    </div>
                </div>
            </div>

            <div class="email-template-sidebar space-y-4">
                <div class="flex justify-end">
                    <button type="button" id="email-sidebar-collapse"
                        class="btn btn-ghost btn-xs btn-circle" title="Ocultar panel">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
                <div class="card bg-base-100 shadow-sm border border-base-200">
                    <div class="card-body gap-3">
                        <div class="form-control w-full">
                            <label class="label">
                                <span class="label-text font-semibold">Asunto del correo</span>
                            </label>
                            <input type="text" id="email-subject" value="{{ $emailSubject }}"
                                class="input input-bordered w-full"
                                placeholder="Ej. @{{document_name}} - @{{event_name}}" />
                            <label class="label">
                                <span class="label-text-alt opacity-70">Puedes usar variables como @{{document_name}} y @{{event_name}}.</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="card bg-base-100 shadow-sm border border-base-200">
                    <div class="card-body text-sm">
                        <p class="font-semibold mb-2">Guía rápida</p>
                        <ul class="list-disc list-inside space-y-1 opacity-80">
                            <li>Arrastra bloques al lienzo para construir tu correo.</li>
                            <li>Para insertar variables, usa los bloques de la izquierda o escribe el token.</li>
                            <li>El diseño se exporta como HTML para envío masivo.</li>
                        </ul>
                    </div>
                </div>

                <div class="card bg-base-100 shadow-sm border border-base-200">
                    <div class="card-body">
                        <h3 class="font-semibold text-sm mb-2">Variables disponibles</h3>
                        <div class="text-xs opacity-80 space-y-1">
                            <div>@{{participant_name}}</div>
                            <div>@{{event_name}}</div>
                            <div>@{{event_logo_url}}</div>
                            <div>@{{event_type}}</div>
                            <div>@{{event_key}}</div>
                            <div>@{{event_dates}}</div>
                            <div>@{{event_description}}</div>
                            <div>@{{document_name}}</div>
                            <div>@{{document_type}}</div>
                            <div>@{{document_description}}</div>
                            <div>@{{recipient_email}}</div>
                            <div>@{{folio}}</div>
                            <div>@{{uuid}}</div>
                            <div>@{{verification_url}}</div>
                            <div>@{{email_message}}</div>
                        </div>
                        @if (!empty($extraTokens))
                            <div class="mt-3">
                                <p class="text-xs font-semibold mb-1">Variables de constancia</p>
                                <div class="text-xs opacity-80 space-y-1">
                                    @foreach ($extraTokens as $token)
                                        <div>{{ '{' . '{' . $token . '}' . '}' }}</div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        <p class="text-xs opacity-70 mt-3">
                            El texto configurado en la constancia siempre se incluye. Si no lo colocas en la plantilla,
                            se agrega al final de forma automática.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <button type="button" id="email-sidebar-expand"
            class="btn btn-primary btn-xs btn-circle email-sidebar-expand" title="Mostrar panel">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
    </div>

    <style>
        .email-template-shell {
            position: relative;
        }
        .email-sidebar-expand {
            position: absolute;
            top: 8px;
            right: 0;
            z-index: 20;
            display: none;
        }
        .email-template-layout {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            align-items: flex-start;
        }
        .email-template-sidebar {
            width: 100%;
        }
        @media (min-width: 1024px) {
            .email-template-layout {
                flex-direction: row;
            }
            .email-template-sidebar {
                width: 320px;
                flex: 0 0 320px;
                position: sticky;
                top: 96px;
                transition: width 0.2s ease, opacity 0.2s ease, transform 0.2s ease;
            }
            .email-template-shell.is-collapsed .email-template-sidebar {
                width: 0;
                flex: 0 0 0;
                opacity: 0;
                transform: translateX(16px);
                pointer-events: none;
            }
            .email-template-shell.is-collapsed .email-template-sidebar .card,
            .email-template-shell.is-collapsed .email-template-sidebar .flex {
                display: none;
            }
            .email-template-shell.is-collapsed .email-sidebar-expand {
                display: inline-flex;
            }
        }
        .email-template-shell.is-collapsed .email-sidebar-expand {
            display: inline-flex;
        }
        #gjs {
            min-height: 560px;
        }
        #gjs .gjs-editor,
        #gjs .gjs-cv-canvas,
        #gjs .gjs-cv-canvas__frames,
        #gjs .gjs-frame-wrapper,
        #gjs .gjs-frame-wrapper iframe {
            min-height: 560px !important;
            height: 100% !important;
        }
    </style>

    <link rel="stylesheet" href="https://unpkg.com/grapesjs/dist/css/grapes.min.css">
    <script src="https://unpkg.com/grapesjs"></script>
    <script src="https://unpkg.com/grapesjs-locale-es"></script>
    <script src="https://unpkg.com/mjml-browser@4"></script>
    <script src="https://unpkg.com/grapesjs-mjml@1.0.2"></script>

    <script>
        // Ensure mjml2html is available for grapesjs-mjml
        if (typeof window.mjml2html !== 'function') {
            if (typeof window.mjml === 'function') {
                window.mjml2html = window.mjml;
            } else if (window.mjml && typeof window.mjml.mjml2html === 'function') {
                window.mjml2html = window.mjml.mjml2html;
            } else if (window.mjmlBrowser && typeof window.mjmlBrowser.mjml2html === 'function') {
                window.mjml2html = window.mjmlBrowser.mjml2html;
            }
        }
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const shell = document.querySelector('.email-template-shell');
            const collapseBtn = document.getElementById('email-sidebar-collapse');
            const expandBtn = document.getElementById('email-sidebar-expand');
            const storageKey = 'emailTemplateSidebarCollapsed';

            const setCollapsed = (collapsed) => {
                if (!shell) return;
                shell.classList.toggle('is-collapsed', collapsed);
                window.localStorage.setItem(storageKey, collapsed ? '1' : '0');
            };

            if (shell) {
                const stored = window.localStorage.getItem(storageKey);
                if (stored === '1') {
                    setCollapsed(true);
                }
            }

            if (collapseBtn) {
                collapseBtn.addEventListener('click', () => setCollapsed(true));
            }
            if (expandBtn) {
                expandBtn.addEventListener('click', () => setCollapsed(false));
            }

            const editor = grapesjs.init({
                container: '#gjs',
                height: '100%',
                fromElement: false,
                storageManager: false,
                i18n: {
                    locale: 'es',
                    localeFallback: 'en',
                    messages: { es: window.grapesjsLocaleEs || {} }
                },
                plugins: ['grapesjs-mjml'],
                pluginsOpts: {
                    'grapesjs-mjml': {
                        resetBlocks: true
                    }
                }
            });

            const initialMjml = @json($templateMjml);
            editor.on('load', () => {
                if (initialMjml) {
                    editor.setComponents(initialMjml);
                }
            });

            const addTokenBlock = (id, label, token) => {
                editor.BlockManager.add(id, {
                    label,
                    category: 'Variables',
                    content: `<mj-text>${token}</mj-text>`
                });
            };

            const token = (name) => '{' + '{' + name + '}' + '}';

            addTokenBlock('token-name', 'Nombre participante', token('participant_name'));
            addTokenBlock('token-event', 'Evento', token('event_name'));
            editor.BlockManager.add('token-event-logo', {
                label: 'Logo del evento',
                category: 'Variables',
                content: `<mj-image src="${token('event_logo_url')}" width="120px" align="left" />`
            });
            addTokenBlock('token-event-type', 'Tipo de evento', token('event_type'));
            addTokenBlock('token-event-key', 'Clave de evento', token('event_key'));
            addTokenBlock('token-event-dates', 'Fechas del evento', token('event_dates'));
            addTokenBlock('token-event-desc', 'Descripción del evento', token('event_description'));
            addTokenBlock('token-doc', 'Documento', token('document_name'));
            addTokenBlock('token-doc-type', 'Tipo de documento', token('document_type'));
            addTokenBlock('token-doc-desc', 'Descripción del documento', token('document_description'));
            addTokenBlock('token-email', 'Correo', token('recipient_email'));
            addTokenBlock('token-folio', 'Folio', token('folio'));
            addTokenBlock('token-uuid', 'UUID', token('uuid'));
            addTokenBlock('token-verify', 'URL verificacion', token('verification_url'));
            addTokenBlock('token-message', 'Texto de correo', token('email_message'));
            const extraTokens = @json($extraTokens ?? []);
            extraTokens.forEach((name) => {
                const safeId = `token-custom-${name}`.replace(/[^a-z0-9\-]+/gi, '-');
                addTokenBlock(safeId, name, token(name));
            });

            const saveButton = document.getElementById('save-template');
            const form = document.getElementById('email-template-form');
            saveButton.addEventListener('click', () => {
                const html = editor.getHtml();
                const css = editor.getCss();
                const mjmlRaw = editor.getMjml ? editor.getMjml() : '';
                const mjmlSource = mjmlRaw || html;
                const normalizedMjml = mjmlSource.replace(
                    /<mj-divider([^>]*)>([\s\S]*?)<\/mj-divider>/gi,
                    '<mj-divider$1 />$2'
                );
                const hasMjml = /<mjml|<mj-/.test(normalizedMjml);
                let templateHtml = '';

                if (hasMjml) {
                    let result = null;
                    if (typeof window.mjml === 'function') {
                        result = window.mjml(normalizedMjml);
                    } else if (typeof window.mjml2html === 'function') {
                        result = window.mjml2html(normalizedMjml, { minify: true });
                    }
                    templateHtml = result && result.html ? result.html : '';
                } else {
                    templateHtml = css ? `<style>${css}</style>${html}` : html;
                }

                document.getElementById('email_subject').value = document.getElementById('email-subject').value || '';
                document.getElementById('email_template_html').value = templateHtml || '';
                document.getElementById('email_template_mjml').value = hasMjml ? normalizedMjml : '';
                form.submit();
            });
        });
    </script>
</x-app-layout>
