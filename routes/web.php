<?php

use App\Http\Controllers\CertificateVerificationController;
use App\Http\Controllers\CertificateResendController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\DocumentConfigurationController;
use App\Http\Controllers\CertificateSendingController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/verificar/{uuid}', [CertificateVerificationController::class, 'show'])
    ->name('certificates.verify');
Route::get('/verificar/{uuid}/preview', [CertificateVerificationController::class, 'preview'])
    ->name('certificates.preview');

Route::post('/certificates/{certificate}/resend', [CertificateResendController::class, 'store'])
    ->middleware('auth')
    ->name('certificates.resend');
Route::put('/certificates/{certificate}/share', [\App\Http\Controllers\CertificateShareController::class, 'update'])
    ->middleware('auth')
    ->name('certificates.share');
Route::put('/events/{event}/share', [\App\Http\Controllers\EventShareController::class, 'update'])
    ->middleware('auth')
    ->name('events.share');
Route::put('/document-configurations/{document_configuration}/share', [\App\Http\Controllers\DocumentConfigurationShareController::class, 'update'])
    ->middleware('auth')
    ->name('document-configurations.share');

Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'permission:ver dashboard'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::post('document-configurations/{document_configuration}/live-preview', [\App\Http\Controllers\DocumentConfigurationController::class, 'preview'])->name('document-configurations.preview');
    Route::get('document-configurations/{document_configuration}/stream-pdf', [\App\Http\Controllers\DocumentConfigurationController::class, 'streamPdf'])->name('document-configurations.stream-pdf');
    Route::get('document-configurations/{document_configuration}/background-image', [\App\Http\Controllers\DocumentConfigurationController::class, 'backgroundImage'])->name('document-configurations.background-image');
    Route::get('document-configurations/{document_configuration}/copy', [\App\Http\Controllers\DocumentConfigurationController::class, 'copy'])->name('document-configurations.copy');
    Route::post('document-configurations/{document_configuration}/copy', [\App\Http\Controllers\DocumentConfigurationController::class, 'storeCopy'])->name('document-configurations.copy.store');
    Route::resource('document-configurations', \App\Http\Controllers\DocumentConfigurationController::class);
    Route::get('/certificate-sending/{history}/status', [CertificateSendingController::class, 'status'])->name('certificate-sending.status');
    Route::resource('certificate-sending', CertificateSendingController::class)
        ->only(['index', 'create', 'store', 'show'])
        ->parameters(['certificate-sending' => 'history']);
    Route::get('/certificate-sending/{history}/csv', [CertificateSendingController::class, 'downloadCsv'])
        ->name('certificate-sending.csv');
    Route::get('/certificate-sending/{document_configuration}/template', [CertificateSendingController::class, 'downloadTemplate'])->name('certificate-sending.template');
    Route::post('/events/{event}/toggle-active', [EventController::class, 'toggleActive'])
        ->name('events.toggle-active');
    Route::resource('events', \App\Http\Controllers\EventController::class);

    // User & Role Management
    Route::resource('users', \App\Http\Controllers\UserController::class)->middleware('permission:gestionar usuarios');
    Route::resource('roles', \App\Http\Controllers\RoleController::class)->middleware('permission:gestionar roles');
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
