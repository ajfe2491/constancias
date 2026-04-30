<?php

namespace App\Http\Controllers;

use App\Models\DocumentConfiguration;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use chillerlan\QRCode\QRCode;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class DocumentConfigurationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Group by event (event_id IS NULL puts generics at the end), then by latest creation
        $query = DocumentConfiguration::with('event', 'sharedUsers')
            ->withCount('sharedUsers')
            ->visibleTo(Auth::user())
            ->orderByRaw('event_id IS NULL, event_id DESC, created_at DESC');

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('document_name', 'like', "%{$search}%")
                    ->orWhere('document_type', 'like', "%{$search}%")
                    ->orWhereHas('event', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('key', 'like', "%{$search}%");
                    });
            });
        }

        $configurations = $query->paginate(10);
        $shareableUsers = \App\Models\User::whereNull('deleted_at')
            ->where('id', '!=', Auth::id())
            ->orderBy('name')
            ->get();
        return view('document_configurations.index', compact('configurations', 'shareableUsers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $eventsQuery = Event::where('is_active', true)->latest();
        if (!Auth::user()->isSuperAdmin()) {
            $eventsQuery->where('user_id', Auth::id());
        }
        $events = $eventsQuery->get();
        $preselectedEventId = $request->query('event_id');
        return view('document_configurations.create', compact('events', 'preselectedEventId'));
    }

    /**
     * Show the form for copying the specified resource.
     */
    public function copy(DocumentConfiguration $documentConfiguration)
    {
        $this->ensureOwner($documentConfiguration);
        $eventsQuery = Event::where('is_active', true)->latest();
        if (!Auth::user()->isSuperAdmin()) {
            $eventsQuery->where('user_id', Auth::id());
        }
        $events = $eventsQuery->get();
        return view('document_configurations.copy', compact('documentConfiguration', 'events'));
    }

    /**
     * Store a copied configuration.
     */
    public function storeCopy(Request $request, DocumentConfiguration $documentConfiguration)
    {
        $this->ensureOwner($documentConfiguration);
        $validated = $request->validate([
            'document_name' => 'required|string|max:255',
            'document_type' => 'required|string|max:255',
            'description' => 'nullable|string',
            'event_id' => [
                'nullable',
                Auth::user()->isSuperAdmin()
                    ? Rule::exists('events', 'id')
                    : Rule::exists('events', 'id')->where('user_id', Auth::id()),
            ],
            'folio_start' => 'required|integer|min:1',
            'folio_digits' => 'required|integer|min:1|max:20',
            'folio_year_prefix' => 'boolean',
        ]);

        $copy = $documentConfiguration->replicate();
        $copy->document_name = $validated['document_name'];
        $copy->document_type = $validated['document_type'];
        $copy->description = $validated['description'] ?? null;
        $copy->event_id = $validated['event_id'] ?? null;
        $copy->user_id = Auth::id();

        if ($documentConfiguration->background_image &&
            Storage::disk('public')->exists($documentConfiguration->background_image)) {
            $extension = pathinfo($documentConfiguration->background_image, PATHINFO_EXTENSION);
            $extension = $extension ?: 'png';
            $newPath = 'backgrounds/' . Str::uuid() . '.' . $extension;

            if (Storage::disk('public')->copy($documentConfiguration->background_image, $newPath)) {
                $copy->background_image = $newPath;
            }
        }

        $copy->save();

        return redirect()->route('document-configurations.edit', $copy)
            ->with('success', 'Configuración copiada. Ahora puedes personalizarla.');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'document_name' => 'required|string|max:255',
            'document_type' => 'required|string|max:255',
            'description' => 'nullable|string',
            'event_id' => [
                'nullable',
                Auth::user()->isSuperAdmin()
                    ? Rule::exists('events', 'id')
                    : Rule::exists('events', 'id')->where('user_id', Auth::id()),
            ],
        ]);

        // Set defaults
        $data = $request->only([
            'document_name',
            'document_type',
            'description',
            'event_id',
            'folio_start',
            'folio_digits',
        ]);
        $data['page_orientation'] = 'L';
        $data['page_size'] = 'Letter';
        $data['is_active'] = true;
        $data['show_qr'] = true;
        $data['show_folio'] = true;
        $data['folio_year_prefix'] = $request->has('folio_year_prefix');
        $data['user_id'] = Auth::id();

        $config = DocumentConfiguration::create($data);

        return redirect()->route('document-configurations.edit', $config)
            ->with('success', 'Configuración creada. Ahora puedes personalizarla.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DocumentConfiguration $documentConfiguration)
    {
        $this->ensureOwner($documentConfiguration);
        $eventsQuery = Event::where('is_active', true)->latest();
        if (!Auth::user()->isSuperAdmin()) {
            $eventsQuery->where('user_id', Auth::id());
        }
        $events = $eventsQuery->get();
        return view('document_configurations.editor', compact('documentConfiguration', 'events'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DocumentConfiguration $documentConfiguration)
    {
        $this->ensureOwner($documentConfiguration);
        $rules = [
            'document_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'background_image' => 'nullable|image|mimes:jpg,jpeg,png|mimetypes:image/jpeg,image/png|max:4096|dimensions:max_width=8000,max_height=8000',
            'page_orientation' => 'required|in:P,L',
            'page_size' => 'required|string',
            'text_elements' => 'nullable|string', // JSON string
            'folio_start' => 'required|integer|min:1',
            'folio_digits' => 'required|integer|min:1|max:20',
            'folio_year_prefix' => 'boolean',
            'event_id' => [
                'nullable',
                Auth::user()->isSuperAdmin()
                    ? Rule::exists('events', 'id')
                    : Rule::exists('events', 'id')->where('user_id', Auth::id()),
            ],
            'show_folio' => 'boolean',
            'folio_x' => 'nullable|numeric',
            'folio_y' => 'nullable|numeric',
            'folio_width' => 'nullable|numeric',
            'folio_height' => 'nullable|numeric',
            'folio_font_size' => 'nullable|integer|min:1',
            'folio_color' => 'nullable|string',
            'folio_alignment' => 'nullable|in:L,C,R',
            'background_fit' => 'boolean',
            'email_message' => 'nullable|string',
        ];
        $messages = [
            'background_image.image' => 'La imagen de fondo debe ser una imagen válida.',
            'background_image.mimes' => 'La imagen de fondo debe ser JPG o PNG.',
            'background_image.mimetypes' => 'La imagen de fondo debe ser JPG o PNG.',
            'background_image.max' => 'La imagen de fondo no debe superar 4 MB.',
            'background_image.dimensions' => 'La imagen de fondo no debe exceder 8000x8000 px.',
        ];
        $validated = $request->validate($rules, $messages);

        $data = $request->except('background_image', 'text_elements');


        if ($request->hasFile('background_image')) {
            if ($documentConfiguration->background_image) {
                Storage::disk('public')->delete($documentConfiguration->background_image);
            }
            $path = $request->file('background_image')->store('backgrounds', 'public');
            $data['background_image'] = $path;
        }

        if ($request->filled('text_elements')) {
            $data['text_elements'] = json_decode($request->text_elements, true);
        }

        if ($request->filled('sample_data')) {
            $sampleData = $request->sample_data;
            if (is_string($sampleData)) {
                $decoded = json_decode($sampleData, true);
                $data['sample_data'] = is_array($decoded) ? $decoded : [];
            } else {
                $data['sample_data'] = $sampleData;
            }
        }

        // Asegurar que los campos booleanos se procesen correctamente
        // Para checkboxes estándar, la presencia del campo indica "true".
        $data['is_active'] = $request->has('is_active');
        $data['show_qr'] = true;
        $data['folio_year_prefix'] = $request->has('folio_year_prefix');
        $data['show_folio'] = true;
        $data['enable_live_preview'] = $request->has('enable_live_preview');
        $data['background_fit'] = $request->has('background_fit');

        $documentConfiguration->update($data);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Guardado correctamente']);
        }

        return redirect()->route('document-configurations.edit', $documentConfiguration)
            ->with('success', 'Configuración actualizada exitosamente.');
    }

    /**
     * Generate a PDF preview for the configuration.
     */
    public function preview(Request $request, DocumentConfiguration $documentConfiguration)
    {
        $this->ensureOwner($documentConfiguration);
        if ($request->hasFile('background_image')) {
            $request->validate([
                'background_image' => 'nullable|image|mimes:jpg,jpeg,png|mimetypes:image/jpeg,image/png|max:4096|dimensions:max_width=8000,max_height=8000',
            ], [
                'background_image.image' => 'La imagen de fondo debe ser una imagen válida.',
                'background_image.mimes' => 'La imagen de fondo debe ser JPG o PNG.',
                'background_image.mimetypes' => 'La imagen de fondo debe ser JPG o PNG.',
                'background_image.max' => 'La imagen de fondo no debe superar 4 MB.',
                'background_image.dimensions' => 'La imagen de fondo no debe exceder 8000x8000 px.',
            ]);
        }

        $tempConfig = clone $documentConfiguration;
        $data = $request->all();


        // Fill temp config with request data
        // Note: fill() only updates attributes present in the array.
        $tempConfig->fill($data);

        // Manually load event relation for preview if event_id is changed/present
        if ($request->filled('event_id')) {
            $event = Event::find($request->event_id);
            if ($event) {
                $tempConfig->setRelation('event', $event);
            }
        } else {
            // If no event_id, ensure no event is attached (or keep existing if not changed? 
            // If changed to empty, fill() sets event_id to null, but relation might persist if lazy loaded? 
            // setRelation('event', null) is safer if we want to simulate "no event")
            if (array_key_exists('event_id', $data) && empty($data['event_id'])) {
                $tempConfig->setRelation('event', null);
            }
        }

        // Handle temporary background image upload
        if ($request->hasFile('background_image')) {
            $path = $request->file('background_image')->store('temp', 'public');
            $tempConfig->background_image = $path;
        } elseif ($documentConfiguration->background_image) {
            // Keep existing image if no new one uploaded
            $tempConfig->background_image = $documentConfiguration->background_image;
        }

        // Handle text_elements if passed as JSON string (from form) or array (from axios)
        if (isset($data['text_elements'])) {
            $tempConfig->text_elements = is_string($data['text_elements'])
                ? json_decode($data['text_elements'], true)
                : $data['text_elements'];
        }

        if (isset($data['sample_data'])) {
            $tempConfig->sample_data = is_string($data['sample_data'])
                ? json_decode($data['sample_data'], true)
                : $data['sample_data'];
        }

        // Ensure boolean fields are correctly set from request
        // If it's an AJAX request with JSON, boolean false is sent.
        // If it's a form submit, unchecked checkboxes are missing.
        // We need to handle both cases.
        $tempConfig->show_qr = true;
        $tempConfig->show_folio = true;

        // Ensure background dimensions are set if image exists
        if ($tempConfig->background_image) {
            if (empty($tempConfig->background_width) || $tempConfig->background_width <= 0) {
                $tempConfig->background_width = 215.9; // Default Letter width mm
            }
            if (empty($tempConfig->background_height) || $tempConfig->background_height <= 0) {
                $tempConfig->background_height = 279.4; // Default Letter height mm
            }
        }

        // Use sample data for placeholders
        $sampleData = $tempConfig->sample_data ?? [
            'nombre_participante' => 'Juan Pérez',
            'folio' => '12345',
            'fecha' => date('d/m/Y'),
        ];

        // If sample_data is a JSON string, decode it
        if (is_string($sampleData)) {
            $sampleData = json_decode($sampleData, true) ?? [];
        }

        // Handle QR Code for preview
        if ($tempConfig->show_qr) {
            $qrPath = $this->ensureExampleQr();
            if ($qrPath) {
                $sampleData['qr_path'] = $qrPath;

                if (isset($data['qr_width']))
                    $tempConfig->qr_width = $data['qr_width'];
                if (isset($data['qr_height']))
                    $tempConfig->qr_height = $data['qr_height'];

                // Campos de Folio
                if (isset($data['folio_start']))
                    $tempConfig->folio_start = $data['folio_start'];
                if (isset($data['folio_digits']))
                    $tempConfig->folio_digits = $data['folio_digits'];
                $tempConfig->folio_year_prefix = isset($data['folio_year_prefix']);
            }
        }

        $pdf = $tempConfig->generatePDF($sampleData);
        $pdfContent = $pdf->Output('S');

        if ($request->query('format') === 'png') {
            if (!class_exists(\Imagick::class)) {
                return response()->json([
                    'message' => 'Imagick no está disponible en el servidor.'
                ], 501);
            }

            $tmpDir = storage_path('app/tmp');
            if (!is_dir($tmpDir)) {
                mkdir($tmpDir, 0755, true);
            }

            $tmpPdf = $tmpDir . '/preview_' . $documentConfiguration->id . '_' . uniqid() . '.pdf';
            file_put_contents($tmpPdf, $pdfContent);

            $imagick = new \Imagick();
            $imagick->setResolution(96, 96);
            $imagick->readImage($tmpPdf . '[0]');
            $imagick->setImageBackgroundColor('white');
            $imagick = $imagick->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
            $imagick->setImageFormat('png');
            
            // Optimización: Redimensionar para pantalla
            $imagick->resizeImage(800, 0, \Imagick::FILTER_LANCZOS, 1);
            
            $png = $imagick->getImageBlob();
            $imagick->clear();
            $imagick->destroy();
            @unlink($tmpPdf);

            return response($png, 200, [
                'Content-Type' => 'image/png',
                'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            ]);
        }

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="preview.pdf"'
        ]);
    }

    /**
     * Ensure an example QR code exists for preview.
     */
    private function ensureExampleQr()
    {
        $dir = public_path('qrs');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $filePath = $dir . '/example.png';

        // Regenerate if file doesn't exist or is not a PNG (simple check by mime type or just force regen if it's the wrong type previously)
        // For simplicity, let's check if it exists and is valid, otherwise regen.
        // Actually, since we had a bug, let's force regen if it's an SVG.
        if (file_exists($filePath)) {
            $mime = mime_content_type($filePath);
            if ($mime === 'image/svg+xml') {
                unlink($filePath);
            }
        }

        if (!file_exists($filePath)) {
            try {
                $options = new \chillerlan\QRCode\QROptions([
                    'outputType' => \chillerlan\QRCode\QRCode::OUTPUT_IMAGE_PNG,
                    'eccLevel' => \chillerlan\QRCode\QRCode::ECC_L,
                    'scale' => 5,
                    'imageBase64' => false,
                ]);

                (new \chillerlan\QRCode\QRCode($options))->render('https://example.com/verify/12345', $filePath);
            } catch (\Exception $e) {
                \Log::error('Failed to generate example QR: ' . $e->getMessage());
                return null;
            }
        }

        return $filePath;
    }

    /**
     * Stream the PDF for the saved configuration.
     */
    public function streamPdf(DocumentConfiguration $documentConfiguration)
    {
        $this->ensureCanView($documentConfiguration);
        // Use sample data for placeholders
        $sampleData = $documentConfiguration->sample_data ?? [
            'nombre_participante' => 'Juan Pérez',
            'folio' => '12345',
            'fecha' => date('d/m/Y'),
        ];

        $pdf = $documentConfiguration->generatePDF($sampleData);

        return response($pdf->Output('S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $documentConfiguration->document_name . '.pdf"'
        ]);
    }

    /**
     * Serve the background image.
     */
    public function backgroundImage(DocumentConfiguration $documentConfiguration)
    {
        $this->ensureCanView($documentConfiguration);
        if (!$documentConfiguration->background_image) {
            abort(404);
        }

        $path = $documentConfiguration->background_image;

        // Handle storage path
        if (Storage::disk('public')->exists($path)) {
            return response()->file(Storage::disk('public')->path($path));
        }

        // Handle absolute path (if any)
        if (file_exists($path)) {
            return response()->file($path);
        }

        abort(404);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DocumentConfiguration $documentConfiguration)
    {
        $this->ensureOwner($documentConfiguration);
        $eventId = $documentConfiguration->event_id;

        if ($documentConfiguration->background_image && $documentConfiguration->isForceDeleting()) {
            Storage::disk('public')->delete($documentConfiguration->background_image);
        }

        $documentConfiguration->delete();

        if ($eventId) {
            return redirect()->route('events.show', $eventId)
                ->with('success', 'Configuración eliminada exitosamente.');
        }

        return redirect()->route('document-configurations.index')
            ->with('success', 'Configuración eliminada exitosamente.');
    }

    private function ensureOwner(DocumentConfiguration $documentConfiguration): void
    {
        $user = Auth::user();
        if (!$user || (!$user->isSuperAdmin() && $documentConfiguration->user_id !== $user->id)) {
            abort(403);
        }
    }

    private function ensureCanView(DocumentConfiguration $documentConfiguration): void
    {
        if (!$documentConfiguration->canBeViewedBy(Auth::user())) {
            abort(403);
        }
    }
}
