<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ContactMessage;
use App\Models\Consultation;

class ContactAndConsultationSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Pesan Kontak Sampel
        $messages = [
            [
                'name' => 'Alexander Wright',
                'email' => 'alex.wright@apexfintech.io',
                'subject' => 'Project Inquiry: High-Performance FinTech Dashboard',
                'message' => 'Hi FatzDev, I came across your portfolio and was thoroughly impressed by your work on scalable SaaS platforms. We are looking for a Senior Full Stack Engineer to lead the rebuild of our analytics dashboard using Laravel and Vue 3. Would love to schedule a technical chat this week.',
                'status' => 'unread',
                'ip_address' => '103.245.38.12',
                'created_at' => now()->subHours(3),
            ],
            [
                'name' => 'Sarah Jenkins',
                'email' => 's.jenkins@cloudscale-solutions.com',
                'subject' => 'Consulting on Microservices Architecture & API Design',
                'message' => 'Hello! Our engineering team is migrating from a monolith to distributed REST services and we need an experienced architect to review our domain boundaries and API contracts. Are you available for a 4-week advisory engagement?',
                'status' => 'read',
                'ip_address' => '180.252.164.88',
                'created_at' => now()->subDay(),
            ],
            [
                'name' => 'David Kurniawan',
                'email' => 'david@nusantaratech.id',
                'subject' => 'Kolaborasi Pengembangan Web App E-Commerce Multi-Vendor',
                'message' => 'Halo Mas Fatz, kami sedang membutuhkan bantuan untuk optimasi query database PostgreSQL dan implementasi real-time notification pada platform e-commerce kami. Mohon info rate card dan ketersediaan jadwal Mas Fatz ya.',
                'status' => 'replied',
                'ip_address' => '36.72.210.45',
                'created_at' => now()->subDays(3),
            ],
            [
                'name' => 'Elena Rostova',
                'email' => 'elena@nordicdesign.co',
                'subject' => 'Design System & Component Library Implementation',
                'message' => 'Hey there! We love your clean UI implementations. We have a complete Figma design system that needs to be translated into reusable Vue 3 / Tailwind components. Let us know if your pipeline is open.',
                'status' => 'archived',
                'ip_address' => '94.254.120.30',
                'created_at' => now()->subWeek(),
            ],
        ];

        foreach ($messages as $msg) {
            ContactMessage::updateOrCreate(
                ['email' => $msg['email'], 'subject' => $msg['subject']],
                $msg
            );
        }

        // 2. Jadwal Konsultasi Sampel
        $consultations = [
            [
                'full_name' => 'Marcus Vance',
                'email' => 'marcus.vance@vancemedia.com',
                'phone' => '+1 (415) 890-3412',
                'service_type' => 'Full-Stack Web App Development',
                'preferred_date' => now()->addDays(2)->format('Y-m-d'),
                'notes' => 'Need an MVP delivered within 6 weeks for investor demo. Key focus on responsive UI and Stripe payment integration.',
                'status' => 'pending',
                'created_at' => now()->subHours(6),
            ],
            [
                'full_name' => 'Budi Santoso',
                'email' => 'budi.santoso@startuphub.id',
                'phone' => '+62 812-3456-7890',
                'service_type' => 'Architecture & Performance Audit',
                'preferred_date' => now()->addDays(4)->format('Y-m-d'),
                'notes' => 'Aplikasi sering mengalami lonjakan traffic di jam tertentu, butuh audit query dan caching layer Redis.',
                'status' => 'confirmed',
                'created_at' => now()->subDays(2),
            ],
            [
                'full_name' => 'Dr. Robert Chen',
                'email' => 'rchen@biomed-systems.org',
                'phone' => '+1 (206) 555-0199',
                'service_type' => 'Custom API & Integration',
                'preferred_date' => now()->addDays(7)->format('Y-m-d'),
                'notes' => 'Integration with medical lab equipment protocols and FHIR JSON API standards.',
                'status' => 'completed',
                'created_at' => now()->subDays(5),
            ],
        ];

        foreach ($consultations as $c) {
            Consultation::updateOrCreate(
                ['email' => $c['email'], 'service_type' => $c['service_type']],
                $c
            );
        }
    }
}