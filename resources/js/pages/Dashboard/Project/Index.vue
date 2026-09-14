<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItemType } from '@/types';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed, h } from 'vue';
import {
    createTableHook,
    createColumnHelper,
    createCoreRowModel,
    createPaginatedRowModel,
    rowPaginationFeature,
    FlexRender,
} from '@tanstack/vue-table';
import {
    Briefcase,
    Plus,
    Save,
    ExternalLink,
    Trash2,
    Edit2,
    CheckCircle2,
    AlertCircle,
    LoaderCircle,
    Star,
    StarOff,
    Eye,
    EyeOff,
    Github,
    Globe,
    Tag,
    X,
    Image as ImageIcon,
    Calendar,
    Search,
    ChevronLeft,
    ChevronRight,
    Sparkles,
    Layers,
    FolderKanban,
    Play,
    Pause,
    ArrowRight,
    ArrowLeft,
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

interface ProjectItem {
    id: number;
    title: string;
    slug: string;
    category_label: string;
    year: string;
    short_description: string;
    full_content?: string;
    thumbnail_url?: string;
    live_preview_url?: string;
    github_url?: string;
    tech_stack?: string[];
    is_featured: boolean;
    order: number;
    is_active: boolean;
}

const props = defineProps<{
    projects: ProjectItem[];
    flash?: {
        success?: string;
        error?: string;
    };
}>();

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Proyek', href: '/dashboard/projects' },
];

const activeTab = ref<'table' | 'preview'>('table');

// --- Filter & Search State ---
const searchQuery = ref('');
const selectedCategory = ref('all');

const uniqueCategories = computed(() => {
    const set = new Set<string>();
    props.projects.forEach((p) => {
        if (p.category_label) set.add(p.category_label);
    });
    return Array.from(set);
});

const filteredProjects = computed(() => {
    return props.projects.filter((p) => {
        const matchesCat =
            selectedCategory.value === 'all' || p.category_label === selectedCategory.value;
        const q = searchQuery.value.toLowerCase().trim();
        const matchesQuery =
            !q ||
            p.title.toLowerCase().includes(q) ||
            p.short_description.toLowerCase().includes(q) ||
            p.category_label.toLowerCase().includes(q) ||
            (p.tech_stack && p.tech_stack.some((t) => t.toLowerCase().includes(q)));
        return matchesCat && matchesQuery;
    });
});

// --- TanStack Table Integration ---
const { useAppTable } = createTableHook({
    features: {
        rowPaginationFeature,
    },
    getCoreRowModel: createCoreRowModel(),
    getPaginatedRowModel: createPaginatedRowModel(),
});

const columnHelper = createColumnHelper<ProjectItem>();

