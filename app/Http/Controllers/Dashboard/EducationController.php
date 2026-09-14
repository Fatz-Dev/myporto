<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Education;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EducationController extends Controller
{
    /**
     * Display the education management list.
     */
    public function index(): Response
    {
        $educations = Education::orderBy('order')->orderByDesc('start_year')->get();

        return Inertia::render('Dashboard/Education/Index', [
            'educations' => $educations,
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
            ],
        ]);
    }

    /**
     * Store a newly created education record.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'degree_title' => 'required|string|max:255',
            'institution_name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'start_year' => 'required|string|max:20',
            'end_year' => 'nullable|string|max:20',
            'grade' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'skills_acquired' => 'nullable|array',
            'skills_acquired.*' => 'string|max:100',
            'icon_class' => 'nullable|string|max:100',
            'credential_id' => 'nullable|string|max:100',
            'credential_url' => 'nullable|string|max:500',
            'image_url' => 'nullable|string|max:500',
            'order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['location'] = $validated['location'] ?? '';
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['order'] = $validated['order'] ?? (Education::max('order') + 1);

        Education::create($validated);

        return back()->with('success', 'Riwayat pendidikan berhasil ditambahkan!');
    }

    /**
     * Update the specified education record.
     */
    public function update(Request $request, Education $education)
    {
        $validated = $request->validate([
            'degree_title' => 'required|string|max:255',
            'institution_name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'start_year' => 'required|string|max:20',
            'end_year' => 'nullable|string|max:20',
            'grade' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'skills_acquired' => 'nullable|array',
            'skills_acquired.*' => 'string|max:100',
            'icon_class' => 'nullable|string|max:100',
            'credential_id' => 'nullable|string|max:100',
            'credential_url' => 'nullable|string|max:500',
            'image_url' => 'nullable|string|max:500',
            'order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['location'] = $validated['location'] ?? '';
        $validated['is_active'] = $request->boolean('is_active');

        $education->update($validated);

        return back()->with('success', 'Riwayat pendidikan berhasil diperbarui!');
    }

    /**
     * Remove the specified education record.
     */
    public function destroy(Education $education)
    {
        $education->delete();

        return back()->with('success', 'Riwayat pendidikan berhasil dihapus!');
    }

    /**
     * Toggle the active status of the specified education record.
     */
    public function toggle(Education $education)
    {
        $education->update([
            'is_active' => !$education->is_active,
        ]);

        return back()->with('success', 'Status visibilitas pendidikan berhasil diubah!');
    }
}
