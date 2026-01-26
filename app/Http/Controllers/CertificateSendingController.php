<?php

namespace App\Http\Controllers;

use App\Jobs\SendCertificateJob;
use App\Models\ConstancyGeneralHistory;
use App\Models\DocumentConfiguration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CertificateSendingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $historyQuery = ConstancyGeneralHistory::with('user')
            ->latest();
        if (!Auth::user()->isSuperAdmin()) {
            $historyQuery->where('user_id', Auth::id());
        }
        $history = $historyQuery->paginate(10);

        $sharedCertificates = collect();
        $sharedCertificatesCount = 0;
        if (!Auth::user()->isSuperAdmin()) {
            $sharedCertificates = \App\Models\Certificate::with(['history.user', 'documentConfiguration.event'])
                ->whereHas('sharedUsers', function ($query) {
                    $query->where('users.id', Auth::id());
                })
                ->latest()
                ->take(10)
                ->get();

            $sharedCertificatesCount = \App\Models\Certificate::whereHas('sharedUsers', function ($query) {
                $query->where('users.id', Auth::id());
            })->count();
        }

        return view('certificate_sending.index', compact('history', 'sharedCertificates', 'sharedCertificatesCount'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $configurations = DocumentConfiguration::where('is_active', true)
            ->visibleTo(Auth::user())
            ->with('event')
            ->latest()
            ->get();

        $configurationMeta = $configurations->map(function ($config) {
            return [
                'id' => $config->id,
                'name' => $config->document_name,
                'event' => $config->event?->name,
                'placeholders' => $this->extractPlaceholders($config),
                'sample_data' => $config->sample_data ?? [],
            ];
        });

        return view('certificate_sending.create', [
            'configurations' => $configurations,
            'configurationMeta' => $configurationMeta,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $mode = $request->input('mode', 'bulk');

        $baseRules = [
            'document_configuration_id' => 'required|exists:document_configurations,id',
        ];

        if ($mode === 'single') {
            $baseRules['email'] = 'required|email';
        } else {
            $baseRules['csv_file'] = [
                'required',
                'file',
                'max:2048',
                'mimes:csv,txt',
                'mimetypes:text/plain,text/csv,application/csv,application/vnd.ms-excel',
                function ($attribute, $value, $fail) {
                    $extension = strtolower($value->getClientOriginalExtension());
                    if (!in_array($extension, ['csv', 'txt'], true)) {
                        $fail('El archivo debe ser CSV.');
                        return;
                    }

                    $handle = @fopen($value->getRealPath(), 'r');
                    if ($handle) {
                        $sample = fread($handle, 2048);
                        fclose($handle);
                        if (stripos($sample, '<?php') !== false || stripos($sample, '<script') !== false) {
                            $fail('El archivo CSV contiene contenido no permitido.');
                        }
                    }
                },
            ];
        }

        $request->validate($baseRules);

        $config = DocumentConfiguration::visibleTo(Auth::user())
            ->findOrFail($request->document_configuration_id);

        if ($mode === 'single') {
            $placeholders = collect($this->extractPlaceholders($config))
                ->filter(fn($field) => $field !== 'email');

            $dynamicRules = $placeholders
                ->mapWithKeys(fn($field) => ["data.$field" => 'required|string'])
                ->toArray();

            $request->validate($dynamicRules);

            $recipientData = array_merge(
                $request->input('data', []),
                ['email' => $request->input('email')]
            );

            $history = ConstancyGeneralHistory::create([
                'total_registros' => 1,
                'procesados_exitosos' => 0,
                'procesados_fallidos' => 0,
                'qrs_generados' => 0,
                'errores' => [],
                'user_id' => Auth::id(),
                'csv_file_path' => null,
                'document_configuration_id' => $config->id,
            ]);

            SendCertificateJob::dispatch($config, $recipientData, $history->id);

            return redirect()->route('certificate-sending.show', $history)
                ->with('success', 'Envío individual iniciado. Actualiza esta página para ver el progreso.');
        }

        $path = $request->file('csv_file')->store('csv_uploads');

        // Parse CSV
        $file = fopen(storage_path('app/' . $path), 'r');
        $header = fgetcsv($file);

        // Normalize headers: lowercase, trim, remove BOM if present
        $header = array_map(function ($h) {
            return trim(strtolower(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $h)));
        }, $header);

        $allowedHeaders = $this->extractPlaceholders($config);
        if (empty($header) || empty($allowedHeaders)) {
            fclose($file);
            return back()->withErrors(['csv_file' => 'El archivo CSV no tiene encabezados válidos.']);
        }

        if (!in_array('email', $header, true)) {
            fclose($file);
            return back()->withErrors(['csv_file' => 'El archivo CSV debe incluir la columna email.']);
        }

        $extraHeaders = array_diff($header, $allowedHeaders);
        if (!empty($extraHeaders)) {
            fclose($file);
            return back()->withErrors([
                'csv_file' => 'El archivo CSV contiene columnas no permitidas: ' . implode(', ', $extraHeaders),
            ]);
        }

        $rows = [];
        $maxRows = 5000;
        while (($row = fgetcsv($file)) !== false) {
            if (count($row) === count($header)) {
                $rows[] = array_combine($header, $row);
            }
            if (count($rows) > $maxRows) {
                fclose($file);
                return back()->withErrors([
                    'csv_file' => 'El archivo CSV excede el límite de registros permitido (' . $maxRows . ').',
                ]);
            }
        }
        fclose($file);

        if (empty($rows)) {
            return back()->withErrors(['csv_file' => 'El archivo CSV está vacío o no tiene el formato correcto.']);
        }

        $history = ConstancyGeneralHistory::create([
            'total_registros' => count($rows),
            'procesados_exitosos' => 0,
            'procesados_fallidos' => 0,
            'qrs_generados' => 0, // Not strictly tracking QRs individually here, but could
            'errores' => [],
            'user_id' => Auth::id(),
            'csv_file_path' => $path,
            'document_configuration_id' => $config->id,
        ]);

        foreach ($rows as $row) {
            if (empty($row['email'])) {
                $history->increment('procesados_fallidos');
                $errors = $history->errores ?? [];
                $errors[] = ['email' => 'N/A', 'error' => 'Email missing in CSV row', 'time' => now()->toDateTimeString()];
                $history->update(['errores' => $errors]);
                continue;
            }

            SendCertificateJob::dispatch($config, $row, $history->id);
        }

        return redirect()->route('certificate-sending.index')
            ->with('success', 'Proceso de envío iniciado. Se están procesando ' . count($rows) . ' registros.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ConstancyGeneralHistory $history)
    {
        $history->load('documentConfiguration.event', 'user');

        $isOwner = $history->user_id === Auth::id() || Auth::user()->isSuperAdmin();

        if (!$isOwner) {
            $hasSharedCertificates = \App\Models\Certificate::where('history_id', $history->id)
                ->whereHas('sharedUsers', function ($query) {
                    $query->where('users.id', Auth::id());
                })
                ->exists();

            if (!$hasSharedCertificates && !Auth::user()->isSuperAdmin()) {
                abort(403);
            }
        }

        $certificatesQuery = \App\Models\Certificate::where('history_id', $history->id)
            ->withCount('sharedUsers')
            ->orderBy('id');

        if ($isOwner) {
            $certificatesQuery->with('sharedUsers');
        } else {
            $certificatesQuery->whereHas('sharedUsers', function ($query) {
                $query->where('users.id', Auth::id());
            });
        }

        $certificates = $certificatesQuery->paginate(25);

        $shareableUsers = [];
        if ($isOwner) {
            $shareableUsers = \App\Models\User::whereNull('deleted_at')
                ->where('id', '!=', Auth::id())
                ->orderBy('name')
                ->get();
        }

        return view('certificate_sending.show', compact('history', 'certificates', 'isOwner', 'shareableUsers'));
    }

    public function downloadCsv(ConstancyGeneralHistory $history)
    {
        if ($history->user_id !== Auth::id() && !Auth::user()->isSuperAdmin()) {
            abort(403);
        }

        if (!$history->csv_file_path) {
            abort(404);
        }

        $path = storage_path('app/' . $history->csv_file_path);
        if (!file_exists($path)) {
            abort(404);
        }

        return response()->download($path, 'envio_' . $history->id . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function downloadTemplate(DocumentConfiguration $documentConfiguration)
    {
        if (!$documentConfiguration->canBeViewedBy(Auth::user())) {
            abort(403);
        }

        $headers = $this->extractPlaceholders($documentConfiguration);

        // Also check for folio if it's not auto-generated (though usually it is)
        // If the user wants to override folio from CSV, they can add 'folio' column.
        // But usually folio is generated by the system or config.
        // Let's add 'folio' as optional if it's in the text elements (already covered above)
        // or if we want to explicitly allow it. For now, regex extraction is sufficient.

        $callback = function () use ($headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            fclose($file);
        };

        $filename = 'plantilla_' . Str::slug($documentConfiguration->document_name) . '.csv';

        return response()->stream($callback, 200, [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ]);
    }

    public function status(ConstancyGeneralHistory $history)
    {
        if ($history->user_id !== Auth::id() && !Auth::user()->isSuperAdmin()) {
            abort(403);
        }

        $history->refresh();

        return response()->json([
            'id' => $history->id,
            'total' => $history->total_registros,
            'success' => $history->procesados_exitosos,
            'failed' => $history->procesados_fallidos,
            'errors' => $history->errores ?? [],
            'completed' => ($history->procesados_exitosos + $history->procesados_fallidos) >= $history->total_registros,
            'updated_at' => $history->updated_at?->toDateTimeString(),
        ]);
    }

    private function extractPlaceholders(DocumentConfiguration $documentConfiguration): array
    {
        $headers = ['email'];

        if ($documentConfiguration->text_elements) {
            foreach ($documentConfiguration->text_elements as $element) {
                if (isset($element['text']) && preg_match_all('/\{(\w+)\}/', $element['text'], $matches)) {
                    foreach ($matches[1] as $match) {
                        if (!in_array($match, $headers)) {
                            $headers[] = $match;
                        }
                    }
                }
            }
        }

        if (!empty($documentConfiguration->sample_data)) {
            foreach (array_keys($documentConfiguration->sample_data) as $key) {
                if (!in_array($key, $headers)) {
                    $headers[] = $key;
                }
            }
        }

        return $headers;
    }
}
