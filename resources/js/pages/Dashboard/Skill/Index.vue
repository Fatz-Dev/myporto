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
    Code2,
    Plus,
    Save,
    ExternalLink,
    Trash2,
    Edit2,
    CheckCircle2,
    AlertCircle,
    LoaderCircle,
    Layers,
    Cpu,
    Check,
    Wrench,
    Sparkles,
    Search,
    X,
    ChevronLeft,
    ChevronRight,
    Eye,
    Tag,
    Filter,
    ArrowUpRight,
    SlidersHorizontal,
    FolderKanban,
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
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

interface SkillItem {
    id: number;
    category_id: number;
    name: string;
    subtitle?: string;
    icon_class?: string;
    level?: string;
    core_tools?: string[];
    discipline_protocol?: string;
    description?: string;
    order: number;
    is_featured: boolean;
    is_active: boolean;
    category_name?: string;
    category_slug?: string;
}

interface SkillCategoryItem {
    id: number;
    name: string;
    slug: string;
    badge_label?: string;
    category_type: string;
    description?: string;
    order: number;
    is_active: boolean;
    skills: SkillItem[];
}

interface ProfileItem {
    id: number;
    primary_stack?: string[];
}

const props = defineProps<{
    profile: ProfileItem;
    skillCategories: SkillCategoryItem[];
    flash?: {
        success?: string;
        error?: string;
    };
}>();

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Skill', href: '/dashboard/skills' },
];

// Active section tab: 'skills_table' | 'categories' | 'primary_stack' | 'preview'
const activeTab = ref<'skills_table' | 'categories' | 'primary_stack' | 'preview'>('skills_table');

// --- Flatten All Skills for TanStack Table ---
const allSkills = computed(() => {
    if (!props.skillCategories) return [];
    return props.skillCategories.flatMap((cat) =>
        (cat.skills || []).map((s) => ({
            ...s,
            category_name: cat.name,
            category_slug: cat.slug,
        }))
    );
});

// --- Search & Category Filter ---
const searchQuery = ref('');
const selectedCategoryFilter = ref('all');

const filteredSkills = computed(() => {
    return allSkills.value.filter((item) => {
        const matchesCategory =
            selectedCategoryFilter.value === 'all' ||
            item.category_id === Number(selectedCategoryFilter.value);
        const q = searchQuery.value.toLowerCase().trim();
        const matchesQuery =
            !q ||
            item.name.toLowerCase().includes(q) ||
            (item.subtitle && item.subtitle.toLowerCase().includes(q)) ||
            (item.category_name && item.category_name.toLowerCase().includes(q));
        return matchesCategory && matchesQuery;
    });
});

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

const columnHelper = createColumnHelper<SkillItem>();

const columns = [
    columnHelper.accessor('name', {
        header: 'Keahlian & Icon',
        cell: ({ row }) => {
            const s = row.original;
            return h('div', { class: 'flex items-center gap-3' }, [
                // Icon box
                h(
                    'div',
                    {
                        class:
                            'w-10 h-10 rounded-xl bg-white/[0.04] border border-white/[0.08] flex items-center justify-center shrink-0 shadow-sm',
                    },
                    s.icon_class?.startsWith('badge:TS')
                        ? [
                              h(
                                  'span',
                                  { class: 'font-mono font-extrabold text-sm text-[#3178c6]' },
                                  'TS'
                              ),
                          ]
                        : s.icon_class?.startsWith('badge:Inertia')
                        ? [
                              h(
                                  'svg',
                                  {
                                      class: 'w-5 h-5 text-[#9553e9]',
                                      viewBox: '0 0 24 24',
                                      fill: 'currentColor',
                                  },
                                  [
                                      h('path', {
                                          d: 'M4.5 4.5l6.75 7.5-6.75 7.5h4.5l6.75-7.5-6.75-7.5h-4.5zm8.25 0l6.75 7.5-6.75 7.5h4.5l-6.75-7.5 6.75-7.5h-4.5z',
                                      }),
                                  ]
                              ),
                          ]
                        : s.icon_class
                        ? [h('i', { class: [s.icon_class, 'text-xl'] })]
                        : [h(Code2, { class: 'w-5 h-5 text-white' })]
                ),
                // Title & subtitle
                h('div', { class: 'min-w-0 flex flex-col' }, [
                    h('span', { class: 'font-bold text-foreground truncate' }, s.name),
                    h(
                        'span',
                        { class: 'text-[11px] font-mono text-muted-foreground' },
                        s.subtitle || 'General'
                    ),
                ]),
            ]);
        },
    }),
    columnHelper.accessor('category_name', {
        header: 'Kategori',
        cell: ({ row }) => {
            const s = row.original;
            return h(
                'span',
                {
                    class:
                        'inline-flex items-center px-2.5 py-1 rounded-md text-xs font-mono bg-white/[0.04] border border-white/[0.08] text-slate-300',
                },
                s.category_name || '-'
            );
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
            const s = row.original;
            return h(
                'button',
                {
                    type: 'button',
                    onClick: () => toggleSkillStatus(s),
                    class: [
                        'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-medium transition-colors cursor-pointer',
                        s.is_active
                            ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 hover:bg-emerald-500/20'
                            : 'bg-muted text-muted-foreground border border-border hover:bg-muted/80',
                    ],
                },
                [
                    h('span', {
                        class: [
                            'w-1.5 h-1.5 rounded-full',
                            s.is_active ? 'bg-emerald-400' : 'bg-muted-foreground',
                        ],
                    }),
                    s.is_active ? 'Aktif' : 'Nonaktif',
                ]
            );
        },
    }),
    columnHelper.display({
        id: 'actions',
        header: () => h('div', { class: 'text-right' }, 'Aksi'),
        cell: ({ row }) => {
            const s = row.original;
            return h('div', { class: 'flex items-center justify-end gap-1.5' }, [
                h(
                    Button,
                    {
                        variant: 'ghost',
                        size: 'icon',
                        class: 'h-8 w-8 text-muted-foreground hover:text-foreground',
                        onClick: () => openEditSkillModal(s),
                    },
                    () => [h(Edit2, { class: 'w-4 h-4' })]
                ),
                h(
                    Button,
                    {
                        variant: 'ghost',
                        size: 'icon',
                        class: 'h-8 w-8 text-muted-foreground hover:text-rose-400',
                        onClick: () => deleteSkill(s.id),
                    },
                    () => [h(Trash2, { class: 'w-4 h-4' })]
                ),
            ]);
        },
    }),
];

