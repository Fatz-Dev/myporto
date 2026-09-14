<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { type BreadcrumbItem } from '@/types';
import {
    User,
    Sparkles,
    Briefcase,
    FileText,
    TrendingUp,
    Code2,
    Share2,
    Camera,
    Upload,
    Check,
    AlertCircle,
    CheckCircle2,
    Plus,
    Trash2,
    Edit2,
    ExternalLink,
    LoaderCircle,
    Globe,
    Save,
    RotateCcw,
    Link2
} from 'lucide-vue-next';

interface SocialLinkItem {
    id?: number;
    platform: string;
    label: string;
    url: string;
    icon?: string;
    order: number;
    is_active: boolean;
}

interface ProfileData {
    id: number;
    user_id: number;
    full_name: string;
    professional_title: string;
    hero_headline?: string;
    hero_subheadline?: string;
    bio_summary_1?: string;
    bio_summary_2?: string;
    avatar_url?: string;
    cv_url?: string;
    location?: string;
    email?: string;
    phone?: string;
    response_time?: string;
    is_available_for_hire: boolean;
    availability_badge_text?: string;
    years_experience?: string;
    projects_delivered?: string;
    client_satisfaction_rate?: string;
    rating_score?: number;
    rating_platform?: string;
    primary_stack?: string[];
    social_links?: SocialLinkItem[];
}

interface Props {
    profile: ProfileData;
    flash?: {
        success?: string;
        error?: string;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Biodata',
        href: '/dashboard/biodata',
    },
];

// Active Tab
const activeTab = ref<'identity' | 'hero' | 'bio' | 'metrics' | 'stack_socials'>('identity');

// Profile Form
const form = useForm({
    full_name: props.profile.full_name || '',
    professional_title: props.profile.professional_title || '',
    hero_headline: props.profile.hero_headline || '',
    hero_subheadline: props.profile.hero_subheadline || '',
    bio_summary_1: props.profile.bio_summary_1 || '',
    bio_summary_2: props.profile.bio_summary_2 || '',
    avatar_url: props.profile.avatar_url || '',
    avatar_file: null as File | null,
    cv_url: props.profile.cv_url || '',
    cv_file: null as File | null,
    location: props.profile.location || '',
    email: props.profile.email || '',
    phone: props.profile.phone || '',
    response_time: props.profile.response_time || '',
    is_available_for_hire: props.profile.is_available_for_hire ?? true,
    availability_badge_text: props.profile.availability_badge_text || '',
    years_experience: props.profile.years_experience || '',
    projects_delivered: props.profile.projects_delivered || '',
    client_satisfaction_rate: props.profile.client_satisfaction_rate || '',
    rating_score: props.profile.rating_score ?? 5.0,
    rating_platform: props.profile.rating_platform || 'Upwork',
});

// Image preview handler
const avatarPreview = ref<string | null>(props.profile.avatar_url || null);
const avatarFileInput = ref<HTMLInputElement | null>(null);

const onAvatarSelected = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        const file = target.files[0];
        form.avatar_file = file;
        const reader = new FileReader();
        reader.onload = (re) => {
            avatarPreview.value = re.target?.result as string;
        };
        reader.readAsDataURL(file);
    }
};


// CV Document handlers
const cvFileInput = ref<HTMLInputElement | null>(null);
const cvFileName = ref<string | null>(null);

const onCvSelected = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        const file = target.files[0];
        form.cv_file = file;
        cvFileName.value = file.name;
    }
};

const triggerCvUpload = () => {
    cvFileInput.value?.click();
};

const triggerAvatarUpload = () => {
    avatarFileInput.value?.click();
};



// Social Link Modal State
const isSocialModalOpen = ref(false);
const editingSocialId = ref<number | null>(null);

const socialForm = useForm({
    platform: 'github',
    label: '',
    url: '',
    order: 1,
    is_active: true,
});

