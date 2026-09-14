<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\LandingSection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ControlPageController extends Controller
{
    /**
     * Display landing sections and page controls management dashboard.
     */
    public function index(): Response
    {
        $sections = LandingSection::orderBy('order')->get();

        $stats = [
            'total_sections' => $sections->count(),
            'visible_sections' => $sections->where('is_visible', true)->count(),
            'navbar_sections' => $sections->where('show_in_navbar', true)->count(),
            'hidden_sections' => $sections->where('is_visible', false)->count(),
        ];

        return Inertia::render('Dashboard/ControlPage/Index', [
            'sections' => $sections,
            'stats' => $stats,
        ]);
    }

    /**
     * Update section details (name, labels, order, custom titles).
     */
    public function update(Request $request, LandingSection $section): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nav_label' => 'nullable|string|max:100',
            'custom_title' => 'nullable|string|max:255',
            'custom_subtitle' => 'nullable|string|max:500',
            'order' => 'required|integer|min:0',
            'is_visible' => 'boolean',
            'show_in_navbar' => 'boolean',
        ]);

        $section->update($validated);

        return back()->with('success', "Pengaturan seksi '{$section->name}' berhasil diperbarui.");
    }

    /**
     * Toggle visibility of a landing section.
     */
    public function toggleVisibility(LandingSection $section): RedirectResponse
    {
        $section->update([
            'is_visible' => !$section->is_visible,
        ]);

        $status = $section->is_visible ? 'ditampilkan' : 'disembunyikan';
        return back()->with('success', "Seksi '{$section->name}' sekarang {$status}.");
    }

    /**
     * Toggle visibility in navbar of a landing section.
     */
    public function toggleNavbar(LandingSection $section): RedirectResponse
    {
        $section->update([
            'show_in_navbar' => !$section->show_in_navbar,
        ]);

        $status = $section->show_in_navbar ? 'ditampilkan di navbar' : 'disembunyikan dari navbar';
        return back()->with('success', "Seksi '{$section->name}' sekarang {$status}.");
    }

    /**
     * Batch update ordering of sections.
     */
    public function reorder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'orders' => 'required|array',
            'orders.*.id' => 'required|integer|exists:landing_sections,id',
            'orders.*.order' => 'required|integer|min:0',
        ]);

        foreach ($validated['orders'] as $item) {
            LandingSection::where('id', $item['id'])->update(['order' => $item['order']]);
        }

        return back()->with('success', 'Urutan seksi berhasil diperbarui.');
    }
}