const columns = [
    columnHelper.accessor('title', {
        header: 'Proyek & Thumbnail',
        cell: ({ row }) => {
            const p = row.original;
            return h('div', { class: 'flex items-center gap-3.5 max-w-md' }, [
                h(
                    'div',
                    {
                        class:
                            'w-16 h-11 rounded-lg bg-white/[0.04] border border-white/[0.08] overflow-hidden shrink-0 shadow-sm',
                    },
                    p.thumbnail_url
                        ? [
                              h('img', {
                                  src: p.thumbnail_url,
                                  alt: p.title,
                                  class: 'w-full h-full object-cover',
                              }),
                          ]
                        : [
                              h(
                                  'div',
                                  { class: 'w-full h-full flex items-center justify-center text-muted-foreground' },
                                  [h(ImageIcon, { class: 'w-4 h-4' })]
                              ),
                          ]
                ),
                h('div', { class: 'min-w-0 flex flex-col' }, [
                    h('span', { class: 'font-bold text-foreground truncate' }, p.title),
                    h(
                        'span',
                        { class: 'text-xs text-muted-foreground line-clamp-1' },
                        p.short_description
                    ),
                ]),
            ]);
        },
    }),
    columnHelper.accessor('category_label', {
        header: 'Kategori & Tahun',
        cell: ({ row }) => {
            const p = row.original;
            return h('div', { class: 'flex flex-col gap-1 text-xs' }, [
                h(
                    'span',
                    {
                        class:
                            'inline-flex items-center w-fit px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase bg-white/10 text-white border border-white/25',
                    },
                    p.category_label || 'PROJECT'
                ),
                h('span', { class: 'font-mono text-muted-foreground' }, p.year || '-'),
            ]);
        },
    }),
    columnHelper.accessor('tech_stack', {
        header: 'Tech Stack',
        cell: ({ row }) => {
            const stack = row.original.tech_stack || [];
            if (!stack.length) return h('span', { class: 'text-xs text-muted-foreground italic' }, '-');
            return h('div', { class: 'flex flex-wrap gap-1 max-w-xs' }, [
                ...stack.slice(0, 3).map((t) =>
                    h(
                        'span',
                        {
                            class:
                                'px-1.5 py-0.5 rounded text-[10px] font-mono bg-white/[0.04] text-slate-300 border border-white/[0.06]',
                        },
                        t
                    )
                ),
                stack.length > 3
                    ? h(
                          'span',
                          { class: 'text-[10px] font-mono text-muted-foreground px-1' },
                          `+${stack.length - 3}`
                      )
                    : null,
            ]);
        },
    }),
    columnHelper.accessor('is_featured', {
        header: 'Featured',
        cell: ({ row }) => {
            const p = row.original;
            return h(
                'button',
                {
                    type: 'button',
                    onClick: () => toggleFeatured(p),
                    class: [
                        'p-1.5 rounded-lg transition-colors cursor-pointer',
                        p.is_featured
                            ? 'text-amber-400 hover:bg-amber-400/10'
                            : 'text-muted-foreground/40 hover:text-amber-400 hover:bg-muted',
                    ],
                    title: p.is_featured ? 'Hapus dari Featured' : 'Jadikan Featured',
                },
                [
                    h(Star, {
                        class: [
                            'w-4 h-4',
                            p.is_featured ? 'fill-amber-400 text-amber-400' : '',
                        ],
                    }),
                ]
            );
        },
    }),
    columnHelper.accessor('is_active', {
        header: 'Status',
        cell: ({ row }) => {
            const p = row.original;
            return h(
                'button',
                {
                    type: 'button',
                    onClick: () => toggleStatus(p),
                    class: [
                        'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-medium transition-colors cursor-pointer',
                        p.is_active
                            ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 hover:bg-emerald-500/20'
                            : 'bg-muted text-muted-foreground border border-border hover:bg-muted/80',
                    ],
                },
                [
                    h('span', {
                        class: [
                            'w-1.5 h-1.5 rounded-full',
                            p.is_active ? 'bg-emerald-400' : 'bg-muted-foreground',
                        ],
                    }),
                    p.is_active ? 'Aktif' : 'Nonaktif',
                ]
            );
        },
    }),
    columnHelper.display({
        id: 'actions',
        header: () => h('div', { class: 'text-right' }, 'Aksi'),
        cell: ({ row }) => {
            const p = row.original;
            return h('div', { class: 'flex items-center justify-end gap-1.5' }, [
                h(
                    Button,
                    {
                        variant: 'ghost',
                        size: 'icon',
                        class: 'h-8 w-8 text-muted-foreground hover:text-foreground',
                        onClick: () => openEditModal(p),
                    },
                    () => [h(Edit2, { class: 'w-4 h-4' })]
                ),
                h(
                    Button,
                    {
                        variant: 'ghost',
                        size: 'icon',
                        class: 'h-8 w-8 text-muted-foreground hover:text-rose-400',
                        onClick: () => deleteProject(p.id),
                    },
                    () => [h(Trash2, { class: 'w-4 h-4' })]
                ),
            ]);
        },
    }),
];

const table = useAppTable({
    data: filteredProjects,
    columns,
});

// --- Modal State & Form ---
const isModalOpen = ref(false);
const editingId = ref<number | null>(null);
const stackInput = ref('');

