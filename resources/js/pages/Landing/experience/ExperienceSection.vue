<script setup lang="ts">
import { useLandingGsap } from '@/composables/useLandingGsap';

const { gsap, setupGsap } = useLandingGsap();

setupGsap(() => {
    if (document.querySelector('.experience-header-anim')) {
        gsap.fromTo('.experience-header-anim',
            { opacity: 0, y: 30 },
            {
                scrollTrigger: {
                    trigger: '.experience-header-anim',
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
    if (document.querySelector('.experience-card-anim')) {
        gsap.fromTo('.experience-card-anim',
            { opacity: 0, y: 25 },
            {
                scrollTrigger: {
                    trigger: '#experience',
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
});

defineProps<{
    experiences?: any[];
}>();

const emit = defineEmits<{
    (e: 'navigate', sectionId: string): void;
}>();
</script>

<template>
    <section id="experience" class="px-6 sm:px-10 lg:px-14 py-28 border-t border-white/[0.08]">
        <div class="max-w-6xl mx-auto space-y-16">
            <!-- Section Header -->
            <div class="experience-header-anim space-y-3">
                <p class="font-mono text-xs text-[#d97736] tracking-[0.2em] uppercase font-semibold">// Career</p>
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight">
                    Work experience
                </h2>
            </div>

            <!-- Experience Timeline Cards -->
            <div class="space-y-6">
                <template v-if="experiences && experiences.length > 0">
                    <div
                        v-for="exp in experiences"
                        :key="exp.id"
                        class="experience-card-anim p-6 sm:p-8 rounded-2xl bg-white/[0.02] border border-white/[0.08] hover:border-[#d97736]/30 transition-all duration-300 flex flex-col sm:flex-row sm:items-center justify-between gap-6 group"
                    >
                        <div class="space-y-2">
                            <span class="inline-block font-mono text-[11px] text-[#d97736] font-semibold px-2.5 py-0.5 rounded-full bg-[#d97736]/10 border border-[#d97736]/20">
                                {{ exp.start_period }} - {{ exp.end_period || (exp.is_current ? 'Present' : '') }}
                            </span>
                            <h3 class="text-xl font-bold text-white group-hover:text-[#d97736] transition-colors">
                                {{ exp.role_title }}
                            </h3>
                            <p class="text-sm text-slate-400">
                                {{ exp.company_name }}<template v-if="exp.location"> &middot; {{ exp.location }}</template>
                            </p>
                            <p v-if="exp.description" class="text-xs text-slate-400 max-w-2xl leading-relaxed pt-2">
                                {{ exp.description }}
                            </p>
                        </div>

                        <a
                            href="#contact"
                            class="inline-flex items-center gap-2 text-xs font-semibold text-slate-400 hover:text-white transition-colors shrink-0 group/link"
                            @click.prevent="emit('navigate', 'contact')"
                        >
                            <span>Contact me</span>
                            <svg class="w-3.5 h-3.5 transition-transform group-hover/link:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </a>
                    </div>
                </template>

                <template v-else>
                    <!-- Default Experience 1 -->
                    <div class="experience-card-anim p-6 sm:p-8 rounded-2xl bg-white/[0.02] border border-white/[0.08] hover:border-[#d97736]/30 transition-all duration-300 flex flex-col sm:flex-row sm:items-center justify-between gap-6 group">
                        <div class="space-y-2">
                            <span class="inline-block font-mono text-[11px] text-[#d97736] font-semibold px-2.5 py-0.5 rounded-full bg-[#d97736]/10 border border-[#d97736]/20">
                                2021 - Present
                            </span>
                            <h3 class="text-xl font-bold text-white group-hover:text-[#d97736] transition-colors">
                                Senior Full Stack Engineer
                            </h3>
                            <p class="text-sm text-slate-400">
                                Stellar Labs &middot; Remote
                            </p>
                        </div>
                        <a href="#contact" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-400 hover:text-white shrink-0" @click.prevent="emit('navigate', 'contact')">
                            <span>Contact me</span> &rarr;
                        </a>
                    </div>

                    <!-- Default Experience 2 -->
                    <div class="experience-card-anim p-6 sm:p-8 rounded-2xl bg-white/[0.02] border border-white/[0.08] hover:border-[#d97736]/30 transition-all duration-300 flex flex-col sm:flex-row sm:items-center justify-between gap-6 group">
                        <div class="space-y-2">
                            <span class="inline-block font-mono text-[11px] text-[#d97736] font-semibold px-2.5 py-0.5 rounded-full bg-[#d97736]/10 border border-[#d97736]/20">
                                2018 - 2021
                            </span>
                            <h3 class="text-xl font-bold text-white group-hover:text-[#d97736] transition-colors">
                                Full Stack Developer
                            </h3>
                            <p class="text-sm text-slate-400">
                                Quantum Digital &middot; Jakarta, Indonesia
                            </p>
                        </div>
                        <a href="#contact" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-400 hover:text-white shrink-0" @click.prevent="emit('navigate', 'contact')">
                            <span>Contact me</span> &rarr;
                        </a>
                    </div>

                    <!-- Default Experience 3 -->
                    <div class="experience-card-anim p-6 sm:p-8 rounded-2xl bg-white/[0.02] border border-white/[0.08] hover:border-[#d97736]/30 transition-all duration-300 flex flex-col sm:flex-row sm:items-center justify-between gap-6 group">
                        <div class="space-y-2">
                            <span class="inline-block font-mono text-[11px] text-[#d97736] font-semibold px-2.5 py-0.5 rounded-full bg-[#d97736]/10 border border-[#d97736]/20">
                                2015 - 2018
                            </span>
                            <h3 class="text-xl font-bold text-white group-hover:text-[#d97736] transition-colors">
                                Junior Developer
                            </h3>
                            <p class="text-sm text-slate-400">
                                DevHaus Agency &middot; Sydney, Australia
                            </p>
                        </div>
                        <a href="#contact" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-400 hover:text-white shrink-0" @click.prevent="emit('navigate', 'contact')">
                            <span>Contact me</span> &rarr;
                        </a>
                    </div>
                </template>
            </div>
        </div>
    </section>
</template>
