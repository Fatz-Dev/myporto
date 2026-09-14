<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use App\Mail\LoginOtpMail;
use App\Models\User;
use App\Models\AppSetting;
use Laravel\Socialite\Facades\Socialite;
use Inertia\Inertia;

class PersonalLoginController extends Controller
{
    /**
     * Send OTP to the configured admin email.
     */
    public function sendOtp(Request $request)
    {
        $adminEmail = env('ADMIN_ALLOWED_EMAIL');
        
        if (!$adminEmail) {
            return response()->json(['message' => 'Admin email not configured.'], 500);
        }

        // Generate a 6-digit OTP
        $otp = rand(100000, 999999);
        
        // Cache the OTP for 5 minutes
        Cache::put('login_otp_' . $adminEmail, $otp, now()->addMinutes(5));

        // Send Email
        try {
            Mail::to($adminEmail)->send(new LoginOtpMail($otp));
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to send OTP email: ' . $e->getMessage()], 500);
        }

        return response()->json([
            'message' => 'OTP sent successfully to your email.'
        ]);
    }

    /**
     * Verify the entered OTP.
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|numeric|digits:6',
        ]);

        $adminEmail = env('ADMIN_ALLOWED_EMAIL');
        $cachedOtp = Cache::get('login_otp_' . $adminEmail);

        if (!$cachedOtp || $cachedOtp != $request->otp) {
            return response()->json([
                'message' => 'Invalid or expired OTP.'
            ], 422);
        }

        // Clear OTP
        Cache::forget('login_otp_' . $adminEmail);

        // Check if Face Recognition is enabled (DB setting takes priority)
        $rawSetting = AppSetting::get('face_recognition_enabled', env('FACE_RECOGNITION_ENABLED', true));
        $faceRecognitionEnabled = filter_var($rawSetting, FILTER_VALIDATE_BOOLEAN);

        if (!$faceRecognitionEnabled) {
            // Login directly
            $user = User::where('email', $adminEmail)->first();
            if ($user) {
                Auth::login($user);
                return response()->json([
                    'message' => 'Login successful.',
                    'redirect' => route('dashboard'),
                    'face_required' => false
                ]);
            }
        }

        // Face recognition is required
        $descriptorRaw = AppSetting::get('face_descriptor');
        $descriptor = null;
        if (!empty($descriptorRaw)) {
            $decoded = json_decode($descriptorRaw, true);
            if (is_array($decoded)) {
                $descriptor = $decoded;
            }
        }

        return response()->json([
            'message' => 'OTP verified. Proceed to biometric scan.',
            'face_required' => true,
            'token' => encrypt($adminEmail),
            'descriptor' => $descriptor
        ]);
    }

    /**
     * Verify the biometric result.
     */
    public function verifyBiometric(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'confidence' => 'required|numeric',
        ]);

        try {
            $email = decrypt($request->token);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Invalid token.'], 400);
        }

        $adminEmail = env('ADMIN_ALLOWED_EMAIL');

        if ($email !== $adminEmail) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        // Check confidence score (Euclidean distance from face-api.js)
        // Usually, a distance < 0.6 is a good match in face-api.js, threshold 0.5 recommended
        if ($request->confidence > 0.5) {
            return response()->json(['message' => 'Face not recognized.'], 422);
        }

        $user = User::where('email', $adminEmail)->first();
        
        if ($user) {
            Auth::login($user);
            return response()->json([
                'message' => 'Login successful.',
                'redirect' => route('dashboard')
            ]);
        }

        return response()->json(['message' => 'User not found.'], 404);
    }

    /**
     * Redirect to Google SSO.
     */
    public function redirectToGoogle()
    {
        try {
            return Socialite::driver('google')->redirect();
        } catch (\Throwable $e) {
            return redirect('/login')->withErrors(['google' => 'Gagal mengarahkan ke Google SSO: ' . $e->getMessage()]);
        }
    }

    /**
     * Handle Google Callback.
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect('/login')->withErrors(['google' => 'Failed to login with Google.']);
        }

        $adminEmail = env('ADMIN_ALLOWED_EMAIL');

        if ($googleUser->getEmail() !== $adminEmail) {
            return redirect('/login')->withErrors(['google' => 'Unauthorized email address.']);
        }

        $user = User::where('email', $adminEmail)->first();

        if ($user) {
            Auth::login($user);
            return redirect()->intended('/dashboard');
        }

        return redirect('/login')->withErrors(['google' => 'User not found in system.']);
    }
}