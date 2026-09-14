<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\Skill;
use App\Models\SkillCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SkillController extends Controller
{
    /**
     * Display the Skill management page.
     */
    public function index(): Response
    {
        $profile = Profile::first();
        $skillCategories = SkillCategory::with(['skills' => function ($query) {
            $query->orderBy('order');
        }])->orderBy('order')->get();

        return Inertia::render('Dashboard/Skill/Index', [
            'profile' => $profile,
            'skillCategories' => $skillCategories,
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
            ],
        ]);
    }

    /**
     * Update the primary tech stack tags for the profile.
     */
    public function updatePrimaryStack(Request $request)
    {
        $profile = Profile::firstOrFail();

        $validated = $request->validate([
            'primary_stack' => 'present|array',
            'primary_stack.*' => 'string|max:50',
        ]);

        $profile->update([
            'primary_stack' => array_values(array_filter($validated['primary_stack'])),
        ]);

        return back()->with('success', 'Primary Tech Stack berhasil diperbarui!');
    }

    /**
     * Store a new skill.
     */
    public function storeSkill(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:skill_categories,id',
            'name' => 'required|string|max:100',
            'icon_class' => 'nullable|string|max:100',
            'subtitle' => 'nullable|string|max:100',
            'level' => 'nullable|string|max:50',
            'core_tools' => 'nullable|array',
            'core_tools.*' => 'string|max:50',
            'discipline_protocol' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'order' => 'nullable|integer',
            'is_featured' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['order'] = $validated['order'] ?? (Skill::where('category_id', $validated['category_id'])->max('order') + 1);

        Skill::create($validated);

        return back()->with('success', 'Keahlian baru berhasil ditambahkan!');
    }

    /**
     * Update an existing skill.
     */
    public function updateSkill(Request $request, Skill $skill)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:skill_categories,id',
            'name' => 'required|string|max:100',
            'icon_class' => 'nullable|string|max:100',
            'subtitle' => 'nullable|string|max:100',
            'level' => 'nullable|string|max:50',
            'core_tools' => 'nullable|array',
            'core_tools.*' => 'string|max:50',
            'discipline_protocol' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'order' => 'nullable|integer',
            'is_featured' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['is_active'] = $request->boolean('is_active');

        $skill->update($validated);

        return back()->with('success', 'Keahlian berhasil diperbarui!');
    }

    /**
     * Delete a skill.
     */
    public function destroySkill(Skill $skill)
    {
        $skill->delete();

        return back()->with('success', 'Keahlian berhasil dihapus!');
    }

    /**
     * Toggle active status of a skill.
     */
    public function toggleActive(Skill $skill)
    {
        $skill->update([
            'is_active' => !$skill->is_active,
        ]);

        return back()->with('success', 'Status keahlian berhasil diperbarui!');
    }

    /**
     * Store a new skill category.
     */
    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'icon_class' => 'nullable|string|max:100',
            'subtitle' => 'nullable|string|max:100',
            'badge_label' => 'nullable|string|max:50',
            'category_type' => 'required|in:technical,philosophy',
            'description' => 'nullable|string|max:1000',
            'order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['slug'] = \Illuminate\Support\Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['order'] = $validated['order'] ?? (SkillCategory::max('order') + 1);

        SkillCategory::create($validated);

        return back()->with('success', 'Kategori skill berhasil ditambahkan!');
    }

    /**
     * Update a skill category.
     */
    public function updateCategory(Request $request, SkillCategory $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'icon_class' => 'nullable|string|max:100',
            'subtitle' => 'nullable|string|max:100',
            'badge_label' => 'nullable|string|max:50',
            'category_type' => 'required|in:technical,philosophy',
            'description' => 'nullable|string|max:1000',
            'order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['slug'] = \Illuminate\Support\Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active');

        $category->update($validated);

        return back()->with('success', 'Kategori skill berhasil diperbarui!');
    }

    /**
     * Delete a skill category.
     */
    public function destroyCategory(SkillCategory $category)
    {
        $category->skills()->delete();
        $category->delete();

        return back()->with('success', 'Kategori beserta keahlian di dalamnya berhasil dihapus!');
    }
}
