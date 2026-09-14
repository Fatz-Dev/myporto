<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import TiptapEditor from '@/components/TiptapEditor.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, h, reactive, ref } from 'vue';
import {
    createTableHook,
    createColumnHelper,
    createCoreRowModel,
    createPaginatedRowModel,
    rowPaginationFeature,
    FlexRender
} from '@tanstack/vue-table';
import {
    AlertCircle,
    BookOpen,
    Calendar,
    CheckCircle2,
    Clock,
    ExternalLink,
    Eye,
    FileEdit,
    FileText,
    Image,
    Layers,
    Plus,
    Search,
    Send,
    Tag,
    Trash2,
    X,
    Sparkles,
    ArrowUpRight
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle
} from '@/components/ui/dialog';

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
    is_published: boolean;
    created_at: string;
}

const props = defineProps<{
    blogs: BlogItem[];
    categories: string[];
    stats: {
        total_blogs: number;
        published_blogs: number;
        draft_blogs: number;
        total_categories: number;
    };
}>();

const page = usePage();
const activeTab = ref<'list' | 'create' | 'preview'>('list');

// Filters
const searchQuery = ref('');
const selectedCategory = ref<string>('all');
const selectedStatus = ref<'all' | 'published' | 'draft'>('all');

// Create Form
const createForm = reactive({
    title: '',
    slug: '',
    category: 'Architecture',
    excerpt: '',
    content: '<p>Tulis artikel lengkap Anda di sini menggunakan editor terformat...</p>',
    read_time: '5 min read',
    published_at: new Date().toISOString().slice(0, 10),
    thumbnail_url: 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?auto=format&fit=crop&w=800&q=80',
    is_published: true,
});
const isCreating = ref(false);

// Edit Modal & Form
const isEditModalOpen = ref(false);
const editingBlog = ref<BlogItem | null>(null);
const editForm = reactive({
    id: 0,
    title: '',
    slug: '',
    category: '',
    excerpt: '',
    content: '',
    read_time: '',
    published_at: '',
    thumbnail_url: '',
    is_published: true,
});
const isUpdating = ref(false);

// Delete Modal
const isDeleteModalOpen = ref(false);
const blogToDelete = ref<BlogItem | null>(null);

// Filtered Blogs
const filteredBlogs = computed(() => {
    return props.blogs.filter((b) => {
        if (selectedCategory.value !== 'all' && b.category !== selectedCategory.value) {
            return false;
        }
        if (selectedStatus.value === 'published' && !b.is_published) {
            return false;
        }
        if (selectedStatus.value === 'draft' && b.is_published) {
            return false;
        }

        const q = searchQuery.value.toLowerCase().trim();
        if (!q) return true;

        return (
            b.title.toLowerCase().includes(q) ||
            b.slug.toLowerCase().includes(q) ||
            b.category.toLowerCase().includes(q) ||
            (b.excerpt && b.excerpt.toLowerCase().includes(q))
        );
    });
});

// Auto-slug generator
const generateSlug = (source: 'create' | 'edit') => {
    if (source === 'create') {
        createForm.slug = createForm.title
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-');
    } else {
        editForm.slug = editForm.title
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-');
    }
};

// Create Blog
const submitCreate = () => {
    isCreating.value = true;
    router.post('/dashboard/blogs', createForm, {
        preserveScroll: true,
        onSuccess: () => {
            createForm.title = '';
            createForm.slug = '';
            createForm.excerpt = '';
            createForm.content = '<p>Tulis artikel lengkap Anda di sini...</p>';
            activeTab.value = 'list';
        },
        onFinish: () => {
            isCreating.value = false;
        },
    });
};

// Open Edit Modal
const openEditModal = (blog: BlogItem) => {
    editingBlog.value = blog;
    editForm.id = blog.id;
    editForm.title = blog.title;
    editForm.slug = blog.slug;
    editForm.category = blog.category;
    editForm.excerpt = blog.excerpt || '';
    editForm.content = blog.content || '';
    editForm.read_time = blog.read_time || '5 min read';
    editForm.published_at = blog.published_at ? blog.published_at.slice(0, 10) : '';
    editForm.thumbnail_url = blog.thumbnail_url || '';
    editForm.is_published = Boolean(blog.is_published);
    isEditModalOpen.value = true;
};

// Submit Edit Blog
const submitEdit = () => {
    if (!editingBlog.value) return;
    isUpdating.value = true;
    router.put(`/dashboard/blogs/${editingBlog.value.id}`, editForm, {
        preserveScroll: true,
        onSuccess: () => {
            isEditModalOpen.value = false;
            editingBlog.value = null;
        },
        onFinish: () => {
            isUpdating.value = false;
        },
    });
};

