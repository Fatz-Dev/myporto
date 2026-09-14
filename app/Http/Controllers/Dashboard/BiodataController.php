<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\SocialLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class BiodataController extends Controller
{
    /**
     * Display the Biodata management page.
     */
    public function index(): Response
    {
        $profile = Profile::with(['socialLinks' => function ($query) {
            $query->orderBy('order');
        }])->first();

        if (!$profile) {
            $profile = Profile::create([
                'user_id' => auth()->id(),
                'full_name' => auth()->user()?->name ?? 'FatzDev',
                'professional_title' => 'Full Stack Software Engineer',
                'email' => auth()->user()?->email ?? 'hello@fatzdev.com',
                'is_available_for_hire' => true,
                'availability_badge_text' => 'Available for freelance work',
                'primary_stack' => ['Laravel', 'Vue.js', 'React', 'TypeScript', 'Node.js', 'PostgreSQL'],
            ]);
        }

        return Inertia::render('Dashboard/Biodata/Index', [
            'profile' => $profile,
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
            ],
        ]);
    }

    /**
     * Update the profile information.
     */
    public function update(Request $request)
    {
        $profile = Profile::firstOrFail();

        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'professional_title' => 'required|string|max:255',
            'hero_headline' => 'nullable|string|max:255',
            'hero_subheadline' => 'nullable|string|max:2000',
            'bio_summary_1' => 'nullable|string|max:5000',
            'bio_summary_2' => 'nullable|string|max:5000',
            'avatar_url' => 'nullable|string|max:1000',
            'avatar_file' => 'nullable|image|max:3072',
            'cv_url' => 'nullable|string|max:1000',
            'cv_file' => 'nullable|file|mimes:pdf|max:10240',
            'location' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'response_time' => 'nullable|string|max:100',
            'is_available_for_hire' => 'nullable|boolean',
            'availability_badge_text' => 'nullable|string|max:100',
            'years_experience' => 'nullable|string|max:50',
            'projects_delivered' => 'nullable|string|max:50',
            'client_satisfaction_rate' => 'nullable|string|max:50',
            'rating_score' => 'nullable|numeric|between:0,5',
            'rating_platform' => 'nullable|string|max:100',
            'primary_stack' => 'nullable|array',
            'primary_stack.*' => 'string|max:50',
        ]);

        if ($request->hasFile('avatar_file')) {
            $path = $request->file('avatar_file')->store('avatars', 'public');
            $validated['avatar_url'] = Storage::url($path);
        }

        unset($validated['avatar_file']);

        if ($request->hasFile('cv_file')) {
            $path = $request->file('cv_file')->store('cv', 'public');
            $validated['cv_url'] = Storage::url($path);
        }

        unset($validated['cv_file']);

        // Default boolean flag if not present in request
        $validated['is_available_for_hire'] = $request->boolean('is_available_for_hire');

        $profile->update($validated);

        return back()->with('success', 'Biodata profil berhasil diperbarui!');
    }

    /**
     * Store a new social media link.
     */
    public function storeSocialLink(Request $request)
    {
        $profile = Profile::firstOrFail();

        $validated = $request->validate([
            'platform' => 'required|string|max:50',
            'label' => 'required|string|max:100',
            'url' => 'required|url|max:255',
            'order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['profile_id'] = $profile->id;
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['order'] = $validated['order'] ?? (SocialLink::where('profile_id', $profile->id)->max('order') + 1);

        SocialLink::create($validated);

        return back()->with('success', 'Tautan media sosial berhasil ditambahkan!');
    }

    /**
     * Update an existing social media link.
     */
    public function updateSocialLink(Request $request, SocialLink $socialLink)
    {
        $validated = $request->validate([
            'platform' => 'required|string|max:50',
            'label' => 'required|string|max:100',
            'url' => 'required|url|max:255',
            'order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $socialLink->update($validated);

        return back()->with('success', 'Tautan media sosial berhasil diperbarui!');
    }

    /**
     * Delete a social media link.
     */
    public function destroySocialLink(SocialLink $socialLink)
    {
        $socialLink->delete();

        return back()->with('success', 'Tautan media sosial berhasil dihapus!');
    }
}
