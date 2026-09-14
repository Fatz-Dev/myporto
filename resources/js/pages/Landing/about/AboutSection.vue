<script setup lang="ts">
import { useLandingGsap } from '@/composables/useLandingGsap';

const { gsap, setupGsap } = useLandingGsap();

setupGsap(() => {
    if (document.querySelector('.about-header-anim')) {
        gsap.fromTo('.about-header-anim',
            { opacity: 0, y: 30 },
            {
                scrollTrigger: {
                    trigger: '.about-header-anim',
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
    if (document.querySelector('.about-stat-card')) {
        gsap.fromTo('.about-stat-card',
            { opacity: 0, y: 25 },
            {
                scrollTrigger: {
                    trigger: '#about',
                    start: 'top 85%',
                },
                opacity: 1,
                y: 0,
                stagger: 0.08,
                duration: 0.7,
                ease: 'power2.out',
                clearProps: 'all',
            }
        );
    }
    if (document.querySelector('.about-bio-anim')) {
        gsap.fromTo('.about-bio-anim',
            { opacity: 0, y: 25 },
            {
                scrollTrigger: {
                    trigger: '.about-bio-anim',
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
});

defineProps<{
    profile?: any;
    section?: any;
}>();
</script>

<template>
    <section id="about" class="px-6 sm:px-10 lg:px-14 py-28 relative border-t border-white/[0.08]">
        <div class="max-w-6xl mx-auto space-y-16">
            <!-- Section Header (Murni dari Database landing_sections) -->
            <div class="about-header-anim space-y-3">
                <p class="font-mono text-xs text-[#d97736] tracking-[0.2em] uppercase font-semibold">// About me</p>
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight">
                    {{ section?.custom_title }}
                </h2>
                <p v-if="section?.custom_subtitle" class="text-slate-400 text-sm sm:text-base max-w-2xl leading-relaxed">
                    {{ section.custom_subtitle }}
                </p>
            </div>

            <!-- Stats & Stacks Grid (Murni dari Database profiles) -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                <!-- Stat 1 -->
                <div class="about-stat-card p-6 rounded-2xl bg-white/[0.02] border border-white/[0.08] hover:border-[#d97736]/30 transition-all duration-300 group">
                    <div class="font-mono text-3xl sm:text-4xl font-extrabold text-white group-hover:text-[#d97736] transition-colors">
                        {{ profile?.projects_delivered }}
                    </div>
                    <div class="text-xs sm:text-sm font-semibold text-slate-300 mt-2">Projects delivered</div>
                    <div class="text-[11px] text-slate-500 mt-1">Across 12+ countries &amp; industries</div>
                </div>

                <!-- Stat 2 -->
                <div class="about-stat-card p-6 rounded-2xl bg-white/[0.02] border border-white/[0.08] hover:border-[#d97736]/30 transition-all duration-300 group">
                    <div class="font-mono text-3xl sm:text-4xl font-extrabold text-white group-hover:text-[#d97736] transition-colors">
                        {{ profile?.years_experience }}
                    </div>
                    <div class="text-xs sm:text-sm font-semibold text-slate-300 mt-2">Years of experience</div>
                    <div class="text-[11px] text-slate-500 mt-1">Full-lifecycle production</div>
                </div>

                <!-- Stat 3 -->
                <div class="about-stat-card p-6 rounded-2xl bg-white/[0.02] border border-white/[0.08] hover:border-[#d97736]/30 transition-all duration-300 group">
                    <div class="font-mono text-3xl sm:text-4xl font-extrabold text-white group-hover:text-[#d97736] transition-colors">
                        {{ profile?.client_satisfaction_rate }}
                    </div>
                    <div class="text-xs sm:text-sm font-semibold text-slate-300 mt-2">Client satisfaction</div>
                    <div class="text-[11px] text-slate-500 mt-1">Proven repeat partnerships</div>
                </div>

                <!-- Stat 4: Primary Stack (Murni dari Database profiles) -->
                <div class="about-stat-card p-6 rounded-2xl bg-white/[0.02] border border-white/[0.08] hover:border-[#d97736]/30 transition-all duration-300 group">
                    <div class="text-xs font-semibold text-slate-400 uppercase tracking-widest font-mono mb-3">Primary Stack</div>
                    <div class="flex flex-wrap gap-1.5">
                        <span
                            v-for="st in (profile?.primary_stack || [])"
                            :key="st"
                            class="px-2 py-0.5 rounded-md bg-white/[0.04] border border-white/[0.08] text-[10px] font-mono text-slate-300"
                        >
                            {{ st }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Narrative Bio & Quick Info Panel (Murni dari Database profiles) -->
            <div class="about-bio-anim grid lg:grid-cols-12 gap-12 items-start">
                <div class="lg:col-span-7 space-y-5 text-slate-300 leading-relaxed text-base">
                    <p v-if="profile?.bio_summary_1">
                        {{ profile.bio_summary_1 }}
                    </p>
                    <p v-if="profile?.bio_summary_2" class="text-slate-400">
                        {{ profile.bio_summary_2 }}
                    </p>
                </div>

                <div class="lg:col-span-5 rounded-2xl bg-[#11141d] border border-white/[0.08] p-6 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-white/[0.06]">
                        <span class="text-xs text-slate-400">Location</span>
                        <span class="text-xs font-semibold text-white font-mono">{{ profile?.location }}</span>
                    </div>
                    <div class="flex items-center justify-between pb-3 border-b border-white/[0.06]">
                        <span class="text-xs text-slate-400">Availability</span>
                        <span class="text-xs font-semibold text-emerald-400 font-mono">{{ profile?.availability_badge_text }}</span>
                    </div>
                    <div class="flex items-center justify-between pb-3 border-b border-white/[0.06]">
                        <span class="text-xs text-slate-400">Email</span>
                        <a :href="'mailto:' + profile?.email" class="text-xs font-semibold text-[#d97736] hover:underline font-mono">
                            {{ profile?.email }}
                        </a>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-slate-400">Response time</span>
                        <span class="text-xs font-semibold text-slate-300 font-mono">{{ profile?.response_time }}</span>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
