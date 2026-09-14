<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Experience;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ExperienceController extends Controller
{
    /**
     * Display the experience management list.
     */
    public function index(): Response
    {
        $experiences = Experience::orderBy('order')->orderByDesc('start_period')->get();

        return Inertia::render('Dashboard/Experience/Index', [
            'experiences' => $experiences,
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
            ],
        ]);
    }

    /**
     * Store a newly created experience record.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'role_title' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'start_period' => 'required|string|max:50',
            'end_period' => 'nullable|string|max:50',
            'is_current' => 'nullable|boolean',
            'description' => 'nullable|string',
            'company_logo_url' => 'nullable|string|max:500',
            'order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['location'] = $validated['location'] ?? 'Remote';
        $validated['is_current'] = $request->boolean('is_current', false);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['order'] = $validated['order'] ?? (Experience::max('order') + 1);

        if ($validated['is_current']) {
            $validated['end_period'] = null;
        }

        Experience::create($validated);

        return back()->with('success', 'Pengalaman kerja berhasil ditambahkan!');
    }

    /**
     * Update the specified experience record.
     */
    public function update(Request $request, Experience $experience)
    {
        $validated = $request->validate([
            'role_title' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'start_period' => 'required|string|max:50',
            'end_period' => 'nullable|string|max:50',
            'is_current' => 'nullable|boolean',
            'description' => 'nullable|string',
            'company_logo_url' => 'nullable|string|max:500',
            'order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['location'] = $validated['location'] ?? 'Remote';
        $validated['is_current'] = $request->boolean('is_current');
        $validated['is_active'] = $request->boolean('is_active');

        if ($validated['is_current']) {
            $validated['end_period'] = null;
        }

        $experience->update($validated);

        return back()->with('success', 'Pengalaman kerja berhasil diperbarui!');
    }

    /**
     * Remove the specified experience record.
     */
    public function destroy(Experience $experience)
    {
        $experience->delete();

        return back()->with('success', 'Pengalaman kerja berhasil dihapus!');
    }

    /**
     * Toggle the active status of the specified experience record.
     */
    public function toggle(Experience $experience)
    {
        $experience->update([
            'is_active' => !$experience->is_active,
        ]);

        return back()->with('success', 'Status visibilitas pengalaman kerja berhasil diubah!');
    }
}
