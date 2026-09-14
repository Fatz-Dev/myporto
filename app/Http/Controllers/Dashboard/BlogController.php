<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class BlogController extends Controller
{
    /**
     * Display blog management dashboard.
     */
    public function index(): Response
    {
        $blogs = Blog::latest('created_at')->get();
        $categories = Blog::select('category')
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->toArray();

        $stats = [
            'total_blogs' => $blogs->count(),
            'published_blogs' => $blogs->where('is_published', true)->count(),
            'draft_blogs' => $blogs->where('is_published', false)->count(),
            'total_categories' => count($categories),
        ];

        return Inertia::render('Dashboard/Blog/Index', [
            'blogs' => $blogs,
            'categories' => $categories,
            'stats' => $stats,
        ]);
    }

    /**
     * Store a new blog post.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:blogs,slug',
            'category' => 'required|string|max:100',
            'excerpt' => 'nullable|string|max:1000',
            'content' => 'nullable|string',
            'read_time' => 'nullable|string|max:50',
            'published_at' => 'nullable|date',
            'thumbnail_url' => 'nullable|string|max:1000',
            'is_published' => 'boolean',
        ]);

        // Auto-generate slug if empty
        if (empty($validated['slug'])) {
            $baseSlug = Str::slug($validated['title']);
            $slug = $baseSlug;
            $counter = 1;
            while (Blog::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }
            $validated['slug'] = $slug;
        } else {
            $validated['slug'] = Str::slug($validated['slug']);
        }

        // Auto calculate read time if empty
        if (empty($validated['read_time']) && !empty($validated['content'])) {
            $words = str_word_count(strip_tags($validated['content']));
            $minutes = max(1, (int) ceil($words / 200));
            $validated['read_time'] = $minutes . ' min read';
        }

        if (empty($validated['published_at'])) {
            $validated['published_at'] = now()->format('Y-m-d');
        }

        $validated['author_id'] = Auth::id();
        $validated['is_published'] = $request->boolean('is_published', true);

        Blog::create($validated);

        return back()->with('success', 'Artikel blog berhasil ditambahkan.');
    }

    /**
     * Update an existing blog post.
     */
    public function update(Request $request, Blog $blog): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:blogs,slug,' . $blog->id,
            'category' => 'required|string|max:100',
            'excerpt' => 'nullable|string|max:1000',
            'content' => 'nullable|string',
            'read_time' => 'nullable|string|max:50',
            'published_at' => 'nullable|date',
            'thumbnail_url' => 'nullable|string|max:1000',
            'is_published' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['slug']);

        if (empty($validated['read_time']) && !empty($validated['content'])) {
            $words = str_word_count(strip_tags($validated['content']));
            $minutes = max(1, (int) ceil($words / 200));
            $validated['read_time'] = $minutes . ' min read';
        }

        $validated['is_published'] = $request->boolean('is_published', true);

        $blog->update($validated);

        return back()->with('success', 'Artikel blog berhasil diperbarui.');
    }

    /**
     * Delete a blog post.
     */
    public function destroy(Blog $blog): RedirectResponse
    {
        $blog->delete();

        return back()->with('success', 'Artikel blog berhasil dihapus.');
    }

    /**
     * Toggle publication status.
     */
    public function togglePublish(Blog $blog): RedirectResponse
    {
        $blog->update([
            'is_published' => !$blog->is_published,
        ]);

        $status = $blog->is_published ? 'diterbitkan' : 'diarsipkan sebagai draf';
        return back()->with('success', "Artikel '{$blog->title}' sekarang {$status}.");
    }
}