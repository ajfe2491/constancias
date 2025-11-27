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

        // Checkboxes
        $data['is_active'] = $request->has('is_active');
        $data['show_qr'] = $request->has('show_qr');

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
