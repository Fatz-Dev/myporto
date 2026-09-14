<?php

use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\SecuritySettingsController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware('auth')->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('settings/password', [PasswordController::class, 'edit'])->name('password.edit');
    Route::put('settings/password', [PasswordController::class, 'update'])->name('password.update');

    Route::get('settings/appearance', function () {
        return Inertia::render('settings/Appearance');
    })->name('appearance');

    // Security & Biometrics Settings
    Route::get('dashboard/settings', [SecuritySettingsController::class, 'index'])->name('dashboard.settings');
    Route::get('settings/security', [SecuritySettingsController::class, 'index'])->name('settings.security');
    Route::post('settings/security/face-toggle', [SecuritySettingsController::class, 'toggleFaceRecognition'])->name('settings.security.face-toggle');
    Route::post('settings/security/face-enroll', [SecuritySettingsController::class, 'enrollFace'])->name('settings.security.face-enroll');
    Route::delete('settings/security/face-delete', [SecuritySettingsController::class, 'deleteFace'])->name('settings.security.face-delete');
});