const categoryPresets = [
    'ENTERPRISE ERP',
    'HEALTHTECH',
    'FINTECH',
    'FULL STACK',
    'E-COMMERCE',
    'AI & ML',
    'MOBILE APP',
    'DEV TOOLS',
];

const form = useForm({
    title: '',
    category_label: 'FULL STACK',
    year: new Date().getFullYear().toString(),
    short_description: '',
    full_content: '',
    thumbnail_url: '',
    live_preview_url: '',
    github_url: '',
    tech_stack: [] as string[],
    is_featured: false,
    order: 1,
    is_active: true,
});

const addStackTag = () => {
    const s = stackInput.value.trim();
    if (s && !form.tech_stack.includes(s)) {
        form.tech_stack.push(s);
        stackInput.value = '';
    }
};

const removeStackTag = (index: number) => {
    form.tech_stack.splice(index, 1);
};

const openAddModal = () => {
    editingId.value = null;
    form.reset();
    form.tech_stack = [];
    stackInput.value = '';
    form.order = props.projects.length + 1;
    form.year = new Date().getFullYear().toString();
    form.category_label = 'FULL STACK';
    form.is_active = true;
    isModalOpen.value = true;
};

const openEditModal = (p: ProjectItem) => {
    editingId.value = p.id;
    form.title = p.title;
    form.category_label = p.category_label || 'FULL STACK';
    form.year = p.year || new Date().getFullYear().toString();
    form.short_description = p.short_description || '';
    form.full_content = p.full_content || '';
    form.thumbnail_url = p.thumbnail_url || '';
    form.live_preview_url = p.live_preview_url || '';
    form.github_url = p.github_url || '';
    form.tech_stack = Array.isArray(p.tech_stack) ? [...p.tech_stack] : [];
    form.is_featured = !!p.is_featured;
    form.order = p.order || 1;
    form.is_active = p.is_active !== false;
    stackInput.value = '';
    isModalOpen.value = true;
};

const submitForm = () => {
    if (editingId.value) {
        form.put(`/dashboard/projects/${editingId.value}`, {
            preserveScroll: true,
            onSuccess: () => {
                isModalOpen.value = false;
            },
        });
    } else {
        form.post('/dashboard/projects', {
            preserveScroll: true,
            onSuccess: () => {
                isModalOpen.value = false;
            },
        });
    }
};

const deleteProject = (id: number) => {
    if (confirm('Apakah Anda yakin ingin menghapus proyek ini?')) {
        router.delete(`/dashboard/projects/${id}`, {
            preserveScroll: true,
        });
    }
};

const toggleStatus = (p: ProjectItem) => {
    router.patch(`/dashboard/projects/${p.id}/toggle`, {}, {
        preserveScroll: true,
    });
};

const toggleFeatured = (p: ProjectItem) => {
    router.patch(`/dashboard/projects/${p.id}/toggle-featured`, {}, {
        preserveScroll: true,
    });
};

// --- Preview Carousel Controls ---
const previewCarouselTrack = ref<HTMLElement | null>(null);
const previewActiveIndex = ref(0);

const scrollPreviewNext = () => {
    if (!previewCarouselTrack.value || !props.projects.length) return;
    previewActiveIndex.value = (previewActiveIndex.value + 1) % props.projects.length;
    const cards = previewCarouselTrack.value.querySelectorAll('.preview-card');
    if (cards[previewActiveIndex.value]) {
        (cards[previewActiveIndex.value] as HTMLElement).scrollIntoView({
            behavior: 'smooth',
            block: 'nearest',
            inline: 'start',
        });
    }
};

const scrollPreviewPrev = () => {
    if (!previewCarouselTrack.value || !props.projects.length) return;
    previewActiveIndex.value =
        (previewActiveIndex.value - 1 + props.projects.length) % props.projects.length;
    const cards = previewCarouselTrack.value.querySelectorAll('.preview-card');
    if (cards[previewActiveIndex.value]) {
        (cards[previewActiveIndex.value] as HTMLElement).scrollIntoView({
            behavior: 'smooth',
            block: 'nearest',
            inline: 'start',
        });
    }
};
</script>

