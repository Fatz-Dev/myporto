<script setup lang="ts">
import { ref, computed } from 'vue';
import { useLandingGsap } from '@/composables/useLandingGsap';

const props = defineProps<{
    section?: {
        custom_title?: string;
        custom_subtitle?: string;
        section_key?: string;
    };
    skillCategories?: Array<{
        id: number;
        name: string;
        slug: string;
        badge_label?: string;
        category_type?: string;
        skills?: Array<{
            id: number;
            name: string;
            subtitle?: string;
            icon_class?: string;
            order?: number;
            is_active?: boolean;
        }>;
    }>;
}>();

const selectedCategory = ref<string>('all');

const allSkills = computed(() => {
    if (!props.skillCategories) return [];
    return props.skillCategories.flatMap(cat => 
        (cat.skills || []).map(skill => ({
            ...skill,
            category_slug: cat.slug,
            category_name: cat.name,
        }))
    );
});

const { gsap, setupGsap } = useLandingGsap();

setupGsap(() => {
    if (document.querySelector('.skills-header-anim')) {
        gsap.fromTo('.skills-header-anim',
            { opacity: 0, y: 30 },
            {
                scrollTrigger: {
                    trigger: '.skills-header-anim',
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
    if (document.querySelector('.skills-pill-anim')) {
        gsap.fromTo('.skills-pill-anim',
            { opacity: 0, y: 15 },
            {
                scrollTrigger: {
                    trigger: '.skills-pill-anim',
                    start: 'top 85%',
                },
                opacity: 1,
                y: 0,
                stagger: 0.03,
                duration: 0.5,
                ease: 'power2.out',
                clearProps: 'all',
            }
        );
    }
    if (document.querySelector('.skill-card-item')) {
        gsap.fromTo('.skill-card-item',
            { opacity: 0, scale: 0.94, y: 20 },
            {
                scrollTrigger: {
                    trigger: '#skills',
                    start: 'top 85%',
                },
                opacity: 1,
                scale: 1,
                y: 0,
                stagger: 0.02,
                duration: 0.4,
                ease: 'power2.out',
                clearProps: 'all',
            }
        );
    }
});

const filteredSkills = computed(() => {
    if (selectedCategory.value === 'all') {
        return allSkills.value;
    }
    return allSkills.value.filter(skill => skill.category_slug === selectedCategory.value);
});
</script>

<template>
    <section id="skills" class="px-6 sm:px-10 lg:px-14 py-28 border-t border-white/[0.08]">
        <div class="w-full max-w-6xl mx-auto">
            <!-- Section Header (Driven 100% from database landing_sections, no fallback strings) -->
            <div class="skills-header-anim max-w-xl mb-10">
                <div class="inline-flex items-center gap-2 font-mono text-[11px] font-semibold tracking-[0.14em] uppercase text-[#d97736] mb-3">
                    <span>//</span> Skills
                </div>
                <h2 v-if="section?.custom_title" class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight mb-4">
                    {{ section.custom_title }}
                </h2>
                <p v-if="section?.custom_subtitle" class="text-slate-400 text-sm sm:text-base leading-relaxed">
                    {{ section.custom_subtitle }}
                </p>
            </div>

            <!-- Category Filter Pill Tabs -->
            <div class="skills-pill-anim flex flex-wrap items-center gap-2 mb-8">
                <button
                    type="button"
                    @click="selectedCategory = 'all'"
                    class="px-4 py-2 rounded-xl text-xs font-mono font-semibold tracking-wide transition-all duration-200 cursor-pointer"
                    :class="selectedCategory === 'all'
                        ? 'bg-[#d97736] text-[#0a0c10] border border-[#d97736] shadow-sm font-bold'
                        : 'text-slate-400 bg-white/[0.03] border border-white/[0.08] hover:text-white hover:border-[#d97736]/40'"
                >
                    All Skills
                </button>
                <button
                    v-for="cat in skillCategories"
                    :key="cat.id"
                    type="button"
                    @click="selectedCategory = cat.slug"
                    class="px-4 py-2 rounded-xl text-xs font-mono font-semibold tracking-wide transition-all duration-200 cursor-pointer"
                    :class="selectedCategory === cat.slug
                        ? 'bg-[#d97736] text-[#0a0c10] border border-[#d97736] shadow-sm font-bold'
                        : 'text-slate-400 bg-white/[0.03] border border-white/[0.08] hover:text-white hover:border-[#d97736]/40'"
                >
                    {{ cat.name }}
                </button>
            </div>

            <!-- Skills Logo Grid (6 Columns) -->
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3 sm:gap-4">
                <div
                    v-for="skill in filteredSkills"
                    :key="skill.id"
                    class="skill-card skill-card-item group bg-white/[0.02] border border-white/[0.08] hover:border-[#d97736]/40 hover:bg-white/[0.04] rounded-2xl p-4 sm:p-5 flex flex-col items-center justify-center text-center transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_12px_24px_-8px_rgba(0,0,0,0.5)] cursor-default"
                >
                    <!-- Custom TS Badge Icon -->
                    <template v-if="skill.icon_class && skill.icon_class.startsWith('badge:TS')">
                        <div class="w-14 h-14 rounded-xl bg-[#3178c6]/10 border border-[#3178c6]/25 group-hover:border-blue-500/40 group-hover:bg-blue-500/20 flex items-center justify-center mb-3 transition-all duration-300">
                            <span class="font-mono font-extrabold text-xl text-[#3178c6] group-hover:scale-110 transition-transform duration-300">TS</span>
                        </div>
                    </template>

                    <!-- Custom Inertia Badge Icon -->
                    <template v-else-if="skill.icon_class && skill.icon_class.startsWith('badge:Inertia')">
                        <div class="w-14 h-14 rounded-xl bg-[#9553e9]/10 border border-[#9553e9]/25 group-hover:border-purple-500/40 group-hover:bg-purple-500/20 flex items-center justify-center mb-3 transition-all duration-300">
                            <svg class="w-7 h-7 text-[#9553e9] group-hover:scale-110 transition-transform duration-300" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M4.5 4.5l6.75 7.5-6.75 7.5h4.5l6.75-7.5-6.75-7.5h-4.5zm8.25 0l6.75 7.5-6.75 7.5h4.5l-6.75-7.5 6.75-7.5h-4.5z"/>
                            </svg>
                        </div>
                    </template>

                    <!-- Standard Brand / FontAwesome Icon -->
                    <template v-else-if="skill.icon_class">
                        <div class="w-14 h-14 rounded-xl bg-white/[0.03] border border-white/[0.06] group-hover:border-[#d97736]/30 group-hover:bg-[#d97736]/10 flex items-center justify-center mb-3 transition-all duration-300">
                            <i :class="[skill.icon_class, 'text-3xl group-hover:scale-110 transition-transform duration-300']"></i>
                        </div>
                    </template>

                    <!-- Fallback Icon Box if no icon_class -->
                    <template v-else>
                        <div class="w-14 h-14 rounded-xl bg-white/[0.03] border border-white/[0.06] group-hover:border-[#d97736]/30 group-hover:bg-[#d97736]/10 flex items-center justify-center mb-3 transition-all duration-300">
                            <i class="fa-solid fa-code text-2xl text-[#d97736] group-hover:scale-110 transition-transform duration-300"></i>
                        </div>
                    </template>

                    <!-- Skill Title -->
                    <h3 class="text-white text-sm font-bold tracking-tight mb-0.5 group-hover:text-[#d97736] transition-colors">
                        {{ skill.name }}
                    </h3>

                    <!-- Subtitle / Role Tag -->
                    <span v-if="skill.subtitle" class="text-[11px] font-mono text-slate-500 group-hover:text-slate-300 transition-colors">
                        {{ skill.subtitle }}
                    </span>
                </div>
            </div>
        </div>
    </section>
</template>
