<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useLandingGsap } from '@/composables/useLandingGsap';
import { ArrowRight, BookOpen, Calendar, Clock, Filter, Image, Search, Tag, X } from 'lucide-vue-next';

const props = defineProps<{
    blogs?: any[];
    categories?: string[];
    currentCategory?: string;
    initialSearch?: string;
}>();

const { gsap, setupGsap } = useLandingGsap();

setupGsap(() => {
    if (document.querySelector('.blog-header-anim')) {
        gsap.fromTo('.blog-header-anim',
            { opacity: 0, y: 30 },
            {
                scrollTrigger: {
                    trigger: '.blog-header-anim',
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
    if (document.querySelector('.blog-filter-anim')) {
        gsap.fromTo('.blog-filter-anim',
            { opacity: 0, y: 15 },
            {
                scrollTrigger: {
                    trigger: '.blog-filter-anim',
                    start: 'top 85%',
                },
                opacity: 1,
                y: 0,
                duration: 0.6,
                ease: 'power2.out',
                clearProps: 'all',
            }
        );
    }
    if (document.querySelector('.blog-card-item')) {
        gsap.fromTo('.blog-card-item',
            { opacity: 0, y: 25 },
            {
                scrollTrigger: {
                    trigger: '#blog',
                    start: 'top 85%',
                },
                opacity: 1,
                y: 0,
                stagger: 0.06,
                duration: 0.7,
                ease: 'power2.out',
                clearProps: 'all',
            }
        );
    }
});

const selectedCategory = ref<string>(props.currentCategory || 'all');
const searchQuery = ref<string>(props.initialSearch || '');

const allCategories = computed(() => {
    if (props.categories && props.categories.length > 0) {
        return props.categories;
    }
    // Fallback if not passed
    const set = new Set<string>();
    (props.blogs || []).forEach((b) => {
        if (b.category) set.add(b.category);
    });
    return Array.from(set);
});

// Client-side filtering for fast responsive typing
const filteredBlogs = computed(() => {
    const list = props.blogs || [];
    return list.filter((b) => {
        if (selectedCategory.value !== 'all' && b.category !== selectedCategory.value) {
            return false;
        }
        const q = searchQuery.value.toLowerCase().trim();
        if (!q) return true;

        return (
            (b.title && b.title.toLowerCase().includes(q)) ||
            (b.excerpt && b.excerpt.toLowerCase().includes(q)) ||
            (b.category && b.category.toLowerCase().includes(q))
        );
    });
});

const featuredBlog = computed(() => {
    return filteredBlogs.value.length > 0 ? filteredBlogs.value[0] : null;
});

const secondaryBlogs = computed(() => {
    return filteredBlogs.value.length > 1 ? filteredBlogs.value.slice(1) : [];
});

const setCategory = (cat: string) => {
    selectedCategory.value = cat;
};

const formatDate = (dateStr?: string) => {
    if (!dateStr) return 'Recent';
    try {
        const d = new Date(dateStr);
        return d.toLocaleDateString('en-GB', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
        });
    } catch {
        return dateStr;
    }
};

const getCategoryColor = (cat: string) => {
    const c = (cat || '').toLowerCase();
    if (c.includes('front')) return 'bg-cyan-500/10 text-cyan-400 border-cyan-500/20';
    if (c.includes('back')) return 'bg-blue-500/10 text-blue-400 border-blue-500/20';
    if (c.includes('arch')) return 'bg-[#d97736]/10 text-[#d97736] border-[#d97736]/20';
    if (c.includes('devops') || c.includes('data')) return 'bg-purple-500/10 text-purple-400 border-purple-500/20';
    return 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20';
};
</script>

<template>
    <section id="blog" class="px-6 sm:px-10 lg:px-14 py-28 border-t border-white/[0.08]">
        <div class="max-w-6xl mx-auto space-y-12">
            <!-- Section Header -->
            <div class="blog-header-anim flex flex-col md:flex-row md:items-end justify-between gap-6">
                <div class="space-y-3">
                    <p class="font-mono text-xs text-[#d97736] tracking-[0.2em] uppercase font-semibold">// Engineering Insights</p>
                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight">
                        Writing &amp; thoughts
                    </h1>
                    <p class="text-slate-400 text-sm sm:text-base max-w-xl leading-relaxed">
                        Pragmatic insights on architecture, distributed backend services, and modern frontend user experiences.
                    </p>
                </div>

                <!-- Search Input -->
                <div class="relative w-full md:w-72">
                    <Search class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search articles..."
                        class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-white/[0.04] border border-white/[0.08] text-white text-xs placeholder:text-slate-500 focus:outline-none focus:border-[#d97736] focus:ring-1 focus:ring-[#d97736] transition-all"
                    />
                    <button
                        v-if="searchQuery"
                        @click="searchQuery = ''"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white"
                    >
                        <X class="w-3.5 h-3.5" />
                    </button>
                </div>
            </div>

            <!-- Categories Filter Bar -->
            <div class="blog-filter-anim flex items-center gap-2 overflow-x-auto pb-2 border-b border-white/[0.06]">
                <button
                    type="button"
                    @click="setCategory('all')"
                    class="px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition-all"
                    :class="selectedCategory === 'all' ? 'bg-[#d97736] text-white shadow-lg shadow-[#d97736]/20' : 'bg-white/[0.03] text-slate-400 hover:text-white hover:bg-white/[0.06] border border-white/[0.06]'"
                >
                    All Articles
                </button>

                <button
                    v-for="cat in allCategories"
                    :key="cat"
                    type="button"
                    @click="setCategory(cat)"
                    class="px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition-all flex items-center gap-1.5"
                    :class="selectedCategory === cat ? 'bg-[#d97736] text-white shadow-lg shadow-[#d97736]/20' : 'bg-white/[0.03] text-slate-400 hover:text-white hover:bg-white/[0.06] border border-white/[0.06]'"
                >
                    <span>{{ cat }}</span>
                </button>
            </div>

            <!-- Empty State -->
            <div
                v-if="filteredBlogs.length === 0"
                class="p-16 rounded-3xl bg-white/[0.02] border border-white/[0.08] text-center space-y-3"
            >
                <div class="w-12 h-12 rounded-2xl bg-white/[0.04] border border-white/[0.08] flex items-center justify-center text-slate-400 mx-auto">
                    <BookOpen class="w-6 h-6" />
                </div>
                <h3 class="text-lg font-bold text-white">No articles found</h3>
                <p class="text-xs text-slate-400 max-w-sm mx-auto">
                    No publications match your current filter criteria. Try clearing the search or choosing another category.
                </p>
                <button
                    @click="selectedCategory = 'all'; searchQuery = ''"
                    class="px-4 py-2 rounded-xl bg-white/[0.05] hover:bg-white/[0.1] text-xs font-semibold text-white border border-white/[0.08] transition-colors inline-block mt-2"
                >
                    Reset Filter
                </button>
            </div>

            <!-- Articles Layout -->
            <div v-else class="space-y-8">
                <!-- Featured Top Article -->
                <div v-if="featuredBlog" class="grid lg:grid-cols-12 gap-8">
                    <div class="lg:col-span-12">
                        <Link
                            :href="'/blog/' + featuredBlog.slug"
                            class="blog-card-item block p-8 md:p-10 rounded-3xl bg-white/[0.02] hover:bg-white/[0.04] border border-white/[0.08] hover:border-[#d97736]/30 transition-all duration-300 group"
                        >
                            <div class="grid md:grid-cols-12 gap-8 items-center">
                                <!-- Thumbnail (if available) -->
                                <div v-if="featuredBlog.thumbnail_url" class="md:col-span-5 rounded-2xl overflow-hidden bg-white/[0.04] border border-white/[0.08] aspect-video">
                                    <img
                                        :src="featuredBlog.thumbnail_url"
                                        :alt="featuredBlog.title"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                    />
                                </div>

                                <div :class="featuredBlog.thumbnail_url ? 'md:col-span-7' : 'md:col-span-12'" class="space-y-4">
                                    <div class="flex flex-wrap items-center gap-3 text-xs font-mono">
                                        <span
                                            class="px-2.5 py-1 rounded-md text-[10px] font-semibold border"
                                            :class="getCategoryColor(featuredBlog.category)"
                                        >
                                            {{ featuredBlog.category }}
                                        </span>
                                        <span class="text-slate-400 flex items-center gap-1.5">
                                            <Calendar class="w-3.5 h-3.5 text-[#d97736]" />
                                            {{ formatDate(featuredBlog.published_at) }}
                                        </span>
                                        <span class="text-slate-500">&middot;</span>
                                        <span class="text-slate-400 flex items-center gap-1.5">
                                            <Clock class="w-3.5 h-3.5 text-slate-400" />
                                            {{ featuredBlog.read_time || '5 min read' }}
                                        </span>
                                    </div>

                                    <h2 class="text-2xl sm:text-3xl font-extrabold text-white group-hover:text-[#d97736] transition-colors leading-tight">
                                        {{ featuredBlog.title }}
                                    </h2>

                                    <p class="text-slate-400 text-sm leading-relaxed line-clamp-3">
                                        {{ featuredBlog.excerpt || 'Read this in-depth technical article exploring modern software architecture, robust engineering practices, and practical developer insights.' }}
                                    </p>

                                    <div class="pt-4 flex items-center gap-2 text-sm font-bold text-[#d97736] group-hover:translate-x-1.5 transition-transform">
                                        <span>Read article</span>
                                        <ArrowRight class="w-4 h-4" />
                                    </div>
                                </div>
                            </div>
                        </Link>
                    </div>
                </div>

                <!-- Secondary Articles Grid -->
                <div v-if="secondaryBlogs.length > 0" class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <article
                        v-for="b in secondaryBlogs"
                        :key="b.id"
                        class="blog-card-item p-6 rounded-2xl bg-white/[0.02] hover:bg-white/[0.04] border border-white/[0.08] hover:border-[#d97736]/30 transition-all duration-300 flex flex-col justify-between group"
                    >
                        <Link :href="'/blog/' + b.slug" class="space-y-3 block">
                            <!-- Thumbnail -->
                            <div v-if="b.thumbnail_url" class="w-full h-44 rounded-xl overflow-hidden bg-white/[0.04] border border-white/[0.06] mb-3">
                                <img
                                    :src="b.thumbnail_url"
                                    :alt="b.title"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                />
                            </div>

                            <div class="flex items-center gap-2 text-[11px] font-mono">
                                <span
                                    class="px-2 py-0.5 rounded text-[10px] font-semibold border"
                                    :class="getCategoryColor(b.category)"
                                >
                                    {{ b.category }}
                                </span>
                                <span class="text-slate-400">{{ formatDate(b.published_at) }}</span>
                            </div>

                            <h3 class="text-base font-bold text-white group-hover:text-[#d97736] transition-colors leading-snug line-clamp-2">
                                {{ b.title }}
                            </h3>

                            <p class="text-xs text-slate-400 line-clamp-2 leading-relaxed">
                                {{ b.excerpt || 'Technical thoughts and pragmatic solutions for real-world production challenges.' }}
                            </p>
                        </Link>

                        <div class="pt-4 border-t border-white/[0.06] mt-4 flex items-center justify-between">
                            <span class="text-xs text-slate-500 font-mono">{{ b.read_time || '4 min read' }}</span>
                            <Link
                                :href="'/blog/' + b.slug"
                                class="text-xs font-semibold text-[#d97736] inline-flex items-center gap-1 group-hover:translate-x-1 transition-transform"
                            >
                                Read &rarr;
                            </Link>
                        </div>
                    </article>
                </div>
            </div>
        </div>
    </section>
</template>