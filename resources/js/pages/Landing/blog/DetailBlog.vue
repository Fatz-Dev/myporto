<script setup lang="ts">
import LandingLayout from '../layouts/LandingLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import {
    ArrowLeft,
    Calendar,
    Check,
    Clock,
    Copy,
    ExternalLink,
    Folder,
    Share2,
    Tag,
    User
} from 'lucide-vue-next';

interface BlogItem {
    id: number;
    title: string;
    slug: string;
    category: string;
    excerpt: string | null;
    content: string | null;
    read_time: string | null;
    published_at: string | null;
    thumbnail_url: string | null;
    author?: {
        name: string;
        email: string;
    };
}

const props = defineProps<{
    blog: BlogItem;
    relatedBlogs?: BlogItem[];
    profile?: any;
    landingSections?: any[];
}>();

const isCopied = ref(false);

const copyArticleUrl = () => {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(window.location.href);
        isCopied.value = true;
        setTimeout(() => {
            isCopied.value = false;
        }, 2000);
    }
};

const formatDate = (dateStr?: string | null) => {
    if (!dateStr) return 'Recent';
    try {
        const d = new Date(dateStr);
        return d.toLocaleDateString('en-GB', {
            day: 'numeric',
            month: 'long',
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
    <LandingLayout
        :profile="profile"
        :landing-sections="landingSections"
        :title="blog.title"
        :description="blog.excerpt || 'Technical writing on web engineering and architecture.'"
    >
        <div class="px-6 sm:px-10 lg:px-14 py-20 lg:py-28">
            <div class="max-w-4xl mx-auto space-y-12">
                <!-- Top Navigation & Breadcrumbs -->
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <Link
                        href="/blog"
                        class="inline-flex items-center gap-2 text-xs font-semibold text-slate-400 hover:text-[#d97736] transition-colors"
                    >
                        <ArrowLeft class="w-4 h-4" />
                        <span>Back to All Articles</span>
                    </Link>

                    <!-- Breadcrumbs -->
                    <div class="flex items-center gap-2 text-xs text-slate-500 font-mono">
                        <Link href="/" class="hover:text-slate-300 transition-colors">Home</Link>
                        <span>/</span>
                        <Link href="/blog" class="hover:text-slate-300 transition-colors">Blog</Link>
                        <span>/</span>
                        <span class="text-slate-400 truncate max-w-[200px]">{{ blog.category }}</span>
                    </div>
                </div>

                <!-- Article Header -->
                <header class="space-y-6">
                    <div class="flex flex-wrap items-center gap-3 text-xs font-mono">
                        <span
                            class="px-3 py-1 rounded-md text-xs font-semibold border"
                            :class="getCategoryColor(blog.category)"
                        >
                            {{ blog.category }}
                        </span>
                        <span class="text-slate-400 flex items-center gap-1.5">
                            <Calendar class="w-3.5 h-3.5 text-[#d97736]" />
                            {{ formatDate(blog.published_at) }}
                        </span>
                        <span class="text-slate-500">&middot;</span>
                        <span class="text-slate-400 flex items-center gap-1.5">
                            <Clock class="w-3.5 h-3.5 text-slate-400" />
                            {{ blog.read_time || '5 min read' }}
                        </span>
                    </div>

                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight leading-tight">
                        {{ blog.title }}
                    </h1>

                    <p
                        v-if="blog.excerpt"
                        class="text-base sm:text-lg text-slate-300 leading-relaxed p-4 rounded-2xl bg-white/[0.02] border-l-4 border-[#d97736] border-y border-r border-white/[0.06]"
                    >
                        {{ blog.excerpt }}
                    </p>

                    <!-- Author Info -->
                    <div class="flex items-center justify-between gap-4 pt-4 border-t border-white/[0.06]">
                        <div class="flex items-center gap-3.5">
                            <img
                                v-if="profile?.avatar_url"
                                :src="profile.avatar_url"
                                alt="Author"
                                class="w-11 h-11 rounded-xl object-cover border border-white/[0.1]"
                            />
                            <div
                                v-else
                                class="w-11 h-11 rounded-xl bg-[#d97736]/10 border border-[#d97736]/20 flex items-center justify-center text-[#d97736] font-bold"
                            >
                                F
                            </div>
                            <div>
                                <p class="text-sm font-bold text-white">{{ profile?.full_name || 'FatzDev' }}</p>
                                <p class="text-xs text-slate-400">{{ profile?.professional_title || 'Full Stack Software Engineer' }}</p>
                            </div>
                        </div>

                        <!-- Share Button -->
                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                @click="copyArticleUrl"
                                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white/[0.04] hover:bg-white/[0.08] text-xs font-semibold text-slate-200 border border-white/[0.08] transition-colors"
                                title="Copy article URL"
                            >
                                <component :is="isCopied ? Check : Copy" class="w-3.5 h-3.5 text-[#d97736]" />
                                <span>{{ isCopied ? 'Copied!' : 'Share' }}</span>
                            </button>
                        </div>
                    </div>
                </header>

                <!-- Featured Cover Image Banner -->
                <div v-if="blog.thumbnail_url" class="rounded-3xl overflow-hidden bg-white/[0.02] border border-white/[0.08] aspect-video sm:aspect-[21/9]">
                    <img
                        :src="blog.thumbnail_url"
                        :alt="blog.title"
                        class="w-full h-full object-cover"
                    />
                </div>

                <!-- Main Formatted Article Content -->
                <article class="blog-article-content py-4">
                    <div
                        v-if="blog.content"
                        class="prose prose-invert max-w-none text-slate-200"
                        v-html="blog.content"
                    />
                    <div v-else class="text-slate-400 italic text-sm">
                        This article does not have any content yet.
                    </div>
                </article>

                <!-- Footer Social Share & CTA -->
                <div class="p-8 rounded-3xl bg-gradient-to-r from-white/[0.03] to-white/[0.01] border border-white/[0.08] space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="space-y-1">
                            <h3 class="text-base font-bold text-white">Enjoyed this reading?</h3>
                            <p class="text-xs text-slate-400">
                                Share this article with other engineers and tech leaders.
                            </p>
                        </div>

                        <div class="flex items-center gap-2.5">
                            <button
                                type="button"
                                @click="copyArticleUrl"
                                class="px-4 py-2 rounded-xl bg-white/[0.05] hover:bg-white/[0.1] text-xs font-semibold text-white border border-white/[0.08] flex items-center gap-2 transition-colors"
                            >
                                <Copy class="w-3.5 h-3.5" />
                                <span>{{ isCopied ? 'Link Copied!' : 'Copy Link' }}</span>
                            </button>

                            <a
                                :href="'https://twitter.com/intent/tweet?text=' + encodeURIComponent(blog.title) + '&url=' + encodeURIComponent('https://fatzdev.com/blog/' + blog.slug)"
                                target="_blank"
                                class="px-4 py-2 rounded-xl bg-[#d97736] hover:bg-[#c26629] text-xs font-semibold text-white transition-colors"
                            >
                                Post on X
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Related Articles Section -->
                <div v-if="relatedBlogs && relatedBlogs.length > 0" class="space-y-6 pt-6 border-t border-white/[0.08]">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-bold text-white">More Articles</h3>
                        <Link href="/blog" class="text-xs font-semibold text-[#d97736] hover:underline">
                            View All Articles &rarr;
                        </Link>
                    </div>

                    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        <article
                            v-for="rel in relatedBlogs"
                            :key="rel.id"
                            class="p-5 rounded-2xl bg-white/[0.02] hover:bg-white/[0.04] border border-white/[0.08] hover:border-[#d97736]/30 transition-all flex flex-col justify-between group"
                        >
                            <Link :href="'/blog/' + rel.slug" class="space-y-2 block">
                                <div class="flex items-center gap-2 text-[10px] font-mono">
                                    <span class="px-2 py-0.5 rounded border" :class="getCategoryColor(rel.category)">
                                        {{ rel.category }}
                                    </span>
                                    <span class="text-slate-400">{{ formatDate(rel.published_at) }}</span>
                                </div>

                                <h4 class="text-sm font-bold text-white group-hover:text-[#d97736] transition-colors line-clamp-2 leading-snug">
                                    {{ rel.title }}
                                </h4>

                                <p class="text-xs text-slate-400 line-clamp-2">
                                    {{ rel.excerpt || 'Read more technical insights.' }}
                                </p>
                            </Link>

                            <div class="pt-3 border-t border-white/[0.06] mt-3 flex items-center justify-between text-xs">
                                <span class="text-[11px] text-slate-500 font-mono">{{ rel.read_time || '4 min read' }}</span>
                                <Link :href="'/blog/' + rel.slug" class="font-semibold text-[#d97736]">
                                    Read &rarr;
                                </Link>
                            </div>
                        </article>
                    </div>
                </div>
            </div>
        </div>
    </LandingLayout>
</template>

<style>
/* Blog Article Typography styling */
.blog-article-content h1 {
    font-size: 2rem;
    font-weight: 800;
    color: #ffffff;
    margin-top: 2rem;
    margin-bottom: 1rem;
    line-height: 1.25;
}

.blog-article-content h2 {
    font-size: 1.5rem;
    font-weight: 700;
    color: #ffffff;
    margin-top: 1.75rem;
    margin-bottom: 0.75rem;
    line-height: 1.3;
}

.blog-article-content h3 {
    font-size: 1.25rem;
    font-weight: 600;
    color: #f8fafc;
    margin-top: 1.5rem;
    margin-bottom: 0.5rem;
}

.blog-article-content p {
    font-size: 1rem;
    line-height: 1.8;
    color: #cbd5e1;
    margin-bottom: 1.25rem;
}

.blog-article-content ul {
    list-style-type: disc;
    padding-left: 1.75rem;
    margin-bottom: 1.25rem;
    color: #cbd5e1;
}

.blog-article-content ol {
    list-style-type: decimal;
    padding-left: 1.75rem;
    margin-bottom: 1.25rem;
    color: #cbd5e1;
}

.blog-article-content li {
    margin-bottom: 0.5rem;
    line-height: 1.7;
}

.blog-article-content blockquote {
    border-left: 4px solid #d97736;
    padding: 0.75rem 1.25rem;
    background-color: rgba(255, 255, 255, 0.02);
    border-radius: 0 0.75rem 0.75rem 0;
    font-style: italic;
    color: #94a3b8;
    margin: 1.5rem 0;
}

.blog-article-content pre {
    background-color: #0d1117;
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 1rem;
    padding: 1.25rem;
    font-family: 'JetBrains Mono', monospace;
    font-size: 0.9rem;
    color: #38bdf8;
    margin: 1.5rem 0;
    overflow-x: auto;
    line-height: 1.6;
}

.blog-article-content code {
    background-color: rgba(255, 255, 255, 0.08);
    padding: 0.2rem 0.4rem;
    border-radius: 0.375rem;
    font-family: 'JetBrains Mono', monospace;
    font-size: 0.875em;
    color: #f43f5e;
}

.blog-article-content hr {
    border: none;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    margin: 2.5rem 0;
}

.blog-article-content a {
    color: #d97736;
    text-decoration: underline;
    text-underline-offset: 3px;
}

.blog-article-content a:hover {
    color: #ea580c;
}
</style>