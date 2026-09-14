<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    /**
     * Display the project management list.
     */
    public function index(): Response
    {
        $projects = Project::orderBy('order')->orderByDesc('year')->get();

        return Inertia::render('Dashboard/Project/Index', [
            'projects' => $projects,
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
            ],
        ]);
    }

    /**
     * Store a newly created project.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category_label' => 'nullable|string|max:100',
            'year' => 'nullable|string|max:10',
            'short_description' => 'required|string',
            'full_content' => 'nullable|string',
            'thumbnail_url' => 'nullable|string|max:500',
            'live_preview_url' => 'nullable|string|max:500',
            'github_url' => 'nullable|string|max:500',
            'tech_stack' => 'nullable|array',
            'tech_stack.*' => 'string|max:100',
            'is_featured' => 'nullable|boolean',
            'order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['slug'] = Str::slug($validated['title']);
        $validated['category_label'] = $validated['category_label'] ?? 'Full Stack';
        $validated['year'] = $validated['year'] ?? date('Y');
        $validated['is_featured'] = $request->boolean('is_featured', false);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['order'] = $validated['order'] ?? (Project::max('order') + 1);

        // Ensure unique slug
        $baseSlug = $validated['slug'];
        $counter = 1;
        while (Project::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $baseSlug . '-' . $counter++;
        }

        Project::create($validated);

        return back()->with('success', 'Proyek berhasil ditambahkan!');
    }

    /**
     * Update the specified project.
     */
    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category_label' => 'nullable|string|max:100',
            'year' => 'nullable|string|max:10',
            'short_description' => 'required|string',
            'full_content' => 'nullable|string',
            'thumbnail_url' => 'nullable|string|max:500',
            'live_preview_url' => 'nullable|string|max:500',
            'github_url' => 'nullable|string|max:500',
            'tech_stack' => 'nullable|array',
            'tech_stack.*' => 'string|max:100',
            'is_featured' => 'nullable|boolean',
            'order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['slug'] = Str::slug($validated['title']);
        $validated['category_label'] = $validated['category_label'] ?? 'Full Stack';
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['is_active'] = $request->boolean('is_active');

        // Ensure unique slug (exclude self)
        $baseSlug = $validated['slug'];
        $counter = 1;
        while (Project::where('slug', $validated['slug'])->where('id', '!=', $project->id)->exists()) {
            $validated['slug'] = $baseSlug . '-' . $counter++;
        }

        $project->update($validated);

        return back()->with('success', 'Proyek berhasil diperbarui!');
    }

    /**
     * Remove the specified project.
     */
    public function destroy(Project $project)
    {
        $project->delete();

        return back()->with('success', 'Proyek berhasil dihapus!');
    }

    /**
     * Toggle the active status of the specified project.
     */
    public function toggle(Project $project)
    {
        $project->update([
            'is_active' => !$project->is_active,
        ]);

        return back()->with('success', 'Status visibilitas proyek berhasil diubah!');
    }

    /**
     * Toggle the featured status of the specified project.
     */
    public function toggleFeatured(Project $project)
    {
        $project->update([
            'is_featured' => !$project->is_featured,
        ]);

        return back()->with('success', 'Status featured proyek berhasil diubah!');
    }
}
