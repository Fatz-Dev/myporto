<?php

use App\Http\Controllers\Auth\PersonalLoginController;
use App\Http\Controllers\Dashboard\BiodataController;
use App\Http\Controllers\Dashboard\SkillController;
use App\Http\Controllers\Dashboard\EducationController;
use App\Http\Controllers\Dashboard\ProjectController;
use App\Http\Controllers\Dashboard\BlogController;
use App\Http\Controllers\Dashboard\ContactController;
use App\Http\Controllers\Dashboard\ControlPageController;
use App\Http\Controllers\Dashboard\ExperienceController;
use App\Http\Controllers\LandingController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Individual Single Pages for Landing / Portfolio
Route::get('/', [LandingController::class, 'index'])->name('home');
Route::get('/about', [LandingController::class, 'about'])->name('about');
Route::get('/skills', [LandingController::class, 'skills'])->name('skills');
Route::get('/education', [LandingController::class, 'education'])->name('education');
Route::get('/portfolio', [LandingController::class, 'portfolio'])->name('portfolio');
Route::get('/experience', [LandingController::class, 'experience'])->name('experience');
Route::get('/blog', [LandingController::class, 'blog'])->name('blog');
Route::get('/blog/{slug}', [LandingController::class, 'blogDetail'])->name('blog.detail');
Route::get('/contact', [LandingController::class, 'contact'])->name('contact');

// Public Form Submissions
Route::post('/contact', [LandingController::class, 'submitContact'])->name('contact.submit');
Route::post('/consultation', [LandingController::class, 'bookConsultation'])->name('consultation.book');
Route::get('/download-cv', [LandingController::class, 'downloadCv'])->name('cv.download');

