<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\DocumentConfigurationController;
use App\Http\Controllers\CertificateSendingController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::post('document-configurations/{document_configuration}/live-preview', [\App\Http\Controllers\DocumentConfigurationController::class, 'preview'])->name('document-configurations.preview');
    Route::get('document-configurations/{document_configuration}/stream-pdf', [\App\Http\Controllers\DocumentConfigurationController::class, 'streamPdf'])->name('document-configurations.stream-pdf');
    Route::get('document-configurations/{document_configuration}/background-image', [\App\Http\Controllers\DocumentConfigurationController::class, 'backgroundImage'])->name('document-configurations.background-image');
    Route::resource('document-configurations', \App\Http\Controllers\DocumentConfigurationController::class);
    Route::get('/certificate-sending/{history}/status', [CertificateSendingController::class, 'status'])->name('certificate-sending.status');
    Route::resource('certificate-sending', CertificateSendingController::class)->only(['index', 'create', 'store', 'show']);
    Route::get('/certificate-sending/{document_configuration}/template', [CertificateSendingController::class, 'downloadTemplate'])->name('certificate-sending.template');
    Route::resource('events', \App\Http\Controllers\EventController::class);
});

Route::get('/test-email', function () {
    try {
        Illuminate\Support\Facades\Mail::raw('Test email content', function ($message) {
            $message->to('erick@example.com') // Replace with a valid email if needed, or check logs if using log driver
                ->subject('Test Email');
        });
        return 'Email sent';
    } catch (\Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
});

require __DIR__ . '/auth.php';