const table = useAppTable({
    data: filteredSkills,
    columns,
});

// --- Skill Form & Presets ---
const isSkillModalOpen = ref(false);
const editingSkillId = ref<number | null>(null);

const skillForm = useForm({
    category_id: props.skillCategories[0]?.id || 1,
    name: '',
    subtitle: '',
    icon_class: '',
    level: 'Expert / Production',
    order: 1,
    is_featured: false,
    is_active: true,
});

// Popular quick presets for easy creation
const iconPresets = [
    { name: 'Laravel', subtitle: 'Backend', icon: 'fa-brands fa-laravel text-[#ff2d20]' },
    { name: 'Vue.js', subtitle: 'Frontend', icon: 'fa-brands fa-vuejs text-[#42b883]' },
    { name: 'React', subtitle: 'Frontend', icon: 'fa-brands fa-react text-[#61dafb]' },
    { name: 'TypeScript', subtitle: 'Language', icon: 'badge:TS:#3178c6' },
    { name: 'JavaScript', subtitle: 'Language', icon: 'fa-brands fa-js text-[#f7df1e]' },
    { name: 'Tailwind CSS', subtitle: 'Styling', icon: 'fa-solid fa-wind text-[#38bdf8]' },
    { name: 'PHP', subtitle: 'Backend', icon: 'fa-brands fa-php text-[#777bb4]' },
    { name: 'Node.js', subtitle: 'Runtime', icon: 'fa-brands fa-node-js text-[#68a063]' },
    { name: 'Inertia.js', subtitle: 'Full Stack', icon: 'badge:Inertia:#9553e9' },
    { name: 'PostgreSQL', subtitle: 'Database', icon: 'fa-solid fa-database text-[#336791]' },
    { name: 'MySQL', subtitle: 'Database', icon: 'fa-solid fa-server text-[#00758f]' },
    { name: 'Redis', subtitle: 'Cache & Queue', icon: 'fa-solid fa-bolt text-[#dc382d]' },
    { name: 'Docker', subtitle: 'Container', icon: 'fa-brands fa-docker text-[#2496ed]' },
    { name: 'Git', subtitle: 'Version Control', icon: 'fa-brands fa-git-alt text-[#f05032]' },
    { name: 'CI / CD', subtitle: 'Automated Pipeline', icon: 'fa-brands fa-github text-slate-200' },
    { name: 'REST APIs', subtitle: 'Architecture', icon: 'fa-solid fa-network-wired text-[#f08c4a]' },
    { name: 'GraphQL', subtitle: 'API Query', icon: 'fa-solid fa-diagram-project text-[#e535ab]' },
    { name: 'Linux', subtitle: 'Server OS', icon: 'fa-brands fa-linux text-[#fcc624]' },
    { name: 'Postman', subtitle: 'API Testing', icon: 'fa-solid fa-paper-plane text-[#ff6c37]' },
    { name: 'HTML5 / CSS3', subtitle: 'Markup', icon: 'fa-brands fa-html5 text-[#e34f26]' },
    { name: 'MongoDB', subtitle: 'NoSQL', icon: 'fa-solid fa-leaf text-[#47a248]' },
    { name: 'Figma', subtitle: 'UI Design', icon: 'fa-brands fa-figma text-[#f24e1e]' },
];

