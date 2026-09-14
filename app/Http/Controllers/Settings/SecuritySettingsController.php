<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SecuritySettingsController extends Controller
{
    /**
     * Display the security & biometric settings page.
     */
    public function index(): Response
    {
        $adminEmail = env('ADMIN_ALLOWED_EMAIL', '');
        
        // Check face recognition enabled (DB takes priority, fallback to .env)
        $envSetting = env('FACE_RECOGNITION_ENABLED', true);
        $rawSetting = AppSetting::get('face_recognition_enabled', $envSetting);
        $faceRecognitionEnabled = filter_var($rawSetting, FILTER_VALIDATE_BOOLEAN);

        // Check if biometric is enrolled
        $descriptorSetting = AppSetting::where('key', 'face_descriptor')->first();
        $hasDescriptor = !empty($descriptorSetting?->value);
        $hasImageFallback = file_exists(public_path('admin-face.jpg'));
        $isEnrolled = $hasDescriptor || $hasImageFallback;

        $enrolledAt = null;
        if ($descriptorSetting && $descriptorSetting->updated_at) {
            $enrolledAt = $descriptorSetting->updated_at->diffForHumans();
        } elseif ($hasImageFallback) {
            $enrolledAt = 'File sistem terdeteksi';
        }

        return Inertia::render('Dashboard/setting/Settings', [
            'adminEmail' => $adminEmail,
            'faceRecognitionEnabled' => $faceRecognitionEnabled,
            'faceEnrolled' => $isEnrolled,
            'faceEnrolledAt' => $enrolledAt,
            'status' => session('status'),
        ]);
    }

    /**
     * Toggle Face Recognition on or off.
     */
    public function toggleFaceRecognition(Request $request)
    {
        $validated = $request->validate([
            'enabled' => ['required', 'boolean'],
        ]);

        $isEnabled = (bool) $validated['enabled'];
        AppSetting::set('face_recognition_enabled', $isEnabled ? '1' : '0');

        $statusMessage = $isEnabled
            ? 'Face Recognition aktif. Verifikasi biometrik akan diminta saat login.'
            : 'Face Recognition dinonaktifkan. Login hanya akan membutuhkan OTP email.';

        return back()->with('status', $statusMessage);
    }

    /**
     * Enroll new face descriptor from webcam capture.
     */
    public function enrollFace(Request $request)
    {
        $validated = $request->validate([
            'descriptor' => ['required', 'array'],
            'image' => ['nullable', 'string'],
        ]);

        // Save descriptor array to app_settings
        AppSetting::set('face_descriptor', $validated['descriptor']);

        // Save backup image if provided
        if (!empty($validated['image'])) {
            try {
                $imageData = $validated['image'];
                if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $type)) {
                    $imageData = substr($imageData, strpos($imageData, ',') + 1);
                    $imageData = base64_decode($imageData);
                    if ($imageData !== false) {
                        file_put_contents(public_path('admin-face.jpg'), $imageData);
                    }
                }
            } catch (\Exception $e) {
                // Ignore image write failure if descriptor is saved
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Biometrik wajah berhasil disimpan dan siap digunakan untuk login.',
        ]);
    }

    /**
     * Delete existing face biometric reference.
     */
    public function deleteFace(Request $request)
    {
        AppSetting::forget('face_descriptor');

        if (file_exists(public_path('admin-face.jpg'))) {
            @unlink(public_path('admin-face.jpg'));
        }

        return back()->with('status', 'Data biometrik wajah berhasil dihapus.');
    }
}