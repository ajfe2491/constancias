<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use Illuminate\View\View;

class CertificateVerificationController extends Controller
{
    public function show(string $uuid): View
    {
        $certificate = Certificate::with(['documentConfiguration.event'])
            ->where('uuid', $uuid)
            ->firstOrFail();

        return view('certificates.verify', [
            'certificate' => $certificate,
        ]);
    }
}
