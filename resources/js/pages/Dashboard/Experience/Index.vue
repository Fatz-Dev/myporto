<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItemType } from '@/types';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import {
    Briefcase,
    Plus,
    ExternalLink,
    Trash2,
    Edit2,
    CheckCircle2,
    AlertCircle,
    LoaderCircle,
    MapPin,
    Eye,
    EyeOff,
    Building2,
    CalendarDays,
    Clock,
    Circle,
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

interface ExperienceItem {
    id: number;
    role_title: string;
    company_name: string;
    location: string;
    start_period: string;
    end_period?: string;
    is_current: boolean;
    description?: string;
    company_logo_url?: string;
    order: number;
    is_active: boolean;
}

const props = defineProps<{
    experiences: ExperienceItem[];
    flash?: {
        success?: string;
        error?: string;
    };
}>();

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Experience', href: '/dashboard/experiences' },
];

const activeTab = ref<'list' | 'preview'>('list');

// Summary stats
const totalExperiences = computed(() => props.experiences.length);
const activeExperiences = computed(() => props.experiences.filter(e => e.is_active).length);
const currentExperiences = computed(() => props.experiences.filter(e => e.is_current).length);

// --- CRUD Modal ---
const isModalOpen = ref(false);
const editingId = ref<number | null>(null);

const form = useForm({
    role_title: '',
    company_name: '',
    location: 'Remote',
    start_period: '',
    end_period: '',
    is_current: false,
    description: '',
    company_logo_url: '',
    order: 0,
    is_active: true,
});

const openAddModal = () => {
    editingId.value = null;
    form.reset();
    form.clearErrors();
    form.location = 'Remote';
    form.is_active = true;
    form.order = totalExperiences.value + 1;
    isModalOpen.value = true;
};

const openEditModal = (experience: ExperienceItem) => {
    editingId.value = experience.id;
    form.clearErrors();
    form.role_title = experience.role_title;
    form.company_name = experience.company_name;
    form.location = experience.location;
    form.start_period = experience.start_period;
    form.end_period = experience.end_period ?? '';
    form.is_current = experience.is_current;
    form.description = experience.description ?? '';
    form.company_logo_url = experience.company_logo_url ?? '';
    form.order = experience.order;
    form.is_active = experience.is_active;
    isModalOpen.value = true;
};

const submitForm = () => {
    if (editingId.value) {
        form.put(`/dashboard/experiences/${editingId.value}`, {
            preserveScroll: true,
            onSuccess: () => { isModalOpen.value = false; },
        });
    } else {
        form.post('/dashboard/experiences', {
            preserveScroll: true,
            onSuccess: () => { isModalOpen.value = false; },
        });
    }
};

const deleteExperience = (id: number) => {
    if (confirm('Apakah Anda yakin ingin menghapus pengalaman kerja ini?')) {
        router.delete(`/dashboard/experiences/${id}`, { preserveScroll: true });
    }
};