<template>
    <Head title="Manajemen Proyek & Portofolio" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex-1 space-y-6 p-4 sm:p-6 lg:p-8 max-w-7xl w-full min-w-0 mx-auto overflow-hidden">
            <!-- Header Section -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-border/40 pb-5">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-foreground">
                        Koleksi Proyek Unggulan
                    </h1>
                    <p class="text-sm text-muted-foreground mt-1">
                        Kelola etalase proyek berskala produksi, SaaS, dan sistem backend yang ditampilkan di portofolio publik.
                    </p>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <Button
                        variant="outline"
                        as-child
                        class="gap-2 bg-card hover:bg-accent text-foreground border border-border"
                    >
                        <a href="/portfolio" target="_blank">
                            <ExternalLink class="w-4 h-4" />
                            <span>Lihat Halaman Publik</span>
                        </a>
                    </Button>
                    <Button
                        @click="openAddModal"
                        class="gap-2 bg-card hover:bg-accent text-foreground border border-border font-bold"
                    >
                        <Plus class="w-4 h-4" />
                        <span>Tambah Proyek</span>
                    </Button>
                </div>
            </div>

            <!-- Stats Bar -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
                <Card class="bg-card/40 border-border/60 backdrop-blur-sm">
                    <CardContent class="p-4 sm:p-5 flex items-center justify-between">
                        <div>
                            <p class="text-xs font-mono text-muted-foreground uppercase tracking-wider">Total Proyek</p>
                            <h3 class="text-2xl font-bold mt-1 text-foreground">{{ projects.length }}</h3>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-white/10 border border-white/20 flex items-center justify-center text-white">
                            <Briefcase class="w-5 h-5" />
                        </div>
                    </CardContent>
                </Card>

                <Card class="bg-card/40 border-border/60 backdrop-blur-sm">
                    <CardContent class="p-4 sm:p-5 flex items-center justify-between">
                        <div>
                            <p class="text-xs font-mono text-muted-foreground uppercase tracking-wider">Proyek Aktif</p>
                            <h3 class="text-2xl font-bold mt-1 text-foreground">{{ projects.filter(p => p.is_active).length }}</h3>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
                            <CheckCircle2 class="w-5 h-5" />
                        </div>
                    </CardContent>
                </Card>

                <Card class="bg-card/40 border-border/60 backdrop-blur-sm">
                    <CardContent class="p-4 sm:p-5 flex items-center justify-between">
                        <div>
                            <p class="text-xs font-mono text-muted-foreground uppercase tracking-wider">Unggulan (Featured)</p>
                            <h3 class="text-2xl font-bold mt-1 text-foreground">{{ projects.filter(p => p.is_featured).length }}</h3>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400">
                            <Star class="w-5 h-5 fill-amber-400" />
                        </div>
                    </CardContent>
                </Card>

                <Card class="bg-card/40 border-border/60 backdrop-blur-sm">
                    <CardContent class="p-4 sm:p-5 flex items-center justify-between">
                        <div>
                            <p class="text-xs font-mono text-muted-foreground uppercase tracking-wider">Kategori Terdaftar</p>
                            <h3 class="text-2xl font-bold mt-1 text-foreground">{{ uniqueCategories.length }}</h3>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-purple-500/10 border border-purple-500/20 flex items-center justify-center text-purple-400">
                            <FolderKanban class="w-5 h-5" />
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Tab Navigation -->
            <div class="flex border-b border-border/60 gap-2 overflow-x-auto pb-px">
                <button
                    type="button"
                    @click="activeTab = 'table'"
                    class="px-4 py-2.5 text-xs sm:text-sm font-medium border-b-2 transition-colors duration-200 whitespace-nowrap cursor-pointer flex items-center gap-2"
                    :class="activeTab === 'table'
                        ? 'border-white text-white font-semibold'
                        : 'border-transparent text-muted-foreground hover:text-foreground hover:border-border'"
                >
                    <Layers class="w-4 h-4" />
                    <span>Daftar Proyek ({{ projects.length }})</span>
                </button>
                <button
                    type="button"
                    @click="activeTab = 'preview'"
                    class="px-4 py-2.5 text-xs sm:text-sm font-medium border-b-2 transition-colors duration-200 whitespace-nowrap cursor-pointer flex items-center gap-2"
                    :class="activeTab === 'preview'
                        ? 'border-white text-white font-semibold'
                        : 'border-transparent text-muted-foreground hover:text-foreground hover:border-border'"
                >
                    <Eye class="w-4 h-4" />
                    <span>Pratinjau Publik (Carousel Selected Work)</span>
                </button>
            </div>

            <!-- TAB 1: TANSTACK TABLE -->
            <div v-if="activeTab === 'table'" class="space-y-4">
                <!-- Toolbar -->
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        <div class="relative w-full sm:w-72">
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                            <Input
                                v-model="searchQuery"
                                placeholder="Cari judul, deskripsi, stack..."
                                class="pl-9 h-9 text-xs bg-background/50 border-border/60 focus:border-white"
                            />
                            <button
                                v-if="searchQuery"
                                @click="searchQuery = ''"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground"
                            >
                                <X class="w-3.5 h-3.5" />
                            </button>
                        </div>

                        <select
                            v-model="selectedCategory"
                            class="h-9 px-3 text-xs rounded-md bg-background/50 border border-border/60 text-foreground focus:border-white focus:outline-none"
                        >
                            <option value="all">Semua Kategori</option>
                            <option v-for="cat in uniqueCategories" :key="cat" :value="cat">
                                {{ cat }}
                            </option>
                        </select>
                    </div>

                    <div class="text-xs font-mono text-muted-foreground shrink-0">
                        Menampilkan {{ filteredProjects.length }} dari {{ projects.length }} proyek
                    </div>
                </div>

                <!-- Table Card -->
                <Card class="bg-card/40 border-border/60 backdrop-blur-sm overflow-hidden">
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
                                        Tidak ada proyek yang cocok dengan filter atau pencarian Anda.
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
                                        class="p-3.5 sm:p-4 align-middle whitespace-nowrap"
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
                            Menampilkan {{ filteredProjects.length }} proyek
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

            <!-- TAB 2: PRATINJAU PUBLIK (CAROUSEL SHOWCASE) -->
            <div v-else-if="activeTab === 'preview'" class="space-y-6">
                <!-- Preview Toolbar -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span class="text-xs font-mono text-white">Live Carousel Preview ({{ projects.length }} Items)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <Button
                            variant="outline"
                            size="icon"
                            class="h-8 w-8"
                            @click="scrollPreviewPrev"
                        >
                            <ArrowLeft class="w-4 h-4" />
                        </Button>
                        <Button
                            variant="outline"
                            size="icon"
                            class="h-8 w-8"
                            @click="scrollPreviewNext"
                        >
                            <ArrowRight class="w-4 h-4" />
                        </Button>
                    </div>
                </div>

                <!-- Preview Carousel Container -->
                <div class="p-6 sm:p-8 rounded-2xl bg-black/40 border border-white/[0.08] overflow-hidden">
                    <div
                        ref="previewCarouselTrack"
                        class="flex gap-6 overflow-x-auto scroll-smooth py-2 no-scrollbar"
                    >
                        <div
                            v-for="project in projects"
                            :key="project.id"
                            class="preview-card w-[320px] sm:w-[380px] shrink-0 bg-white/[0.02] border border-white/[0.08] rounded-2xl overflow-hidden flex flex-col justify-between"
                        >
                            <!-- Image -->
                            <div class="relative aspect-video w-full overflow-hidden bg-slate-900">
                                <img
                                    v-if="project.thumbnail_url"
                                    :src="project.thumbnail_url"
                                    :alt="project.title"
                                    class="w-full h-full object-cover"
                                />
                                <div v-else class="w-full h-full flex items-center justify-center text-slate-600">
                                    <ImageIcon class="w-8 h-8" />
                                </div>
                                <div class="absolute inset-0 bg-gradient-to-t from-[#11141d] via-transparent to-transparent opacity-80"></div>
                                <div class="absolute top-3 left-3 flex gap-2">
                                    <span class="px-2.5 py-1 font-mono text-[9px] font-bold rounded-lg uppercase tracking-wider bg-[#0a0c10]/80 text-[#f08c4a] border border-white/30 backdrop-blur-md">
                                        {{ project.category_label }}
                                    </span>
                                    <span class="px-2.5 py-1 font-mono text-[9px] font-bold rounded-lg uppercase tracking-wider bg-[#0a0c10]/80 text-slate-300 border border-white/10 backdrop-blur-md">
                                        {{ project.year }}
                                    </span>
                                </div>
                            </div>

                            <!-- Content -->
                            <div class="p-5 flex-1 flex flex-col justify-between">
                                <div>
                                    <h3 class="text-lg font-bold text-white tracking-tight mb-2">{{ project.title }}</h3>
                                    <p class="text-xs text-slate-400 leading-relaxed mb-4 line-clamp-3">{{ project.short_description }}</p>
                                    <div v-if="project.tech_stack" class="flex flex-wrap gap-1.5 mb-4">
                                        <span
                                            v-for="(tech, idx) in project.tech_stack"
                                            :key="idx"
                                            class="px-2 py-0.5 rounded text-[10px] font-mono bg-white/[0.04] text-slate-300 border border-white/[0.06]"
                                        >
                                            {{ tech }}
                                        </span>
                                    </div>
                                </div>

                                <span class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-white pt-3 border-t border-white/[0.06]">
                                    <span>Explore case study</span>
                                    <ArrowRight class="w-3.5 h-3.5" />
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL: ADD / EDIT PROJECT -->
        <Dialog :open="isModalOpen" @update:open="isModalOpen = $event">
            <DialogContent class="sm:max-w-xl max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle>
                        {{ editingId ? 'Edit Proyek' : 'Tambah Proyek Baru' }}
                    </DialogTitle>
                    <DialogDescription>
                        Lengkapi detail judul, kategori, tahun, deskripsi, dan teknologi yang digunakan.
                    </DialogDescription>
                </DialogHeader>

                <form @submit.prevent="submitForm" class="space-y-4 pt-2">
                    <div class="space-y-1.5">
                        <Label for="title">Judul Proyek *</Label>
                        <Input
                            id="title"
                            v-model="form.title"
                            placeholder="Misal: Investment Portfolio Portal, Supply Chain ERP"
                            class="h-9 text-xs"
                            required
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <Label for="category_label">Kategori Label *</Label>
                            <Input
                                id="category_label"
                                v-model="form.category_label"
                                placeholder="ENTERPRISE ERP / FINTECH"
                                class="h-9 text-xs uppercase font-mono"
                                required
                            />
                            <!-- Presets -->
                            <div class="flex flex-wrap gap-1 pt-1">
                                <button
                                    v-for="cat in categoryPresets"
                                    :key="cat"
                                    type="button"
                                    @click="form.category_label = cat"
                                    class="px-1.5 py-0.5 rounded text-[9px] font-mono bg-muted/60 border border-border hover:border-white hover:text-white transition-colors"
                                >
                                    {{ cat }}
                                </button>
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <Label for="year">Tahun Pembuatan *</Label>
                            <Input
                                id="year"
                                v-model="form.year"
                                placeholder="2024"
                                class="h-9 text-xs font-mono"
                                required
                            />
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <Label for="thumbnail_url">URL Foto / Thumbnail Proyek</Label>
                        <Input
                            id="thumbnail_url"
                            v-model="form.thumbnail_url"
                            placeholder="https://images.unsplash.com/..."
                            class="h-9 text-xs font-mono"
                        />
                        <!-- Live Image Preview -->
                        <div v-if="form.thumbnail_url" class="relative aspect-video w-full rounded-lg overflow-hidden border border-border mt-2 bg-muted/40">
                            <img :src="form.thumbnail_url" alt="Preview" class="w-full h-full object-cover" />
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <Label for="short_description">Deskripsi Ringkas *</Label>
                        <Textarea
                            id="short_description"
                            v-model="form.short_description"
                            placeholder="Ringkasan dampak, arsitektur, dan fitur utama proyek..."
                            class="text-xs min-h-[75px]"
                            required
                        />
                    </div>

                    <!-- Tech Stack Tag Input -->
                    <div class="space-y-1.5">
                        <Label>Tech Stack Tags</Label>
                        <div class="flex items-center gap-2">
                            <Input
                                v-model="stackInput"
                                @keydown.enter.prevent="addStackTag"
                                placeholder="Ketik nama stack (misal: Laravel, PostgreSQL, Docker) lalu tekan Enter..."
                                class="h-9 text-xs bg-background/50"
                            />
                            <Button
                                type="button"
                                @click="addStackTag"
                                class="gap-1.5 h-9 bg-white hover:bg-[#e58546] text-[#0a0c10] font-bold shrink-0"
                            >
                                <Plus class="w-4 h-4" />
                                <span>Tambah</span>
                            </Button>
                        </div>
                        <div class="flex flex-wrap gap-1.5 p-2 rounded-lg bg-muted/40 border border-border/50 min-h-[38px]">
                            <span
                                v-for="(tag, tIdx) in form.tech_stack"
                                :key="tIdx"
                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded text-xs font-mono bg-background border border-border text-foreground"
                            >
                                {{ tag }}
                                <button
                                    type="button"
                                    @click="removeStackTag(tIdx)"
                                    class="text-muted-foreground hover:text-rose-400 ml-1"
                                >
                                    <X class="w-3 h-3" />
                                </button>
                            </span>
                            <span v-if="!form.tech_stack.length" class="text-xs text-muted-foreground italic py-0.5">
                                Belum ada tech stack ditambahkan.
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <Label for="live_preview_url">Tautan Live Demo</Label>
                            <Input
                                id="live_preview_url"
                                v-model="form.live_preview_url"
                                placeholder="https://..."
                                class="h-9 text-xs font-mono"
                            />
                        </div>

                        <div class="space-y-1.5">
                            <Label for="github_url">Tautan Repository (GitHub)</Label>
                            <Input
                                id="github_url"
                                v-model="form.github_url"
                                placeholder="https://github.com/..."
                                class="h-9 text-xs font-mono"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 pt-1">
                        <div class="space-y-1.5">
                            <Label for="order">Urutan Tampil</Label>
                            <Input
                                id="order"
                                type="number"
                                v-model="form.order"
                                class="h-9 text-xs"
                                min="1"
                            />
                        </div>

                        <div class="flex flex-col justify-end space-y-2 pb-1">
                            <div class="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    id="is_featured"
                                    v-model="form.is_featured"
                                    class="rounded border-border text-white focus:ring-white"
                                />
                                <Label for="is_featured" class="cursor-pointer text-xs">
                                    Proyek Unggulan (Featured)
                                </Label>
                            </div>

                            <div class="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    id="is_active"
                                    v-model="form.is_active"
                                    class="rounded border-border text-white focus:ring-white"
                                />
                                <Label for="is_active" class="cursor-pointer text-xs">
                                    Status Aktif (Tampilkan di portofolio)
                                </Label>
                            </div>
                        </div>
                    </div>

                    <DialogFooter class="pt-4 border-t border-border/60">
                        <Button
                            type="button"
                            variant="outline"
                            @click="isModalOpen = false"
                        >
                            Batal
                        </Button>
                        <Button
                            type="submit"
                            :disabled="form.processing"
                            class="bg-white hover:bg-[#e58546] text-[#0a0c10] font-bold"
                        >
                            <LoaderCircle v-if="form.processing" class="w-4 h-4 animate-spin mr-1.5" />
                            <span>{{ editingId ? 'Perbarui Proyek' : 'Simpan Proyek' }}</span>
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