// Authenticated Dashboard Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard/Index');
    })->name('dashboard');

    // Biodata Management
    Route::get('/dashboard/biodata', [BiodataController::class, 'index'])->name('dashboard.biodata');
    Route::post('/dashboard/biodata', [BiodataController::class, 'update'])->name('dashboard.biodata.update');
    Route::post('/dashboard/biodata/social-links', [BiodataController::class, 'storeSocialLink'])->name('dashboard.biodata.social.store');
    Route::put('/dashboard/biodata/social-links/{socialLink}', [BiodataController::class, 'updateSocialLink'])->name('dashboard.biodata.social.update');
    Route::delete('/dashboard/biodata/social-links/{socialLink}', [BiodataController::class, 'destroySocialLink'])->name('dashboard.biodata.social.destroy');
    Route::redirect('/biodata', '/dashboard/biodata');

    // Skill Management
    Route::get('/dashboard/skills', [SkillController::class, 'index'])->name('dashboard.skills');
    Route::post('/dashboard/skills/primary-stack', [SkillController::class, 'updatePrimaryStack'])->name('dashboard.skills.primary_stack');
    Route::post('/dashboard/skills', [SkillController::class, 'storeSkill'])->name('dashboard.skills.store');
    Route::put('/dashboard/skills/{skill}', [SkillController::class, 'updateSkill'])->name('dashboard.skills.update');
    Route::delete('/dashboard/skills/{skill}', [SkillController::class, 'destroySkill'])->name('dashboard.skills.destroy');
    Route::patch('/dashboard/skills/{skill}/toggle', [SkillController::class, 'toggleActive'])->name('dashboard.skills.toggle');
    Route::post('/dashboard/skill-categories', [SkillController::class, 'storeCategory'])->name('dashboard.skill-categories.store');
    Route::put('/dashboard/skill-categories/{category}', [SkillController::class, 'updateCategory'])->name('dashboard.skill-categories.update');
    Route::delete('/dashboard/skill-categories/{category}', [SkillController::class, 'destroyCategory'])->name('dashboard.skill-categories.destroy');

    // Education Management
    Route::get('/dashboard/educations', [EducationController::class, 'index'])->name('dashboard.educations');
    Route::post('/dashboard/educations', [EducationController::class, 'store'])->name('dashboard.educations.store');
    Route::put('/dashboard/educations/{education}', [EducationController::class, 'update'])->name('dashboard.educations.update');
    Route::delete('/dashboard/educations/{education}', [EducationController::class, 'destroy'])->name('dashboard.educations.destroy');
    Route::patch('/dashboard/educations/{education}/toggle', [EducationController::class, 'toggle'])->name('dashboard.educations.toggle');
    Route::redirect('/educations', '/dashboard/educations');

    // Project Management
    Route::get('/dashboard/projects', [ProjectController::class, 'index'])->name('dashboard.projects');
    Route::post('/dashboard/projects', [ProjectController::class, 'store'])->name('dashboard.projects.store');
    Route::put('/dashboard/projects/{project}', [ProjectController::class, 'update'])->name('dashboard.projects.update');
    Route::delete('/dashboard/projects/{project}', [ProjectController::class, 'destroy'])->name('dashboard.projects.destroy');
    Route::patch('/dashboard/projects/{project}/toggle', [ProjectController::class, 'toggle'])->name('dashboard.projects.toggle');
    Route::patch('/dashboard/projects/{project}/toggle-featured', [ProjectController::class, 'toggleFeatured'])->name('dashboard.projects.toggle-featured');
    Route::redirect('/projects', '/dashboard/projects');

    // Experience Management
    Route::get('/dashboard/experiences', [ExperienceController::class, 'index'])->name('dashboard.experiences');
    Route::post('/dashboard/experiences', [ExperienceController::class, 'store'])->name('dashboard.experiences.store');
    Route::put('/dashboard/experiences/{experience}', [ExperienceController::class, 'update'])->name('dashboard.experiences.update');
    Route::delete('/dashboard/experiences/{experience}', [ExperienceController::class, 'destroy'])->name('dashboard.experiences.destroy');
    Route::patch('/dashboard/experiences/{experience}/toggle', [ExperienceController::class, 'toggle'])->name('dashboard.experiences.toggle');
    Route::redirect('/experiences', '/dashboard/experiences');

    // Contact Management
    Route::get('/dashboard/contacts', [ContactController::class, 'index'])->name('dashboard.contacts');
    Route::patch('/dashboard/contacts/{contactMessage}/status', [ContactController::class, 'updateMessageStatus'])->name('dashboard.contacts.message.status');
    Route::delete('/dashboard/contacts/{contactMessage}', [ContactController::class, 'destroyMessage'])->name('dashboard.contacts.message.destroy');
    Route::patch('/dashboard/consultations/{consultation}/status', [ContactController::class, 'updateConsultationStatus'])->name('dashboard.consultations.status');
    Route::delete('/dashboard/consultations/{consultation}', [ContactController::class, 'destroyConsultation'])->name('dashboard.consultations.destroy');
    Route::redirect('/contacts', '/dashboard/contacts');

    // Control Pages Management
    Route::get('/dashboard/control-pages', [ControlPageController::class, 'index'])->name('dashboard.control_pages');
    Route::put('/dashboard/control-pages/{section}', [ControlPageController::class, 'update'])->name('dashboard.control_pages.update');
    Route::patch('/dashboard/control-pages/{section}/toggle-visibility', [ControlPageController::class, 'toggleVisibility'])->name('dashboard.control_pages.toggle_visibility');
    Route::patch('/dashboard/control-pages/{section}/toggle-navbar', [ControlPageController::class, 'toggleNavbar'])->name('dashboard.control_pages.toggle_navbar');
    Route::post('/dashboard/control-pages/reorder', [ControlPageController::class, 'reorder'])->name('dashboard.control_pages.reorder');
    Route::redirect('/control-pages', '/dashboard/control-pages');

    // Blog Management
    Route::get('/dashboard/blogs', [BlogController::class, 'index'])->name('dashboard.blogs');
    Route::post('/dashboard/blogs', [BlogController::class, 'store'])->name('dashboard.blogs.store');
    Route::put('/dashboard/blogs/{blog}', [BlogController::class, 'update'])->name('dashboard.blogs.update');
    Route::delete('/dashboard/blogs/{blog}', [BlogController::class, 'destroy'])->name('dashboard.blogs.destroy');
    Route::patch('/dashboard/blogs/{blog}/toggle', [BlogController::class, 'togglePublish'])->name('dashboard.blogs.toggle');
    Route::redirect('/blogs', '/dashboard/blogs');

});

// Dev Login Helper for local browser validation
if (app()->environment('local')) {
    Route::get('/dev-login', function () {
        $adminEmail = env('ADMIN_ALLOWED_EMAIL', 'fatazikrillah007@gmail.com');
        $user = \App\Models\User::where('email', $adminEmail)->first();
        if ($user) {
            \Illuminate\Support\Facades\Auth::login($user);
            return redirect('/dashboard/biodata');
        }
        return 'User not found';
    });
}

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';

// Guest Authentication Routes
Route::middleware('guest')->group(function () {
    Route::post('/login/otp/send', [PersonalLoginController::class, 'sendOtp'])->name('login.otp.send');
    Route::post('/login/otp/verify', [PersonalLoginController::class, 'verifyOtp'])->name('login.otp.verify');
    Route::post('/login/biometric', [PersonalLoginController::class, 'verifyBiometric'])->name('login.biometric');
    
    Route::get('/auth/google', [PersonalLoginController::class, 'redirectToGoogle'])->name('login.google');
    Route::get('/auth/google/callback', [PersonalLoginController::class, 'handleGoogleCallback']);
});
