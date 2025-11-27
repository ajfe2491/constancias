<?php

namespace App\Http\Controllers;

use App\Models\DocumentConfiguration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use chillerlan\QRCode\QRCode;

class DocumentConfigurationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = DocumentConfiguration::whereNull('event_id')->latest();

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('document_name', 'like', "%{$search}%")
                    ->orWhere('document_type', 'like', "%{$search}%");
            });
        }

        $configurations = $query->paginate(10);
        return view('document_configurations.index', compact('configurations'));
    }

    /**
     * Store a newly created resource in storage.
     */
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('document_configurations.create');
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
        ]);

        // Set defaults
        $data = $request->only(['document_name', 'document_type', 'description']);
        $data['page_orientation'] = 'L';
        $data['page_size'] = 'Letter';
        $data['is_active'] = true;
        $data['show_qr'] = true;

        $config = DocumentConfiguration::create($data);

        return redirect()->route('document-configurations.edit', $config)
            ->with('success', 'Configuración creada. Ahora puedes personalizarla.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DocumentConfiguration $documentConfiguration)
    {
        return view('document_configurations.editor', compact('documentConfiguration'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DocumentConfiguration $documentConfiguration)
    {
        $validated = $request->validate([
            'document_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'background_image' => 'nullable|image|max:2048',
            'page_orientation' => 'required|in:P,L',
            'page_size' => 'required|string',
            'text_elements' => 'nullable|string', // JSON string
            'folio_start' => 'required|integer|min:1',
            'folio_digits' => 'required|integer|min:1|max:20',
            'folio_year_prefix' => 'boolean',
        ]);

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
            $data['sample_data'] = $request->sample_data;
        }

        // Asegurar que los campos booleanos se procesen correctamente
        $data['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : false;
        $data['show_qr'] = $request->has('show_qr') ? $request->boolean('show_qr') : false;
        $data['folio_year_prefix'] = $request->has('folio_year_prefix') ? $request->boolean('folio_year_prefix') : false;
        $data['enable_live_preview'] = $request->has('enable_live_preview') ? true : false;

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
        $data = $request->all();

        // Create a temporary instance with the incoming data
        $tempConfig = $documentConfiguration->replicate();
        $tempConfig->fill($data);

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

        // Ensure boolean fields are correctly set from request
        // If it's an AJAX request with JSON, boolean false is sent.
        // If it's a form submit, unchecked checkboxes are missing.
        // We need to handle both cases.
        if ($request->isJson()) {
            $tempConfig->show_qr = $request->boolean('show_qr');
        } else {
            // For form submissions, presence means true, absence means false (usually)
            // But here we are likely using axios to send form data or JSON.
            // Let's rely on $request->boolean which handles "true", "1", "on" and true.
            // However, if the key is missing in a form submit, it defaults to false.
            // But if we are just filling from $data which comes from $request->all(),
            // and $data['show_qr'] is missing, fill() might not touch it if it's not in the array?
            // No, fill() only updates keys present in the array.
            // So if 'show_qr' is missing from $data, it keeps the original value.
            // We must explicitly set it.
            $tempConfig->show_qr = $request->has('show_qr') ? $request->boolean('show_qr') : false;
        }

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

        return response($pdf->Output('S'), 200, [
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
        $eventId = $documentConfiguration->event_id;

        if ($documentConfiguration->background_image) {
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
}
