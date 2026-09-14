<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useLandingGsap } from '@/composables/useLandingGsap';

const props = defineProps<{
    section?: {
        custom_title?: string;
        custom_subtitle?: string;
        section_key?: string;
    };
    projects?: Array<{
        id: number;
        title: string;
        slug: string;
        category_label: string;
        year: string;
        short_description: string;
        thumbnail_url?: string;
        live_preview_url?: string;
        github_url?: string;
        tech_stack?: string[];
        is_featured: boolean;
        order: number;
        is_active: boolean;
    }>;
}>();

const carouselTrack = ref<HTMLElement | null>(null);
const isPaused = ref(false);
let animFrameId: number | null = null;
const scrollSpeed = 0.8; // Kecepatan pergeseran halus (piksel per frame)

const projectCount = computed(() => (props.projects ? props.projects.length : 0));

// Gandakan list proyek untuk efek seamless infinite loop
const displayProjects = computed(() => {
    if (!props.projects || props.projects.length === 0) return [];
    if (props.projects.length === 1) return props.projects;
    return [...props.projects, ...props.projects];
});

const pauseScroll = () => {
    isPaused.value = true;
};

const resumeScroll = () => {
    isPaused.value = false;
};

const tick = () => {
    if (carouselTrack.value && !isPaused.value && projectCount.value > 1) {
        carouselTrack.value.scrollLeft += scrollSpeed;
        const halfScroll = carouselTrack.value.scrollWidth / 2;
        if (carouselTrack.value.scrollLeft >= halfScroll) {
            carouselTrack.value.scrollLeft -= halfScroll;
        }
    }
    animFrameId = requestAnimationFrame(tick);
};

const { gsap, setupGsap } = useLandingGsap();

setupGsap(() => {
    if (document.querySelector('.portfolio-header-anim')) {
        gsap.fromTo('.portfolio-header-anim',
            { opacity: 0, y: 30 },
            {
                scrollTrigger: {
                    trigger: '.portfolio-header-anim',
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
    if (document.querySelector('.portfolio-carousel-anim')) {
        gsap.fromTo('.portfolio-carousel-anim',
            { opacity: 0, y: 35 },
            {
                scrollTrigger: {
                    trigger: '#portfolio',
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

onMounted(() => {
    animFrameId = requestAnimationFrame(tick);
});

onUnmounted(() => {
    if (animFrameId) {
        cancelAnimationFrame(animFrameId);
        animFrameId = null;
    }
});
</script>

<template>
    <section id="portfolio" class="px-6 sm:px-10 lg:px-14 py-28 border-t border-white/[0.08]">
        <div class="w-full max-w-6xl mx-auto">
            <!-- Section Header (100% database-driven, zero fallback strings) -->
            <div class="portfolio-header-anim flex flex-col md:flex-row md:items-end justify-between gap-6 mb-12">
                <div class="max-w-xl">
                    <div class="inline-flex items-center gap-2 font-mono text-[11px] font-semibold tracking-[0.14em] uppercase text-[#d97736] mb-3">
                        <span>//</span> Showcase
                    </div>
                    <h2 v-if="section?.custom_title" class="text-4xl sm:text-5xl font-extrabold text-white tracking-tighter leading-tight">
                        {{ section.custom_title }}
                    </h2>
                    <p v-if="section?.custom_subtitle" class="text-slate-400 text-sm sm:text-base leading-relaxed mt-4">
                        {{ section.custom_subtitle }}
                    </p>
                </div>
            </div>

            <!-- CAROUSEL TRACK -->
            <div
                class="portfolio-carousel-anim relative -mx-6 sm:-mx-10 lg:-mx-14 px-6 sm:px-10 lg:px-14"
                @mouseenter="pauseScroll"
                @mouseleave="resumeScroll"
            >
                <div
                    ref="carouselTrack"
                    class="flex gap-6 overflow-x-auto py-4 no-scrollbar cursor-grab select-none"
                    @pointerdown="pauseScroll"
                    @pointerup="resumeScroll"
                >
                    <div
                        v-for="(project, pIdx) in displayProjects"
                        :key="`${project.id}-${pIdx}`"
                        @mouseenter="pauseScroll"
                        @mouseleave="resumeScroll"
                        class="carousel-item w-[300px] sm:w-[380px] md:w-[420px] shrink-0 bg-white/[0.02] border border-white/[0.08] hover:border-[#d97736]/50 rounded-2xl overflow-hidden transition-all duration-300 hover:-translate-y-1.5 hover:shadow-[0_20px_40px_-15px_rgba(0,0,0,0.7)] group flex flex-col justify-between"
                    >
                        <!-- Image Container with Aspect 16:9 -->
                        <div class="relative aspect-video w-full overflow-hidden bg-slate-900">
                            <img
                                v-if="project.thumbnail_url"
                                :src="project.thumbnail_url"
                                :alt="project.title"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                loading="lazy"
                            />
                            <div v-else class="w-full h-full flex items-center justify-center bg-white/[0.02] text-slate-600">
                                <i class="fa-solid fa-code text-3xl"></i>
                            </div>

                            <div class="absolute inset-0 bg-gradient-to-t from-[#11141d] via-transparent to-transparent opacity-80"></div>

                            <!-- Overlay Badges (Top Left) -->
                            <div class="absolute top-3 left-3 flex gap-2">
                                <span
                                    v-if="project.category_label"
                                    class="px-2.5 py-1 font-mono text-[9px] font-bold rounded-lg uppercase tracking-wider bg-[#0a0c10]/80 text-[#f08c4a] border border-[#d97736]/30 backdrop-blur-md"
                                >
                                    {{ project.category_label }}
                                </span>
                                <span
                                    v-if="project.year"
                                    class="px-2.5 py-1 font-mono text-[9px] font-bold rounded-lg uppercase tracking-wider bg-[#0a0c10]/80 text-slate-300 border border-white/10 backdrop-blur-md"
                                >
                                    {{ project.year }}
                                </span>
                            </div>
                        </div>

                        <!-- Card Content -->
                        <div class="p-6 flex-1 flex flex-col justify-between">
                            <div>
                                <h3 class="text-xl font-bold text-white tracking-tight mb-2 group-hover:text-[#f08c4a] transition-colors">
                                    {{ project.title }}
                                </h3>
                                <p class="text-xs sm:text-sm text-slate-400 leading-relaxed mb-4 line-clamp-3">
                                    {{ project.short_description }}
                                </p>
                                <div
                                    v-if="project.tech_stack && project.tech_stack.length > 0"
                                    class="flex flex-wrap gap-1.5 mb-5"
                                >
                                    <span
                                        v-for="(tech, tIdx) in project.tech_stack"
                                        :key="tIdx"
                                        class="px-2 py-0.5 rounded text-[10px] font-mono bg-white/[0.04] text-slate-300 border border-white/[0.06]"
                                    >
                                        {{ tech }}
                                    </span>
                                </div>
                            </div>

                            <!-- Bottom Link -->
                            <a
                                :href="project.live_preview_url || '#contact'"
                                class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-[#d97736] hover:text-amber-300 transition-colors pt-3 border-t border-white/[0.06]"
                            >
                                <span>Explore case study</span>
                                <i class="fa-solid fa-arrow-right text-[10px]"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