const openAddSocialModal = () => {
    editingSocialId.value = null;
    socialForm.reset();
    socialForm.platform = 'github';
    socialForm.label = 'GitHub';
    socialForm.order = (props.profile.social_links?.length || 0) + 1;
    socialForm.is_active = true;
    isSocialModalOpen.value = true;
};

const openEditSocialModal = (link: SocialLinkItem) => {
    editingSocialId.value = link.id || null;
    socialForm.platform = link.platform;
    socialForm.label = link.label;
    socialForm.url = link.url;
    socialForm.order = link.order;
    socialForm.is_active = link.is_active;
    isSocialModalOpen.value = true;
};

const onPlatformChange = () => {
    const platforms: Record<string, string> = {
        github: 'GitHub',
        linkedin: 'LinkedIn',
        twitter: 'X (Twitter)',
        instagram: 'Instagram',
        youtube: 'YouTube',
        discord: 'Discord',
        tiktok: 'TikTok',
        twitch: 'Twitch',
        dribbble: 'Dribbble',
        medium: 'Medium',
    };
    if (platforms[socialForm.platform] && !socialForm.label) {
        socialForm.label = platforms[socialForm.platform];
    }
};

const submitSocialForm = () => {
    if (editingSocialId.value) {
        socialForm.put(`/dashboard/biodata/social-links/${editingSocialId.value}`, {
            preserveScroll: true,
            onSuccess: () => {
                isSocialModalOpen.value = false;
            },
        });
    } else {
        socialForm.post('/dashboard/biodata/social-links', {
            preserveScroll: true,
            onSuccess: () => {
                isSocialModalOpen.value = false;
            },
        });
    }
};

const deleteSocialLink = (id?: number) => {
    if (!id) return;
    if (confirm('Yakin ingin menghapus tautan media sosial ini?')) {
        router.delete(`/dashboard/biodata/social-links/${id}`, {
            preserveScroll: true,
        });
    }
};

