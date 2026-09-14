<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use App\Models\ContactMessage;
use App\Models\Profile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ContactController extends Controller
{
    /**
     * Display contact messages and consultations management dashboard.
     */
    public function index(): Response
    {
        $messages = ContactMessage::latest()->get();
        $consultations = Consultation::latest()->get();
        $profile = Profile::with('socialLinks')->first();

        $stats = [
            'total_messages' => $messages->count(),
            'unread_messages' => $messages->where('status', 'unread')->count(),
            'total_consultations' => $consultations->count(),
            'pending_consultations' => $consultations->where('status', 'pending')->count(),
        ];

        return Inertia::render('Dashboard/Contact/Index', [
            'messages' => $messages,
            'consultations' => $consultations,
            'profile' => $profile,
            'stats' => $stats,
        ]);
    }

    /**
     * Update the status of a contact message.
     */
    public function updateMessageStatus(Request $request, ContactMessage $contactMessage): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|string|in:unread,read,replied,archived',
        ]);

        $contactMessage->update([
            'status' => $validated['status'],
        ]);

        return back()->with('success', 'Status pesan berhasil diperbarui.');
    }

    /**
     * Delete a contact message.
     */
    public function destroyMessage(ContactMessage $contactMessage): RedirectResponse
    {
        $contactMessage->delete();

        return back()->with('success', 'Pesan berhasil dihapus.');
    }

    /**
     * Update the status of a consultation.
     */
    public function updateConsultationStatus(Request $request, Consultation $consultation): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|string|in:pending,confirmed,completed,cancelled',
        ]);

        $consultation->update([
            'status' => $validated['status'],
        ]);

        return back()->with('success', 'Status konsultasi berhasil diperbarui.');
    }

    /**
     * Delete a consultation booking.
     */
    public function destroyConsultation(Consultation $consultation): RedirectResponse
    {
        $consultation->delete();

        return back()->with('success', 'Jadwal konsultasi berhasil dihapus.');
    }
}