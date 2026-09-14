<script setup lang="ts">
import { ref, onMounted } from 'vue';
import anime from 'animejs';
import HeroThreeCanvas from './HeroThreeCanvas.vue';
import HeroTerminal from './HeroTerminal.vue';
import { useLandingGsap } from '@/composables/useLandingGsap';

defineProps<{
    profile?: any;
}>();

const emit = defineEmits<{
    (e: 'open-appointment'): void;
}>();

const { gsap, setupGsap } = useLandingGsap();
const screenContainerRef = ref<HTMLElement | null>(null);

// 3D perspective mouse tilt effect for the developer screen
const screenTilt = ref('');

const onScreenMouseMove = (e: MouseEvent) => {
    if (!screenContainerRef.value) return;
    const rect = screenContainerRef.value.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;
    const centerX = rect.width / 2;
    const centerY = rect.height / 2;
    const rotateX = ((y - centerY) / centerY) * -2.5; // subtle tilt
    const rotateY = ((x - centerX) / centerX) * 2.5;
    screenTilt.value = `perspective(1200px) rotateX(${rotateX.toFixed(2)}deg) rotateY(${rotateY.toFixed(2)}deg)`;
};

const onScreenMouseLeave = () => {
    screenTilt.value = 'perspective(1200px) rotateX(0deg) rotateY(0deg)';
};

// Anime.js Entrance Timeline for Programmer Command Screen
onMounted(() => {
    anime.timeline({ easing: 'easeOutExpo' })
        .add({
            targets: '.hero-dev-screen',
            opacity: [0, 1],
            scale: [0.97, 1],
            translateY: [20, 0],
            duration: 850,
        });
});

// GSAP entrance for right-column developer card
setupGsap(() => {
    if (document.querySelector('.hero-right-card')) {
        gsap.from('.hero-right-card', {
            opacity: 0,
            y: 35,
            scale: 0.95,
            duration: 1.1,
            ease: 'power3.out',
            delay: 0.2,
        });
    }
});
</script>

