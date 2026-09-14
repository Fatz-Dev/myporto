<script setup lang="ts">
import { useLandingGsap } from '@/composables/useLandingGsap';

const { gsap, setupGsap } = useLandingGsap();

setupGsap(() => {
    if (document.querySelector('.education-header-anim')) {
        gsap.fromTo('.education-header-anim',
            { opacity: 0, y: 30 },
            {
                scrollTrigger: {
                    trigger: '.education-header-anim',
                    start: 'top 85%',
                },
                opacity: 1,
                y: 0,
                duration: 0.8,
                ease: 'power2.out',
                clearProps: 'all',
            }
        );
    }
    if (document.querySelector('.education-card-anim')) {
        gsap.fromTo('.education-card-anim',
            { opacity: 0, x: -25 },
            {
                scrollTrigger: {
                    trigger: '#education',
                    start: 'top 85%',
                },
                opacity: 1,
                x: 0,
                stagger: 0.1,
                duration: 0.7,
                ease: 'power2.out',
                clearProps: 'all',
            }
        );
    }
});

defineProps<{
    section?: {
        custom_title?: string;
        custom_subtitle?: string;
        section_key?: string;
    };
    educations?: Array<{
        id: number;
        degree_title: string;
        institution_name: string;
        location?: string;
        start_year: string;
        end_year?: string;
        grade?: string;
        description?: string;
        skills_acquired?: string[];
        icon_class?: string;
        credential_url?: string;
        image_url?: string;
        order: number;
        is_active: boolean;
    }>;
}>();
</script>

<template>
    <section id="education" class="px-6 sm:px-10 lg:px-14 py-28 border-t border-white/[0.08]">
        <div class="w-full max-w-5xl mx-auto">
            <!-- Section Header (100% database-driven from landing_sections, zero fallback strings) -->
            <div class="education-header-anim max-w-xl mb-16">
                <div class="inline-flex items-center gap-2 font-mono text-[11px] font-semibold tracking-[0.14em] uppercase text-[#d97736] mb-3">
                    <span>//</span> Academic Journey
                </div>
                <h2 v-if="section?.custom_title" class="text-4xl sm:text-5xl font-extrabold text-white tracking-tighter leading-tight">
                    {{ section.custom_title }}
                </h2>
                <p v-if="section?.custom_subtitle" class="text-slate-400 text-sm sm:text-base leading-relaxed mt-4">
                    {{ section.custom_subtitle }}
                </p>
            </div>

            <!-- Vertical Timeline Container -->
            <div class="relative pl-6 sm:pl-10 border-l-2 border-white/[0.12] space-y-12 ml-3 sm:ml-6">
                <div
                    v-for="edu in educations"
                    :key="edu.id"
                    class="education-card-anim relative group"
                >
                    <!-- Timeline Marker Node on the line -->
                    <div class="absolute -left-[31px] sm:-left-[47px] top-1.5 w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-[#11141d] border-2 border-[#d97736] flex items-center justify-center text-[#d97736] group-hover:scale-110 group-hover:bg-[#d97736] group-hover:text-[#0a0c10] shadow-[0_0_15px_rgba(217,119,54,0.4)] transition-all duration-300">
                        <i :class="[edu.icon_class || 'fa-solid fa-graduation-cap', 'text-[10px] sm:text-xs']"></i>
                    </div>

                    <!-- Content Card -->
                    <div class="bg-white/[0.02] border border-white/[0.08] group-hover:border-[#d97736]/40 group-hover:bg-white/[0.04] rounded-2xl p-6 sm:p-7 transition-all duration-300 shadow-sm hover:shadow-[0_12px_28px_-10px_rgba(0,0,0,0.6)]">
                        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-4">
                            <div class="flex items-start gap-4">
                                <!-- Institution Image / Thumbnail -->
                                <div v-if="edu.image_url" class="w-16 h-16 rounded-xl overflow-hidden shrink-0 border border-white/[0.08] bg-white/[0.03]">
                                    <img
                                        :src="edu.image_url"
                                        :alt="edu.institution_name"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                    />
                                </div>
                                <div v-else class="w-16 h-16 rounded-xl overflow-hidden shrink-0 border border-white/[0.08] bg-white/[0.03] flex items-center justify-center text-[#d97736]">
                                    <i class="fa-solid fa-school text-xl"></i>
                                </div>

                                <!-- Institution, Degree & Badges -->
                                <div>
                                    <div class="flex flex-wrap items-center gap-2 mb-1.5">
                                        <span v-if="edu.start_year" class="px-2.5 py-0.5 rounded-md font-mono text-[10.5px] font-bold uppercase tracking-wider bg-[#d97736]/15 text-[#f08c4a] border border-[#d97736]/25">
                                            {{ edu.start_year }} {{ edu.end_year ? `— ${edu.end_year}` : '— Present' }}
                                        </span>
                                        <span v-if="edu.grade" class="text-xs text-emerald-400 font-medium">
                                            {{ edu.grade }}
                                        </span>
                                    </div>
                                    <h3 class="text-lg sm:text-xl font-bold text-white tracking-tight">
                                        {{ edu.degree_title }}
                                    </h3>
                                    <p class="text-xs sm:text-sm text-slate-400 font-medium mt-0.5 flex items-center gap-1.5">
                                        <i class="fa-solid fa-location-dot text-[#d97736] text-xs"></i>
                                        <span>{{ edu.institution_name }}</span>
                                        <span v-if="edu.location"> · {{ edu.location }}</span>
                                    </p>
                                </div>
                            </div>

                            <!-- Right Arrow Link -->
                            <a
                                :href="edu.credential_url || '#contact'"
                                class="hidden sm:inline-flex items-center justify-center w-9 h-9 rounded-xl bg-white/[0.03] border border-white/[0.08] text-slate-400 hover:text-white hover:bg-[#d97736] hover:border-[#d97736] transition-all shrink-0"
                                aria-label="Learn more"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                                </svg>
                            </a>
                        </div>

                        <!-- Curriculum / Description Paragraph -->
                        <p v-if="edu.description" class="text-slate-400 text-xs sm:text-sm leading-relaxed mb-4">
                            {{ edu.description }}
                        </p>

                        <!-- Coursework & Skills Acquired Pills -->
                        <div v-if="edu.skills_acquired && edu.skills_acquired.length > 0" class="flex flex-wrap gap-2 pt-3 border-t border-white/[0.06]">
                            <span
                                v-for="(skill, sIdx) in edu.skills_acquired"
                                :key="sIdx"
                                class="px-2.5 py-1 rounded-lg text-[11px] font-mono bg-white/[0.03] text-slate-300 border border-white/[0.06]"
                            >
                                {{ skill }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
