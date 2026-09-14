<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItemType } from '@/types';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed, h, reactive } from 'vue';
import {
    createTableHook,
    createColumnHelper,
    createCoreRowModel,
    createPaginatedRowModel,
    rowPaginationFeature,
    FlexRender,
} from '@tanstack/vue-table';
import {
    GraduationCap,
    Plus,
    Save,
    ExternalLink,
    Trash2,
    Edit2,
    CheckCircle2,
    AlertCircle,
    LoaderCircle,
    Award,
    School,
    Calendar,
    MapPin,
    Eye,
    Search,
    X,
    ChevronLeft,
    ChevronRight,
    Sparkles,
    Layers,
    Code2,
    BookOpen,
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

interface EducationItem {
    id: number;
    degree_title: string;
    institution_name: string;
    location?: string;
    start_year: string;
    end_year?: string;
    grade?: string;
    description?: string;
    skills_acquired?: string[];
    icon_class?: string;
    credential_id?: string;
    credential_url?: string;
    image_url?: string;
    order: number;
    is_active: boolean;
}

const props = defineProps<{
    educations: EducationItem[];
    flash?: {
        success?: string;
        error?: string;
    };
}>();

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Education', href: '/dashboard/educations' },
];

const activeTab = ref<'table' | 'preview'>('table');

// --- Search Filter ---
const searchQuery = ref('');