// Submit Profile
const saveProfile = () => {
    form.post('/dashboard/biodata', {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Manajemen Biodata" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex-1 space-y-6 p-4 sm:p-6 lg:p-8 max-w-7xl w-full min-w-0 mx-auto overflow-x-hidden">
            <!-- Header Banner -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-6 border-b border-sidebar-border/70 dark:border-sidebar-border min-w-0">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-foreground flex items-center gap-2.5">
                        <User class="w-7 h-7" />
                        <span>Manajemen Biodata</span>
                    </h1>
                    <p class="text-sm text-muted-foreground mt-1">
                        Kelola data profil, biografi, status ketersediaan, serta tautan media sosial yang tampil di portofolio publik.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2.5 sm:gap-3 shrink-0">
                    <a
                        href="/"
                        target="_blank"
                        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-medium border border-border bg-card hover:bg-accent text-foreground transition-colors"
                    >
                        <ExternalLink class="w-3.5 h-3.5" />
                        <span>Lihat Publik</span>
                    </a>
                    <Button
                        type="button"
                        class="bg-card hover:bg-accent text-foreground border border-border font-semibold text-xs tracking-wide uppercase px-5"
                        :disabled="form.processing"
                        @click="saveProfile"
                    >
                        <LoaderCircle v-if="form.processing" class="w-4 h-4 mr-2 animate-spin" />
                        <Save v-else class="w-4 h-4 mr-2" />
                        <span>Simpan Perubahan</span>
                    </Button>
                </div>
            </div>

            <!-- Flash Notifications -->
            <div
                v-if="$page.props.flash?.success"
                class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 flex items-center gap-3 text-sm animate-in fade-in duration-300"
            >
                <CheckCircle2 class="w-5 h-5 shrink-0" />
                <span>{{ $page.props.flash.success }}</span>
            </div>

            <div
                v-if="form.hasErrors"
                class="p-4 rounded-xl bg-destructive/10 border border-destructive/30 text-destructive flex items-center gap-3 text-sm animate-in fade-in duration-300"
            >
                <AlertCircle class="w-5 h-5 shrink-0" />
                <span>Ada beberapa input yang belum valid. Mohon periksa form di bawah.</span>
            </div>

            <!-- Navigation Tabs -->
            <div class="flex items-center gap-1.5 overflow-x-auto pb-2 border-b border-border text-sm scrollbar-none w-full max-w-full min-w-0">
                <button
                    type="button"
                    :class="[
                        'flex items-center gap-2 px-4 py-2.5 rounded-lg font-medium text-xs tracking-wide uppercase whitespace-nowrap transition-all duration-200',
                        activeTab === 'identity'
                            ? 'bg-card hover:bg-accent text-foreground border border-white font-bold'
                            : 'text-muted-foreground hover:text-foreground hover:bg-accent/50'
                    ]"
                    @click="activeTab = 'identity'"
                >
                    <User class="w-4 h-4" />
                    <span>Identitas &amp; Kontak</span>
                </button>

                <button
                    type="button"
                    :class="[
                        'flex items-center gap-2 px-4 py-2.5 rounded-lg font-medium text-xs tracking-wide uppercase whitespace-nowrap transition-all duration-200',
                        activeTab === 'hero'
                            ? 'bg-card hover:bg-accent text-foreground border border-white font-bold'
                            : 'text-muted-foreground hover:text-foreground hover:bg-accent/50'
                    ]"
                    @click="activeTab = 'hero'"
                >
                    <Sparkles class="w-4 h-4" />
                    <span>Hero &amp; Ketersediaan</span>
                </button>

                <button
                    type="button"
                    :class="[
                        'flex items-center gap-2 px-4 py-2.5 rounded-lg font-medium text-xs tracking-wide uppercase whitespace-nowrap transition-all duration-200',
                        activeTab === 'bio'
                            ? 'bg-card hover:bg-accent text-foreground border border-white font-bold'
                            : 'text-muted-foreground hover:text-foreground hover:bg-accent/50'
                    ]"
                    @click="activeTab = 'bio'"
                >
                    <FileText class="w-4 h-4" />
                    <span>Narasi Bio (About)</span>
                </button>

                <!-- <button
                    type="button"
                    :class="[
                        'flex items-center gap-2 px-4 py-2.5 rounded-lg font-medium text-xs tracking-wide uppercase whitespace-nowrap transition-all duration-200',
                        activeTab === 'metrics'
                            ? 'bg-[#d97736]/15 text-[#d97736] border border-[#d97736]/30 font-bold'
                            : 'text-muted-foreground hover:text-foreground hover:bg-accent/50'
                    ]"
                    @click="activeTab = 'metrics'"
                >
                    <TrendingUp class="w-4 h-4" />
                    <span>Metrik &amp; Rating</span>
                </button> -->

                <button
                    type="button"
                    :class="[
                        'flex items-center gap-2 px-4 py-2.5 rounded-lg font-medium text-xs tracking-wide uppercase whitespace-nowrap transition-all duration-200',
                        activeTab === 'stack_socials'
                            ? 'bg-card hover:bg-accent text-foreground border border-white font-bold'
                            : 'text-muted-foreground hover:text-foreground hover:bg-accent/50'
                    ]"
                    @click="activeTab = 'stack_socials'"
                >
                    <Link2 class="w-4 h-4" />
                    <span>Media Sosial</span>
                </button>
            </div>

            <!-- Tab 1: Identitas & Kontak -->
            <div v-show="activeTab === 'identity'" class="space-y-6">
                <!-- CV Document Section -->
                <Card>
                    <CardHeader>
                        <CardTitle class="text-lg flex items-center gap-2">
                            <FileText class="w-5 h-5" />
                            <span>Dokumen CV (Curriculum Vitae / Resume)</span>
                        </CardTitle>
                        <CardDescription>
                            File CV PDF yang akan diunduh oleh pengunjung ketika menekan tombol "Download CV" di landing page.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 p-4 rounded-xl bg-accent/20 border border-border">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-red-500/10 text-red-500 flex items-center justify-center border border-red-500/20 font-mono text-xs font-bold shrink-0">
                                    PDF
                                </div>
                                <div class="min-w-0">
                                    <div class="text-sm font-semibold text-foreground truncate">
                                        {{ cvFileName || (props.profile.cv_url ? 'Dokumen CV Terunggah' : 'File CV Standar Aktif') }}
                                    </div>
                                    <div class="text-xs text-muted-foreground truncate">
                                        {{ props.profile.cv_url ? props.profile.cv_url : 'Menggunakan file template default (/storage/cv/CV_FatzDev.pdf)' }}
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 w-full sm:w-auto justify-end shrink-0">
                                <a
                                    href="/download-cv"
                                    target="_blank"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-border text-xs font-medium hover:bg-accent text-foreground transition-colors"
                                >
                                    <ExternalLink class="w-3.5 h-3.5" />
                                    <span>Tes Unduh</span>
                                </a>

                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    class="cursor-pointer"
                                    @click="triggerCvUpload"
                                >
                                    <Upload class="w-3.5 h-3.5 mr-1.5" />
                                    <span>{{ cvFileName ? 'Ganti File' : 'Unggah PDF CV' }}</span>
                                </Button>
                                <input
                                    ref="cvFileInput"
                                    type="file"
                                    accept="application/pdf"
                                    class="hidden"
                                    @change="onCvSelected"
                                />
                            </div>
                        </div>

                        <div>
                            <Label class="text-xs text-muted-foreground">Atau Masukkan URL Dokumen CV Eksternal (Google Drive / Dropbox / Cloud Storage):</Label>
                            <Input
                                v-model="form.cv_url"
                                type="url"
                                placeholder="https://drive.google.com/..."
                                class="mt-1.5 text-xs font-mono"
                            />
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle class="text-lg">Foto Avatar &amp; Informasi Dasar</CardTitle>
                        <CardDescription>
                            Foto profil yang ditampilkan di navbar sidebar, header, dan kartu hero.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <!-- Avatar Section -->
                        <div class="flex flex-col sm:flex-row items-center gap-6 p-4 rounded-xl bg-accent/20 border border-border">
                            <div class="relative w-28 h-28 rounded-full overflow-hidden ring-4 ring-white/90 shrink-0 bg-slate-900 group shadow-lg">
                                <img
                                    v-if="avatarPreview"
                                    :src="avatarPreview"
                                    alt="Preview Avatar"
                                    class="w-full h-full object-cover object-top"
                                />
                                <div v-else class="w-full h-full flex items-center justify-center text-muted-foreground">
                                    <User class="w-12 h-12" />
                                </div>
                                <button
                                    type="button"
                                    class="absolute inset-0 bg-black/60 backdrop-blur-[2px] opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center text-white text-[11px] font-semibold"
                                    @click="triggerAvatarUpload"
                                >
                                    <Camera class="w-5 h-5 mb-1" />
                                    <span>Ganti Foto</span>
                                </button>
                            </div>

                            <div class="space-y-3 flex-1 text-center sm:text-left">
                                <h3 class="font-semibold text-foreground text-sm">Unggah Foto Profil Baru</h3>
                                <p class="text-xs text-muted-foreground">
                                    Format JPG, PNG, atau WebP. Maksimal 3MB. Disarankan foto portrait rasio 1:1 atau 4:5 resolusi tinggi.
                                </p>
                                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-3">
                                    <Button
                                        type="button"
                                        variant="outline"
                                        size="sm"
                                        class="cursor-pointer"
                                        @click="triggerAvatarUpload"
                                    >
                                        <Upload class="w-3.5 h-3.5 mr-2" />
                                        <span>Pilih File Gambar</span>
                                    </Button>
                                    <input
                                        ref="avatarFileInput"
                                        type="file"
                                        accept="image/*"
                                        class="hidden"
                                        @change="onAvatarSelected"
                                    />
                                </div>
                                <div class="pt-2">
                                    <Label class="text-xs text-muted-foreground">Atau gunakan URL Gambar Eksternal:</Label>
                                    <Input
                                        v-model="form.avatar_url"
                                        type="url"
                                        placeholder="https://images.unsplash.com/..."
                                        class="mt-1 text-xs"
                                        @input="avatarPreview = form.avatar_url"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Name & Title -->
                        <div class="grid sm:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="full_name">Nama Lengkap / Brand *</Label>
                                <Input
                                    id="full_name"
                                    v-model="form.full_name"
                                    placeholder="Contoh: FatzDev"
                                    required
                                />
                                <span v-if="form.errors.full_name" class="text-xs text-destructive">
                                    {{ form.errors.full_name }}
                                </span>
                            </div>

                            <div class="space-y-2">
                                <Label for="professional_title">Gelar / Posisi Profesional *</Label>
                                <Input
                                    id="professional_title"
                                    v-model="form.professional_title"
                                    placeholder="Contoh: Full Stack Software Engineer"
                                    required
                                />
                                <span v-if="form.errors.professional_title" class="text-xs text-destructive">
                                    {{ form.errors.professional_title }}
                                </span>
                            </div>
                        </div>

                        <!-- Location & Email & Phone -->
                        <div class="grid sm:grid-cols-3 gap-4">
                            <div class="space-y-2">
                                <Label for="location">Lokasi Kerja</Label>
                                <Input
                                    id="location"
                                    v-model="form.location"
                                    placeholder="Contoh: Remote · Worldwide / Jakarta, ID"
                                />
                            </div>

                            <div class="space-y-2">
                                <Label for="email">Email Publik Kontak</Label>
                                <Input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    placeholder="hello@fatzdev.com"
                                />
                            </div>

                            <div class="space-y-2">
                                <Label for="phone">No. Telepon / WhatsApp</Label>
                                <Input
                                    id="phone"
                                    v-model="form.phone"
                                    placeholder="+62 812-3456-7890"
                                />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="response_time">Perkiraan Waktu Respons</Label>
                            <Input
                                id="response_time"
                                v-model="form.response_time"
                                placeholder="Contoh: Within 24 hours"
                            />
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Tab 2: Hero & Ketersediaan -->
            <div v-show="activeTab === 'hero'" class="space-y-6">
                <Card>
                    <CardHeader>
                        <CardTitle class="text-lg">Hero Headline &amp; Status Ketersediaan</CardTitle>
                        <CardDescription>
                            Teks utama yang langsung pertama kali menyambut pengunjung pada halaman muka.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <!-- Headline & Subheadline -->
                        <div class="space-y-2">
                            <Label for="hero_headline">Hero Main Headline</Label>
                            <Input
                                id="hero_headline"
                                v-model="form.hero_headline"
                                placeholder="Contoh: Building software that matters."
                                class="font-semibold"
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="hero_subheadline">Hero Sub-headline / Ringkasan Singkat</Label>
                            <textarea
                                id="hero_subheadline"
                                v-model="form.hero_subheadline"
                                rows="3"
                                placeholder="Tuliskan 1-2 kalimat pengantar keahlian utama Anda..."
                                class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                            ></textarea>
                        </div>

                        <!-- Freelance Availability Toggle -->
                        <div class="p-4 rounded-xl border border-border bg-accent/20 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span :class="['w-2.5 h-2.5 rounded-full', form.is_available_for_hire ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500']"></span>
                                    <span class="font-semibold text-sm text-foreground">Status Ketersediaan Proyek</span>
                                </div>
                                <p class="text-xs text-muted-foreground">
                                    Menampilkan indikator status di pojok foto profil dan badge availability di hero section.
                                </p>
                            </div>

                            <div class="flex items-center gap-3">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input
                                        type="checkbox"
                                        v-model="form.is_available_for_hire"
                                        class="sr-only peer"
                                    />
                                    <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                                </label>
                                <span class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                    {{ form.is_available_for_hire ? 'Aktif' : 'Non-Aktif' }}
                                </span>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="availability_badge_text">Teks Badge Ketersediaan</Label>
                            <Input
                                id="availability_badge_text"
                                v-model="form.availability_badge_text"
                                placeholder="Contoh: Available for freelance work"
                            />
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Tab 3: Narasi Bio (About Me) -->
            <div v-show="activeTab === 'bio'" class="space-y-6">
                <Card>
                    <CardHeader>
                        <CardTitle class="text-lg">Narasi Biografi Pengembang</CardTitle>
                        <CardDescription>
                            Teks narasi yang tampil di halaman/seksi **About Me** untuk menjelaskan latar belakang teknis dan filosofi kerja Anda.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <div class="space-y-2">
                            <Label for="bio_summary_1">Paragraf Bio 1 (Latar Belakang &amp; Passion)</Label>
                            <textarea
                                id="bio_summary_1"
                                v-model="form.bio_summary_1"
                                rows="4"
                                placeholder="Contoh: I'm FatzDev, a Full Stack Software Engineer with a CS degree and a relentless focus on building software that is both technically solid and a joy to use."
                                class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring leading-relaxed"
                            ></textarea>
                        </div>

                        <div class="space-y-2">
                            <Label for="bio_summary_2">Paragraf Bio 2 (Spesialisasi &amp; Komitmen Kualitas)</Label>
                            <textarea
                                id="bio_summary_2"
                                v-model="form.bio_summary_2"
                                rows="4"
                                placeholder="Contoh: From architecting REST APIs and microservices to implementing pixel-perfect interfaces - I work across the entire stack and treat performance, accessibility, and maintainability as non-negotiable."
                                class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring leading-relaxed"
                            ></textarea>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Tab 4: Metrik & Rating -->
            <div v-show="activeTab === 'metrics'" class="space-y-6">
                <Card>
                    <CardHeader>
                        <CardTitle class="text-lg">Statistik Prestasi &amp; Reputasi</CardTitle>
                        <CardDescription>
                            Angka pencapaian dan bukti sosial (social proof) yang memperkuat kredibilitas profesional Anda.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <div class="grid sm:grid-cols-3 gap-4">
                            <div class="space-y-2">
                                <Label for="years_experience">Tahun Pengalaman</Label>
                                <Input
                                    id="years_experience"
                                    v-model="form.years_experience"
                                    placeholder="Contoh: 5+"
                                />
                            </div>

                            <div class="space-y-2">
                                <Label for="projects_delivered">Jumlah Proyek Selesai</Label>
                                <Input
                                    id="projects_delivered"
                                    v-model="form.projects_delivered"
                                    placeholder="Contoh: 47+"
                                />
                            </div>

                            <div class="space-y-2">
                                <Label for="client_satisfaction_rate">Tingkat Kepuasan Klien</Label>
                                <Input
                                    id="client_satisfaction_rate"
                                    v-model="form.client_satisfaction_rate"
                                    placeholder="Contoh: 100%"
                                />
                            </div>
                        </div>

                        <div class="grid sm:grid-cols-2 gap-4 pt-4 border-t border-border">
                            <div class="space-y-2">
                                <Label for="rating_score">Skor Rating (0 - 5.0)</Label>
                                <Input
                                    id="rating_score"
                                    v-model.number="form.rating_score"
                                    type="number"
                                    step="0.1"
                                    min="0"
                                    max="5"
                                    placeholder="5.0"
                                />
                            </div>

                            <div class="space-y-2">
                                <Label for="rating_platform">Nama Platform Rating</Label>
                                <Input
                                    id="rating_platform"
                                    v-model="form.rating_platform"
                                    placeholder="Contoh: Upwork / Fiverr / Clutch"
                                />
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Tab 5: Primary Tech Stack & Media Sosial -->
            <div v-show="activeTab === 'stack_socials'" class="space-y-6">
                <!-- Social Media Table -->
                <Card>
                    <CardHeader class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <CardTitle class="text-lg">Tautan Media Sosial &amp; Portofolio</CardTitle>
                            <CardDescription>
                                Tautan akun publik yang tampil di sidebar desktop, mobile drawer, dan footer.
                            </CardDescription>
                        </div>
                        <Button
                            type="button"
                            size="sm"
                            class="bg-[#d97736] hover:bg-[#c66a2e] text-white cursor-pointer"
                            @click="openAddSocialModal"
                        >
                            <Plus class="w-4 h-4 mr-1" />
                            <span>Tambah Akun</span>
                        </Button>
                    </CardHeader>
                    <CardContent>
                        <div class="overflow-x-auto rounded-xl border border-border w-full max-w-full min-w-0">
                            <table class="w-full text-left text-sm min-w-[600px]">
                                <thead class="bg-accent/40 text-xs uppercase tracking-wider text-muted-foreground border-b border-border">
                                    <tr>
                                        <th class="py-3 px-4">Platform</th>
                                        <th class="py-3 px-4">Label</th>
                                        <th class="py-3 px-4">URL Tujuan</th>
                                        <th class="py-3 px-4 text-center">Urutan</th>
                                        <th class="py-3 px-4 text-center">Status</th>
                                        <th class="py-3 px-4 text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-border">
                                    <template v-if="profile.social_links && profile.social_links.length > 0">
                                        <tr
                                            v-for="soc in profile.social_links"
                                            :key="soc.id"
                                            class="hover:bg-accent/20 transition-colors"
                                        >
                                            <td class="py-3 px-4 font-mono font-semibold capitalize flex items-center gap-2">
                                                <i :class="['fa-brands', 'fa-' + (soc.platform === 'twitter' ? 'x-twitter' : soc.platform), 'text-[#d97736] text-base']"></i>
                                                <span>{{ soc.platform }}</span>
                                            </td>
                                            <td class="py-3 px-4 font-medium text-foreground">
                                                {{ soc.label }}
                                            </td>
                                            <td class="py-3 px-4 text-xs font-mono text-muted-foreground max-w-xs truncate">
                                                <a :href="soc.url" target="_blank" class="hover:text-[#d97736] hover:underline flex items-center gap-1">
                                                    <span>{{ soc.url }}</span>
                                                    <ExternalLink class="w-3 h-3 shrink-0" />
                                                </a>
                                            </td>
                                            <td class="py-3 px-4 text-center font-mono text-xs">
                                                {{ soc.order }}
                                            </td>
                                            <td class="py-3 px-4 text-center">
                                                <span
                                                    :class="[
                                                        'inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wider',
                                                        soc.is_active ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30' : 'bg-slate-500/15 text-slate-400 border border-slate-500/30'
                                                    ]"
                                                >
                                                    {{ soc.is_active ? 'Aktif' : 'Nonaktif' }}
                                                </span>
                                            </td>
                                            <td class="py-3 px-4 text-right space-x-2">
                                                <Button
                                                    type="button"
                                                    variant="ghost"
                                                    size="sm"
                                                    class="h-8 px-2 text-muted-foreground hover:text-foreground"
                                                    @click="openEditSocialModal(soc)"
                                                >
                                                    <Edit2 class="w-3.5 h-3.5" />
                                                </Button>
                                                <Button
                                                    type="button"
                                                    variant="ghost"
                                                    size="sm"
                                                    class="h-8 px-2 text-destructive hover:bg-destructive/10"
                                                    @click="deleteSocialLink(soc.id)"
                                                >
                                                    <Trash2 class="w-3.5 h-3.5" />
                                                </Button>
                                            </td>
                                        </tr>
                                    </template>
                                    <tr v-else>
                                        <td colspan="6" class="py-8 text-center text-muted-foreground text-xs italic">
                                            Belum ada tautan media sosial yang dikonfigurasi.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Bottom Floating/Sticky Save Action Bar -->
            <div class="sticky bottom-4 z-20 p-4 rounded-2xl bg-card/90 backdrop-blur-md border border-border shadow-2xl flex flex-col sm:flex-row items-center justify-between gap-4 w-full max-w-full min-w-0">
                <div class="text-xs text-muted-foreground hidden sm:block">
                    Pastikan seluruh data yang diperbarui sudah sesuai sebelum menyimpan.
                </div>
                <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                    <Button
                        type="button"
                        class="bg-card hover:bg-accent text-foreground border border-white font-bold text-xs tracking-wider uppercase px-6 py-2.5 rounded-xl cursor-pointer"
                        :disabled="form.processing"
                        @click="saveProfile"
                    >
                        <LoaderCircle v-if="form.processing" class="w-4 h-4 mr-2 animate-spin" />
                        <Save v-else class="w-4 h-4 mr-2" />
                        <span>Simpan Perubahan</span>
                    </Button>
                </div>
            </div>
        </div>

        <!-- Dialog Modal: Add / Edit Social Link -->
        <Dialog :open="isSocialModalOpen" @update:open="isSocialModalOpen = $event">
            <DialogContent class="sm:max-w-[480px]">
                <DialogHeader>
                    <DialogTitle>{{ editingSocialId ? 'Edit Tautan Media Sosial' : 'Tambah Tautan Media Sosial' }}</DialogTitle>
                    <DialogDescription>
                        Konfigurasikan platform media sosial, label tampilan, dan URL profil Anda.
                    </DialogDescription>
                </DialogHeader>

                <form @submit.prevent="submitSocialForm" class="space-y-4 py-3">
                    <div class="space-y-2">
                        <Label for="soc_platform">Platform *</Label>
                        <select
                            id="soc_platform"
                            v-model="socialForm.platform"
                            class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                            @change="onPlatformChange"
                        >
                            <option value="github">GitHub</option>
                            <option value="linkedin">LinkedIn</option>
                            <option value="twitter">X / Twitter</option>
                            <option value="instagram">Instagram</option>
                            <option value="youtube">YouTube</option>
                            <option value="discord">Discord</option>
                            <option value="tiktok">TikTok</option>
                            <option value="twitch">Twitch</option>
                            <option value="dribbble">Dribbble</option>
                            <option value="medium">Medium</option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <Label for="soc_label">Label Tampilan *</Label>
                        <Input
                            id="soc_label"
                            v-model="socialForm.label"
                            placeholder="Contoh: GitHub / Portfolio / My Blog"
                            required
                        />
                    </div>

                    <div class="space-y-2">
                        <Label for="soc_url">URL Tautan Lengkap *</Label>
                        <Input
                            id="soc_url"
                            v-model="socialForm.url"
                            type="url"
                            placeholder="https://github.com/username"
                            required
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label for="soc_order">Urutan Tampilan</Label>
                            <Input
                                id="soc_order"
                                v-model.number="socialForm.order"
                                type="number"
                                min="1"
                            />
                        </div>

                        <div class="space-y-2 flex flex-col justify-end">
                            <label class="flex items-center gap-2 cursor-pointer pt-2">
                                <input
                                    type="checkbox"
                                    v-model="socialForm.is_active"
                                    class="w-4 h-4 rounded text-[#d97736] focus:ring-[#d97736]"
                                />
                                <span class="text-sm font-medium text-foreground">Tampilkan ke Publik</span>
                            </label>
                        </div>
                    </div>

                    <DialogFooter class="pt-4">
                        <Button
                            type="button"
                            variant="outline"
                            @click="isSocialModalOpen = false"
                        >
                            Batal
                        </Button>
                        <Button
                            type="submit"
                            class="bg-[#d97736] hover:bg-[#c66a2e] text-white"
                            :disabled="socialForm.processing"
                        >
                            <LoaderCircle v-if="socialForm.processing" class="w-4 h-4 mr-2 animate-spin" />
                            <span>{{ editingSocialId ? 'Simpan Pembaruan' : 'Tambahkan Tautan' }}</span>
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