// Toggle Publish
const togglePublish = (blog: BlogItem) => {
    router.patch(`/dashboard/blogs/${blog.id}/toggle`, {}, {
        preserveScroll: true,
    });
};

// Delete Blog
const confirmDelete = (blog: BlogItem) => {
    blogToDelete.value = blog;
    isDeleteModalOpen.value = true;
};

const executeDelete = () => {
    if (!blogToDelete.value) return;
    router.delete(`/dashboard/blogs/${blogToDelete.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            isDeleteModalOpen.value = false;
            blogToDelete.value = null;
        },
    });
};

const formatDate = (dateStr?: string | null) => {
    if (!dateStr) return '-';
    try {
        const d = new Date(dateStr);
        return d.toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'short',
            year: 'numeric',
        });
    } catch {
        return dateStr;
    }
};

const getCategoryColor = (cat: string) => {
    const c = (cat || '').toLowerCase();
    if (c.includes('front')) return 'bg-cyan-500/10 text-cyan-500 dark:text-cyan-400 border-cyan-500/20';
    if (c.includes('back')) return 'bg-blue-500/10 text-blue-500 dark:text-blue-400 border-blue-500/20';
    if (c.includes('arch')) return 'bg-amber-500/10 text-amber-500 dark:text-amber-400 border-amber-500/20';
    if (c.includes('devops') || c.includes('data')) return 'bg-purple-500/10 text-purple-500 dark:text-purple-400 border-purple-500/20';
    return 'bg-emerald-500/10 text-emerald-500 dark:text-emerald-400 border-emerald-500/20';
};

// =========================================================================
// TANSTACK TABLE INTEGRATION
// =========================================================================
const { useAppTable } = createTableHook({
    features: {
        rowPaginationFeature,
    },
    getCoreRowModel: createCoreRowModel(),
    getPaginatedRowModel: createPaginatedRowModel(),
});

const columnHelper = createColumnHelper<BlogItem>();
const columns = [
    columnHelper.accessor('title', {
        header: 'Artikel & Thumbnail',
        cell: ({ row }) => {
            const b = row.original;
            return h('div', { class: 'flex items-center gap-3.5 max-w-md' }, [
                h(
                    'div',
                    {
                        class: 'w-14 h-11 rounded-lg bg-muted border border-border overflow-hidden shrink-0',
                    },
                    b.thumbnail_url
                        ? [
                              h('img', {
                                  src: b.thumbnail_url,
                                  alt: b.title,
                                  class: 'w-full h-full object-cover',
                              }),
                          ]
                        : [
                              h(
                                  'div',
                                  { class: 'w-full h-full flex items-center justify-center text-muted-foreground' },
                                  [h(Image, { class: 'w-4 h-4' })]
                              ),
                          ]
                ),
                h('div', { class: 'space-y-0.5 min-w-0' }, [
                    h('p', { class: 'font-semibold text-foreground text-xs line-clamp-1' }, b.title),
                    h('p', { class: 'text-muted-foreground text-[11px] line-clamp-1' }, b.excerpt || 'Tidak ada ringkasan.'),
                    h('p', { class: 'text-muted-foreground font-mono text-[10px]' }, `/blog/${b.slug}`),
                ]),
            ]);
        },
    }),
    columnHelper.accessor('category', {
        header: 'Kategori',
        cell: ({ row }) => {
            const b = row.original;
            return h(
                'span',
                {
                    class: [
                        'inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold border whitespace-nowrap',
                        getCategoryColor(b.category),
                    ],
                },
                b.category
            );
        },
    }),
    columnHelper.accessor('published_at', {
        header: 'Waktu Baca & Tanggal',
        cell: ({ row }) => {
            const b = row.original;
            return h('div', { class: 'space-y-0.5 text-xs text-muted-foreground' }, [
                h('div', { class: 'flex items-center gap-1.5 font-medium text-foreground/90' }, [
                    h(Clock, { class: 'w-3.5 h-3.5 text-primary shrink-0' }),
                    h('span', b.read_time || '5 min read'),
                ]),
                h('div', { class: 'flex items-center gap-1.5 text-[10px]' }, [
                    h(Calendar, { class: 'w-3 h-3 text-muted-foreground shrink-0' }),
                    h('span', formatDate(b.published_at)),
                ]),
            ]);
        },
    }),
    columnHelper.accessor('is_published', {
        header: () => h('div', { class: 'text-center' }, 'Status Publikasi'),
        cell: ({ row }) => {
            const b = row.original;
            return h('div', { class: 'flex flex-col items-center justify-center' }, [
                h(
                    'button',
                    {
                        type: 'button',
                        class: [
                            'relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none',
                            b.is_published ? 'bg-emerald-500' : 'bg-muted-foreground/30',
                        ],
                        onClick: () => togglePublish(b),
                        title: b.is_published ? 'Klik untuk simpan ke draf' : 'Klik untuk terbitkan artikel',
                    },
                    [
                        h('span', {
                            class: [
                                'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-lg ring-0 transition duration-200 ease-in-out',
                                b.is_published ? 'translate-x-5' : 'translate-x-0',
                            ],
                        }),
                    ]
                ),
                h(
                    'p',
                    {
                        class: [
                            'text-[10px] mt-1 font-mono font-medium',
                            b.is_published ? 'text-emerald-500 dark:text-emerald-400' : 'text-muted-foreground',
                        ],
                    },
                    b.is_published ? 'Diterbitkan' : 'Draf'
                ),
            ]);
        },
    }),
    columnHelper.display({
        id: 'actions',
        header: () => h('div', { class: 'text-right' }, 'Aksi'),
        cell: ({ row }) => {
            const b = row.original;
            return h('div', { class: 'flex items-center justify-end gap-1.5' }, [
                h(
                    'a',
                    {
                        href: `/blog/${b.slug}`,
                        target: '_blank',
                        class: 'inline-flex items-center justify-center h-8 px-2.5 rounded-md text-xs font-medium border border-border bg-card hover:bg-accent text-foreground transition-colors',
                        title: 'Lihat artikel publik',
                    },
                    [h(ExternalLink, { class: 'w-3.5 h-3.5 mr-1' }), 'Lihat']
                ),
                h(
                    Button,
                    {
                        size: 'sm',
                        variant: 'outline',
                        class: 'h-8 px-2.5 text-xs bg-card border-border hover:bg-accent text-foreground',
                        onClick: () => openEditModal(b),
                    },
                    () => [h(FileEdit, { class: 'w-3.5 h-3.5 mr-1' }), 'Edit']
                ),
                h(
                    Button,
                    {
                        size: 'sm',
                        variant: 'ghost',
                        class: 'h-8 w-8 p-0 text-muted-foreground hover:text-destructive hover:bg-destructive/10',
                        onClick: () => confirmDelete(b),
                    },
                    () => h(Trash2, { class: 'w-3.5 h-3.5' })
                ),
            ]);
        },
    }),
];

const table = useAppTable({
    data: filteredBlogs,
    columns,
});
</script>

<template>
    <Head title="Manajemen Blog" />

    <AppLayout
        :breadcrumbs="[
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Manajemen Blog', href: '/dashboard/blogs' },
        ]"
    >
        <div class="flex-1 space-y-6 p-4 sm:p-6 lg:p-8 max-w-7xl w-full min-w-0 mx-auto overflow-x-hidden">
            <!-- Header Banner (Biodata Clean Style) -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-6 border-b border-sidebar-border/70 dark:border-sidebar-border min-w-0">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-foreground flex items-center gap-2.5">
                        <BookOpen class="w-7 h-7" />
                        <span>Manajemen Artikel Blog</span>
                    </h1>
                    <p class="text-sm text-muted-foreground mt-1">
                        Tulis dan kelola artikel arsitektur, frontend modern, dan rekayasa perangkat lunak dengan Rich Text Editor Tiptap.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2.5 sm:gap-3 shrink-0">
                    <a
                        href="/blog"
                        target="_blank"
                        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-medium border border-border bg-card hover:bg-accent text-foreground transition-colors"
                    >
                        <ExternalLink class="w-3.5 h-3.5" />
                        <span>Lihat Halaman Blog Publik</span>
                    </a>

                    <Button
                        type="button"
                        class="bg-card hover:bg-accent text-foreground border border-border font-semibold text-xs tracking-wide uppercase px-4"
                        @click="activeTab = 'create'"
                    >
                        <Plus class="w-4 h-4 mr-1.5" />
                        <span>Tulis Artikel Baru</span>
                    </Button>
                </div>
            </div>

            <!-- Flash Message Alert -->
            <div
                v-if="page.props.flash?.success"
                class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-500 dark:text-emerald-400 flex items-center gap-3 text-sm animate-in fade-in duration-300"
            >
                <CheckCircle2 class="w-5 h-5 shrink-0" />
                <span>{{ page.props.flash.success }}</span>
            </div>

            <!-- Stats Counters Cards Grid -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <Card class="border-border bg-card shadow-sm">
                    <CardContent class="p-4 flex items-center gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-primary/10 border border-primary/20 flex items-center justify-center text-primary shrink-0">
                            <FileText class="w-5 h-5" />
                        </div>
                        <div>
                            <p class="text-xs text-muted-foreground font-medium">Total Artikel</p>
                            <p class="text-xl sm:text-2xl font-bold text-foreground">{{ stats.total_blogs }}</p>
                        </div>
                    </CardContent>
                </Card>

                <Card class="border-border bg-card shadow-sm">
                    <CardContent class="p-4 flex items-center gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-500 dark:text-emerald-400 shrink-0">
                            <Send class="w-5 h-5" />
                        </div>
                        <div>
                            <p class="text-xs text-muted-foreground font-medium">Diterbitkan</p>
                            <p class="text-xl sm:text-2xl font-bold text-foreground">{{ stats.published_blogs }}</p>
                        </div>
                    </CardContent>
                </Card>

                <Card class="border-border bg-card shadow-sm">
                    <CardContent class="p-4 flex items-center gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-500 dark:text-amber-400 shrink-0">
                            <FileEdit class="w-5 h-5" />
                        </div>
                        <div>
                            <p class="text-xs text-muted-foreground font-medium">Draf</p>
                            <p class="text-xl sm:text-2xl font-bold text-foreground">{{ stats.draft_blogs }}</p>
                        </div>
                    </CardContent>
                </Card>

                <Card class="border-border bg-card shadow-sm">
                    <CardContent class="p-4 flex items-center gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-500 dark:text-blue-400 shrink-0">
                            <Tag class="w-5 h-5" />
                        </div>
                        <div>
                            <p class="text-xs text-muted-foreground font-medium">Kategori</p>
                            <p class="text-xl sm:text-2xl font-bold text-foreground">{{ stats.total_categories }}</p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Navigation Tabs (Biodata Clean Style) -->
            <div class="flex items-center gap-1.5 overflow-x-auto pb-2 border-b border-border text-sm scrollbar-none w-full max-w-full min-w-0">
                <button
                    type="button"
                    :class="[
                        'flex items-center gap-2 px-4 py-2.5 rounded-lg font-medium text-xs tracking-wide uppercase whitespace-nowrap transition-all duration-200',
                        activeTab === 'list'
                            ? 'bg-card hover:bg-accent text-foreground border border-white font-bold'
                            : 'text-muted-foreground hover:text-foreground hover:bg-accent/50'
                    ]"
                    @click="activeTab = 'list'"
                >
                    <Layers class="w-3.5 h-3.5" />
                    <span>Daftar Artikel</span>
                    <span class="ml-1 px-1.5 py-0.5 rounded-full text-[10px] bg-accent text-foreground border border-border font-bold">
                        {{ blogs.length }}
                    </span>
                </button>

                <button
                    type="button"
                    :class="[
                        'flex items-center gap-2 px-4 py-2.5 rounded-lg font-medium text-xs tracking-wide uppercase whitespace-nowrap transition-all duration-200',
                        activeTab === 'create'
                            ? 'bg-card hover:bg-accent text-foreground border border-white font-bold'
                            : 'text-muted-foreground hover:text-foreground hover:bg-accent/50'
                    ]"
                    @click="activeTab = 'create'"
                >
                    <Plus class="w-3.5 h-3.5" />
                    <span>Tulis Artikel Baru</span>
                </button>

                <button
                    type="button"
                    :class="[
                        'flex items-center gap-2 px-4 py-2.5 rounded-lg font-medium text-xs tracking-wide uppercase whitespace-nowrap transition-all duration-200',
                        activeTab === 'preview'
                            ? 'bg-card hover:bg-accent text-foreground border border-white font-bold'
                            : 'text-muted-foreground hover:text-foreground hover:bg-accent/50'
                    ]"
                    @click="activeTab = 'preview'"
                >
                    <Eye class="w-3.5 h-3.5" />
                    <span>Pratinjau Publik</span>
                </button>
            </div>

            <!-- ============================================== -->
            <!-- TAB 1: DAFTAR ARTIKEL (TANSTACK TABLE)         -->
            <!-- ============================================== -->
            <div v-show="activeTab === 'list'" class="space-y-4">
                <Card class="border-border bg-card shadow-sm">
                    <CardHeader class="p-4 sm:p-5 border-b border-border/60">
                        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
                            <!-- Search -->
                            <div class="relative flex-1 max-w-md">
                                <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                                <Input
                                    v-model="searchQuery"
                                    placeholder="Cari judul artikel, slug, kategori, ringkasan..."
                                    class="pl-9 h-9 text-xs bg-card border-input"
                                />
                            </div>

                            <!-- Filter Controls -->
                            <div class="flex flex-wrap items-center gap-2">
                                <!-- Status Pills -->
                                <div class="flex items-center gap-1.5">
                                    <button
                                        v-for="st in [
                                            { key: 'all', label: 'Semua' },
                                            { key: 'published', label: 'Diterbitkan' },
                                            { key: 'draft', label: 'Draf' },
                                        ]"
                                        :key="st.key"
                                        type="button"
                                        :class="[
                                            'px-3 py-1.5 rounded-lg text-xs font-medium transition-colors whitespace-nowrap border',
                                            selectedStatus === st.key
                                                ? 'bg-primary text-primary-foreground border-primary font-semibold shadow-sm'
                                                : 'bg-card hover:bg-accent text-muted-foreground border-border'
                                        ]"
                                        @click="selectedStatus = st.key as any"
                                    >
                                        {{ st.label }}
                                    </button>
                                </div>

                                <div class="h-4 w-px bg-border mx-1"></div>

                                <!-- Category Dropdown -->
                                <select
                                    v-model="selectedCategory"
                                    class="h-8 px-3 rounded-lg text-xs bg-card border border-border text-foreground focus:outline-none focus:ring-1 focus:ring-primary"
                                >
                                    <option value="all">Semua Kategori</option>
                                    <option v-for="cat in categories" :key="cat" :value="cat">
                                        {{ cat }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </CardHeader>

                    <!-- TanStack Table Content -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-muted/40 border-b border-border text-muted-foreground uppercase text-[11px] tracking-wider font-semibold">
                                <tr v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
                                    <th
                                        v-for="header in headerGroup.headers"
                                        :key="header.id"
                                        class="p-3.5 sm:p-4 whitespace-nowrap"
                                    >
                                        <FlexRender :header="header" />
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border/60">
                                <tr v-if="table.getPaginatedRowModel().rows.length === 0">
                                    <td :colspan="columns.length" class="p-8 text-center text-muted-foreground text-sm">
                                        Tidak ada artikel blog yang ditemukan.
                                    </td>
                                </tr>
                                <tr
                                    v-for="row in table.getPaginatedRowModel().rows"
                                    :key="row.id"
                                    class="hover:bg-muted/30 transition-colors"
                                >
                                    <td
                                        v-for="cell in row.getAllCells()"
                                        :key="cell.id"
                                        class="p-3.5 sm:p-4 align-middle"
                                    >
                                        <FlexRender :cell="cell" />
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Footer -->
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-3 px-4 py-3 border-t border-border bg-muted/10 text-xs text-muted-foreground">
                        <div>
                            Menampilkan {{ filteredBlogs.length }} artikel
                            <span v-if="table.getPageCount() > 1">
                                (Halaman {{ (table.atoms.pagination?.get()?.pageIndex ?? 0) + 1 }} dari {{ table.getPageCount() }})
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <Button
                                size="sm"
                                variant="outline"
                                class="h-8 text-xs bg-card border-border"
                                :disabled="!table.getCanPreviousPage()"
                                @click="table.previousPage()"
                            >
                                Sebelumnya
                            </Button>
                            <Button
                                size="sm"
                                variant="outline"
                                class="h-8 text-xs bg-card border-border"
                                :disabled="!table.getCanNextPage()"
                                @click="table.nextPage()"
                            >
                                Berikutnya
                            </Button>
                        </div>
                    </div>
                </Card>
            </div>

            <!-- ============================================== -->
            <!-- TAB 2: TULIS ARTIKEL BARU (CREATE)             -->
            <!-- ============================================== -->
            <div v-show="activeTab === 'create'" class="space-y-6">
                <Card class="border-border bg-card shadow-sm">
                    <CardHeader class="border-b border-border/60 pb-5">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div>
                                <CardTitle class="text-lg font-bold text-foreground flex items-center gap-2">
                                    <Sparkles class="w-5 h-5 text-primary" />
                                    <span>Tulis Artikel Blog Baru</span>
                                </CardTitle>
                                <CardDescription class="text-xs text-muted-foreground mt-0.5">
                                    Masukkan rincian artikel dan gunakan Rich Text Editor Tiptap untuk memformat konten secara visual.
                                </CardDescription>
                            </div>
                            <div class="flex items-center gap-2">
                                <Button
                                    type="button"
                                    variant="outline"
                                    class="h-8 text-xs bg-card border-border"
                                    @click="activeTab = 'list'"
                                >
                                    Batal
                                </Button>
                                <Button
                                    type="button"
                                    class="h-8 text-xs font-semibold"
                                    :disabled="isCreating"
                                    @click="submitCreate"
                                >
                                    {{ isCreating ? 'Menyimpan...' : 'Simpan & Publikasikan' }}
                                </Button>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent class="p-6 space-y-5">
                        <!-- Judul & Slug -->
                        <div class="grid md:grid-cols-12 gap-4">
                            <div class="md:col-span-7 space-y-1.5">
                                <Label class="text-[11px] font-semibold text-muted-foreground uppercase tracking-wider">
                                    Judul Artikel
                                </Label>
                                <Input
                                    v-model="createForm.title"
                                    type="text"
                                    required
                                    placeholder="Contoh: Mengapa Memilih Laravel untuk Proyek Skala Besar di 2024"
                                    class="h-9 text-xs bg-card border-input"
                                    @input="generateSlug('create')"
                                />
                            </div>

                            <div class="md:col-span-5 space-y-1.5">
                                <Label class="text-[11px] font-semibold text-muted-foreground uppercase tracking-wider">
                                    URL Slug
                                </Label>
                                <Input
                                    v-model="createForm.slug"
                                    type="text"
                                    required
                                    placeholder="mengapa-memilih-laravel-2024"
                                    class="h-9 text-xs font-mono bg-card border-input"
                                />
                            </div>
                        </div>

                        <!-- Kategori, Waktu Baca, Tanggal -->
                        <div class="grid sm:grid-cols-3 gap-4">
                            <div class="space-y-1.5">
                                <Label class="text-[11px] font-semibold text-muted-foreground uppercase tracking-wider">
                                    Kategori
                                </Label>
                                <Input
                                    v-model="createForm.category"
                                    type="text"
                                    required
                                    placeholder="Architecture, Frontend..."
                                    class="h-9 text-xs bg-card border-input"
                                />
                                <div class="flex items-center gap-1.5 flex-wrap pt-1">
                                    <button
                                        v-for="chip in ['Architecture', 'Frontend', 'Backend', 'DevOps', 'Database']"
                                        :key="chip"
                                        type="button"
                                        class="px-2 py-0.5 rounded text-[10px] bg-muted hover:bg-accent text-muted-foreground hover:text-foreground border border-border transition-colors"
                                        @click="createForm.category = chip"
                                    >
                                        {{ chip }}
                                    </button>
                                </div>
                            </div>

                            <div class="space-y-1.5">
                                <Label class="text-[11px] font-semibold text-muted-foreground uppercase tracking-wider">
                                    Waktu Baca (Read Time)
                                </Label>
                                <Input
                                    v-model="createForm.read_time"
                                    type="text"
                                    placeholder="Contoh: 5 min read"
                                    class="h-9 text-xs bg-card border-input"
                                />
                            </div>

                            <div class="space-y-1.5">
                                <Label class="text-[11px] font-semibold text-muted-foreground uppercase tracking-wider">
                                    Tanggal Publikasi
                                </Label>
                                <Input
                                    v-model="createForm.published_at"
                                    type="date"
                                    class="h-9 text-xs bg-card border-input"
                                />
                            </div>
                        </div>

                        <!-- Thumbnail URL -->
                        <div class="space-y-1.5">
                            <Label class="text-[11px] font-semibold text-muted-foreground uppercase tracking-wider">
                                URL Gambar Thumbnail / Cover
                            </Label>
                            <Input
                                v-model="createForm.thumbnail_url"
                                type="text"
                                placeholder="https://images.unsplash.com/photo-..."
                                class="h-9 text-xs bg-card border-input"
                            />
                        </div>

                        <!-- Excerpt -->
                        <div class="space-y-1.5">
                            <Label class="text-[11px] font-semibold text-muted-foreground uppercase tracking-wider">
                                Ringkasan Singkat (Excerpt)
                            </Label>
                            <Textarea
                                v-model="createForm.excerpt"
                                rows="2"
                                placeholder="Ringkasan 1-2 kalimat pengantar artikel yang memikat pembaca..."
                                class="text-xs bg-card border-input resize-none"
                            />
                        </div>

                        <!-- Rich Text Editor (Tiptap) -->
                        <div class="space-y-1.5">
                            <Label class="text-[11px] font-semibold text-muted-foreground uppercase tracking-wider">
                                Konten Lengkap Artikel (Rich Editor)
                            </Label>
                            <TiptapEditor
                                v-model="createForm.content"
                                placeholder="Tulis artikel lengkap Anda di sini..."
                            />
                        </div>

                        <!-- Publish Checkbox Toggle -->
                        <div class="flex items-center gap-3 pt-3 border-t border-border">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input
                                    type="checkbox"
                                    v-model="createForm.is_published"
                                    class="rounded border-input text-primary focus:ring-primary w-4 h-4"
                                />
                                <span class="text-xs font-semibold text-foreground">Langsung Terbitkan Artikel Ini</span>
                            </label>
                            <span class="text-[11px] text-muted-foreground">(Jika tidak dicentang, artikel akan tersimpan sebagai draf)</span>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- ============================================== -->
            <!-- TAB 3: PRATINJAU KARTU PUBLIK                  -->
            <!-- ============================================== -->
            <div v-show="activeTab === 'preview'" class="space-y-6">
                <Card class="border-border bg-card shadow-sm">
                    <CardHeader class="border-b border-border/60 pb-5">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div>
                                <CardTitle class="text-lg font-bold text-foreground">Pratinjau Kartu Artikel Publik</CardTitle>
                                <CardDescription class="text-xs text-muted-foreground mt-0.5">
                                    Simulasi tampilan kartu artikel yang dilihat oleh pengunjung pada halaman landing page publik.
                                </CardDescription>
                            </div>
                            <a
                                href="/blog"
                                target="_blank"
                                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-card hover:bg-accent text-xs font-semibold text-foreground border border-border transition-colors"
                            >
                                <ExternalLink class="w-3.5 h-3.5" />
                                <span>Buka Halaman Publik</span>
                            </a>
                        </div>
                    </CardHeader>
                    <CardContent class="p-6">
                        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <article
                                v-for="b in filteredBlogs"
                                :key="b.id"
                                class="rounded-2xl bg-card border border-border overflow-hidden flex flex-col hover:border-foreground/20 transition-all group"
                            >
                                <!-- Thumbnail -->
                                <div class="relative aspect-video w-full overflow-hidden bg-muted">
                                    <img
                                        v-if="b.thumbnail_url"
                                        :src="b.thumbnail_url"
                                        :alt="b.title"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                    />
                                    <div class="absolute top-3 left-3">
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold border backdrop-blur-md" :class="getCategoryColor(b.category)">
                                            {{ b.category }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Content -->
                                <div class="p-5 flex flex-col flex-1">
                                    <div class="flex items-center gap-3 text-xs text-muted-foreground mb-2.5 font-mono">
                                        <div class="flex items-center gap-1">
                                            <Clock class="w-3.5 h-3.5 text-primary" />
                                            <span>{{ b.read_time || '5 min read' }}</span>
                                        </div>
                                        <span>•</span>
                                        <div class="flex items-center gap-1">
                                            <Calendar class="w-3.5 h-3.5" />
                                            <span>{{ formatDate(b.published_at) }}</span>
                                        </div>
                                    </div>

                                    <h3 class="text-base font-bold text-foreground group-hover:text-primary transition-colors line-clamp-2 mb-2">
                                        {{ b.title }}
                                    </h3>

                                    <p class="text-xs text-muted-foreground line-clamp-3 mb-4 flex-1 leading-relaxed">
                                        {{ b.excerpt || 'Tidak ada ringkasan yang tersedia untuk artikel ini.' }}
                                    </p>

                                    <div class="pt-4 border-t border-border flex items-center justify-between">
                                        <a
                                            :href="'/blog/' + b.slug"
                                            target="_blank"
                                            class="inline-flex items-center gap-1.5 text-xs font-semibold text-primary hover:underline"
                                        >
                                            <span>Baca Selengkapnya</span>
                                            <ArrowUpRight class="w-3.5 h-3.5" />
                                        </a>

                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-mono"
                                            :class="b.is_published ? 'bg-emerald-500/10 text-emerald-500 dark:text-emerald-400' : 'bg-muted text-muted-foreground'"
                                        >
                                            {{ b.is_published ? 'Publik' : 'Draf' }}
                                        </span>
                                    </div>
                                </div>
                            </article>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- DIALOG: EDIT ARTIKEL BLOG                      -->
        <!-- ============================================== -->
        <Dialog :open="isEditModalOpen" @update:open="isEditModalOpen = $event">
            <DialogContent class="sm:max-w-4xl bg-card border-border text-foreground max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                    <div class="flex items-center justify-between gap-4">
                        <DialogTitle class="text-lg font-bold text-foreground flex items-center gap-2">
                            <FileEdit class="w-5 h-5 text-primary" />
                            <span>Edit Artikel Blog</span>
                        </DialogTitle>
                        <span
                            v-if="editingBlog"
                            class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold border"
                            :class="getCategoryColor(editingBlog.category)"
                        >
                            {{ editingBlog.category }}
                        </span>
                    </div>
                    <DialogDescription class="text-muted-foreground text-xs">
                        Perbarui rincian, kategori, thumbnail, atau isi artikel dengan Rich Text Editor.
                    </DialogDescription>
                </DialogHeader>

                <form @submit.prevent="submitEdit" class="space-y-4 py-2 text-xs">
                    <!-- Judul & Slug -->
                    <div class="grid md:grid-cols-12 gap-4">
                        <div class="md:col-span-7 space-y-1.5">
                            <Label class="text-[11px] font-semibold text-muted-foreground uppercase tracking-wider">
                                Judul Artikel
                            </Label>
                            <Input
                                v-model="editForm.title"
                                type="text"
                                required
                                class="h-9 text-xs bg-card border-input"
                                @input="generateSlug('edit')"
                            />
                        </div>

                        <div class="md:col-span-5 space-y-1.5">
                            <Label class="text-[11px] font-semibold text-muted-foreground uppercase tracking-wider">
                                URL Slug
                            </Label>
                            <Input
                                v-model="editForm.slug"
                                type="text"
                                required
                                class="h-9 text-xs font-mono bg-card border-input"
                            />
                        </div>
                    </div>

                    <!-- Kategori, Waktu Baca, Tanggal -->
                    <div class="grid sm:grid-cols-3 gap-4">
                        <div class="space-y-1.5">
                            <Label class="text-[11px] font-semibold text-muted-foreground uppercase tracking-wider">
                                Kategori
                            </Label>
                            <Input
                                v-model="editForm.category"
                                type="text"
                                required
                                class="h-9 text-xs bg-card border-input"
                            />
                        </div>

                        <div class="space-y-1.5">
                            <Label class="text-[11px] font-semibold text-muted-foreground uppercase tracking-wider">
                                Waktu Baca
                            </Label>
                            <Input
                                v-model="editForm.read_time"
                                type="text"
                                class="h-9 text-xs bg-card border-input"
                            />
                        </div>

                        <div class="space-y-1.5">
                            <Label class="text-[11px] font-semibold text-muted-foreground uppercase tracking-wider">
                                Tanggal Publikasi
                            </Label>
                            <Input
                                v-model="editForm.published_at"
                                type="date"
                                class="h-9 text-xs bg-card border-input"
                            />
                        </div>
                    </div>

                    <!-- Thumbnail URL -->
                    <div class="space-y-1.5">
                        <Label class="text-[11px] font-semibold text-muted-foreground uppercase tracking-wider">
                            URL Gambar Thumbnail / Cover
                        </Label>
                        <Input
                            v-model="editForm.thumbnail_url"
                            type="text"
                            class="h-9 text-xs bg-card border-input"
                        />
                    </div>

                    <!-- Excerpt -->
                    <div class="space-y-1.5">
                        <Label class="text-[11px] font-semibold text-muted-foreground uppercase tracking-wider">
                            Ringkasan Singkat (Excerpt)
                        </Label>
                        <Textarea
                            v-model="editForm.excerpt"
                            rows="2"
                            class="text-xs bg-card border-input resize-none"
                        />
                    </div>

                    <!-- Rich Text Editor (Tiptap) -->
                    <div class="space-y-1.5">
                        <Label class="text-[11px] font-semibold text-muted-foreground uppercase tracking-wider">
                            Konten Lengkap Artikel (Rich Editor)
                        </Label>
                        <TiptapEditor
                            v-model="editForm.content"
                            placeholder="Tulis artikel lengkap Anda di sini..."
                        />
                    </div>

                    <!-- Publish Toggle Switch -->
                    <div class="flex items-center gap-3 pt-3 border-t border-border">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input
                                type="checkbox"
                                v-model="editForm.is_published"
                                class="rounded border-input text-primary focus:ring-primary w-4 h-4"
                            />
                            <span class="text-xs font-semibold text-foreground">Artikel Diterbitkan (Aktif)</span>
                        </label>
                        <span class="text-[11px] text-muted-foreground">(Jika tidak aktif, artikel berstatus Draf dan tidak muncul di publik)</span>
                    </div>

                    <DialogFooter class="flex items-center justify-end gap-2 pt-4 border-t border-border">
                        <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            class="h-8 text-xs bg-card border-border"
                            @click="isEditModalOpen = false"
                        >
                            Batal
                        </Button>
                        <Button
                            type="submit"
                            size="sm"
                            class="h-8 text-xs font-semibold"
                            :disabled="isUpdating"
                        >
                            {{ isUpdating ? 'Menyimpan...' : 'Simpan Perubahan' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- ============================================== -->
        <!-- DIALOG: KONFIRMASI HAPUS ARTIKEL               -->
        <!-- ============================================== -->
        <Dialog :open="isDeleteModalOpen" @update:open="isDeleteModalOpen = $event">
            <DialogContent class="sm:max-w-md bg-card border-border text-foreground">
                <DialogHeader>
                    <DialogTitle class="text-base font-bold text-foreground flex items-center gap-2">
                        <AlertCircle class="w-5 h-5 text-destructive shrink-0" />
                        <span>Hapus Artikel Blog?</span>
                    </DialogTitle>
                    <DialogDescription class="text-muted-foreground text-xs pt-1">
                        Apakah Anda yakin ingin menghapus artikel <strong class="text-foreground">{{ blogToDelete?.title }}</strong>? Tindakan ini tidak dapat dibatalkan.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="flex items-center justify-end gap-2 pt-4 border-t border-border">
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        class="h-8 text-xs bg-card border-border"
                        @click="isDeleteModalOpen = false"
                    >
                        Batal
                    </Button>
                    <Button
                        type="button"
                        variant="destructive"
                        size="sm"
                        class="h-8 text-xs"
                        @click="executeDelete"
                    >
                        Hapus Artikel
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