const filteredEducations = computed(() => {
    const q = searchQuery.value.toLowerCase().trim();
    if (!q) return props.educations;
    return props.educations.filter((edu) => {
        return (
            edu.degree_title.toLowerCase().includes(q) ||
            edu.institution_name.toLowerCase().includes(q) ||
            (edu.location && edu.location.toLowerCase().includes(q)) ||
            (edu.grade && edu.grade.toLowerCase().includes(q))
        );
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

const columnHelper = createColumnHelper<EducationItem>();

const columns = [
    columnHelper.accessor('degree_title', {
        header: 'Institusi & Gelar',
        cell: ({ row }) => {
            const edu = row.original;
            return h('div', { class: 'flex items-center gap-3' }, [
                // Thumbnail / Icon
                h(
                    'div',
                    {
                        class:
                            'w-11 h-11 rounded-xl bg-white/[0.04] border border-white/[0.08] overflow-hidden flex items-center justify-center shrink-0 shadow-sm',
                    },
                    edu.image_url
                        ? [
                              h('img', {
                                  src: edu.image_url,
                                  alt: edu.institution_name,
                                  class: 'w-full h-full object-cover',
                              }),
                          ]
                        : [
                              h('i', {
                                  class: [edu.icon_class || 'fa-solid fa-graduation-cap', 'text-base text-white'],
                              }),
                          ]
                ),
                // Title and institution
                h('div', { class: 'min-w-0 flex flex-col' }, [
                    h('span', { class: 'font-bold text-foreground truncate' }, edu.degree_title),
                    h(
                        'span',
                        { class: 'text-xs text-muted-foreground truncate' },
                        edu.institution_name
                    ),
                ]),
            ]);
        },
    }),
    columnHelper.accessor('location', {
        header: 'Lokasi & Tahun',
        cell: ({ row }) => {
            const edu = row.original;
            return h('div', { class: 'flex flex-col text-xs' }, [
                h(
                    'span',
                    { class: 'font-mono text-white font-semibold' },
                    `${edu.start_year} — ${edu.end_year || 'Present'}`
                ),
                h(
                    'span',
                    { class: 'text-muted-foreground' },
                    edu.location || '-'
                ),
            ]);
        },
    }),
    columnHelper.accessor('grade', {
        header: 'Predikat / Honours',
        cell: ({ row }) => {
            const edu = row.original;
            return edu.grade
                ? h(
                      'span',
                      {
                          class:
                              'inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20',
                      },
                      edu.grade
                  )
                : h('span', { class: 'text-xs text-muted-foreground italic' }, '-');
        },
    }),
    columnHelper.accessor('order', {
        header: 'Urutan',
        cell: ({ row }) => {
            return h(
                'span',
                { class: 'font-mono text-xs text-muted-foreground' },
                `#${row.original.order}`
            );
        },
    }),
    columnHelper.accessor('is_active', {
        header: 'Status',
        cell: ({ row }) => {
            const edu = row.original;
            return h(
                'button',
                {
                    type: 'button',
                    onClick: () => toggleStatus(edu),
                    class: [
                        'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-medium transition-colors cursor-pointer',
                        edu.is_active
                            ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 hover:bg-emerald-500/20'
                            : 'bg-muted text-muted-foreground border border-border hover:bg-muted/80',
                    ],
                },
                [
                    h('span', {
                        class: [
                            'w-1.5 h-1.5 rounded-full',
                            edu.is_active ? 'bg-emerald-400' : 'bg-muted-foreground',
                        ],
                    }),
                    edu.is_active ? 'Aktif' : 'Nonaktif',
                ]
            );
        },
    }),
    columnHelper.display({
        id: 'actions',
        header: () => h('div', { class: 'text-right' }, 'Aksi'),
        cell: ({ row }) => {
            const edu = row.original;
            return h('div', { class: 'flex items-center justify-end gap-1.5' }, [
                h(
                    Button,
                    {
                        variant: 'ghost',
                        size: 'icon',
                        class: 'h-8 w-8 text-muted-foreground hover:text-foreground',
                        onClick: () => openEditModal(edu),
                    },
                    () => [h(Edit2, { class: 'w-4 h-4' })]
                ),
                h(
                    Button,
                    {
                        variant: 'ghost',
                        size: 'icon',
                        class: 'h-8 w-8 text-muted-foreground hover:text-rose-400',
                        onClick: () => deleteEducation(edu.id),
                    },
                    () => [h(Trash2, { class: 'w-4 h-4' })]
                ),
            ]);
        },
    }),
];

const table = useAppTable({
    data: filteredEducations,
    columns,
});

// --- Modal State & Form ---
const isModalOpen = ref(false);
const editingId = ref<number | null>(null);
const skillInput = ref('');

const form = useForm({
    degree_title: '',
    institution_name: '',
    location: '',
    start_year: '',
    end_year: '',
    grade: '',
    description: '',
    skills_acquired: [] as string[],
    icon_class: 'fa-solid fa-graduation-cap',
    credential_url: '',
    image_url: '',
    order: 1,
    is_active: true,
});

const iconPresets = [
    { label: 'Wisuda (Cap)', icon: 'fa-solid fa-graduation-cap' },
    { label: 'Kode / Dev', icon: 'fa-solid fa-code' },
    { label: 'Sertifikat (Award)', icon: 'fa-solid fa-award' },
    { label: 'Sekolah / Kampus', icon: 'fa-solid fa-school' },
    { label: 'Buku / Riset', icon: 'fa-solid fa-book' },
];

const addSkillTag = () => {
    const s = skillInput.value.trim();
    if (s && !form.skills_acquired.includes(s)) {
        form.skills_acquired.push(s);
        skillInput.value = '';
    }
};

const removeSkillTag = (index: number) => {
    form.skills_acquired.splice(index, 1);
};

const openAddModal = () => {
    editingId.value = null;
    form.reset();
    form.skills_acquired = [];
    skillInput.value = '';
    form.order = props.educations.length + 1;
    form.icon_class = 'fa-solid fa-graduation-cap';
    form.is_active = true;
    isModalOpen.value = true;
};

const openEditModal = (edu: EducationItem) => {
    editingId.value = edu.id;
    form.degree_title = edu.degree_title;
    form.institution_name = edu.institution_name;
    form.location = edu.location || '';
    form.start_year = edu.start_year;
    form.end_year = edu.end_year || '';
    form.grade = edu.grade || '';
    form.description = edu.description || '';
    form.skills_acquired = Array.isArray(edu.skills_acquired) ? [...edu.skills_acquired] : [];
    form.icon_class = edu.icon_class || 'fa-solid fa-graduation-cap';
    form.credential_url = edu.credential_url || '';
    form.image_url = edu.image_url || '';
    form.order = edu.order || 1;
    form.is_active = edu.is_active !== false;
    skillInput.value = '';
    isModalOpen.value = true;
};

const submitForm = () => {
    if (editingId.value) {
        form.put(`/dashboard/educations/${editingId.value}`, {
            preserveScroll: true,
            onSuccess: () => {
                isModalOpen.value = false;
            },
        });
    } else {
        form.post('/dashboard/educations', {
            preserveScroll: true,
            onSuccess: () => {
                isModalOpen.value = false;
            },
        });
    }
};

const deleteEducation = (id: number) => {
    if (confirm('Apakah Anda yakin ingin menghapus riwayat pendidikan ini?')) {
        router.delete(`/dashboard/educations/${id}`, {
            preserveScroll: true,
        });
    }
};

const toggleStatus = (edu: EducationItem) => {
    router.patch(`/dashboard/educations/${edu.id}/toggle`, {}, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Manajemen Riwayat Pendidikan" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex-1 space-y-6 p-4 sm:p-6 lg:p-8 max-w-7xl w-full min-w-0 mx-auto overflow-hidden">
            <!-- Header Section -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-border/40 pb-5">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-foreground">
                        Riwayat Pendidikan &amp; Pelatihan
                    </h1>
                    <p class="text-sm text-muted-foreground mt-1">
                        Kelola riwayat pendidikan tinggi, bootcamp, sertifikasi intensif, dan materi keahlian akademis.
                    </p>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <Button
                        variant="outline"
                        as-child
                        class="gap-2 bg-card hover:bg-accent text-foreground border border-border"
                    >
                        <a href="/education" target="_blank">
                            <ExternalLink class="w-4 h-4" />
                            <span>Lihat Halaman Publik</span>
                        </a>
                    </Button>
                    <Button
                        @click="openAddModal"
                        class="gap-2 bg-card hover:bg-accent text-foreground border border-border font-bold"
                    >
                        <Plus class="w-4 h-4" />
                        <span>Tambah Pendidikan</span>
                    </Button>
                </div>
            </div>

            <!-- Stats Bar -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
                <Card class="bg-card/40 border-border/60 backdrop-blur-sm">
                    <CardContent class="p-4 sm:p-5 flex items-center justify-between">
                        <div>
                            <p class="text-xs font-mono text-muted-foreground uppercase tracking-wider">Total Riwayat</p>
                            <h3 class="text-2xl font-bold mt-1 text-foreground">{{ educations.length }}</h3>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-white/10 border border-white/20 flex items-center justify-center text-white">
                            <School class="w-5 h-5" />
                        </div>
                    </CardContent>
                </Card>

                <Card class="bg-card/40 border-border/60 backdrop-blur-sm">
                    <CardContent class="p-4 sm:p-5 flex items-center justify-between">
                        <div>
                            <p class="text-xs font-mono text-muted-foreground uppercase tracking-wider">Pendidikan Aktif</p>
                            <h3 class="text-2xl font-bold mt-1 text-foreground">{{ educations.filter(e => e.is_active).length }}</h3>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
                            <CheckCircle2 class="w-5 h-5" />
                        </div>
                    </CardContent>
                </Card>

                <Card class="bg-card/40 border-border/60 backdrop-blur-sm">
                    <CardContent class="p-4 sm:p-5 flex items-center justify-between">
                        <div>
                            <p class="text-xs font-mono text-muted-foreground uppercase tracking-wider">Prestasi / Honours</p>
                            <h3 class="text-2xl font-bold mt-1 text-foreground">{{ educations.filter(e => e.grade).length }}</h3>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-400">
                            <Award class="w-5 h-5" />
                        </div>
                    </CardContent>
                </Card>

                <Card class="bg-card/40 border-border/60 backdrop-blur-sm">
                    <CardContent class="p-4 sm:p-5 flex items-center justify-between">
                        <div>
                            <p class="text-xs font-mono text-muted-foreground uppercase tracking-wider">Status Publik</p>
                            <h3 class="text-sm font-bold mt-1.5 text-emerald-400 flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                Live di Portofolio
                            </h3>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-purple-500/10 border border-purple-500/20 flex items-center justify-center text-purple-400">
                            <Sparkles class="w-5 h-5" />
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
                    <span>Daftar Riwayat Pendidikan ({{ educations.length }})</span>
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
                    <span>Pratinjau Publik (Vertical Timeline)</span>
                </button>
            </div>

            <!-- TAB 1: TANSTACK TABLE -->
            <div v-if="activeTab === 'table'" class="space-y-4">
                <!-- Search Toolbar -->
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
                    <div class="relative w-full sm:w-80">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                        <Input
                            v-model="searchQuery"
                            placeholder="Cari gelar, kampus, lokasi, atau predikat..."
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

                    <div class="text-xs font-mono text-muted-foreground shrink-0">
                        Menampilkan {{ filteredEducations.length }} dari {{ educations.length }} data
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
                                        Tidak ada riwayat pendidikan yang ditemukan.
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
                            Menampilkan {{ filteredEducations.length }} riwayat
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

            <!-- TAB 2: PRATINJAU PUBLIK -->
            <div v-else-if="activeTab === 'preview'" class="space-y-6 max-w-4xl">
                <div class="p-6 sm:p-8 rounded-2xl bg-black/40 border border-white/[0.08]">
                    <div class="relative pl-6 sm:pl-10 border-l-2 border-white/[0.12] space-y-10 ml-3">
                        <div
                            v-for="edu in educations"
                            :key="edu.id"
                            class="relative group"
                        >
                            <!-- Marker Node -->
                            <div class="absolute -left-[31px] sm:-left-[47px] top-1.5 w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-[#11141d] border-2 border-white flex items-center justify-center text-white shadow-[0_0_15px_rgba(217,119,54,0.4)]">
                                <i :class="[edu.icon_class || 'fa-solid fa-graduation-cap', 'text-[10px] sm:text-xs']"></i>
                            </div>

                            <!-- Card -->
                            <div class="bg-white/[0.02] border border-white/[0.08] rounded-2xl p-6 sm:p-7">
                                <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-4">
                                    <div class="flex items-start gap-4">
                                        <div v-if="edu.image_url" class="w-16 h-16 rounded-xl overflow-hidden shrink-0 border border-white/[0.08]">
                                            <img :src="edu.image_url" :alt="edu.institution_name" class="w-full h-full object-cover" />
                                        </div>
                                        <div v-else class="w-16 h-16 rounded-xl bg-white/[0.03] border border-white/[0.08] flex items-center justify-center text-white shrink-0">
                                            <i class="fa-solid fa-school text-xl"></i>
                                        </div>

                                        <div>
                                            <div class="flex flex-wrap items-center gap-2 mb-1.5">
                                                <span class="px-2.5 py-0.5 rounded-md font-mono text-[10.5px] font-bold uppercase tracking-wider bg-white/15 text-[#f08c4a] border border-white/25">
                                                    {{ edu.start_year }} — {{ edu.end_year || 'Present' }}
                                                </span>
                                                <span v-if="edu.grade" class="text-xs text-emerald-400 font-medium">
                                                    {{ edu.grade }}
                                                </span>
                                            </div>
                                            <h3 class="text-lg font-bold text-white tracking-tight">{{ edu.degree_title }}</h3>
                                            <p class="text-xs text-slate-400 font-medium mt-0.5 flex items-center gap-1.5">
                                                <i class="fa-solid fa-location-dot text-white text-xs"></i>
                                                {{ edu.institution_name }} · {{ edu.location }}
                                            </p>
                                        </div>
                                    </div>

                                    <span class="hidden sm:inline-flex items-center justify-center w-9 h-9 rounded-xl bg-white/[0.03] border border-white/[0.08] text-slate-400 shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                                    </span>
                                </div>

                                <p v-if="edu.description" class="text-slate-400 text-xs sm:text-sm leading-relaxed mb-4">
                                    {{ edu.description }}
                                </p>

                                <div v-if="edu.skills_acquired && edu.skills_acquired.length > 0" class="flex flex-wrap gap-2 pt-3 border-t border-white/[0.06]">
                                    <span
                                        v-for="(skill, sIdx) in edu.skills_acquired"
                                        :key="sIdx"
                                        class="px-2.5 py-1 rounded-lg text-[11px] font-mono bg-white/[0.03] text-slate-300 border border-white/[0.06]"
                                    >
                                        {{ skill }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL: ADD / EDIT EDUCATION -->
        <Dialog :open="isModalOpen" @update:open="isModalOpen = $event">
            <DialogContent class="sm:max-w-xl max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle>
                        {{ editingId ? 'Edit Riwayat Pendidikan' : 'Tambah Riwayat Pendidikan' }}
                    </DialogTitle>
                    <DialogDescription>
                        Lengkapi informasi gelar, institusi, tahun, dan materi keahlian yang dipelajari.
                    </DialogDescription>
                </DialogHeader>

                <form @submit.prevent="submitForm" class="space-y-4 pt-2">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <Label for="degree_title">Gelar / Nama Program *</Label>
                            <Input
                                id="degree_title"
                                v-model="form.degree_title"
                                placeholder="Misal: Bachelor of Computer Science"
                                class="h-9 text-xs"
                                required
                            />
                        </div>

                        <div class="space-y-1.5">
                            <Label for="institution_name">Institusi / Universitas *</Label>
                            <Input
                                id="institution_name"
                                v-model="form.institution_name"
                                placeholder="Misal: Tech University"
                                class="h-9 text-xs"
                                required
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div class="space-y-1.5">
                            <Label for="location">Lokasi</Label>
                            <Input
                                id="location"
                                v-model="form.location"
                                placeholder="Sydney, Australia"
                                class="h-9 text-xs"
                            />
                        </div>

                        <div class="space-y-1.5">
                            <Label for="start_year">Tahun Mulai *</Label>
                            <Input
                                id="start_year"
                                v-model="form.start_year"
                                placeholder="2015"
                                class="h-9 text-xs font-mono"
                                required
                            />
                        </div>

                        <div class="space-y-1.5">
                            <Label for="end_year">Tahun Selesai</Label>
                            <Input
                                id="end_year"
                                v-model="form.end_year"
                                placeholder="2020 (kosongkan jika sekarang)"
                                class="h-9 text-xs font-mono"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <Label for="grade">Predikat / Honours</Label>
                            <Input
                                id="grade"
                                v-model="form.grade"
                                placeholder="First Class Honours / Distinction"
                                class="h-9 text-xs"
                            />
                        </div>

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
                    </div>

                    <!-- Icon Preset & Class -->
                    <div class="space-y-1.5">
                        <Label>Icon Timeline</Label>
                        <div class="flex items-center gap-3">
                            <Input
                                v-model="form.icon_class"
                                placeholder="fa-solid fa-graduation-cap"
                                class="h-9 text-xs font-mono flex-1"
                            />
                            <div class="w-10 h-10 rounded-xl bg-[#11141d] border-2 border-white flex items-center justify-center text-white shrink-0">
                                <i :class="[form.icon_class || 'fa-solid fa-graduation-cap', 'text-sm']"></i>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-1.5 pt-1">
                            <button
                                v-for="preset in iconPresets"
                                :key="preset.icon"
                                type="button"
                                @click="form.icon_class = preset.icon"
                                class="px-2 py-1 rounded text-[11px] font-mono bg-muted/60 border border-border hover:border-white hover:text-white transition-colors cursor-pointer"
                            >
                                {{ preset.label }}
                            </button>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <Label for="image_url">URL Foto / Logo Institusi</Label>
                        <Input
                            id="image_url"
                            v-model="form.image_url"
                            placeholder="https://images.unsplash.com/..."
                            class="h-9 text-xs font-mono"
                        />
                    </div>

                    <div class="space-y-1.5">
                        <Label for="description">Deskripsi Kurikulum &amp; Studi</Label>
                        <Textarea
                            id="description"
                            v-model="form.description"
                            placeholder="Jelaskan fokus studi, kurikulum mendalam, atau metodologi yang dipelajari..."
                            class="text-xs min-h-[75px]"
                        />
                    </div>

                    <!-- Coursework / Skills Acquired Tag Input -->
                    <div class="space-y-1.5">
                        <Label>Materi / Coursework / Keahlian yang Dipelajari</Label>
                        <div class="flex items-center gap-2">
                            <Input
                                v-model="skillInput"
                                @keydown.enter.prevent="addSkillTag"
                                placeholder="Ketik topik (misal: Distributed Systems) lalu tekan Enter..."
                                class="h-9 text-xs bg-background/50"
                            />
                            <Button
                                type="button"
                                @click="addSkillTag"
                                class="gap-1.5 h-9 bg-white hover:bg-[#e58546] text-[#0a0c10] font-bold shrink-0"
                            >
                                <Plus class="w-4 h-4" />
                                <span>Tambah</span>
                            </Button>
                        </div>
                        <div class="flex flex-wrap gap-1.5 p-2 rounded-lg bg-muted/40 border border-border/50 min-h-[38px]">
                            <span
                                v-for="(tag, tIdx) in form.skills_acquired"
                                :key="tIdx"
                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded text-xs font-mono bg-background border border-border text-foreground"
                            >
                                {{ tag }}
                                <button
                                    type="button"
                                    @click="removeSkillTag(tIdx)"
                                    class="text-muted-foreground hover:text-rose-400 ml-1"
                                >
                                    <X class="w-3 h-3" />
                                </button>
                            </span>
                            <span v-if="!form.skills_acquired.length" class="text-xs text-muted-foreground italic py-0.5">
                                Belum ada topik coursework ditambahkan.
                            </span>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <Label for="credential_url">URL Tautan / Sertifikat</Label>
                        <Input
                            id="credential_url"
                            v-model="form.credential_url"
                            placeholder="#contact atau URL sertifikat digital"
                            class="h-9 text-xs font-mono"
                        />
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <input
                            type="checkbox"
                            id="is_active"
                            v-model="form.is_active"
                            class="rounded border-border text-white focus:ring-white"
                        />
                        <Label for="is_active" class="cursor-pointer text-xs">
                            Status Aktif (Tampilkan di timeline publik)
                        </Label>
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
                            <span>{{ editingId ? 'Perbarui Pendidikan' : 'Simpan Pendidikan' }}</span>
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
