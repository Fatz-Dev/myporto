<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, h, reactive, ref } from 'vue';
import {
    createTableHook,
    createColumnHelper,
    createCoreRowModel,
    FlexRender
} from '@tanstack/vue-table';
import {
    ArrowDown,
    ArrowUp,
    Check,
    CheckCircle2,
    Compass,
    ExternalLink,
    Eye,
    EyeOff,
    Layers,
    LayoutGrid,
    LayoutTemplate,
    Move,
    Navigation,
    Pencil,
    Search,
    Sliders,
    Sparkles,
    ToggleLeft,
    ToggleRight
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

interface LandingSection {
    id: number;
    section_key: string;
    name: string;
    nav_label: string | null;
    is_visible: boolean;
    show_in_navbar: boolean;
    order: number;
    custom_title: string | null;
    custom_subtitle: string | null;
    metadata?: any;
    updated_at: string;
}

const props = defineProps<{
    sections: LandingSection[];
    stats: {
        total_sections: number;
        visible_sections: number;
        navbar_sections: number;
        hidden_sections: number;
    };
}>();

const page = usePage();
const activeTab = ref<'sections' | 'preview'>('sections');
const searchSection = ref('');
const visibilityFilter = ref<'all' | 'visible' | 'hidden' | 'navbar'>('all');

// Edit Modal
const isEditModalOpen = ref(false);
const editingSection = ref<LandingSection | null>(null);
const editForm = reactive({
    name: '',
    nav_label: '',
    custom_title: '',
    custom_subtitle: '',
    order: 0,
    is_visible: true,
    show_in_navbar: true,
});
const isSubmitting = ref(false);

const filteredSections = computed(() => {
    return props.sections.filter((sec) => {
        if (visibilityFilter.value === 'visible' && !sec.is_visible) return false;
        if (visibilityFilter.value === 'hidden' && sec.is_visible) return false;
        if (visibilityFilter.value === 'navbar' && !sec.show_in_navbar) return false;

        const q = searchSection.value.toLowerCase().trim();
        if (!q) return true;

        return (
            sec.name.toLowerCase().includes(q) ||
            sec.section_key.toLowerCase().includes(q) ||
            (sec.nav_label && sec.nav_label.toLowerCase().includes(q)) ||
            (sec.custom_title && sec.custom_title.toLowerCase().includes(q))
        );
    });
});

// Active Navbar Preview Items
const activeNavbarItems = computed(() => {
    return props.sections
        .filter((s) => s.is_visible && s.show_in_navbar)
        .sort((a, b) => a.order - b.order);
});

// Toggle Visibility
const toggleVisibility = (sec: LandingSection) => {
    router.patch(`/dashboard/control-pages/${sec.id}/toggle-visibility`, {}, {
        preserveScroll: true,
    });
};

// Toggle Navbar
const toggleNavbar = (sec: LandingSection) => {
    router.patch(`/dashboard/control-pages/${sec.id}/toggle-navbar`, {}, {
        preserveScroll: true,
    });
};

// Quick Reorder Up / Down
const moveOrder = (sec: LandingSection, direction: 'up' | 'down') => {
    const sorted = [...props.sections].sort((a, b) => a.order - b.order);
    const currentIndex = sorted.findIndex((s) => s.id === sec.id);
    if (currentIndex === -1) return;

    const targetIndex = direction === 'up' ? currentIndex - 1 : currentIndex + 1;
    if (targetIndex < 0 || targetIndex >= sorted.length) return;

    const targetSec = sorted[targetIndex];

    // Swap order values
    const orders = [
        { id: sec.id, order: targetSec.order },
        { id: targetSec.id, order: sec.order },
    ];

    router.post('/dashboard/control-pages/reorder', { orders }, {
        preserveScroll: true,
    });
};

// Open Edit Modal
const openEditModal = (sec: LandingSection) => {
    editingSection.value = sec;
    editForm.name = sec.name || '';
    editForm.nav_label = sec.nav_label || '';
    editForm.custom_title = sec.custom_title || '';
    editForm.custom_subtitle = sec.custom_subtitle || '';
    editForm.order = sec.order;
    editForm.is_visible = Boolean(sec.is_visible);
    editForm.show_in_navbar = Boolean(sec.show_in_navbar);
    isEditModalOpen.value = true;
};

// Submit Edit Form
const submitEditForm = () => {
    if (!editingSection.value) return;
    isSubmitting.value = true;

    router.put(`/dashboard/control-pages/${editingSection.value.id}`, editForm, {
        preserveScroll: true,
        onSuccess: () => {
            isEditModalOpen.value = false;
            editingSection.value = null;
        },
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
};

const getSectionBadge = (key: string) => {
    switch (key) {
        case 'hero':
            return { color: 'bg-indigo-500/10 text-indigo-500 dark:text-indigo-400 border-indigo-500/20' };
        case 'about':
            return { color: 'bg-emerald-500/10 text-emerald-500 dark:text-emerald-400 border-emerald-500/20' };
        case 'skills':
            return { color: 'bg-cyan-500/10 text-cyan-500 dark:text-cyan-400 border-cyan-500/20' };
        case 'education':
            return { color: 'bg-purple-500/10 text-purple-500 dark:text-purple-400 border-purple-500/20' };
        case 'portfolio':
            return { color: 'bg-amber-500/10 text-amber-500 dark:text-amber-400 border-amber-500/20' };
        case 'experience':
            return { color: 'bg-blue-500/10 text-blue-500 dark:text-blue-400 border-blue-500/20' };
        case 'blog':
            return { color: 'bg-pink-500/10 text-pink-500 dark:text-pink-400 border-pink-500/20' };
        case 'contact':
            return { color: 'bg-orange-500/10 text-orange-500 dark:text-orange-400 border-orange-500/20' };
        case 'appointment':
            return { color: 'bg-rose-500/10 text-rose-500 dark:text-rose-400 border-rose-500/20' };
        default:
            return { color: 'bg-muted text-muted-foreground border-border' };
    }
};

// =========================================================================
// TANSTACK TABLE INTEGRATION
// =========================================================================
const { useAppTable } = createTableHook({
    getCoreRowModel: createCoreRowModel(),
});

const columnHelper = createColumnHelper<LandingSection>();
const columns = [
    columnHelper.accessor('order', {
        header: () => h('div', { class: 'text-center w-16' }, 'Urutan'),
        cell: ({ row }) => {
            const sec = row.original;
            const idx = row.index;
            const total = filteredSections.value.length;
            return h('div', { class: 'flex items-center justify-center gap-1.5' }, [
                h(
                    'span',
                    {
                        class: 'w-6 h-6 rounded-md bg-muted border border-border text-foreground font-mono text-xs flex items-center justify-center font-bold',
                    },
                    sec.order
                ),
                h('div', { class: 'flex flex-col gap-0.5' }, [
                    h(
                        'button',
                        {
                            type: 'button',
                            class: 'p-0.5 text-muted-foreground hover:text-foreground hover:bg-accent rounded transition-colors disabled:opacity-30 disabled:cursor-not-allowed',
                            disabled: idx === 0,
                            title: 'Pindah ke Atas',
                            onClick: () => moveOrder(sec, 'up'),
                        },
                        [h(ArrowUp, { class: 'w-3 h-3' })]
                    ),
                    h(
                        'button',
                        {
                            type: 'button',
                            class: 'p-0.5 text-muted-foreground hover:text-foreground hover:bg-accent rounded transition-colors disabled:opacity-30 disabled:cursor-not-allowed',
                            disabled: idx === total - 1,
                            title: 'Pindah ke Bawah',
                            onClick: () => moveOrder(sec, 'down'),
                        },
                        [h(ArrowDown, { class: 'w-3 h-3' })]
                    ),
                ]),
            ]);
        },
    }),
    columnHelper.accessor('name', {
        header: 'Seksi & Kunci',
        cell: ({ row }) => {
            const sec = row.original;
            const badge = getSectionBadge(sec.section_key);
            return h('div', { class: 'flex items-center gap-2.5' }, [
                h(
                    'span',
                    {
                        class: [
                            'px-2 py-0.5 rounded text-[10px] font-mono uppercase font-bold border shrink-0',
                            badge.color,
                        ],
                    },
                    sec.section_key
                ),
                h('div', { class: 'min-w-0' }, [
                    h('p', { class: 'font-semibold text-foreground text-xs' }, sec.name),
                ]),
            ]);
        },
    }),
    columnHelper.accessor('nav_label', {
        header: 'Label Navbar',
        cell: ({ row }) => {
            const sec = row.original;
            if (sec.nav_label) {
                return h('div', { class: 'flex items-center gap-1.5' }, [
                    h(Navigation, { class: 'w-3 h-3 text-primary shrink-0' }),
                    h('span', { class: 'font-medium text-foreground text-xs' }, sec.nav_label),
                ]);
            }
            return h('span', { class: 'text-muted-foreground text-xs' }, '-');
        },
    }),
    columnHelper.accessor('custom_title', {
        header: 'Judul Kustom',
        cell: ({ row }) => {
            const sec = row.original;
            if (sec.custom_title) {
                return h('div', { class: 'max-w-xs space-y-0.5' }, [
                    h('p', { class: 'font-semibold text-foreground text-xs truncate' }, sec.custom_title),
                    sec.custom_subtitle
                        ? h('p', { class: 'text-[11px] text-muted-foreground line-clamp-1' }, sec.custom_subtitle)
                        : null,
                ]);
            }
            return h('span', { class: 'text-muted-foreground text-xs italic' }, 'Default');
        },
    }),
    columnHelper.accessor('is_visible', {
        header: () => h('div', { class: 'text-center' }, 'Visibilitas Halaman'),
        cell: ({ row }) => {
            const sec = row.original;
            return h('div', { class: 'flex justify-center' }, [
                h(
                    'button',
                    {
                        type: 'button',
                        class: [
                            'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold transition-all border shadow-sm',
                            sec.is_visible
                                ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/30 hover:bg-emerald-500/20'
                                : 'bg-muted text-muted-foreground border-border hover:bg-accent',
                        ],
                        onClick: () => toggleVisibility(sec),
                    },
                    [
                        sec.is_visible
                            ? h(Eye, { class: 'w-3.5 h-3.5' })
                            : h(EyeOff, { class: 'w-3.5 h-3.5' }),
                        h('span', sec.is_visible ? 'Aktif' : 'Tersembunyi'),
                    ]
                ),
            ]);
        },
    }),
    columnHelper.accessor('show_in_navbar', {
        header: () => h('div', { class: 'text-center' }, 'Tampil di Navbar'),
        cell: ({ row }) => {
            const sec = row.original;
            return h('div', { class: 'flex justify-center' }, [
                h(
                    'button',
                    {
                        type: 'button',
                        class: [
                            'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold transition-all border shadow-sm',
                            sec.show_in_navbar
                                ? 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/30 hover:bg-blue-500/20'
                                : 'bg-muted text-muted-foreground border-border hover:bg-accent',
                        ],
                        onClick: () => toggleNavbar(sec),
                    },
                    [
                        h(Navigation, { class: 'w-3.5 h-3.5' }),
                        h('span', sec.show_in_navbar ? 'Tampil' : 'Sembunyi'),
                    ]
                ),
            ]);
        },
    }),
    columnHelper.display({
        id: 'actions',
        header: () => h('div', { class: 'text-right' }, 'Aksi'),
        cell: ({ row }) => {
            const sec = row.original;
            return h('div', { class: 'flex items-center justify-end' }, [
                h(
                    Button,
                    {
                        size: 'sm',
                        variant: 'outline',
                        class: 'h-8 px-2.5 text-xs bg-card border-border hover:bg-accent text-foreground',
                        onClick: () => openEditModal(sec),
                    },
                    () => [h(Pencil, { class: 'w-3.5 h-3.5 mr-1.5' }), 'Edit']
                ),
            ]);
        },
    }),
];

const table = useAppTable({
    data: filteredSections,
    columns,
});
</script>

<template>
    <Head title="Kontrol Halaman & Seksi Landing" />

    <AppLayout
        :breadcrumbs="[
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Kontrol Halaman', href: '/dashboard/control-pages' },
        ]"
    >
        <div class="flex-1 space-y-6 p-4 sm:p-6 lg:p-8 max-w-7xl w-full min-w-0 mx-auto overflow-x-hidden">
            <!-- Header Banner (Biodata Clean Style) -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-6 border-b border-sidebar-border/70 dark:border-sidebar-border min-w-0">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-foreground flex items-center gap-2.5">
                        <Sliders class="w-7 h-7" />
                        <span>Kontrol Halaman &amp; Seksi Landing</span>
                    </h1>
                    <p class="text-sm text-muted-foreground mt-1">
                        Atur visibilitas, urutan kemunculan, label navigasi, serta judul kustom setiap seksi landing page portofolio secara dinamis.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2.5 sm:gap-3 shrink-0">
                    <a
                        href="/"
                        target="_blank"
                        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-medium border border-border bg-card hover:bg-accent text-foreground transition-colors"
                    >
                        <ExternalLink class="w-3.5 h-3.5" />
                        <span>Lihat Landing Page Publik</span>
                    </a>
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
                            <Layers class="w-5 h-5" />
                        </div>
                        <div>
                            <p class="text-xs text-muted-foreground font-medium">Total Seksi</p>
                            <p class="text-xl sm:text-2xl font-bold text-foreground">{{ stats.total_sections }}</p>
                        </div>
                    </CardContent>
                </Card>

                <Card class="border-border bg-card shadow-sm">
                    <CardContent class="p-4 flex items-center gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-500 dark:text-emerald-400 shrink-0">
                            <Eye class="w-5 h-5" />
                        </div>
                        <div>
                            <p class="text-xs text-muted-foreground font-medium">Seksi Aktif</p>
                            <p class="text-xl sm:text-2xl font-bold text-foreground">{{ stats.visible_sections }}</p>
                        </div>
                    </CardContent>
                </Card>

                <Card class="border-border bg-card shadow-sm">
                    <CardContent class="p-4 flex items-center gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-500 dark:text-blue-400 shrink-0">
                            <Navigation class="w-5 h-5" />
                        </div>
                        <div>
                            <p class="text-xs text-muted-foreground font-medium">Tampil di Navbar</p>
                            <p class="text-xl sm:text-2xl font-bold text-foreground">{{ stats.navbar_sections }}</p>
                        </div>
                    </CardContent>
                </Card>

                <Card class="border-border bg-card shadow-sm">
                    <CardContent class="p-4 flex items-center gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-500 dark:text-amber-400 shrink-0">
                            <EyeOff class="w-5 h-5" />
                        </div>
                        <div>
                            <p class="text-xs text-muted-foreground font-medium">Tersembunyi</p>
                            <p class="text-xl sm:text-2xl font-bold text-foreground">{{ stats.hidden_sections }}</p>
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
                        activeTab === 'sections'
                            ? 'bg-card hover:bg-accent text-foreground border border-white font-bold'
                            : 'text-muted-foreground hover:text-foreground hover:bg-accent/50'
                    ]"
                    @click="activeTab = 'sections'"
                >
                    <Layers class="w-3.5 h-3.5" />
                    <span>Daftar Seksi Halaman</span>
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
                    <LayoutGrid class="w-3.5 h-3.5" />
                    <span>Preview Tata Letak</span>
                </button>
            </div>

            <!-- ============================================== -->
            <!-- TAB 1: DAFTAR SEKSI (TANSTACK TABLE)           -->
            <!-- ============================================== -->
            <div v-show="activeTab === 'sections'" class="space-y-4">
                <Card class="border-border bg-card shadow-sm">
                    <CardHeader class="p-4 sm:p-5 border-b border-border/60">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <!-- Search -->
                            <div class="relative flex-1 max-w-md">
                                <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                                <Input
                                    v-model="searchSection"
                                    placeholder="Cari nama seksi, kunci seksi, label..."
                                    class="pl-9 h-9 text-xs bg-card border-input"
                                />
                            </div>

                            <!-- Filter Pills -->
                            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0">
                                <button
                                    v-for="opt in [
                                        { key: 'all', label: 'Semua' },
                                        { key: 'visible', label: 'Seksi Aktif' },
                                        { key: 'hidden', label: 'Tersembunyi' },
                                        { key: 'navbar', label: 'Tampil di Navbar' },
                                    ]"
                                    :key="opt.key"
                                    type="button"
                                    :class="[
                                        'px-3 py-1.5 rounded-lg text-xs font-medium transition-colors whitespace-nowrap border',
                                        visibilityFilter === opt.key
                                            ? 'bg-primary text-primary-foreground border-primary font-semibold shadow-sm'
                                            : 'bg-card hover:bg-accent text-muted-foreground border-border'
                                    ]"
                                    @click="visibilityFilter = opt.key as any"
                                >
                                    {{ opt.label }}
                                </button>
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
                                <tr v-if="table.getRowModel().rows.length === 0">
                                    <td :colspan="columns.length" class="p-8 text-center text-muted-foreground text-sm">
                                        Tidak ada seksi yang sesuai dengan pencarian.
                                    </td>
                                </tr>
                                <tr
                                    v-for="row in table.getRowModel().rows"
                                    :key="row.id"
                                    class="hover:bg-muted/30 transition-colors"
                                    :class="!row.original.is_visible ? 'opacity-60 bg-muted/10' : ''"
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

                    <!-- Table Footer Summary -->
                    <div class="px-4 py-3 border-t border-border bg-muted/10 text-xs text-muted-foreground flex items-center justify-between">
                        <span>Menampilkan {{ filteredSections.length }} seksi landing page</span>
                        <span class="text-[11px]">Gunakan panah naik/turun pada kolom Urutan untuk mengubah posisi</span>
                    </div>
                </Card>
            </div>

            <!-- ============================================== -->
            <!-- TAB 2: PREVIEW TATA LETAK                      -->
            <!-- ============================================== -->
            <div v-show="activeTab === 'preview'" class="space-y-6">
                <!-- Navbar Mockup Card -->
                <Card class="border-border bg-card shadow-sm">
                    <CardHeader class="pb-3 border-b border-border/60">
                        <div class="flex items-center justify-between">
                            <div>
                                <CardTitle class="text-base font-bold text-foreground flex items-center gap-2">
                                    <Navigation class="w-4 h-4 text-primary" />
                                    <span>Pratinjau Navigasi Header (Navbar)</span>
                                </CardTitle>
                                <CardDescription class="text-xs text-muted-foreground mt-0.5">
                                    Item navigasi yang aktif dan muncul di menu navbar landing page.
                                </CardDescription>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-primary/10 border border-primary/20 text-primary">
                                {{ activeNavbarItems.length }} Menu Aktif
                            </span>
                        </div>
                    </CardHeader>
                    <CardContent class="p-4 sm:p-6">
                        <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none">
                            <div
                                v-for="item in activeNavbarItems"
                                :key="item.id"
                                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-muted border border-border text-xs font-semibold text-foreground whitespace-nowrap"
                            >
                                <Navigation class="w-3 h-3 text-primary" />
                                <span>{{ item.nav_label || item.name }}</span>
                                <span class="text-[10px] text-muted-foreground font-mono">#{{ item.order }}</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Visual Section Sequence Flow -->
                <Card class="border-border bg-card shadow-sm">
                    <CardHeader class="pb-3 border-b border-border/60">
                        <CardTitle class="text-base font-bold text-foreground flex items-center gap-2">
                            <LayoutTemplate class="w-4 h-4 text-primary" />
                            <span>Alur Seksi Halaman dari Atas ke Bawah</span>
                        </CardTitle>
                        <CardDescription class="text-xs text-muted-foreground mt-0.5">
                            Urutan tampilan seksi di landing page publik portofolio Anda.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="p-4 sm:p-6 space-y-3">
                        <div
                            v-for="(sec, idx) in props.sections.slice().sort((a, b) => a.order - b.order)"
                            :key="sec.id"
                            class="p-4 rounded-xl border transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-4"
                            :class="[
                                sec.is_visible
                                    ? 'bg-card border-border hover:border-foreground/20'
                                    : 'bg-muted/30 border-dashed border-border opacity-50'
                            ]"
                        >
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="w-8 h-8 rounded-lg bg-muted border border-border text-foreground font-mono text-xs flex items-center justify-center font-bold shrink-0">
                                    {{ sec.order }}
                                </span>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <h3 class="font-bold text-foreground text-sm truncate">{{ sec.name }}</h3>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-mono uppercase font-bold border shrink-0" :class="getSectionBadge(sec.section_key).color">
                                            {{ sec.section_key }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-muted-foreground truncate mt-0.5">
                                        {{ sec.custom_title || 'Judul default seksi' }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 shrink-0">
                                <button
                                    type="button"
                                    :class="[
                                        'px-2.5 py-1 rounded-md text-[11px] font-semibold border transition-all',
                                        sec.is_visible
                                            ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/30'
                                            : 'bg-muted text-muted-foreground border-border'
                                    ]"
                                    @click="toggleVisibility(sec)"
                                >
                                    {{ sec.is_visible ? 'Aktif' : 'Non-Aktif' }}
                                </button>
                                <Button
                                    size="sm"
                                    variant="outline"
                                    class="h-7 px-2.5 text-xs bg-card border-border hover:bg-accent"
                                    @click="openEditModal(sec)"
                                >
                                    <Pencil class="w-3 h-3 mr-1" />
                                    Edit
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- DIALOG: EDIT PENGATURAN SEKSI                  -->
        <!-- ============================================== -->
        <Dialog :open="isEditModalOpen" @update:open="isEditModalOpen = $event">
            <DialogContent class="sm:max-w-xl bg-card border-border text-foreground">
                <DialogHeader>
                    <div class="flex items-center justify-between gap-4">
                        <DialogTitle class="text-lg font-bold text-foreground">Edit Pengaturan Seksi</DialogTitle>
                        <span
                            v-if="editingSection"
                            :class="[
                                'px-2 py-0.5 rounded text-[10px] font-mono uppercase font-bold border',
                                getSectionBadge(editingSection.section_key).color,
                            ]"
                        >
                            {{ editingSection.section_key }}
                        </span>
                    </div>
                    <DialogDescription class="text-muted-foreground text-xs">
                        Konfigurasikan penamaan, judul khusus, posisi urutan, dan visibilitas seksi ini.
                    </DialogDescription>
                </DialogHeader>

                <form @submit.prevent="submitEditForm" class="space-y-4 py-2 text-xs">
                    <!-- Name & Nav Label -->
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <Label for="sec_name" class="text-[11px] font-semibold text-muted-foreground uppercase tracking-wider">
                                Nama Seksi
                            </Label>
                            <Input
                                id="sec_name"
                                v-model="editForm.name"
                                placeholder="Contoh: Portofolio Proyek"
                                class="h-9 text-xs bg-card border-input"
                                required
                            />
                        </div>

                        <div class="space-y-1.5">
                            <Label for="sec_nav" class="text-[11px] font-semibold text-muted-foreground uppercase tracking-wider">
                                Label Menu Navbar
                            </Label>
                            <Input
                                id="sec_nav"
                                v-model="editForm.nav_label"
                                placeholder="Contoh: Portofolio"
                                class="h-9 text-xs bg-card border-input"
                            />
                        </div>
                    </div>

                    <!-- Custom Title -->
                    <div class="space-y-1.5">
                        <Label for="sec_title" class="text-[11px] font-semibold text-muted-foreground uppercase tracking-wider">
                            Judul Kustom (Opsional)
                        </Label>
                        <Input
                            id="sec_title"
                            v-model="editForm.custom_title"
                            placeholder="Biarkan kosong untuk judul default seksi"
                            class="h-9 text-xs bg-card border-input"
                        />
                    </div>

                    <!-- Custom Subtitle -->
                    <div class="space-y-1.5">
                        <Label for="sec_sub" class="text-[11px] font-semibold text-muted-foreground uppercase tracking-wider">
                            Subjudul / Deskripsi Kustom
                        </Label>
                        <Textarea
                            id="sec_sub"
                            v-model="editForm.custom_subtitle"
                            rows="2"
                            placeholder="Biarkan kosong untuk subjudul default seksi"
                            class="text-xs bg-card border-input resize-none"
                        />
                    </div>

                    <!-- Order & Toggles -->
                    <div class="grid sm:grid-cols-3 gap-4 pt-2 border-t border-border">
                        <div class="space-y-1.5">
                            <Label for="sec_order" class="text-[11px] font-semibold text-muted-foreground uppercase tracking-wider">
                                Nomor Urut
                            </Label>
                            <Input
                                id="sec_order"
                                v-model.number="editForm.order"
                                type="number"
                                min="1"
                                class="h-9 text-xs bg-card border-input"
                                required
                            />
                        </div>

                        <div class="flex flex-col justify-end">
                            <label class="flex items-center gap-2 cursor-pointer h-9 px-3 rounded-lg border border-border bg-muted/20 hover:bg-muted/40 transition-colors">
                                <input
                                    type="checkbox"
                                    v-model="editForm.is_visible"
                                    class="rounded border-input text-primary focus:ring-primary w-4 h-4"
                                />
                                <span class="text-xs font-semibold text-foreground">Visibel di Halaman</span>
                            </label>
                        </div>

                        <div class="flex flex-col justify-end">
                            <label class="flex items-center gap-2 cursor-pointer h-9 px-3 rounded-lg border border-border bg-muted/20 hover:bg-muted/40 transition-colors">
                                <input
                                    type="checkbox"
                                    v-model="editForm.show_in_navbar"
                                    class="rounded border-input text-primary focus:ring-primary w-4 h-4"
                                />
                                <span class="text-xs font-semibold text-foreground">Tampil di Navbar</span>
                            </label>
                        </div>
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
                            :disabled="isSubmitting"
                        >
                            {{ isSubmitting ? 'Menyimpan...' : 'Simpan Perubahan' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