const applyPreset = (preset: typeof iconPresets[0]) => {
    skillForm.name = preset.name;
    skillForm.subtitle = preset.subtitle;
    skillForm.icon_class = preset.icon;
};

const openAddSkillModal = (categoryId?: number) => {
    editingSkillId.value = null;
    skillForm.reset();
    if (categoryId) {
        skillForm.category_id = categoryId;
    } else if (props.skillCategories.length > 0) {
        skillForm.category_id = props.skillCategories[0].id;
    }
    skillForm.order = (allSkills.value.length || 0) + 1;
    skillForm.is_active = true;
    isSkillModalOpen.value = true;
};

const openEditSkillModal = (skill: SkillItem) => {
    editingSkillId.value = skill.id;
    skillForm.category_id = skill.category_id;
    skillForm.name = skill.name;
    skillForm.subtitle = skill.subtitle || '';
    skillForm.icon_class = skill.icon_class || '';
    skillForm.level = skill.level || 'Expert / Production';
    skillForm.order = skill.order || 1;
    skillForm.is_featured = !!skill.is_featured;
    skillForm.is_active = skill.is_active !== false;
    isSkillModalOpen.value = true;
};

const submitSkillForm = () => {
    if (editingSkillId.value) {
        skillForm.put(`/dashboard/skills/${editingSkillId.value}`, {
            preserveScroll: true,
            onSuccess: () => {
                isSkillModalOpen.value = false;
            },
        });
    } else {
        skillForm.post('/dashboard/skills', {
            preserveScroll: true,
            onSuccess: () => {
                isSkillModalOpen.value = false;
            },
        });
    }
};

const deleteSkill = (id?: number) => {
    if (!id) return;
    if (confirm('Apakah Anda yakin ingin menghapus keahlian ini?')) {
        router.delete(`/dashboard/skills/${id}`, {
            preserveScroll: true,
        });
    }
};

const toggleSkillStatus = (skill: SkillItem) => {
    if (!skill.id) return;
    router.patch(`/dashboard/skills/${skill.id}/toggle`, {}, {
        preserveScroll: true,
    });
};

// --- Category CRUD Modal State ---
const isCategoryModalOpen = ref(false);
const editingCategoryId = ref<number | null>(null);

const categoryForm = useForm({
    name: '',
    badge_label: '',
    category_type: 'technical',
    description: '',
    order: 1,
    is_active: true,
});

const openAddCategoryModal = () => {
    editingCategoryId.value = null;
    categoryForm.reset();
    categoryForm.order = props.skillCategories.length + 1;
    categoryForm.is_active = true;
    isCategoryModalOpen.value = true;
};

const openEditCategoryModal = (cat: SkillCategoryItem) => {
    editingCategoryId.value = cat.id;
    categoryForm.name = cat.name;
    categoryForm.badge_label = cat.badge_label || '';
    categoryForm.category_type = cat.category_type || 'technical';
    categoryForm.description = cat.description || '';
    categoryForm.order = cat.order || 1;
    categoryForm.is_active = cat.is_active !== false;
    isCategoryModalOpen.value = true;
};

const submitCategoryForm = () => {
    if (editingCategoryId.value) {
        categoryForm.put(`/dashboard/skill-categories/${editingCategoryId.value}`, {
            preserveScroll: true,
            onSuccess: () => {
                isCategoryModalOpen.value = false;
            },
        });
    } else {
        categoryForm.post('/dashboard/skill-categories', {
            preserveScroll: true,
            onSuccess: () => {
                isCategoryModalOpen.value = false;
            },
        });
    }
};

const deleteCategory = (id: number) => {
    if (confirm('Hapus kategori ini beserta semua keahlian di dalamnya?')) {
        router.delete(`/dashboard/skill-categories/${id}`, {
            preserveScroll: true,
        });
    }
};

// --- Primary Tech Stack Management ---
const primaryStackForm = useForm({
    primary_stack: Array.isArray(props.profile?.primary_stack)
        ? [...props.profile.primary_stack]
        : [],
});

const newTagInput = ref('');

const addTag = () => {
    const tag = newTagInput.value.trim();
    if (tag && !primaryStackForm.primary_stack.includes(tag)) {
        primaryStackForm.primary_stack.push(tag);
        newTagInput.value = '';
    }
};

const removeTag = (index: number) => {
    primaryStackForm.primary_stack.splice(index, 1);
};

const savePrimaryStack = () => {
    primaryStackForm.post('/dashboard/skills/primary-stack', {
        preserveScroll: true,
    });
};

