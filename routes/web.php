<?php

use App\Http\Controllers\ProfileController;
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
    Route::resource('document-configurations', \App\Http\Controllers\DocumentConfigurationController::class);
    Route::resource('events', \App\Http\Controllers\EventController::class);
});

require __DIR__ . '/auth.php';
