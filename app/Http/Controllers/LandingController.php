<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\Consultation;
use App\Models\ContactMessage;
use App\Models\Education;
use App\Models\Experience;
use App\Models\LandingSection;
use App\Models\Profile;
use App\Models\Project;
use App\Models\SkillCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class LandingController extends Controller
{
    /**
     * Shared common landing page data (Profile and Navbar/Section settings).
     */
    private function getCommonData(): array
    {
        $landingSections = LandingSection::orderBy('order')->get();
        $profile = Profile::with('socialLinks')->first();

        return [
            'landingSections' => $landingSections,
            'profile' => $profile,
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
            ],
        ];
    }

    /**
     * Display the Home / Hero page.
     */
    public function index(): Response
    {
        return Inertia::render('Landing/Index', $this->getCommonData());
    }

    /**
     * Display the About page.
     */
    public function about(): Response
    {
        return Inertia::render('Landing/about/Index', $this->getCommonData());
    }

    /**
     * Display the Skills & Expertise page.
     */
    public function skills(): Response
    {
        $data = $this->getCommonData();
        $data['skillCategories'] = SkillCategory::with(['skills' => function ($q) {
            $q->where('is_active', true)->orderBy('order');
        }])->where('is_active', true)->orderBy('order')->get();

        return Inertia::render('Landing/skills/Index', $data);
    }

    /**
     * Display the Education & Certifications page.
     */
    public function education(): Response
    {
        $data = $this->getCommonData();
        $data['educations'] = Education::where('is_active', true)->orderBy('order')->get();

        return Inertia::render('Landing/education/Index', $data);
    }

    /**
     * Display the Portfolio / Projects page.
     */
    public function portfolio(): Response
    {
        $data = $this->getCommonData();
        $data['projects'] = Project::where('is_active', true)->orderBy('order')->get();

        return Inertia::render('Landing/portfolio/Index', $data);
    }

    /**
     * Display the Experience & Career page.
     */
    public function experience(): Response
    {
        $data = $this->getCommonData();
        $data['experiences'] = Experience::where('is_active', true)->orderBy('order')->get();

        return Inertia::render('Landing/experience/Index', $data);
    }

    /**
     * Display the Blog / Articles page.
     */
    public function blog(Request $request): Response
    {
        $data = $this->getCommonData();

        $query = Blog::where('is_published', true);

        if ($request->filled('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $data['blogs'] = $query->latest('published_at')->get();
        $data['categories'] = Blog::where('is_published', true)
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->toArray();
        $data['currentCategory'] = $request->input('category', 'all');
        $data['search'] = $request->input('search', '');

        return Inertia::render('Landing/blog/Index', $data);
    }

    /**
     * Display single Blog detail page.
     */
    public function blogDetail(string $slug): Response
    {
        $data = $this->getCommonData();
        $blog = Blog::with('author')->where('slug', $slug)->where('is_published', true)->firstOrFail();

        $relatedBlogs = Blog::where('is_published', true)
            ->where('id', '!=', $blog->id)
            ->where('category', $blog->category)
            ->latest('published_at')
            ->take(3)
            ->get();

        if ($relatedBlogs->count() < 2) {
            $moreBlogs = Blog::where('is_published', true)
                ->where('id', '!=', $blog->id)
                ->whereNotIn('id', $relatedBlogs->pluck('id'))
                ->latest('published_at')
                ->take(3 - $relatedBlogs->count())
                ->get();
            $relatedBlogs = $relatedBlogs->merge($moreBlogs);
        }

        $data['blog'] = $blog;
        $data['relatedBlogs'] = $relatedBlogs;

        return Inertia::render('Landing/blog/DetailBlog', $data);
    }

    /**
     * Display the Contact & Inquiries page.
     */
    public function contact(): Response
    {
        return Inertia::render('Landing/contact/Index', $this->getCommonData());
    }

    /**
     * Handle incoming contact form submission.
     */
    public function submitContact(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        ContactMessage::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'status' => 'unread',
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Thank you! Your message has been sent successfully. I will get back to you within 24 hours.');
    }

    /**
     * Handle incoming appointment / discovery call booking.
     */
    public function bookConsultation(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:50',
            'service_type' => 'required|string|max:100',
            'preferred_date' => 'required|date|after_or_equal:today',
            'notes' => 'nullable|string|max:2000',
        ]);

        Consultation::create([
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'service_type' => $validated['service_type'],
            'preferred_date' => $validated['preferred_date'],
            'notes' => $validated['notes'] ?? null,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Booking confirmed! I will reach out within 24 hours to confirm the session details.');
    }
    /**
     * Download or view the curriculum vitae / resume document.
     */
    public function downloadCv()
    {
        $profile = Profile::first();
        $safeName = $profile && !empty($profile->full_name)
            ? preg_replace('/[^A-Za-z0-9_\-]/', '_', $profile->full_name)
            : 'FatzDev';
        $downloadFileName = 'CV_' . $safeName . '.pdf';

        if ($profile && !empty($profile->cv_url)) {
            // Check if it's a relative storage path (e.g. /storage/cv/file.pdf)
            if (str_starts_with($profile->cv_url, '/storage/')) {
                $relativePath = substr($profile->cv_url, strlen('/storage/'));
                if (Storage::disk('public')->exists($relativePath)) {
                    return Storage::disk('public')->download($relativePath, $downloadFileName);
                }
            }

            // Check if it's external URL
            if (filter_var($profile->cv_url, FILTER_VALIDATE_URL)) {
                return redirect()->away($profile->cv_url);
            }
        }

        // Check default storage CV
        $defaultStoragePath = 'cv/CV_FatzDev.pdf';
        if (Storage::disk('public')->exists($defaultStoragePath)) {
            return Storage::disk('public')->download($defaultStoragePath, $downloadFileName);
        }

        // Fallback to public asset
        $publicAsset = public_path('assets/CV_FatzDev.pdf');
        if (file_exists($publicAsset)) {
            return response()->download($publicAsset, $downloadFileName);
        }

        return back()->with('error', 'CV file is not currently available.');
    }
}
