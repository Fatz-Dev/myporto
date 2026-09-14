<script setup lang="ts">
import { useLandingGsap } from '@/composables/useLandingGsap';

const { gsap, setupGsap } = useLandingGsap();

setupGsap(() => {
    if (document.querySelector('.landing-footer-anim')) {
        gsap.fromTo('.landing-footer-anim',
            { opacity: 0, y: 20 },
            {
                scrollTrigger: {
                    trigger: '.landing-footer-anim',
                    start: 'top 95%',
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
}>();

const scrollToTop = () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
};
</script>

<template>
    <footer class="landing-footer-anim py-10 px-6 sm:px-10 lg:px-14 text-center text-xs text-slate-500 border-t border-white/[0.08] bg-[#0a0c10]">
        <div class="max-w-6xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-[#d97736]"></span>
                <span class="text-slate-400 font-medium">
                    &copy; {{ new Date().getFullYear() }} {{ profile?.full_name || 'FatzDev' }}. All rights reserved.
                </span>
            </div>

            <p class="text-[11px] text-slate-600 font-mono">
                Crafted with Laravel 12 &middot; Vue 3 &middot; Tailwind CSS
            </p>

            <button
                type="button"
                class="inline-flex items-center gap-1.5 text-xs text-slate-400 hover:text-[#d97736] transition-colors cursor-pointer group"
                @click="scrollToTop"
            >
                <span>Back to top</span>
                <svg class="w-3.5 h-3.5 transition-transform group-hover:-translate-y-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                </svg>
            </button>
        </div>
    </footer>
</template>