// Preview category filter
const previewCategory = ref('all');
const previewSkills = computed(() => {
    if (previewCategory.value === 'all') {
        return allSkills.value;
    }
    return allSkills.value.filter((s) => s.category_slug === previewCategory.value);
});
</script>

<template>
    <Head title="Manajemen Keahlian & Tech Stack" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex-1 space-y-6 p-4 sm:p-6 lg:p-8 max-w-7xl w-full min-w-0 mx-auto overflow-hidden">
            <!-- Header Section -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-border/40 pb-5">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-foreground">
                        Keahlian &amp; Tech Stack
                    </h1>
                    <p class="text-sm text-muted-foreground mt-1">
                        Kelola daftar alat, framework, bahasa, dan kategori teknologi yang ditampilkan di portofolio publik.
                    </p>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <Button
                        variant="outline"
                        as-child
                        class="gap-2 bg-card hover:bg-accent text-foreground border border-border"
                    >
                        <a href="/skills" target="_blank">
                            <ExternalLink class="w-4 h-4" />
                            <span>Lihat Halaman Publik</span>
                        </a>
                    </Button>
                    <Button
                        @click="openAddSkillModal()"
                        class="gap-2 bg-card hover:bg-accent text-foreground border border-border font-bold"
                    >
                        <Plus class="w-4 h-4" />
                        <span>Tambah Keahlian</span>
                    </Button>
                </div>
            </div>

            <!-- Stats Bar -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
                <Card class="bg-card/40 border-border/60 backdrop-blur-sm">
                    <CardContent class="p-4 sm:p-5 flex items-center justify-between">
                        <div>
                            <p class="text-xs font-mono text-muted-foreground uppercase tracking-wider">Total Keahlian</p>
                            <h3 class="text-2xl font-bold mt-1 text-foreground">{{ allSkills.length }}</h3>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-white/10 border border-white/20 flex items-center justify-center text-white">
                            <Cpu class="w-5 h-5" />
                        </div>
                    </CardContent>
                </Card>

                <Card class="bg-card/40 border-border/60 backdrop-blur-sm">
                    <CardContent class="p-4 sm:p-5 flex items-center justify-between">
                        <div>
                            <p class="text-xs font-mono text-muted-foreground uppercase tracking-wider">Kategori Aktif</p>
                            <h3 class="text-2xl font-bold mt-1 text-foreground">{{ skillCategories.filter(c => c.is_active).length }}</h3>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-400">
                            <FolderKanban class="w-5 h-5" />
                        </div>
                    </CardContent>
                </Card>

                <Card class="bg-card/40 border-border/60 backdrop-blur-sm">
                    <CardContent class="p-4 sm:p-5 flex items-center justify-between">
                        <div>
                            <p class="text-xs font-mono text-muted-foreground uppercase tracking-wider">Primary Stack</p>
                            <h3 class="text-2xl font-bold mt-1 text-foreground">{{ primaryStackForm.primary_stack.length }}</h3>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-purple-500/10 border border-purple-500/20 flex items-center justify-center text-purple-400">
                            <Sparkles class="w-5 h-5" />
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
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
                            <CheckCircle2 class="w-5 h-5" />
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Tab Navigation -->
            <div class="flex border-b border-border/60 gap-2 overflow-x-auto pb-px">
                <button
                    type="button"
                    @click="activeTab = 'skills_table'"
                    class="px-4 py-2.5 text-xs sm:text-sm font-medium border-b-2 transition-colors duration-200 whitespace-nowrap cursor-pointer flex items-center gap-2"
                    :class="activeTab === 'skills_table'
                        ? 'border-white text-white font-semibold'
                        : 'border-transparent text-muted-foreground hover:text-foreground hover:border-border'"
                >
                    <Layers class="w-4 h-4" />
                    <span>Daftar Keahlian ({{ allSkills.length }})</span>
                </button>
                <button
                    type="button"
                    @click="activeTab = 'categories'"
                    class="px-4 py-2.5 text-xs sm:text-sm font-medium border-b-2 transition-colors duration-200 whitespace-nowrap cursor-pointer flex items-center gap-2"
                    :class="activeTab === 'categories'
                        ? 'border-white text-white font-semibold'
                        : 'border-transparent text-muted-foreground hover:text-foreground hover:border-border'"
                >
                    <FolderKanban class="w-4 h-4" />
                    <span>Kategori Keahlian ({{ skillCategories.length }})</span>
                </button>
                <button
                    type="button"
                    @click="activeTab = 'primary_stack'"
                    class="px-4 py-2.5 text-xs sm:text-sm font-medium border-b-2 transition-colors duration-200 whitespace-nowrap cursor-pointer flex items-center gap-2"
                    :class="activeTab === 'primary_stack'
                        ? 'border-white text-white font-semibold'
                        : 'border-transparent text-muted-foreground hover:text-foreground hover:border-border'"
                >
                    <Tag class="w-4 h-4" />
                    <span>Primary Tech Stack (Hero Badge)</span>
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
                    <span>Pratinjau Publik (6 Kolom)</span>
                </button>
            </div>

            <!-- ============================================================== -->
            <!-- TAB 1: TANSTACK TABLE OF ALL SKILLS -->
            <!-- ============================================================== -->
            <div v-if="activeTab === 'skills_table'" class="space-y-4">
                <!-- Filter & Search Toolbar -->
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        <div class="relative w-full sm:w-72">
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                            <Input
                                v-model="searchQuery"
                                placeholder="Cari nama skill, role, kategori..."
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
                            v-model="selectedCategoryFilter"
                            class="h-9 px-3 text-xs rounded-md bg-background/50 border border-border/60 text-foreground focus:border-white focus:outline-none"
                        >
                            <option value="all">Semua Kategori</option>
                            <option v-for="cat in skillCategories" :key="cat.id" :value="cat.id">
                                {{ cat.name }}
                            </option>
                        </select>
                    </div>

                    <div class="text-xs font-mono text-muted-foreground shrink-0">
                        Menampilkan {{ filteredSkills.length }} dari {{ allSkills.length }} keahlian
                    </div>
                </div>

                <!-- TanStack Table Card -->
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
                                        Tidak ada data keahlian yang cocok dengan pencarian atau filter Anda.
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

                    <!-- Pagination -->
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-3 px-4 py-3 border-t border-border bg-muted/10 text-xs text-muted-foreground">
                        <div>
                            Menampilkan {{ filteredSkills.length }} keahlian
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

            <!-- ============================================================== -->
            <!-- TAB 2: KATEGORI KEAHLIAN -->
            <!-- ============================================================== -->
            <div v-else-if="activeTab === 'categories'" class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-foreground">Kategori Keahlian</h2>
                        <p class="text-xs text-muted-foreground">Kategori digunakan untuk memfilter kartu di landing page (Frontend, Backend, Database, DevOps, dll).</p>
                    </div>
                    <Button
                        @click="openAddCategoryModal()"
                        class="gap-2 bg-card hover:bg-accent text-foreground border border-border font-bold shadow-sm"
                    >
                        <Plus class="w-4 h-4" />
                        <span>Tambah Kategori</span>
                    </Button>
                </div>

                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <Card
                        v-for="cat in skillCategories"
                        :key="cat.id"
                        class="bg-card/40 border-border/60 backdrop-blur-sm flex flex-col justify-between hover:border-white/40 transition-colors"
                    >
                        <CardHeader class="p-4 pb-2">
                            <div class="flex items-center justify-between gap-2">
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono uppercase bg-white/10 text-white border border-white/20">
                                    {{ cat.slug }}
                                </span>
                                <span
                                    class="w-2 h-2 rounded-full"
                                    :class="cat.is_active ? 'bg-emerald-400' : 'bg-muted-foreground'"
                                ></span>
                            </div>
                            <CardTitle class="text-base font-bold mt-2 text-foreground">
                                {{ cat.name }}
                            </CardTitle>
                            <CardDescription class="text-xs line-clamp-2">
                                {{ cat.description || 'Tidak ada deskripsi.' }}
                            </CardDescription>
                        </CardHeader>

                        <CardContent class="p-4 pt-2">
                            <div class="pt-3 border-t border-border/60 flex items-center justify-between text-xs text-muted-foreground">
                                <span>{{ (cat.skills || []).length }} keahlian</span>
                                <div class="flex items-center gap-1">
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="h-7 w-7 text-muted-foreground hover:text-foreground"
                                        @click="openEditCategoryModal(cat)"
                                    >
                                        <Edit2 class="w-3.5 h-3.5" />
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="h-7 w-7 text-muted-foreground hover:text-rose-400"
                                        @click="deleteCategory(cat.id)"
                                    >
                                        <Trash2 class="w-3.5 h-3.5" />
                                    </Button>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>

            <!-- ============================================================== -->
            <!-- TAB 3: PRIMARY TECH STACK -->
            <!-- ============================================================== -->
            <div v-else-if="activeTab === 'primary_stack'" class="space-y-4 max-w-3xl">
                <Card class="bg-card/40 border-border/60 backdrop-blur-sm">
                    <CardHeader class="p-5 pb-3">
                        <CardTitle class="text-lg font-bold text-foreground flex items-center gap-2">
                            <Sparkles class="w-5 h-5 text-white" />
                            Primary Tech Stack (Hero &amp; Profile Tags)
                        </CardTitle>
                        <CardDescription class="text-xs">
                            Tag teknologi utama yang ditampilkan di samping atau bawah nama Anda pada section perkenalan/hero profil.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="p-5 pt-3 space-y-5">
                        <div class="flex items-center gap-2">
                            <Input
                                v-model="newTagInput"
                                @keydown.enter.prevent="addTag"
                                placeholder="Ketik nama stack (misal: Laravel, Vue, Docker) lalu tekan Enter..."
                                class="h-9 text-xs bg-background/50 border-border/60"
                            />
                            <Button
                                type="button"
                                @click="addTag"
                                class="gap-1.5 h-9 bg-white hover:bg-white text-[#0a0c10] font-bold shrink-0"
                            >
                                <Plus class="w-4 h-4" />
                                <span>Tambah Tag</span>
                            </Button>
                        </div>

                        <!-- Tag Badges -->
                        <div class="flex flex-wrap gap-2 min-h-[48px] p-3 rounded-xl bg-white/[0.02] border border-border/60">
                            <span
                                v-for="(tag, index) in primaryStackForm.primary_stack"
                                :key="index"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-mono font-semibold bg-white/10 text-white border border-white/25 group"
                            >
                                {{ tag }}
                                <button
                                    type="button"
                                    @click="removeTag(index)"
                                    class="text-white/70 hover:text-rose-400 transition-colors ml-0.5"
                                >
                                    <X class="w-3.5 h-3.5" />
                                </button>
                            </span>
                            <span
                                v-if="primaryStackForm.primary_stack.length === 0"
                                class="text-xs text-muted-foreground italic py-1"
                            >
                                Belum ada primary stack tag. Tambahkan stack di atas.
                            </span>
                        </div>

                        <div class="flex justify-end pt-2">
                            <Button
                                @click="savePrimaryStack"
                                :disabled="primaryStackForm.processing"
                                class="gap-2 bg-white hover:bg-white text-[#0a0c10] font-bold"
                            >
                                <Save class="w-4 h-4" />
                                <span>Simpan Perubahan Primary Stack</span>
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- ============================================================== -->
            <!-- TAB 4: PRATINJAU PUBLIK (6 KOLOM) -->
            <!-- ============================================================== -->
            <div v-else-if="activeTab === 'preview'" class="space-y-6">
                <!-- Preview Category Filter Pills -->
                <div class="flex flex-wrap items-center gap-2">
                    <button
                        type="button"
                        @click="previewCategory = 'all'"
                        class="px-4 py-2 rounded-xl text-xs font-mono font-semibold tracking-wide transition-all duration-200 cursor-pointer"
                        :class="previewCategory === 'all'
                            ? 'bg-white text-[#0a0c10] border border-white font-bold shadow-sm'
                            : 'text-slate-400 bg-white/[0.03] border border-white/[0.08] hover:text-white hover:border-white/40'"
                    >
                        All Skills ({{ allSkills.length }})
                    </button>
                    <button
                        v-for="cat in skillCategories"
                        :key="cat.id"
                        type="button"
                        @click="previewCategory = cat.slug"
                        class="px-4 py-2 rounded-xl text-xs font-mono font-semibold tracking-wide transition-all duration-200 cursor-pointer"
                        :class="previewCategory === cat.slug
                            ? 'bg-white text-[#0a0c10] border border-white font-bold shadow-sm'
                            : 'text-slate-400 bg-white/[0.03] border border-white/[0.08] hover:text-white hover:border-white/40'"
                    >
                        {{ cat.name }} ({{ (cat.skills || []).length }})
                    </button>
                </div>

                <!-- 6 Column Grid -->
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3 sm:gap-4 p-4 sm:p-6 rounded-2xl bg-black/40 border border-white/[0.08]">
                    <div
                        v-for="skill in previewSkills"
                        :key="skill.id"
                        class="skill-card group bg-white/[0.02] border border-white/[0.08] hover:border-white/40 hover:bg-white/[0.04] rounded-2xl p-4 sm:p-5 flex flex-col items-center justify-center text-center transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_12px_24px_-8px_rgba(0,0,0,0.5)] cursor-default"
                    >
                        <!-- TS Badge -->
                        <template v-if="skill.icon_class && skill.icon_class.startsWith('badge:TS')">
                            <div class="w-14 h-14 rounded-xl bg-[#3178c6]/10 border border-[#3178c6]/25 group-hover:border-blue-500/40 group-hover:bg-blue-500/20 flex items-center justify-center mb-3 transition-all duration-300">
                                <span class="font-mono font-extrabold text-xl text-[#3178c6] group-hover:scale-110 transition-transform duration-300">TS</span>
                            </div>
                        </template>

                        <!-- Inertia Badge -->
                        <template v-else-if="skill.icon_class && skill.icon_class.startsWith('badge:Inertia')">
                            <div class="w-14 h-14 rounded-xl bg-[#9553e9]/10 border border-[#9553e9]/25 group-hover:border-purple-500/40 group-hover:bg-purple-500/20 flex items-center justify-center mb-3 transition-all duration-300">
                                <svg class="w-7 h-7 text-[#9553e9] group-hover:scale-110 transition-transform duration-300" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M4.5 4.5l6.75 7.5-6.75 7.5h4.5l6.75-7.5-6.75-7.5h-4.5zm8.25 0l6.75 7.5-6.75 7.5h4.5l-6.75-7.5 6.75-7.5h-4.5z"/>
                                </svg>
                            </div>
                        </template>

                        <!-- Standard Brand Icon -->
                        <template v-else-if="skill.icon_class">
                            <div class="w-14 h-14 rounded-xl bg-white/[0.03] border border-white/[0.06] group-hover:border-white/30 group-hover:bg-white/10 flex items-center justify-center mb-3 transition-all duration-300">
                                <i :class="[skill.icon_class, 'text-3xl group-hover:scale-110 transition-transform duration-300']"></i>
                            </div>
                        </template>

                        <template v-else>
                            <div class="w-14 h-14 rounded-xl bg-white/[0.03] border border-white/[0.06] group-hover:border-white/30 group-hover:bg-white/10 flex items-center justify-center mb-3 transition-all duration-300">
                                <i class="fa-solid fa-code text-2xl text-white"></i>
                            </div>
                        </template>

                        <h3 class="text-white text-sm font-bold tracking-tight mb-0.5 group-hover:text-white transition-colors">
                            {{ skill.name }}
                        </h3>

                        <span v-if="skill.subtitle" class="text-[11px] font-mono text-slate-500 group-hover:text-slate-300 transition-colors">
                            {{ skill.subtitle }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- MODAL: TAMBAH / EDIT KEAHLIAN -->
        <!-- ============================================================== -->
        <Dialog :open="isSkillModalOpen" @update:open="isSkillModalOpen = $event">
            <DialogContent class="sm:max-w-xl max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle>
                        {{ editingSkillId ? 'Edit Keahlian' : 'Tambah Keahlian Baru' }}
                    </DialogTitle>
                    <DialogDescription>
                        Lengkapi detail nama, kategori, icon, dan role keahlian untuk portofolio Anda.
                    </DialogDescription>
                </DialogHeader>

                <!-- Quick Presets -->
                <div class="space-y-2 pt-1">
                    <Label class="text-xs text-muted-foreground font-mono uppercase tracking-wider">Preset Cepat (Klik untuk auto-fill)</Label>
                    <div class="flex flex-wrap gap-1.5 max-h-24 overflow-y-auto p-2 rounded-lg bg-muted/40 border border-border/50">
                        <button
                            v-for="p in iconPresets"
                            :key="p.name"
                            type="button"
                            @click="applyPreset(p)"
                            class="px-2 py-1 rounded text-[11px] font-mono bg-background border border-border hover:border-white hover:text-white transition-colors cursor-pointer shrink-0"
                        >
                            {{ p.name }}
                        </button>
                    </div>
                </div>

                <form @submit.prevent="submitSkillForm" class="space-y-4 pt-2">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <Label for="category_id">Kategori *</Label>
                            <select
                                id="category_id"
                                v-model="skillForm.category_id"
                                class="w-full h-9 px-3 rounded-md bg-background border border-border text-xs focus:border-white focus:outline-none"
                                required
                            >
                                <option v-for="cat in skillCategories" :key="cat.id" :value="cat.id">
                                    {{ cat.name }}
                                </option>
                            </select>
                        </div>

                        <div class="space-y-1.5">
                            <Label for="order">Urutan Tampil</Label>
                            <Input
                                id="order"
                                type="number"
                                v-model="skillForm.order"
                                class="h-9 text-xs"
                                min="1"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <Label for="name">Nama Keahlian *</Label>
                            <Input
                                id="name"
                                v-model="skillForm.name"
                                placeholder="Misal: Laravel, Vue.js, Docker"
                                class="h-9 text-xs"
                                required
                            />
                        </div>

                        <div class="space-y-1.5">
                            <Label for="subtitle">Subtitle / Role Tag</Label>
                            <Input
                                id="subtitle"
                                v-model="skillForm.subtitle"
                                placeholder="Misal: Backend, Frontend, Database"
                                class="h-9 text-xs"
                            />
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <Label for="icon_class">FontAwesome Icon Class atau Badge Code</Label>
                        <div class="flex items-center gap-3">
                            <Input
                                id="icon_class"
                                v-model="skillForm.icon_class"
                                placeholder="fa-brands fa-laravel text-[#ff2d20] atau badge:TS:#3178c6"
                                class="h-9 text-xs flex-1 font-mono"
                            />
                            <!-- Live icon preview -->
                            <div class="w-10 h-10 rounded-lg bg-white/[0.04] border border-white/[0.1] flex items-center justify-center shrink-0">
                                <template v-if="skillForm.icon_class?.startsWith('badge:TS')">
                                    <span class="font-mono font-bold text-sm text-[#3178c6]">TS</span>
                                </template>
                                <template v-else-if="skillForm.icon_class?.startsWith('badge:Inertia')">
                                    <svg class="w-5 h-5 text-[#9553e9]" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M4.5 4.5l6.75 7.5-6.75 7.5h4.5l6.75-7.5-6.75-7.5h-4.5zm8.25 0l6.75 7.5-6.75 7.5h4.5l-6.75-7.5 6.75-7.5h-4.5z"/>
                                    </svg>
                                </template>
                                <template v-else-if="skillForm.icon_class">
                                    <i :class="[skillForm.icon_class, 'text-xl']"></i>
                                </template>
                                <template v-else>
                                    <Code2 class="w-4 h-4 text-muted-foreground" />
                                </template>
                            </div>
                        </div>
                        <p class="text-[11px] text-muted-foreground">
                            Gunakan icon Font Awesome 6 (contoh: <code>fa-brands fa-vuejs text-[#42b883]</code>) atau format badge (<code>badge:TS:#3178c6</code>).
                        </p>
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <input
                            type="checkbox"
                            id="is_active"
                            v-model="skillForm.is_active"
                            class="rounded border-border text-white focus:ring-white"
                        />
                        <Label for="is_active" class="cursor-pointer text-xs">
                            Status Aktif (Tampilkan di halaman publik portofolio)
                        </Label>
                    </div>

                    <DialogFooter class="pt-4 border-t border-border/60">
                        <Button
                            type="button"
                            variant="outline"
                            @click="isSkillModalOpen = false"
                        >
                            Batal
                        </Button>
                        <Button
                            type="submit"
                            :disabled="skillForm.processing"
                            class="bg-white hover:bg-white text-[#0a0c10] font-bold"
                        >
                            <LoaderCircle v-if="skillForm.processing" class="w-4 h-4 animate-spin mr-1.5" />
                            <span>{{ editingSkillId ? 'Perbarui Keahlian' : 'Simpan Keahlian' }}</span>
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- ============================================================== -->
        <!-- MODAL: TAMBAH / EDIT KATEGORI -->
        <!-- ============================================================== -->
        <Dialog :open="isCategoryModalOpen" @update:open="isCategoryModalOpen = $event">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>
                        {{ editingCategoryId ? 'Edit Kategori' : 'Tambah Kategori Baru' }}
                    </DialogTitle>
                    <DialogDescription>
                        Kelola grup kategori untuk mengelompokkan keahlian teknis Anda.
                    </DialogDescription>
                </DialogHeader>

                <form @submit.prevent="submitCategoryForm" class="space-y-4 pt-2">
                    <div class="space-y-1.5">
                        <Label for="cat_name">Nama Kategori *</Label>
                        <Input
                            id="cat_name"
                            v-model="categoryForm.name"
                            placeholder="Misal: Frontend, Backend, Database"
                            class="h-9 text-xs"
                            required
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <Label for="cat_type">Tipe Kategori *</Label>
                            <select
                                id="cat_type"
                                v-model="categoryForm.category_type"
                                class="w-full h-9 px-3 rounded-md bg-background border border-border text-xs focus:border-white focus:outline-none"
                                required
                            >
                                <option value="technical">Technical</option>
                                <option value="philosophy">Philosophy / Soft Skills</option>
                            </select>
                        </div>

                        <div class="space-y-1.5">
                            <Label for="cat_order">Urutan Tampil</Label>
                            <Input
                                id="cat_order"
                                type="number"
                                v-model="categoryForm.order"
                                class="h-9 text-xs"
                                min="1"
                            />
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <Label for="cat_desc">Deskripsi Singkat</Label>
                        <Input
                            id="cat_desc"
                            v-model="categoryForm.description"
                            placeholder="Penjelasan singkat kategori..."
                            class="h-9 text-xs"
                        />
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <input
                            type="checkbox"
                            id="cat_active"
                            v-model="categoryForm.is_active"
                            class="rounded border-border text-white focus:ring-white"
                        />
                        <Label for="cat_active" class="cursor-pointer text-xs">
                            Status Aktif (Aktifkan kategori)
                        </Label>
                    </div>

                    <DialogFooter class="pt-4 border-t border-border/60">
                        <Button
                            type="button"
                            variant="outline"
                            @click="isCategoryModalOpen = false"
                        >
                            Batal
                        </Button>
                        <Button
                            type="submit"
                            :disabled="categoryForm.processing"
                            class="bg-white hover:bg-white text-[#0a0c10] font-bold"
                        >
                            <LoaderCircle v-if="categoryForm.processing" class="w-4 h-4 animate-spin mr-1.5" />
                            <span>{{ editingCategoryId ? 'Perbarui Kategori' : 'Simpan Kategori' }}</span>
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
