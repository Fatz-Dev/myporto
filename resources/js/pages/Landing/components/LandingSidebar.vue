<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps<{
    profile?: any;
    sections?: any[];
}>();

const emit = defineEmits<{
    (e: 'open-appointment'): void;
}>();

const mobileMenuOpen = ref(false);
const page = usePage();



const toggleMobileMenu = () => {
    mobileMenuOpen.value = !mobileMenuOpen.value;
};

const closeMobileMenu = () => {
    mobileMenuOpen.value = false;
};

const navItems = [
    { id: 'home', label: 'Home', href: '/', icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' },
    { id: 'about', label: 'About', href: '/about', icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z' },
    { id: 'skills', label: 'Skills', href: '/skills', icon: 'M13 10V3L4 14h7v7l9-11h-7z' },
    { id: 'education', label: 'Education', href: '/education', icon: 'M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222' },
    { id: 'portfolio', label: 'Portfolio', href: '/portfolio', icon: 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z' },
    { id: 'experience', label: 'Experience', href: '/experience', icon: 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z' },
    { id: 'blog', label: 'Blog', href: '/blog', icon: 'M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z' },
    { id: 'contact', label: 'Contact', href: '/contact', icon: 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z' },
];

const isItemVisible = (id: string) => {
    if (!props.sections || props.sections.length === 0) return true;
    const sec = props.sections.find((s: any) => s.section_key === id);
    if (!sec) return true;
    return sec.is_visible && sec.show_in_navbar;
};

const isLinkActive = (itemHref: string) => {
    const current = (page.url || '').split('?')[0];
    if (itemHref === '/') {
        return current === '/' || current === '';
    }
    return current === itemHref || current.startsWith(itemHref + '/');
};

const getSocialIconClass = (platform?: string) => {
    const p = (platform || '').toLowerCase().trim();
    if (p === 'x' || p === 'twitter') return 'fa-brands fa-x-twitter';
    if (p === 'linkedin') return 'fa-brands fa-linkedin';
    if (p === 'github') return 'fa-brands fa-github';
    if (p === 'instagram') return 'fa-brands fa-instagram';
    if (p === 'youtube') return 'fa-brands fa-youtube';
    if (p === 'facebook') return 'fa-brands fa-facebook';
    if (p === 'discord') return 'fa-brands fa-discord';
    if (p === 'tiktok') return 'fa-brands fa-tiktok';
    if (p === 'twitch') return 'fa-brands fa-twitch';
    if (p === 'dribbble') return 'fa-brands fa-dribbble';
    if (p === 'behance') return 'fa-brands fa-behance';
    if (p === 'medium') return 'fa-brands fa-medium';
    if (p === 'dev') return 'fa-brands fa-dev';
    return `fa-brands fa-${p || 'globe'}`;
};
</script>

<template>
    <!-- ===== MOBILE HEADER ===== -->
    <header class="lg:hidden sticky top-0 z-40 px-4 py-3 flex items-center justify-between bg-[#11141d]/90 backdrop-blur-md border-b border-white/[0.08]">
        <Link href="/" class="flex items-center gap-2.5">
            <div class="relative w-8 h-8 rounded-full overflow-hidden ring-2 ring-[#d97736]/40 shrink-0">
                <img
                    :src="profile?.avatar_url || 'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?auto=format&fit=crop&w=800&q=80'"
                    :alt="profile?.full_name || 'FatzDev'"
                    class="w-full h-full object-cover object-top"
                />
            </div>
            <div>
                <span class="font-bold text-sm text-white tracking-tight block leading-tight">{{ profile?.full_name || 'FatzDev' }}</span>
                <span class="font-mono text-[9px] text-[#d97736] font-semibold tracking-wider uppercase block">Engineer</span>
            </div>
        </Link>

        <button
            type="button"
            class="w-9 h-9 flex items-center justify-center rounded-xl bg-white/[0.04] border border-white/[0.08] text-slate-300 hover:text-white transition-colors"
            aria-label="Toggle navigation menu"
            @click="toggleMobileMenu"
        >
            <svg v-if="!mobileMenuOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </header>

    <!-- ===== MOBILE BACKDROP & DRAWER ===== -->
    <div
        v-if="mobileMenuOpen"
        class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 lg:hidden transition-opacity duration-300"
        @click="closeMobileMenu"
    ></div>

    <aside
        :class="[
            'fixed inset-y-0 left-0 z-50 w-72 bg-[#11141d] border-r border-white/[0.08] flex flex-col justify-between transform transition-transform duration-300 lg:hidden',
            mobileMenuOpen ? 'translate-x-0' : '-translate-x-full'
        ]"
    >
        <div class="p-6 border-b border-white/[0.08] flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full overflow-hidden ring-2 ring-[#d97736]/40 shrink-0">
                    <img
                        :src="profile?.avatar_url || 'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?auto=format&fit=crop&w=800&q=80'"
                        :alt="profile?.full_name || 'FatzDev'"
                        class="w-full h-full object-cover object-top"
                    />
                </div>
                <div>
                    <h2 class="text-white font-bold text-sm">{{ profile?.full_name || 'FatzDev' }}</h2>
                    <p class="font-mono text-[9px] text-[#d97736] tracking-wider uppercase font-semibold">Full Stack Engineer</p>
                </div>
            </div>
            <button class="text-slate-400 hover:text-white p-1" @click="closeMobileMenu">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <nav class="px-4 py-5 flex-1 space-y-1 overflow-y-auto no-scrollbar">
            <template v-for="item in navItems" :key="item.id">
                <Link
                    v-if="isItemVisible(item.id)"
                    :href="item.href"
                    :class="[
                        'flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-[11px] font-semibold tracking-widest uppercase transition-all duration-200',
                        isLinkActive(item.href)
                            ? 'text-[#d97736] bg-[#d97736]/10 border border-[#d97736]/25 font-bold'
                            : 'text-slate-400 border border-transparent hover:text-white hover:bg-white/[0.04]'
                    ]"
                    @click="closeMobileMenu"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="item.icon" />
                    </svg>
                    {{ item.label }}
                </Link>
            </template>
        </nav>

        <div class="p-4 border-t border-white/[0.08] space-y-3">
            <a
                href="/download-cv"
                download
                class="w-full inline-flex items-center justify-center gap-2 bg-[#d97736] hover:bg-[#e58546] text-[#0a0c10] font-bold text-xs tracking-wider uppercase rounded-xl py-3 px-4 shadow-[0_4px_16px_rgba(217,119,54,0.25)] active:scale-95 transition-all cursor-pointer"
                @click="closeMobileMenu"
            >
                <span>Download CV</span>
                <i class="fa-solid fa-download text-xs"></i>
            </a>
            <div class="flex items-center justify-center gap-2 pt-2">
                <template v-if="profile?.social_links && profile.social_links.length > 0">
                    <a
                        v-for="soc in profile.social_links"
                        :key="soc.id"
                        :href="soc.url"
                        target="_blank"
                        rel="noopener noreferrer"
                        :aria-label="soc.label"
                        class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-white bg-white/[0.04] border border-white/[0.08] text-xs transition-colors"
                    >
                        <i :class="[getSocialIconClass(soc.platform), 'fa-fw']"></i>
                    </a>
                </template>
                <template v-else>
                    <a href="https://github.com" target="_blank" rel="noopener noreferrer" aria-label="GitHub" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-white bg-white/[0.04] border border-white/[0.08] text-xs transition-colors">
                        <i class="fa-brands fa-github fa-fw"></i>
                    </a>
                    <a href="https://linkedin.com" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-[#d97736] bg-white/[0.04] border border-white/[0.08] text-xs transition-colors">
                        <i class="fa-brands fa-linkedin fa-fw"></i>
                    </a>
                    <a href="https://twitter.com" target="_blank" rel="noopener noreferrer" aria-label="Twitter" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-[#d97736] bg-white/[0.04] border border-white/[0.08] text-xs transition-colors">
                        <i class="fa-brands fa-x-twitter fa-fw"></i>
                    </a>
                </template>
            </div>
        </div>
    </aside>

    <!-- ===== DESKTOP SIDEBAR ===== -->
    <aside class="hidden lg:flex fixed top-0 left-0 h-screen w-[268px] z-30 bg-[#11141d] border-r border-white/[0.08] flex-col justify-between overflow-y-auto no-scrollbar">
        <!-- Top Profile Brand -->
        <div class="px-6 pt-8 pb-5 border-b border-white/[0.08] text-center">
            <Link href="/" class="block group">
                <div class="relative w-20 h-20 mx-auto rounded-full overflow-hidden ring-2 ring-[#d97736]/40 p-0.5 mb-3 shadow-[0_0_24px_rgba(217,119,54,0.18)] group-hover:ring-[#d97736] transition-all">
                    <img
                        :src="profile?.avatar_url || 'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?auto=format&fit=crop&w=800&q=80'"
                        :alt="profile?.full_name || 'FatzDev'"
                        class="w-full h-full object-cover object-top rounded-full"
                    />
                    <span
                        v-if="profile?.is_available_for_hire ?? true"
                        class="absolute bottom-1 right-1 w-3.5 h-3.5 bg-emerald-500 rounded-full ring-2 ring-[#11141d]"
                        title="Available for freelance"
                    ></span>
                </div>
                <h2 class="text-white font-bold text-base tracking-tight group-hover:text-[#d97736] transition-colors">{{ profile?.full_name || 'FatzDev' }}</h2>
                <p class="font-mono text-[10px] text-[#d97736] font-semibold tracking-[0.18em] mt-1 uppercase">
                    {{ profile?.professional_title || 'Full Stack Software Engineer' }}
                </p>
            </Link>
        </div>

        <!-- Navigation Links -->
        <nav class="px-4 py-5 flex-1 space-y-1" aria-label="Main navigation">
            <template v-for="item in navItems" :key="item.id">
                <Link
                    v-if="isItemVisible(item.id)"
                    :href="item.href"
                    :class="[
                        'flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-[11px] font-semibold tracking-widest uppercase transition-all duration-200',
                        isLinkActive(item.href)
                            ? 'text-[#d97736] bg-[#d97736]/10 border border-[#d97736]/25 font-bold shadow-[0_2px_8px_rgba(217,119,54,0.1)]'
                            : 'text-slate-400 border border-transparent hover:text-white hover:bg-white/[0.04]'
                    ]"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="item.icon" />
                    </svg>
                    {{ item.label }}
                </Link>
            </template>
        </nav>


        <!-- Social Links Footer -->
        <div class="px-6 pb-8 pt-4 border-t border-white/[0.08]">
            <p class="text-[10px] font-semibold tracking-[0.2em] text-slate-500 uppercase text-center mb-3">Connect</p>
            <div class="flex items-center justify-center gap-2">
                <template v-if="profile?.social_links && profile.social_links.length > 0">
                    <a
                        v-for="soc in profile.social_links"
                        :key="soc.id"
                        :href="soc.url"
                        target="_blank"
                        rel="noopener noreferrer"
                        :aria-label="soc.label"
                        class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-white bg-white/[0.04] border border-white/[0.08] hover:border-white/20 transition-all duration-200 text-xs font-mono font-bold"
                    >
                        <i :class="[getSocialIconClass(soc.platform), 'fa-fw']"></i>
                    </a>
                </template>
                <template v-else>
                    <a href="https://github.com" target="_blank" rel="noopener noreferrer" aria-label="GitHub" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-white bg-white/[0.04] border border-white/[0.08] hover:border-white/20 transition-all duration-200 text-xs font-mono font-bold">
                        <i class="fa-brands fa-github fa-fw"></i>
                    </a>
                    <a href="https://linkedin.com" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-[#d97736] bg-white/[0.04] border border-white/[0.08] hover:border-[#d97736]/40 transition-all duration-200 text-xs font-mono font-bold">
                        <i class="fa-brands fa-linkedin fa-fw"></i>
                    </a>
                    <a href="https://twitter.com" target="_blank" rel="noopener noreferrer" aria-label="Twitter" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-[#d97736] bg-white/[0.04] border border-white/[0.08] hover:border-[#d97736]/40 transition-all duration-200 text-xs font-mono font-bold">
                        <i class="fa-brands fa-x-twitter fa-fw"></i>
                    </a>
                </template>
            </div>
        </div>
    </aside>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
