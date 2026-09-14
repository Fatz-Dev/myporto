<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class PortfolioDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Akun Pengguna Utama (Admin)
        $user = User::firstOrCreate(
            ['email' => 'hello@fatzdev.com'],
            [
                'name' => 'FatzDev',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        // 2. Kontrol Halaman / Seksi Landing Page (Control Pages)
        $sections = [
            [
                'section_key' => 'hero',
                'name' => 'Hero / Beranda Utama',
                'nav_label' => 'Home',
                'is_visible' => true,
                'show_in_navbar' => true,
                'order' => 1,
                'custom_title' => 'Building software that matters.',
                'custom_subtitle' => 'Full Stack Engineer crafting robust, scalable digital experiences.',
            ],
            [
                'section_key' => 'about',
                'name' => 'Tentang Saya & Statistik',
                'nav_label' => 'About',
                'is_visible' => true,
                'show_in_navbar' => true,
                'order' => 2,
                'custom_title' => 'Obsessed with clean code & great UX.',
                'custom_subtitle' => 'Across 12+ countries & industries.',
            ],
            [
                'section_key' => 'skills',
                'name' => 'Keahlian & Filosofi Rekayasa',
                'nav_label' => 'Skills',
                'is_visible' => true,
                'show_in_navbar' => true,
                'order' => 3,
                'custom_title' => 'Mastery built through real-world production.',
                'custom_subtitle' => 'Across every layer of the stack.',
            ],
            [
                'section_key' => 'education',
                'name' => 'Riwayat Pendidikan & Pelatihan',
                'nav_label' => 'Education',
                'is_visible' => true,
                'show_in_navbar' => true,
                'order' => 4,
                'custom_title' => 'Education & background',
                'custom_subtitle' => 'Academic foundations and intensive programs.',
            ],
            [
                'section_key' => 'portfolio',
                'name' => 'Karya Proyek Terpilih',
                'nav_label' => 'Work',
                'is_visible' => true,
                'show_in_navbar' => true,
                'order' => 5,
                'custom_title' => 'Selected work',
                'custom_subtitle' => 'A curated selection of projects built with care and precision.',
            ],
            [
                'section_key' => 'experience',
                'name' => 'Pengalaman Kerja & Karier',
                'nav_label' => 'Experience',
                'is_visible' => true,
                'show_in_navbar' => true,
                'order' => 6,
                'custom_title' => 'Work experience',
                'custom_subtitle' => 'Engineering leadership and hands-on contribution.',
            ],
            [
                'section_key' => 'blog',
                'name' => 'Artikel Blog & Pemikiran',
                'nav_label' => 'Blog',
                'is_visible' => true,
                'show_in_navbar' => true,
                'order' => 7,
                'custom_title' => 'Writing & thoughts',
                'custom_subtitle' => 'Pragmatic insights on architecture and modern web tech.',
            ],
            [
                'section_key' => 'contact',
                'name' => 'Formulir Kontak & Pesan',
                'nav_label' => 'Contact',
                'is_visible' => true,
                'show_in_navbar' => true,
                'order' => 8,
                'custom_title' => "Let's start something great.",
                'custom_subtitle' => "Whether you have a clear brief or just an idea.",
            ],
            [
                'section_key' => 'appointment',
                'name' => 'Jadwal Konsultasi (Discovery Call)',
                'nav_label' => 'Book Call',
                'is_visible' => true,
                'show_in_navbar' => false,
                'order' => 9,
                'custom_title' => 'Book a 30-min discovery call',
                'custom_subtitle' => 'Reserve a direct slot to discuss technical feasibility.',
            ],
        ];

        foreach ($sections as $sec) {
            DB::table('landing_sections')->updateOrInsert(
                ['section_key' => $sec['section_key']],
                array_merge($sec, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        // 3. Profil Biodata FatzDev
        DB::table('profiles')->updateOrInsert(
            ['user_id' => $user->id],
            [
                'full_name' => 'FatzDev',
                'professional_title' => 'Full Stack Software Engineer',
                'hero_headline' => 'Building software that matters.',
                'hero_subheadline' => 'Full Stack Engineer crafting robust, scalable, and beautiful digital experiences - from API design to pixel-perfect interfaces.',
                'bio_summary_1' => "I'm FatzDev, a Full Stack Software Engineer with a CS degree and a relentless focus on building software that is both technically solid and a joy to use.",
                'bio_summary_2' => 'From architecting REST APIs and microservices to implementing pixel-perfect interfaces - I work across the entire stack and treat performance, accessibility, and maintainability as non-negotiable.',
                'avatar_url' => 'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?auto=format&fit=crop&w=800&q=80',
                'location' => 'Remote · Worldwide',
                'email' => 'hello@fatzdev.com',
                'phone' => '+1 (907) 555-0101',
                'response_time' => 'Within 24 hours',
                'is_available_for_hire' => true,
                'availability_badge_text' => 'Available for freelance work',
                'years_experience' => '5+',
                'projects_delivered' => '47+',
                'client_satisfaction_rate' => '100%',
                'rating_score' => 5.0,
                'rating_platform' => 'Upwork',
                'primary_stack' => json_encode(['Laravel', 'Vue.js', 'React', 'TypeScript', 'Node.js', 'PostgreSQL']),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $profileId = DB::table('profiles')->where('user_id', $user->id)->value('id');

        // 4. Link Jejaring Sosial & Profesional
        $socials = [
            ['platform' => 'github', 'label' => 'GitHub', 'url' => 'https://github.com', 'icon' => 'github', 'order' => 1],
            ['platform' => 'linkedin', 'label' => 'LinkedIn', 'url' => 'https://linkedin.com', 'icon' => 'linkedin', 'order' => 2],
            ['platform' => 'twitter', 'label' => 'X / Twitter', 'url' => 'https://twitter.com', 'icon' => 'twitter', 'order' => 3],
            ['platform' => 'instagram', 'label' => 'Instagram', 'url' => 'https://instagram.com', 'icon' => 'instagram', 'order' => 4],
        ];
        foreach ($socials as $soc) {
            DB::table('social_links')->updateOrInsert(
                ['profile_id' => $profileId, 'platform' => $soc['platform']],
                array_merge($soc, ['is_active' => true, 'created_at' => now(), 'updated_at' => now()])
            );
        }

        // 5. Kategori Keahlian & Skills
        $categories = [
            [
                'name' => 'Frontend Development',
                'slug' => 'frontend-development',
                'badge_label' => 'Production & UI Systems',
                'description' => 'React, Vue.js, Inertia, TypeScript, Tailwind - building interfaces that feel alive and fast.',
                'category_type' => 'technical',
                'order' => 1,
                'skills' => ['Vue 3', 'React 19', 'Tailwind v4', 'GSAP', 'Framer Motion']
            ],
            [
                'name' => 'Backend Development',
                'slug' => 'backend-development',
                'badge_label' => 'Distributed APIs & Services',
                'description' => 'Scalable architecture, resilient background processing, and clean API design.',
                'category_type' => 'technical',
                'order' => 2,
                'skills' => ['Laravel', 'Node.js', 'REST / GraphQL', 'Microservices']
            ],
            [
                'name' => 'Database & DevOps',
                'slug' => 'database-devops',
                'badge_label' => 'Data Modeling & CI/CD',
                'description' => 'High performance persistence, reproducible containers, and reliable deployment pipelines.',
                'category_type' => 'technical',
                'order' => 3,
                'skills' => ['PostgreSQL', 'MySQL', 'Docker', 'CI / CD']
            ],
            [
                'name' => 'Engineering Philosophy',
                'slug' => 'engineering-philosophy',
                'badge_label' => 'Other proficiencies',
                'description' => 'Problem solving, system architecture, team collaboration, and agile workflows are cornerstones of how I work.',
                'category_type' => 'philosophy',
                'order' => 4,
                'philosophies' => [
                    ['name' => 'Problem Solving', 'desc' => 'Systematic root-cause diagnosis, algorithmic efficiency, and resilient edge-case handling.', 'tag' => 'Root Cause'],
                    ['name' => 'Communication', 'desc' => 'Direct, transparent asynchronous updates, rigorous documentation, and cross-functional alignment.', 'tag' => 'Clear Async'],
                    ['name' => 'Architecture', 'desc' => 'Domain-driven modular structure, decoupled layers, and clean separation of concerns.', 'tag' => 'Modular DDD'],
                ]
            ]
        ];

        foreach ($categories as $catData) {
            $existingCat = DB::table('skill_categories')->where('slug', $catData['slug'])->first();
            if ($existingCat) {
                $catId = $existingCat->id;
                DB::table('skill_categories')->where('id', $catId)->update([
                    'name' => $catData['name'],
                    'badge_label' => $catData['badge_label'],
                    'description' => $catData['description'],
                    'category_type' => $catData['category_type'],
                    'order' => $catData['order'],
                    'is_active' => true,
                    'updated_at' => now(),
                ]);
            } else {
                $catId = DB::table('skill_categories')->insertGetId([
                    'name' => $catData['name'],
                    'slug' => $catData['slug'],
                    'badge_label' => $catData['badge_label'],
                    'description' => $catData['description'],
                    'category_type' => $catData['category_type'],
                    'order' => $catData['order'],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if (isset($catData['skills'])) {
                foreach ($catData['skills'] as $idx => $skillName) {
                    DB::table('skills')->updateOrInsert(
                        ['category_id' => $catId, 'name' => $skillName],
                        [
                            'core_tools' => json_encode([$skillName]),
                            'order' => $idx + 1,
                            'is_featured' => true,
                            'is_active' => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            } elseif (isset($catData['philosophies'])) {
                foreach ($catData['philosophies'] as $idx => $phil) {
                    DB::table('skills')->updateOrInsert(
                        ['category_id' => $catId, 'name' => $phil['name']],
                        [
                            'description' => $phil['desc'],
                            'discipline_protocol' => $phil['tag'],
                            'order' => $idx + 1,
                            'is_featured' => true,
                            'is_active' => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            }
        }

        // 6. Riwayat Pendidikan
        $educations = [
            [
                'degree_title' => 'Bachelor of Computer Science',
                'institution_name' => 'Tech University',
                'location' => 'Sydney, Australia',
                'start_year' => '2015',
                'end_year' => '2020',
                'order' => 1,
            ],
            [
                'degree_title' => 'Software Engineering Diploma',
                'institution_name' => 'Institute of Technology',
                'location' => 'New Delhi, India',
                'start_year' => '2010',
                'end_year' => '2014',
                'order' => 2,
            ],
            [
                'degree_title' => 'Intensive Full Stack Program',
                'institution_name' => 'Web Dev Bootcamp',
                'location' => 'Baltimore, Maryland, USA',
                'start_year' => '2009',
                'end_year' => '2010',
                'order' => 3,
            ],
        ];
        foreach ($educations as $edu) {
            DB::table('educations')->updateOrInsert(
                ['degree_title' => $edu['degree_title'], 'institution_name' => $edu['institution_name']],
                array_merge($edu, [
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        // 7. Karya Proyek Terpilih
        $projects = [
            [
                'title' => 'SaaS Dashboard Platform',
                'slug' => 'saas-dashboard-platform',
                'category_label' => 'Full Stack',
                'year' => '2024',
                'short_description' => 'Real-time analytics, secure payments, and a comprehensive admin dashboard built with Laravel and Vue.js.',
                'tech_stack' => json_encode(['Laravel', 'Vue 3', 'Inertia.js', 'PostgreSQL', 'Tailwind CSS']),
                'thumbnail_url' => 'https://images.unsplash.com/photo-1551650975-87deedd944c3?auto=format&fit=crop&w=800&q=80',
                'is_featured' => true,
                'order' => 1,
            ],
            [
                'title' => 'E-Commerce Platform',
                'slug' => 'e-commerce-platform',
                'category_label' => 'Full Stack',
                'year' => '2023',
                'short_description' => 'Multi-vendor marketplace with Stripe payments and real-time inventory.',
                'tech_stack' => json_encode(['Laravel', 'Vue 3', 'Stripe', 'Redis', 'Docker']),
                'thumbnail_url' => 'https://images.unsplash.com/photo-1563013544-824ae1b704d3?auto=format&fit=crop&w=800&q=80',
                'is_featured' => true,
                'order' => 2,
            ],
            [
                'title' => 'REST API Gateway',
                'slug' => 'rest-api-gateway',
                'category_label' => 'Backend',
                'year' => '2023',
                'short_description' => 'High-throughput API serving 500k+ daily requests with Redis caching.',
                'tech_stack' => json_encode(['Laravel', 'Go', 'Redis', 'Docker', 'Kong']),
                'thumbnail_url' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=800&q=80',
                'is_featured' => true,
                'order' => 3,
            ],
        ];
        foreach ($projects as $proj) {
            DB::table('projects')->updateOrInsert(
                ['slug' => $proj['slug']],
                array_merge($proj, [
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        // 8. Pengalaman Kerja
        $experiences = [
            [
                'role_title' => 'Senior Full Stack Engineer',
                'company_name' => 'Stellar Labs',
                'location' => 'Remote',
                'start_period' => '2021',
                'end_period' => 'Present',
                'is_current' => true,
                'description' => 'Leading architecture and full-stack development of enterprise web applications, mentoring junior engineers, and optimizing distributed services.',
                'order' => 1,
            ],
            [
                'role_title' => 'Full Stack Developer',
                'company_name' => 'Quantum Digital',
                'location' => 'Jakarta, Indonesia',
                'start_period' => '2018',
                'end_period' => '2021',
                'is_current' => false,
                'description' => 'Engineered high-scale SaaS products with Laravel, Vue.js, and relational databases. Implemented automated CI/CD deployment pipelines.',
                'order' => 2,
            ],
            [
                'role_title' => 'Junior Developer',
                'company_name' => 'DevHaus Agency',
                'location' => 'Sydney, Australia',
                'start_period' => '2015',
                'end_period' => '2018',
                'is_current' => false,
                'description' => 'Developed responsive frontend interfaces, integrated third-party REST APIs, and managed client web applications.',
                'order' => 3,
            ],
        ];
        foreach ($experiences as $exp) {
            DB::table('experiences')->updateOrInsert(
                ['role_title' => $exp['role_title'], 'company_name' => $exp['company_name']],
                array_merge($exp, [
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        // 9. Artikel Blog Teknis
        $blogs = [
            [
                'author_id' => $user->id,
                'title' => 'Why I chose Laravel for every serious project in 2024',
                'slug' => 'why-i-chose-laravel-for-every-serious-project-in-2024',
                'category' => 'Architecture',
                'excerpt' => 'A pragmatic breakdown of why the Laravel ecosystem remains one of the most productive choices for full-stack applications.',
                'content' => 'Full article breakdown on Laravel ecosystem productivity, elegant architecture, and robust community tooling...',
                'read_time' => '5 min read',
                'published_at' => '2024-06-12',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?auto=format&fit=crop&w=800&q=80',
                'is_published' => true,
            ],
            [
                'author_id' => $user->id,
                'title' => 'Tailwind v4 is a paradigm shift, not just an update',
                'slug' => 'tailwind-v4-is-a-paradigm-shift-not-just-an-update',
                'category' => 'Frontend',
                'excerpt' => 'Exploring the next evolution of utility-first CSS, CSS-first configuration, and blazing fast build speeds.',
                'content' => 'Deep dive into Tailwind v4 engine, CSS native variables, and streamlined configuration...',
                'read_time' => '4 min read',
                'published_at' => '2024-05-02',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1627398242454-45a1465c2479?auto=format&fit=crop&w=300&q=80',
                'is_published' => true,
            ],
            [
                'author_id' => $user->id,
                'title' => 'How I design REST APIs that developers actually enjoy using',
                'slug' => 'how-i-design-rest-apis-that-developers-actually-enjoy-using',
                'category' => 'Backend',
                'excerpt' => 'Practical principles for consistent payload conventions, ergonomic error handling, and developer empathy.',
                'content' => 'Guidelines on designing predictable HTTP status codes, error schemas, and comprehensive documentation...',
                'read_time' => '6 min read',
                'published_at' => '2024-03-17',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1618401471353-b98afee0b2eb?auto=format&fit=crop&w=300&q=80',
                'is_published' => true,
            ],
        ];
        foreach ($blogs as $b) {
            DB::table('blogs')->updateOrInsert(
                ['slug' => $b['slug']],
                array_merge($b, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