const toggleActive = (id: number) => {
    router.patch(`/dashboard/experiences/${id}/toggle`, {}, { preserveScroll: true });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Manajemen Pengalaman Kerja" />

        <div class="w-full max-w-full min-w-0 overflow-x-hidden flex flex-col gap-6 p-4 md:p-6">

            <!-- Flash Message -->
            <div v-if="flash?.success"
                class="flex items-center gap-2 rounded-lg border border-green-500/30 bg-green-500/10 px-4 py-3 text-sm text-green-400">
                <CheckCircle2 class="h-4 w-4 shrink-0" />
                {{ flash.success }}
            </div>
            <div v-if="flash?.error"
                class="flex items-center gap-2 rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-400">
                <AlertCircle class="h-4 w-4 shrink-0" />
                {{ flash.error }}
            </div>

            <!-- Header Banner -->
            <div class="rounded-xl border border-border bg-card p-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10">
                            <Briefcase class="h-6 w-6 text-primary" />
                        </div>
                        <div>
                            <h1 class="text-xl font-bold text-foreground">Manajemen Pengalaman Kerja</h1>
                            <p class="text-sm text-muted-foreground">
                                {{ totalExperiences }} pengalaman &bull; {{ activeExperiences }} aktif &bull;
                                {{ currentExperiences }} saat ini
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="/experience" target="_blank"
                            class="inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-primary transition-colors">
                            <ExternalLink class="h-4 w-4" />
                            Lihat Halaman Publik
                        </a>
                        <Button @click="openAddModal" size="sm" class="gap-1.5">
                            <Plus class="h-4 w-4" />
                            Tambah Pengalaman
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Tab Navigation -->
            <div class="flex gap-1 rounded-lg border border-border bg-card p-1">
                <button @click="activeTab = 'list'"
                    :class="[
                        'flex-1 rounded-md px-4 py-2 text-sm font-medium transition-colors',
                        activeTab === 'list'
                            ? 'bg-primary text-primary-foreground'
                            : 'text-muted-foreground hover:text-foreground hover:bg-accent'
                    ]">
                    Daftar Pengalaman Kerja
                </button>
                <button @click="activeTab = 'preview'"
                    :class="[
                        'flex-1 rounded-md px-4 py-2 text-sm font-medium transition-colors',
                        activeTab === 'preview'
                            ? 'bg-primary text-primary-foreground'
                            : 'text-muted-foreground hover:text-foreground hover:bg-accent'
                    ]">
                    Pratinjau Timeline
                </button>
            </div>

            <!-- Tab 1: Experience List Table -->
            <div v-if="activeTab === 'list'" class="w-full min-w-0">
                <Card class="border-border">
                    <CardHeader>
                        <CardTitle class="text-foreground">Daftar Pengalaman Kerja</CardTitle>
                        <CardDescription>Kelola seluruh riwayat pengalaman kerja Anda</CardDescription>
                    </CardHeader>
                    <CardContent class="p-0">
                        <div class="w-full overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-border bg-muted/50">
                                        <th class="px-4 py-3 text-left font-medium text-muted-foreground">Jabatan</th>
                                        <th class="px-4 py-3 text-left font-medium text-muted-foreground">Perusahaan & Lokasi</th>
                                        <th class="px-4 py-3 text-left font-medium text-muted-foreground">Periode</th>
                                        <th class="px-4 py-3 text-left font-medium text-muted-foreground">Deskripsi</th>
                                        <th class="px-4 py-3 text-center font-medium text-muted-foreground">Order</th>
                                        <th class="px-4 py-3 text-center font-medium text-muted-foreground">Status</th>
                                        <th class="px-4 py-3 text-center font-medium text-muted-foreground">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="exp in experiences" :key="exp.id"
                                        class="border-b border-border hover:bg-accent/50 transition-colors">
                                        <td class="px-4 py-3">
                                            <div class="min-w-0">
                                                <p class="font-medium text-foreground">{{ exp.role_title }}</p>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-start gap-2 min-w-0">
                                                <div v-if="exp.company_logo_url"
                                                    class="h-8 w-8 shrink-0 rounded-md bg-muted overflow-hidden mt-0.5">
                                                    <img :src="exp.company_logo_url" :alt="exp.company_name"
                                                        class="h-full w-full object-cover" />
                                                </div>
                                                <div v-else
                                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-muted mt-0.5">
                                                    <Building2 class="h-4 w-4 text-muted-foreground" />
                                                </div>
                                                <div class="min-w-0">
                                                    <p class="font-medium text-foreground text-sm">{{ exp.company_name }}</p>
                                                    <p class="text-xs text-muted-foreground flex items-center gap-1">
                                                        <MapPin class="h-3 w-3 shrink-0" />
                                                        {{ exp.location }}
                                                    </p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-1.5 text-muted-foreground">
                                                <CalendarDays class="h-3.5 w-3.5 shrink-0" />
                                                <span class="text-sm whitespace-nowrap">
                                                    {{ exp.start_period }} –
                                                    <template v-if="exp.is_current">
                                                        <span class="inline-flex items-center gap-0.5 rounded-full bg-green-500/10 px-1.5 py-0.5 text-xs font-medium text-green-500">
                                                            <Clock class="h-3 w-3" /> Sekarang
                                                        </span>
                                                    </template>
                                                    <template v-else>{{ exp.end_period }}</template>
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <p class="text-xs text-muted-foreground line-clamp-2 max-w-[200px]">
                                                {{ exp.description || '—' }}
                                            </p>
                                        </td>
                                        <td class="px-4 py-3 text-center text-muted-foreground">{{ exp.order }}</td>
                                        <td class="px-4 py-3 text-center">
                                            <button @click="toggleActive(exp.id)">
                                                <span v-if="exp.is_active"
                                                    class="inline-flex items-center gap-1 rounded-full bg-green-500/10 px-2 py-0.5 text-xs font-medium text-green-500">
                                                    <Eye class="h-3 w-3" /> Aktif
                                                </span>
                                                <span v-else
                                                    class="inline-flex items-center gap-1 rounded-full bg-red-500/10 px-2 py-0.5 text-xs font-medium text-red-500">
                                                    <EyeOff class="h-3 w-3" /> Nonaktif
                                                </span>
                                            </button>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center justify-center gap-1">
                                                <Button variant="ghost" size="icon" class="h-8 w-8" @click="openEditModal(exp)">
                                                    <Edit2 class="h-4 w-4" />
                                                </Button>
                                                <Button variant="ghost" size="icon" class="h-8 w-8 text-destructive hover:text-destructive"
                                                    @click="deleteExperience(exp.id)">
                                                    <Trash2 class="h-4 w-4" />
                                                </Button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="experiences.length === 0">
                                        <td colspan="7" class="px-4 py-12 text-center text-muted-foreground">
                                            Belum ada pengalaman kerja. Klik "Tambah Pengalaman" untuk memulai.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Tab 2: Timeline Preview -->
            <div v-if="activeTab === 'preview'" class="w-full min-w-0">
                <Card class="border-border">
                    <CardHeader>
                        <CardTitle class="text-foreground">Pratinjau Timeline Pengalaman</CardTitle>
                        <CardDescription>Tampilan timeline seperti di halaman publik /experience</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div v-if="experiences.filter(e => e.is_active).length === 0"
                            class="py-12 text-center text-muted-foreground">
                            Tidak ada pengalaman aktif untuk ditampilkan.
                        </div>
                        <div v-else class="relative">
                            <!-- Vertical line -->
                            <div class="absolute left-6 top-0 bottom-0 w-px bg-border"></div>

                            <div v-for="(exp, idx) in experiences.filter(e => e.is_active)" :key="'timeline-' + exp.id"
                                class="relative flex gap-6 pb-8 last:pb-0">
                                <!-- Timeline dot -->
                                <div class="relative z-10 flex h-12 w-12 shrink-0 items-center justify-center rounded-full border-2"
                                    :class="exp.is_current
                                        ? 'border-primary bg-primary/10'
                                        : 'border-border bg-card'">
                                    <div v-if="exp.company_logo_url" class="h-8 w-8 rounded-full overflow-hidden">
                                        <img :src="exp.company_logo_url" :alt="exp.company_name" class="h-full w-full object-cover" />
                                    </div>
                                    <Building2 v-else class="h-5 w-5"
                                        :class="exp.is_current ? 'text-primary' : 'text-muted-foreground'" />
                                </div>

                                <!-- Content card -->
                                <div class="flex-1 rounded-xl border border-border bg-card p-4 hover:border-primary/30 transition-colors min-w-0">
                                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2">
                                        <div class="min-w-0">
                                            <h3 class="font-semibold text-foreground">{{ exp.role_title }}</h3>
                                            <p class="text-sm text-muted-foreground flex items-center gap-1.5 mt-0.5">
                                                <Building2 class="h-3.5 w-3.5 shrink-0" />
                                                {{ exp.company_name }}
                                                <span class="text-border">|</span>
                                                <MapPin class="h-3.5 w-3.5 shrink-0" />
                                                {{ exp.location }}
                                            </p>
                                        </div>
                                        <div class="flex items-center gap-1.5 text-xs text-muted-foreground whitespace-nowrap shrink-0">
                                            <CalendarDays class="h-3.5 w-3.5" />
                                            {{ exp.start_period }} –
                                            <template v-if="exp.is_current">
                                                <span class="inline-flex items-center gap-0.5 rounded-full bg-green-500/10 px-1.5 py-0.5 text-xs font-medium text-green-500">
                                                    Sekarang
                                                </span>
                                            </template>
                                            <template v-else>{{ exp.end_period }}</template>
                                        </div>
                                    </div>
                                    <p v-if="exp.description" class="mt-3 text-sm text-muted-foreground leading-relaxed">
                                        {{ exp.description }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Add/Edit Modal -->
            <Dialog v-model:open="isModalOpen">
                <DialogContent class="max-w-xl max-h-[85vh] overflow-y-auto">
                    <DialogHeader>
                        <DialogTitle>{{ editingId ? 'Edit Pengalaman Kerja' : 'Tambah Pengalaman Baru' }}</DialogTitle>
                        <DialogDescription>
                            {{ editingId ? 'Perbarui informasi pengalaman kerja.' : 'Isi detail pengalaman kerja baru.' }}
                        </DialogDescription>
                    </DialogHeader>

                    <form @submit.prevent="submitForm" class="space-y-4">
                        <!-- Role Title -->
                        <div class="space-y-2">
                            <Label for="role_title">Jabatan / Posisi <span class="text-destructive">*</span></Label>
                            <Input id="role_title" v-model="form.role_title" placeholder="Contoh: Full Stack Developer" />
                            <p v-if="form.errors.role_title" class="text-xs text-destructive">{{ form.errors.role_title }}</p>
                        </div>

                        <!-- Company Name -->
                        <div class="space-y-2">
                            <Label for="company_name">Nama Perusahaan <span class="text-destructive">*</span></Label>
                            <Input id="company_name" v-model="form.company_name" placeholder="Contoh: PT Teknologi Nusantara" />
                            <p v-if="form.errors.company_name" class="text-xs text-destructive">{{ form.errors.company_name }}</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Location -->
                            <div class="space-y-2">
                                <Label for="location">Lokasi</Label>
                                <Input id="location" v-model="form.location" placeholder="Remote / Jakarta / dll." />
                            </div>
                            <!-- Order -->
                            <div class="space-y-2">
                                <Label for="order">Urutan</Label>
                                <Input id="order" v-model.number="form.order" type="number" />
                            </div>
                        </div>

                        <!-- Period -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="start_period">Periode Mulai <span class="text-destructive">*</span></Label>
                                <Input id="start_period" v-model="form.start_period" placeholder="Jan 2023" />
                                <p v-if="form.errors.start_period" class="text-xs text-destructive">{{ form.errors.start_period }}</p>
                            </div>
                            <div class="space-y-2">
                                <Label for="end_period">Periode Selesai</Label>
                                <Input id="end_period" v-model="form.end_period"
                                    :placeholder="form.is_current ? 'Sekarang' : 'Des 2024'"
                                    :disabled="form.is_current" />
                            </div>
                        </div>

                        <!-- Is Current -->
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" v-model="form.is_current" class="rounded border-border" />
                            <span class="text-sm text-foreground">Masih bekerja di sini (posisi saat ini)</span>
                        </label>

                        <!-- Description -->
                        <div class="space-y-2">
                            <Label for="description">Deskripsi Pekerjaan</Label>
                            <Textarea id="description" v-model="form.description" rows="4"
                                placeholder="Jelaskan tanggung jawab dan pencapaian utama..." />
                        </div>

                        <!-- Company Logo URL -->
                        <div class="space-y-2">
                            <Label for="company_logo_url">URL Logo Perusahaan</Label>
                            <Input id="company_logo_url" v-model="form.company_logo_url" placeholder="https://..." />
                        </div>

                        <!-- Active Switch -->
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" v-model="form.is_active" class="rounded border-border" />
                            <span class="text-sm text-foreground">Aktif / Ditampilkan di halaman publik</span>
                        </label>

                        <DialogFooter>
                            <Button type="button" variant="outline" @click="isModalOpen = false">Batal</Button>
                            <Button type="submit" :disabled="form.processing">
                                <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin mr-1" />
                                {{ editingId ? 'Simpan Perubahan' : 'Tambah Pengalaman' }}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </div>
    </AppLayout>
</template>