<template>
    <section id="home" class="relative min-h-[100dvh] flex items-center px-6 sm:px-10 lg:px-14 py-16 sm:py-20 overflow-hidden">
        <!-- Ambient radial glow background -->
        <div class="absolute inset-0 pointer-events-none" aria-hidden="true">
            <div class="absolute top-[-10%] left-[-5%] w-[600px] h-[600px] rounded-full opacity-[0.035] bg-[radial-gradient(circle,rgba(217,119,54,0.06)_0%,transparent_70%)]"></div>
            <div class="absolute bottom-[-10%] right-[-5%] w-[500px] h-[500px] rounded-full opacity-[0.025] bg-[radial-gradient(circle,rgba(217,119,54,0.05)_0%,transparent_70%)]"></div>
        </div>

        <div class="max-w-6xl mx-auto w-full relative z-10">
            <div class="grid lg:grid-cols-12 gap-8 lg:gap-8 items-center">
                <!-- Left Column: Unified Programmer Workstation Screen (Three.js + Anime.js + Full CLI Terminal) -->
                <div class="lg:col-span-7 relative group">
                    <!-- Ambient Glow Behind the Screen -->
                    <div class="absolute -inset-1 rounded-3xl bg-gradient-to-tr from-[#d97736]/25 via-sky-500/10 to-transparent opacity-40 blur-xl"></div>

                    <!-- Developer Command Screen Frame -->
                    <div
                        ref="screenContainerRef"
                        class="hero-dev-screen relative rounded-3xl bg-[#0c0f17]/95 border border-white/[0.1] shadow-[0_20px_50px_-15px_rgba(0,0,0,0.85)] backdrop-blur-xl overflow-hidden transition-transform duration-300 ease-out"
                        :style="{ transform: screenTilt }"
                        @mousemove="onScreenMouseMove"
                        @mouseleave="onScreenMouseLeave"
                    >
                        <!-- Top Window Titlebar -->
                        <div class="flex items-center justify-between px-4 sm:px-5 py-3 bg-white/[0.03] border-b border-white/[0.07] select-none">
                            <!-- macOS Traffic Light Dots -->
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-rose-500/80 border border-rose-600/40 shadow-sm"></span>
                                <span class="w-3 h-3 rounded-full bg-amber-500/80 border border-amber-600/40 shadow-sm"></span>
                                <span class="w-3 h-3 rounded-full bg-emerald-500/80 border border-emerald-600/40 shadow-sm"></span>
                            </div>

                            <!-- Screen Directory & Shell Identity -->
                            <div class="flex items-center gap-1.5 font-mono text-[11px] text-slate-400">
                                <i class="fa-solid fa-terminal text-[10px] text-[#d97736]"></i>
                                <span class="text-white font-semibold">{{ profile?.full_name?.toLowerCase().replace(' ', '') || 'fatz' }}@dev</span>
                                <span class="text-slate-600">:</span>
                                <span class="text-sky-400 font-medium">~/portfolio</span>
                                <span class="text-emerald-400/80 text-[10px] font-bold">main&#9889;</span>
                            </div>

                            <!-- Live Status Indicator -->
                            <div class="flex items-center gap-1.5 font-mono text-[10px] text-emerald-400">
                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                <span class="hidden sm:inline font-bold tracking-wider">LIVE</span>
                            </div>
                        </div>

                        <!-- Screen Content Area with Three.js 3D Grid & Node Particles Background -->
                        <div class="relative overflow-hidden">
                            <!-- Three.js Ambient 3D Grid Canvas Background -->
                            <HeroThreeCanvas />

                            <!-- Interactive CLI Terminal (Contains Status, Headline, Bio, Buttons, and Commands) -->
                            <div class="relative z-10">
                                <HeroTerminal
                                    :profile="profile"
                                    :embedded="true"
                                    @open-appointment="emit('open-appointment')"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Developer Portrait Card -->
                <div class="lg:col-span-5 relative">
                    <div class="hero-right-card relative rounded-3xl bg-[#11141d]/90 border border-white/[0.08] p-4 sm:p-5 shadow-[0_20px_50px_-20px_rgba(0,0,0,0.8)] backdrop-blur-md overflow-hidden group">
                        <!-- Portrait Image Container -->
                        <div class="relative aspect-[4/5] rounded-2xl overflow-hidden bg-slate-900 border border-white/[0.06]">
                            <img
                                :src="profile?.avatar_url || 'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?auto=format&fit=crop&w=800&q=80'"
                                :alt="profile?.full_name || 'FatzDev'"
                                class="w-full h-full object-cover object-top filter grayscale contrast-125 brightness-95 group-hover:grayscale-0 group-hover:scale-105 transition-all duration-700 ease-out"
                            />

                            <!-- Gradient Vignette -->
                            <div class="absolute inset-0 bg-gradient-to-t from-[#11141d] via-[#11141d]/30 to-transparent"></div>

                            <!-- Bottom Floating Card Overlay -->
                            <div class="absolute bottom-4 left-4 right-4 p-4 rounded-xl bg-[#0a0c10]/80 border border-white/[0.08] backdrop-blur-md space-y-2">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                        <span class="text-xs font-bold text-white font-sans">Open to work</span>
                                    </div>
                                    <span class="text-[10px] font-mono text-slate-400 uppercase">Full Time / Contract</span>
                                </div>
                                <div class="flex items-center justify-between text-[11px] font-mono text-slate-400 pt-2 border-t border-white/[0.06]">
                                    <span>Experience</span>
                                    <span class="text-white font-bold">{{ profile?.years_experience || '5+' }} Years</span>
                                </div>
                            </div>
                        </div>

                        <!-- Technical Stack Bar at the Bottom of Card -->
                        <div class="mt-4 pt-3 border-t border-white/[0.06] flex items-center justify-between text-xs font-mono">
                            <span class="text-slate-500">Main Stack</span>
                            <div class="flex items-center gap-2 text-slate-300">
                                <span class="text-[#d97736] font-bold">Laravel</span>
                                <span class="text-slate-600">&middot;</span>
                                <span class="text-sky-400 font-bold">Vue.js</span>
                                <span class="text-slate-600">&middot;</span>
                                <span class="text-emerald-400 font-bold">TypeScript</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
