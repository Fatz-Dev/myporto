<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, h, ref } from 'vue';
import {
    createTableHook,
    createColumnHelper,
    createCoreRowModel,
    createPaginatedRowModel,
    rowPaginationFeature,
    FlexRender
} from '@tanstack/vue-table';
import {
    Calendar,
    Check,
    CheckCircle2,
    Clock,
    ExternalLink,
    Eye,
    Globe,
    Inbox,
    Mail,
    MessageSquare,
    Phone,
    PhoneCall,
    Search,
    Send,
    Trash2,
    User,
    X,
    AlertCircle,
    Archive,
    RotateCcw,
    MapPin,
    ArrowUpRight
} from 'lucide-vue-next';
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
    DialogTitle
} from '@/components/ui/dialog';

interface ContactMessage {
    id: number;
    name: string;
    email: string;
    subject: string;
    message: string;
    status: 'unread' | 'read' | 'replied' | 'archived';
    ip_address?: string;
    created_at: string;
}

interface Consultation {
    id: number;
    full_name: string;
    email: string;
    phone: string;
    service_type: string;
    preferred_date: string;
    notes?: string;
    status: 'pending' | 'confirmed' | 'completed' | 'cancelled';
    created_at: string;
}

const props = defineProps<{
    messages: ContactMessage[];
    consultations: Consultation[];
    profile?: any;
    stats: {
        total_messages: number;
        unread_messages: number;
        total_consultations: number;
        pending_consultations: number;
    };
}>();

const page = usePage();
const activeTab = ref<'messages' | 'consultations' | 'preview'>('messages');

// Filters
const searchMessage = ref('');
const messageStatusFilter = ref<string>('all');
const searchConsultation = ref('');
const consultationStatusFilter = ref<string>('all');

// Modals
const selectedMessage = ref<ContactMessage | null>(null);
const isMessageModalOpen = ref(false);

const selectedConsultation = ref<Consultation | null>(null);
const isConsultationModalOpen = ref(false);

const isDeleteMessageModalOpen = ref(false);
const messageToDelete = ref<ContactMessage | null>(null);

const isDeleteConsultationModalOpen = ref(false);
const consultationToDelete = ref<Consultation | null>(null);

// Filtered Messages
const filteredMessages = computed(() => {
    return props.messages.filter((msg) => {
        const matchesStatus = messageStatusFilter.value === 'all' || msg.status === messageStatusFilter.value;
        const q = searchMessage.value.toLowerCase().trim();
        const matchesSearch =
            !q ||
            msg.name.toLowerCase().includes(q) ||
            msg.email.toLowerCase().includes(q) ||
            msg.subject.toLowerCase().includes(q) ||
            msg.message.toLowerCase().includes(q);
        return matchesStatus && matchesSearch;
    });
});

// Filtered Consultations
const filteredConsultations = computed(() => {
    return props.consultations.filter((c) => {
        const matchesStatus = consultationStatusFilter.value === 'all' || c.status === consultationStatusFilter.value;
        const q = searchConsultation.value.toLowerCase().trim();
        const matchesSearch =
            !q ||
            c.full_name.toLowerCase().includes(q) ||
            c.email.toLowerCase().includes(q) ||
            c.phone.toLowerCase().includes(q) ||
            c.service_type.toLowerCase().includes(q) ||
            (c.notes && c.notes.toLowerCase().includes(q));
        return matchesStatus && matchesSearch;
    });
});

// Message Actions
const openMessageModal = (msg: ContactMessage) => {
    selectedMessage.value = msg;
    isMessageModalOpen.value = true;
    if (msg.status === 'unread') {
        updateMessageStatus(msg, 'read');
    }
};

const updateMessageStatus = (msg: ContactMessage, newStatus: string) => {
    router.patch(
        `/dashboard/contacts/${msg.id}/status`,
        { status: newStatus },
        {
            preserveScroll: true,
            onSuccess: () => {
                if (selectedMessage.value && selectedMessage.value.id === msg.id) {
                    selectedMessage.value.status = newStatus as any;
                }
            },
        }
    );
};

const confirmDeleteMessage = (msg: ContactMessage, e?: Event) => {
    if (e) e.stopPropagation();
    messageToDelete.value = msg;
    isDeleteMessageModalOpen.value = true;
};

const executeDeleteMessage = () => {
    if (!messageToDelete.value) return;
    router.delete(`/dashboard/contacts/${messageToDelete.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            isDeleteMessageModalOpen.value = false;
            messageToDelete.value = null;
            if (isMessageModalOpen.value) {
                isMessageModalOpen.value = false;
            }
        },
    });
};

// Consultation Actions
const openConsultationModal = (c: Consultation) => {
    selectedConsultation.value = c;
    isConsultationModalOpen.value = true;
};

const updateConsultationStatus = (c: Consultation, newStatus: string) => {
    router.patch(
        `/dashboard/consultations/${c.id}/status`,
        { status: newStatus },
        {
            preserveScroll: true,
            onSuccess: () => {
                if (selectedConsultation.value && selectedConsultation.value.id === c.id) {
                    selectedConsultation.value.status = newStatus as any;
                }
            },
        }
    );
};

const confirmDeleteConsultation = (c: Consultation, e?: Event) => {
    if (e) e.stopPropagation();
    consultationToDelete.value = c;
    isDeleteConsultationModalOpen.value = true;
};

const executeDeleteConsultation = () => {
    if (!consultationToDelete.value) return;
    router.delete(`/dashboard/consultations/${consultationToDelete.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            isDeleteConsultationModalOpen.value = false;
            consultationToDelete.value = null;
            if (isConsultationModalOpen.value) {
                isConsultationModalOpen.value = false;
            }
        },
    });
};

const formatDate = (dateStr: string) => {
    try {
        const d = new Date(dateStr);
        return d.toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    } catch {
        return dateStr;
    }
};

const formatShortDate = (dateStr: string) => {
    try {
        const d = new Date(dateStr);
        return d.toLocaleDateString('id-ID', {
            weekday: 'short',
            day: 'numeric',
            month: 'short',
            year: 'numeric',
        });
    } catch {
        return dateStr;
    }
};

const getCleanPhone = (phone: string) => {
    return phone.replace(/[^0-9]/g, '');
};

const getMessageStatusBadge = (status: string) => {
    switch (status) {
        case 'unread':
            return { label: 'Belum Dibaca', class: 'bg-rose-500/10 text-rose-500 dark:text-rose-400 border-rose-500/20' };
        case 'read':
            return { label: 'Dibaca', class: 'bg-muted text-muted-foreground border-border' };
        case 'replied':
            return { label: 'Dibalas', class: 'bg-blue-500/10 text-blue-500 dark:text-blue-400 border-blue-500/20' };
        case 'archived':
            return { label: 'Diarsipkan', class: 'bg-amber-500/10 text-amber-500 dark:text-amber-400 border-amber-500/20' };
        default:
            return { label: status, class: 'bg-muted text-muted-foreground border-border' };
    }
};

const getConsultationStatusBadge = (status: string) => {
    switch (status) {
        case 'pending':
            return { label: 'Menunggu', class: 'bg-amber-500/10 text-amber-500 dark:text-amber-400 border-amber-500/20' };
        case 'confirmed':
            return { label: 'Dikonfirmasi', class: 'bg-emerald-500/10 text-emerald-500 dark:text-emerald-400 border-emerald-500/20' };
        case 'completed':
            return { label: 'Selesai', class: 'bg-blue-500/10 text-blue-500 dark:text-blue-400 border-blue-500/20' };
        case 'cancelled':
            return { label: 'Dibatalkan', class: 'bg-rose-500/10 text-rose-500 dark:text-rose-400 border-rose-500/20' };
        default:
            return { label: status, class: 'bg-muted text-muted-foreground border-border' };
    }
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

// Messages Table Columns
const msgColumnHelper = createColumnHelper<ContactMessage>();
const messageColumns = [
    msgColumnHelper.accessor('name', {
        header: 'Pengirim',
        cell: ({ row }) => {
            const msg = row.original;
            return h('div', { class: 'flex items-center gap-3' }, [
                h(
                    'div',
                    {
                        class: [
                            'w-8 h-8 rounded-lg flex items-center justify-center font-bold text-xs shrink-0',
                            msg.status === 'unread'
                                ? 'bg-rose-500/10 text-rose-500 border border-rose-500/30'
                                : 'bg-muted text-muted-foreground border border-border',
                        ],
                    },
                    msg.name ? msg.name.charAt(0).toUpperCase() : '?'
                ),
                h('div', { class: 'min-w-0' }, [
                    h('p', { class: 'font-semibold text-foreground truncate' }, msg.name),
                    h('p', { class: 'text-muted-foreground text-[11px] font-mono truncate' }, msg.email),
                ]),
            ]);
        },
    }),
    msgColumnHelper.accessor('subject', {
        header: 'Subjek & Pesan',
        cell: ({ row }) => {
            const msg = row.original;
            return h('div', { class: 'space-y-0.5 max-w-md' }, [
                h('div', { class: 'flex items-center gap-2' }, [
                    msg.status === 'unread'
                        ? h('span', { class: 'w-2 h-2 rounded-full bg-rose-500 shrink-0' })
                        : null,
                    h(
                        'span',
                        {
                            class: [
                                'text-xs truncate font-medium',
                                msg.status === 'unread' ? 'text-foreground font-bold' : 'text-foreground/90',
                            ],
                        },
                        msg.subject
                    ),
                ]),
                h('p', { class: 'text-[11px] text-muted-foreground line-clamp-1' }, msg.message),
            ]);
        },
    }),
    msgColumnHelper.accessor('created_at', {
        header: 'Waktu & IP',
        cell: ({ row }) => {
            const msg = row.original;
            return h('div', { class: 'space-y-0.5 text-xs text-muted-foreground' }, [
                h('p', { class: 'font-medium text-foreground/80' }, formatDate(msg.created_at)),
                msg.ip_address
                    ? h('p', { class: 'text-[10px] font-mono opacity-70' }, msg.ip_address)
                    : null,
            ]);
        },
    }),
    msgColumnHelper.accessor('status', {
        header: 'Status',
        cell: ({ row }) => {
            const badge = getMessageStatusBadge(row.original.status);
            return h(
                'span',
                {
                    class: [
                        'inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium border whitespace-nowrap',
                        badge.class,
                    ],
                },
                badge.label
            );
        },
    }),
    msgColumnHelper.display({
        id: 'actions',
        header: () => h('div', { class: 'text-right' }, 'Aksi'),
        cell: ({ row }) => {
            const msg = row.original;
            return h('div', { class: 'flex items-center justify-end gap-1.5' }, [
                h(
                    Button,
                    {
                        size: 'sm',
                        variant: 'ghost',
                        class: 'h-8 px-2 text-xs text-muted-foreground hover:text-foreground',
                        onClick: (e: Event) => {
                            e.stopPropagation();
                            openMessageModal(msg);
                        },
                    },
                    () => [h(Eye, { class: 'w-3.5 h-3.5 mr-1' }), 'Detail']
                ),
                h(
                    'a',
                    {
                        href: `mailto:${msg.email}?subject=Re: ${encodeURIComponent(msg.subject)}`,
                        class: 'inline-flex items-center justify-center h-8 px-2 rounded-md text-xs font-medium border border-border bg-card hover:bg-accent text-foreground transition-colors',
                        onClick: (e: Event) => e.stopPropagation(),
                    },
                    [h(Send, { class: 'w-3.5 h-3.5 mr-1' }), 'Balas']
                ),
                h(
                    Button,
                    {
                        size: 'sm',
                        variant: 'ghost',
                        class: 'h-8 w-8 p-0 text-muted-foreground hover:text-destructive hover:bg-destructive/10',
                        onClick: (e: Event) => confirmDeleteMessage(msg, e),
                    },
                    () => h(Trash2, { class: 'w-3.5 h-3.5' })
                ),
            ]);
        },
    }),
];

const messageTable = useAppTable({
    data: filteredMessages,
    columns: messageColumns,
});

// Consultations Table Columns
const consColumnHelper = createColumnHelper<Consultation>();
const consultationColumns = [
    consColumnHelper.accessor('full_name', {
        header: 'Klien',
        cell: ({ row }) => {
            const c = row.original;
            return h('div', { class: 'flex items-center gap-3' }, [
                h(
                    'div',
                    {
                        class: 'w-8 h-8 rounded-lg bg-blue-500/10 border border-blue-500/20 text-blue-500 dark:text-blue-400 flex items-center justify-center font-bold text-xs shrink-0',
                    },
                    c.full_name ? c.full_name.charAt(0).toUpperCase() : '?'
                ),
                h('div', { class: 'min-w-0' }, [
                    h('p', { class: 'font-semibold text-foreground truncate' }, c.full_name),
                    h('p', { class: 'text-muted-foreground text-[11px] font-mono truncate' }, `${c.email} • ${c.phone}`),
                ]),
            ]);
        },
    }),
    consColumnHelper.accessor('service_type', {
        header: 'Layanan',
        cell: ({ row }) => {
            return h(
                'span',
                {
                    class: 'inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium border border-border bg-muted/50 text-foreground',
                },
                row.original.service_type
            );
        },
    }),
    consColumnHelper.accessor('preferred_date', {
        header: 'Jadwal Diminta',
        cell: ({ row }) => {
            return h('div', { class: 'flex items-center gap-1.5 text-xs text-foreground font-medium' }, [
                h(Calendar, { class: 'w-3.5 h-3.5 text-muted-foreground shrink-0' }),
                h('span', formatShortDate(row.original.preferred_date)),
            ]);
        },
    }),
    consColumnHelper.accessor('notes', {
        header: 'Catatan',
        cell: ({ row }) => {
            return h(
                'p',
                { class: 'text-xs text-muted-foreground line-clamp-1 max-w-xs' },
                row.original.notes || '-'
            );
        },
    }),
    consColumnHelper.accessor('status', {
        header: 'Status',
        cell: ({ row }) => {
            const badge = getConsultationStatusBadge(row.original.status);
            return h(
                'span',
                {
                    class: [
                        'inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium border whitespace-nowrap',
                        badge.class,
                    ],
                },
                badge.label
            );
        },
    }),
    consColumnHelper.display({
        id: 'actions',
        header: () => h('div', { class: 'text-right' }, 'Aksi'),
        cell: ({ row }) => {
            const c = row.original;
            return h('div', { class: 'flex items-center justify-end gap-1.5' }, [
                h(
                    Button,
                    {
                        size: 'sm',
                        variant: 'ghost',
                        class: 'h-8 px-2 text-xs text-muted-foreground hover:text-foreground',
                        onClick: (e: Event) => {
                            e.stopPropagation();
                            openConsultationModal(c);
                        },
                    },
                    () => [h(Eye, { class: 'w-3.5 h-3.5 mr-1' }), 'Detail']
                ),
                c.phone
                    ? h(
                          'a',
                          {
                              href: `https://wa.me/${getCleanPhone(c.phone)}?text=${encodeURIComponent('Halo ' + c.full_name + ', saya mengonfirmasi jadwal konsultasi Anda...')}`,
                              target: '_blank',
                              class: 'inline-flex items-center justify-center h-8 px-2 rounded-md text-xs font-semibold bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 transition-colors',
                              title: 'Hubungi via WhatsApp',
                              onClick: (e: Event) => e.stopPropagation(),
                          },
                          [h(Phone, { class: 'w-3.5 h-3.5 mr-1' }), 'WA']
                      )
                    : null,
                h(
                    Button,
                    {
                        size: 'sm',
                        variant: 'ghost',
                        class: 'h-8 w-8 p-0 text-muted-foreground hover:text-destructive hover:bg-destructive/10',
                        onClick: (e: Event) => confirmDeleteConsultation(c, e),
                    },
                    () => h(Trash2, { class: 'w-3.5 h-3.5' })
                ),
            ]);
        },
    }),
];

const consultationTable = useAppTable({
    data: filteredConsultations,
    columns: consultationColumns,
});
</script>

<template>
    <Head title="Manajemen Pesan & Kontak" />

    <AppLayout
        :breadcrumbs="[
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Pesan & Kontak', href: '/dashboard/contacts' },
        ]"
    >
        <div class="flex-1 space-y-6 p-4 sm:p-6 lg:p-8 max-w-7xl w-full min-w-0 mx-auto overflow-x-hidden">
            <!-- Header Banner (Biodata Clean Style) -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-6 border-b border-sidebar-border/70 dark:border-sidebar-border min-w-0">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-foreground flex items-center gap-2.5">
                        <MessageSquare class="w-7 h-7" />
                        <span>Manajemen Pesan &amp; Kontak</span>
                    </h1>
                    <p class="text-sm text-muted-foreground mt-1">
                        Pantau pesan masuk formulir kontak dan kelola reservasi discovery call calon klien secara terpusat.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2.5 sm:gap-3 shrink-0">
                    <a
                        href="/contact"
                        target="_blank"
                        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-medium border border-border bg-card hover:bg-accent text-foreground transition-colors"
                    >
                        <ExternalLink class="w-3.5 h-3.5" />
                        <span>Lihat Halaman Kontak</span>
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
                        <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-500 dark:text-amber-400 shrink-0">
                            <Inbox class="w-5 h-5" />
                        </div>
                        <div>
                            <p class="text-xs text-muted-foreground font-medium">Total Pesan</p>
                            <p class="text-xl sm:text-2xl font-bold text-foreground">{{ stats.total_messages }}</p>
                        </div>
                    </CardContent>
                </Card>

                <Card class="border-border bg-card shadow-sm">
                    <CardContent class="p-4 flex items-center gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-rose-500/10 border border-rose-500/20 flex items-center justify-center text-rose-500 dark:text-rose-400 shrink-0">
                            <Mail class="w-5 h-5" />
                        </div>
                        <div>
                            <p class="text-xs text-muted-foreground font-medium">Belum Dibaca</p>
                            <p class="text-xl sm:text-2xl font-bold text-foreground">{{ stats.unread_messages }}</p>
                        </div>
                    </CardContent>
                </Card>

                <Card class="border-border bg-card shadow-sm">
                    <CardContent class="p-4 flex items-center gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-500 dark:text-blue-400 shrink-0">
                            <Calendar class="w-5 h-5" />
                        </div>
                        <div>
                            <p class="text-xs text-muted-foreground font-medium">Total Konsultasi</p>
                            <p class="text-xl sm:text-2xl font-bold text-foreground">{{ stats.total_consultations }}</p>
                        </div>
                    </CardContent>
                </Card>

                <Card class="border-border bg-card shadow-sm">
                    <CardContent class="p-4 flex items-center gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-500 dark:text-emerald-400 shrink-0">
                            <Clock class="w-5 h-5" />
                        </div>
                        <div>
                            <p class="text-xs text-muted-foreground font-medium">Konsultasi Menunggu</p>
                            <p class="text-xl sm:text-2xl font-bold text-foreground">{{ stats.pending_consultations }}</p>
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
                        activeTab === 'messages'
                            ? 'bg-card hover:bg-accent text-foreground border border-white font-bold'
                            : 'text-muted-foreground hover:text-foreground hover:bg-accent/50'
                    ]"
                    @click="activeTab = 'messages'"
                >
                    <Inbox class="w-3.5 h-3.5" />
                    <span>Pesan Masuk</span>
                    <span
                        v-if="stats.unread_messages > 0"
                        class="ml-1 px-1.5 py-0.5 rounded-full text-[10px] bg-rose-500/20 text-rose-500 dark:text-rose-400 border border-rose-500/30 font-bold"
                    >
                        {{ stats.unread_messages }}
                    </span>
                </button>

                <button
                    type="button"
                    :class="[
                        'flex items-center gap-2 px-4 py-2.5 rounded-lg font-medium text-xs tracking-wide uppercase whitespace-nowrap transition-all duration-200',
                        activeTab === 'consultations'
                            ? 'bg-card hover:bg-accent text-foreground border border-white font-bold'
                            : 'text-muted-foreground hover:text-foreground hover:bg-accent/50'
                    ]"
                    @click="activeTab = 'consultations'"
                >
                    <Calendar class="w-3.5 h-3.5" />
                    <span>Jadwal Konsultasi</span>
                    <span
                        v-if="stats.pending_consultations > 0"
                        class="ml-1 px-1.5 py-0.5 rounded-full text-[10px] bg-amber-500/20 text-amber-500 dark:text-amber-400 border border-amber-500/30 font-bold"
                    >
                        {{ stats.pending_consultations }}
                    </span>
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
                    <span>Pratinjau Kontak</span>
                </button>
            </div>

            <!-- ============================================== -->
            <!-- TAB 1: PESAN MASUK (TANSTACK TABLE)            -->
            <!-- ============================================== -->
            <div v-show="activeTab === 'messages'" class="space-y-4">
                <Card class="border-border bg-card shadow-sm">
                    <CardHeader class="p-4 sm:p-5 border-b border-border/60">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <!-- Search -->
                            <div class="relative flex-1 max-w-md">
                                <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                                <Input
                                    v-model="searchMessage"
                                    placeholder="Cari pengirim, email, subjek, isi pesan..."
                                    class="pl-9 h-9 text-xs bg-card border-input"
                                />
                            </div>

                            <!-- Filter Pills -->
                            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0">
                                <button
                                    v-for="opt in [
                                        { key: 'all', label: 'Semua' },
                                        { key: 'unread', label: 'Belum Dibaca' },
                                        { key: 'read', label: 'Dibaca' },
                                        { key: 'replied', label: 'Dibalas' },
                                        { key: 'archived', label: 'Diarsipkan' },
                                    ]"
                                    :key="opt.key"
                                    type="button"
                                    :class="[
                                        'px-3 py-1.5 rounded-lg text-xs font-medium transition-colors whitespace-nowrap border',
                                        messageStatusFilter === opt.key
                                            ? 'bg-primary text-primary-foreground border-primary font-semibold shadow-sm'
                                            : 'bg-card hover:bg-accent text-muted-foreground border-border'
                                    ]"
                                    @click="messageStatusFilter = opt.key"
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
                                <tr v-for="headerGroup in messageTable.getHeaderGroups()" :key="headerGroup.id">
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
                                <tr v-if="messageTable.getPaginatedRowModel().rows.length === 0">
                                    <td :colspan="messageColumns.length" class="p-8 text-center text-muted-foreground text-sm">
                                        Tidak ada pesan kontak yang ditemukan.
                                    </td>
                                </tr>
                                <tr
                                    v-for="row in messageTable.getPaginatedRowModel().rows"
                                    :key="row.id"
                                    class="hover:bg-muted/30 transition-colors cursor-pointer group"
                                    :class="row.original.status === 'unread' ? 'bg-rose-500/[0.03]' : ''"
                                    @click="openMessageModal(row.original)"
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
                            Menampilkan {{ filteredMessages.length }} pesan
                            <span v-if="messageTable.getPageCount() > 1">
                                (Halaman {{ (messageTable.atoms.pagination?.get()?.pageIndex ?? 0) + 1 }} dari {{ messageTable.getPageCount() }})
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <Button
                                size="sm"
                                variant="outline"
                                class="h-8 text-xs bg-card border-border"
                                :disabled="!messageTable.getCanPreviousPage()"
                                @click="messageTable.previousPage()"
                            >
                                Sebelumnya
                            </Button>
                            <Button
                                size="sm"
                                variant="outline"
                                class="h-8 text-xs bg-card border-border"
                                :disabled="!messageTable.getCanNextPage()"
                                @click="messageTable.nextPage()"
                            >
                                Berikutnya
                            </Button>
                        </div>
                    </div>
                </Card>
            </div>

            <!-- ============================================== -->
            <!-- TAB 2: JADWAL KONSULTASI (TANSTACK TABLE)      -->
            <!-- ============================================== -->
            <div v-show="activeTab === 'consultations'" class="space-y-4">
                <Card class="border-border bg-card shadow-sm">
                    <CardHeader class="p-4 sm:p-5 border-b border-border/60">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <!-- Search -->
                            <div class="relative flex-1 max-w-md">
                                <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                                <Input
                                    v-model="searchConsultation"
                                    placeholder="Cari nama klien, email, telepon, layanan..."
                                    class="pl-9 h-9 text-xs bg-card border-input"
                                />
                            </div>

                            <!-- Filter Pills -->
                            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0">
                                <button
                                    v-for="opt in [
                                        { key: 'all', label: 'Semua' },
                                        { key: 'pending', label: 'Menunggu' },
                                        { key: 'confirmed', label: 'Dikonfirmasi' },
                                        { key: 'completed', label: 'Selesai' },
                                        { key: 'cancelled', label: 'Dibatalkan' },
                                    ]"
                                    :key="opt.key"
                                    type="button"
                                    :class="[
                                        'px-3 py-1.5 rounded-lg text-xs font-medium transition-colors whitespace-nowrap border',
                                        consultationStatusFilter === opt.key
                                            ? 'bg-primary text-primary-foreground border-primary font-semibold shadow-sm'
                                            : 'bg-card hover:bg-accent text-muted-foreground border-border'
                                    ]"
                                    @click="consultationStatusFilter = opt.key"
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
                                <tr v-for="headerGroup in consultationTable.getHeaderGroups()" :key="headerGroup.id">
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
                                <tr v-if="consultationTable.getPaginatedRowModel().rows.length === 0">
                                    <td :colspan="consultationColumns.length" class="p-8 text-center text-muted-foreground text-sm">
                                        Tidak ada jadwal konsultasi yang ditemukan.
                                    </td>
                                </tr>
                                <tr
                                    v-for="row in consultationTable.getPaginatedRowModel().rows"
                                    :key="row.id"
                                    class="hover:bg-muted/30 transition-colors cursor-pointer group"
                                    @click="openConsultationModal(row.original)"
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
                            Menampilkan {{ filteredConsultations.length }} jadwal konsultasi
                            <span v-if="consultationTable.getPageCount() > 1">
                                (Halaman {{ (consultationTable.atoms.pagination?.get()?.pageIndex ?? 0) + 1 }} dari {{ consultationTable.getPageCount() }})
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <Button
                                size="sm"
                                variant="outline"
                                class="h-8 text-xs bg-card border-border"
                                :disabled="!consultationTable.getCanPreviousPage()"
                                @click="consultationTable.previousPage()"
                            >
                                Sebelumnya
                            </Button>
                            <Button
                                size="sm"
                                variant="outline"
                                class="h-8 text-xs bg-card border-border"
                                :disabled="!consultationTable.getCanNextPage()"
                                @click="consultationTable.nextPage()"
                            >
                                Berikutnya
                            </Button>
                        </div>
                    </div>
                </Card>
            </div>

            <!-- ============================================== -->
            <!-- TAB 3: PRATINJAU INFO KONTAK                   -->
            <!-- ============================================== -->
            <div v-show="activeTab === 'preview'" class="space-y-6">
                <Card class="border-border bg-card shadow-sm">
                    <CardHeader class="border-b border-border/60 pb-5">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div>
                                <CardTitle class="text-lg font-bold text-foreground">Informasi Kontak Publik</CardTitle>
                                <CardDescription class="text-xs text-muted-foreground mt-0.5">
                                    Data kontak berikut ditampilkan langsung pada kartu kontak di halaman landing page publik.
                                </CardDescription>
                            </div>
                            <Link
                                href="/dashboard/biodata"
                                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-card hover:bg-accent text-xs font-semibold text-foreground border border-border transition-colors"
                            >
                                <User class="w-3.5 h-3.5 text-primary" />
                                <span>Edit di Biodata</span>
                            </Link>
                        </div>
                    </CardHeader>
                    <CardContent class="p-6 space-y-6">
                        <!-- Cards Grid -->
                        <div class="grid sm:grid-cols-2 gap-4">
                            <!-- Direct Line Card -->
                            <div class="p-5 rounded-xl bg-card border border-border space-y-3">
                                <span class="text-[10px] font-mono font-semibold uppercase tracking-widest text-primary">
                                    Direct line
                                </span>
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-primary/10 border border-primary/20 flex items-center justify-center text-primary shrink-0">
                                        <Phone class="w-5 h-5" />
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-bold text-foreground text-sm truncate">
                                            {{ profile?.phone || '+62 822-8517-5784' }}
                                        </p>
                                        <p class="text-xs text-muted-foreground">WhatsApp / Direct Call</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Email Card -->
                            <div class="p-5 rounded-xl bg-card border border-border space-y-3">
                                <span class="text-[10px] font-mono font-semibold uppercase tracking-widest text-blue-500 dark:text-blue-400">
                                    Official email
                                </span>
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-500 dark:text-blue-400 shrink-0">
                                        <Mail class="w-5 h-5" />
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-bold text-foreground text-sm truncate">
                                            {{ profile?.email || 'contact@fatzdev.com' }}
                                        </p>
                                        <p class="text-xs text-muted-foreground">Inquiry Proyek &amp; Kolaborasi</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Location Card -->
                            <div class="p-5 rounded-xl bg-card border border-border space-y-3">
                                <span class="text-[10px] font-mono font-semibold uppercase tracking-widest text-emerald-500 dark:text-emerald-400">
                                    Work location
                                </span>
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-500 dark:text-emerald-400 shrink-0">
                                        <MapPin class="w-5 h-5" />
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-bold text-foreground text-sm truncate">
                                            {{ profile?.location || 'Indonesia (WIB/UTC+7)' }}
                                        </p>
                                        <p class="text-xs text-muted-foreground">Tersedia Remote &amp; On-Site</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Response Time Card -->
                            <div class="p-5 rounded-xl bg-card border border-border space-y-3">
                                <span class="text-[10px] font-mono font-semibold uppercase tracking-widest text-amber-500 dark:text-amber-400">
                                    Response time
                                </span>
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-500 dark:text-amber-400 shrink-0">
                                        <Clock class="w-5 h-5" />
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-bold text-foreground text-sm truncate">
                                            {{ profile?.response_time || '< 24 Jam' }}
                                        </p>
                                        <p class="text-xs text-muted-foreground">Waktu Respon Cepat</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- DIALOG: DETAIL PESAN KONTAK                    -->
        <!-- ============================================== -->
        <Dialog :open="isMessageModalOpen" @update:open="isMessageModalOpen = $event">
            <DialogContent class="sm:max-w-xl bg-card border-border text-foreground">
                <DialogHeader>
                    <div class="flex items-center justify-between gap-4">
                        <DialogTitle class="text-lg font-bold text-foreground">Detail Pesan Kontak</DialogTitle>
                        <span
                            v-if="selectedMessage"
                            :class="[
                                'inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium border',
                                getMessageStatusBadge(selectedMessage.status).class,
                            ]"
                        >
                            {{ getMessageStatusBadge(selectedMessage.status).label }}
                        </span>
                    </div>
                    <DialogDescription class="text-muted-foreground text-xs">
                        Informasi lengkap pesan yang dikirimkan melalui formulir kontak portofolio.
                    </DialogDescription>
                </DialogHeader>

                <div v-if="selectedMessage" class="space-y-4 py-2 text-xs">
                    <!-- Sender Profile Strip -->
                    <div class="p-3.5 rounded-xl bg-muted/40 border border-border flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-primary/10 border border-primary/20 flex items-center justify-center text-primary font-bold text-sm shrink-0">
                            {{ selectedMessage.name.charAt(0).toUpperCase() }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="font-bold text-foreground text-sm truncate">{{ selectedMessage.name }}</p>
                            <p class="text-muted-foreground font-mono truncate">{{ selectedMessage.email }}</p>
                        </div>
                        <div class="text-right text-[11px] text-muted-foreground shrink-0">
                            <p>{{ formatDate(selectedMessage.created_at) }}</p>
                            <p v-if="selectedMessage.ip_address" class="font-mono text-[10px] opacity-70">
                                IP: {{ selectedMessage.ip_address }}
                            </p>
                        </div>
                    </div>

                    <!-- Subject -->
                    <div class="space-y-1">
                        <label class="text-[11px] font-semibold text-muted-foreground uppercase tracking-wider">Subjek</label>
                        <p class="p-3 rounded-lg bg-muted/30 border border-border font-semibold text-foreground text-xs">
                            {{ selectedMessage.subject }}
                        </p>
                    </div>

                    <!-- Message Body -->
                    <div class="space-y-1">
                        <label class="text-[11px] font-semibold text-muted-foreground uppercase tracking-wider">Isi Pesan</label>
                        <div class="p-4 rounded-lg bg-muted/30 border border-border text-foreground/90 whitespace-pre-wrap leading-relaxed max-h-60 overflow-y-auto">
                            {{ selectedMessage.message }}
                        </div>
                    </div>
                </div>

                <DialogFooter class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pt-4 border-t border-border">
                    <div class="flex items-center gap-1.5">
                        <Button
                            v-if="selectedMessage && selectedMessage.status !== 'archived'"
                            type="button"
                            size="sm"
                            variant="outline"
                            class="h-8 text-xs bg-card border-border"
                            @click="updateMessageStatus(selectedMessage, 'archived')"
                        >
                            <Archive class="w-3.5 h-3.5 mr-1 text-amber-500" />
                            Arsipkan
                        </Button>
                        <Button
                            v-if="selectedMessage && selectedMessage.status === 'archived'"
                            type="button"
                            size="sm"
                            variant="outline"
                            class="h-8 text-xs bg-card border-border"
                            @click="updateMessageStatus(selectedMessage, 'read')"
                        >
                            <RotateCcw class="w-3.5 h-3.5 mr-1" />
                            Batal Arsip
                        </Button>
                        <Button
                            v-if="selectedMessage"
                            type="button"
                            size="sm"
                            variant="ghost"
                            class="h-8 text-xs text-destructive hover:bg-destructive/10"
                            @click="confirmDeleteMessage(selectedMessage)"
                        >
                            <Trash2 class="w-3.5 h-3.5 mr-1" />
                            Hapus
                        </Button>
                    </div>

                    <div class="flex items-center gap-2">
                        <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            class="h-8 text-xs bg-card border-border"
                            @click="isMessageModalOpen = false"
                        >
                            Tutup
                        </Button>
                        <a
                            v-if="selectedMessage"
                            :href="'mailto:' + selectedMessage.email + '?subject=Re: ' + encodeURIComponent(selectedMessage.subject)"
                            class="inline-flex items-center justify-center h-8 px-3 rounded-lg text-xs font-semibold bg-primary hover:bg-primary/90 text-primary-foreground shadow transition-colors"
                            @click="updateMessageStatus(selectedMessage, 'replied')"
                        >
                            <Send class="w-3.5 h-3.5 mr-1.5" />
                            Balas via Email
                        </a>
                    </div>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- ============================================== -->
        <!-- DIALOG: DETAIL JADWAL KONSULTASI               -->
        <!-- ============================================== -->
        <Dialog :open="isConsultationModalOpen" @update:open="isConsultationModalOpen = $event">
            <DialogContent class="sm:max-w-xl bg-card border-border text-foreground">
                <DialogHeader>
                    <div class="flex items-center justify-between gap-4">
                        <DialogTitle class="text-lg font-bold text-foreground">Detail Jadwal Konsultasi</DialogTitle>
                        <span
                            v-if="selectedConsultation"
                            :class="[
                                'inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium border',
                                getConsultationStatusBadge(selectedConsultation.status).class,
                            ]"
                        >
                            {{ getConsultationStatusBadge(selectedConsultation.status).label }}
                        </span>
                    </div>
                    <DialogDescription class="text-muted-foreground text-xs">
                        Informasi lengkap reservasi discovery call yang diajukan oleh calon klien.
                    </DialogDescription>
                </DialogHeader>

                <div v-if="selectedConsultation" class="space-y-4 py-2 text-xs">
                    <!-- Client Profile Strip -->
                    <div class="p-3.5 rounded-xl bg-muted/40 border border-border flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-blue-500/10 border border-blue-500/20 text-blue-500 dark:text-blue-400 flex items-center justify-center font-bold text-sm shrink-0">
                            {{ selectedConsultation.full_name.charAt(0).toUpperCase() }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="font-bold text-foreground text-sm truncate">{{ selectedConsultation.full_name }}</p>
                            <p class="text-muted-foreground font-mono truncate">{{ selectedConsultation.email }}</p>
                        </div>
                        <div class="text-right text-[11px] text-muted-foreground shrink-0">
                            <p class="font-mono">{{ selectedConsultation.phone }}</p>
                            <p class="text-[10px]">Masuk: {{ formatDate(selectedConsultation.created_at) }}</p>
                        </div>
                    </div>

                    <!-- Details Grid -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="p-3 rounded-lg bg-muted/30 border border-border space-y-1">
                            <p class="text-[10px] uppercase font-semibold text-muted-foreground">Tipe Layanan</p>
                            <p class="font-bold text-foreground text-xs">{{ selectedConsultation.service_type }}</p>
                        </div>
                        <div class="p-3 rounded-lg bg-muted/30 border border-border space-y-1">
                            <p class="text-[10px] uppercase font-semibold text-muted-foreground">Jadwal yang Diminta</p>
                            <p class="font-bold text-foreground text-xs flex items-center gap-1.5">
                                <Calendar class="w-3.5 h-3.5 text-muted-foreground" />
                                {{ formatShortDate(selectedConsultation.preferred_date) }}
                            </p>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="space-y-1">
                        <label class="text-[11px] font-semibold text-muted-foreground uppercase tracking-wider">Catatan &amp; Detail Kebutuhan</label>
                        <div class="p-3 rounded-lg bg-muted/30 border border-border text-foreground/90 whitespace-pre-wrap leading-relaxed max-h-40 overflow-y-auto">
                            {{ selectedConsultation.notes || 'Tidak ada catatan tambahan yang dilampirkan.' }}
                        </div>
                    </div>

                    <!-- Quick Status Updater -->
                    <div class="space-y-1.5 pt-2">
                        <label class="text-[11px] font-semibold text-muted-foreground uppercase tracking-wider">Perbarui Status Reservasi</label>
                        <div class="grid grid-cols-4 gap-2">
                            <button
                                v-for="s in [
                                    { key: 'pending', label: 'Menunggu', class: 'hover:bg-amber-500/20 text-amber-600 dark:text-amber-400' },
                                    { key: 'confirmed', label: 'Konfirmasi', class: 'hover:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400' },
                                    { key: 'completed', label: 'Selesai', class: 'hover:bg-blue-500/20 text-blue-600 dark:text-blue-400' },
                                    { key: 'cancelled', label: 'Batal', class: 'hover:bg-rose-500/20 text-rose-600 dark:text-rose-400' },
                                ]"
                                :key="s.key"
                                type="button"
                                :class="[
                                    'p-2 rounded-lg text-xs font-semibold border transition-all text-center',
                                    selectedConsultation.status === s.key
                                        ? 'bg-muted border-foreground/30 text-foreground font-bold shadow-sm'
                                        : 'bg-card border-border ' + s.class
                                ]"
                                @click="updateConsultationStatus(selectedConsultation, s.key)"
                            >
                                {{ s.label }}
                            </button>
                        </div>
                    </div>
                </div>

                <DialogFooter class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pt-4 border-t border-border">
                    <Button
                        v-if="selectedConsultation"
                        type="button"
                        size="sm"
                        variant="ghost"
                        class="h-8 text-xs text-destructive hover:bg-destructive/10"
                        @click="confirmDeleteConsultation(selectedConsultation)"
                    >
                        <Trash2 class="w-3.5 h-3.5 mr-1" />
                        Hapus
                    </Button>

                    <div class="flex items-center gap-2">
                        <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            class="h-8 text-xs bg-card border-border"
                            @click="isConsultationModalOpen = false"
                        >
                            Tutup
                        </Button>
                        <a
                            v-if="selectedConsultation && selectedConsultation.phone"
                            :href="'https://wa.me/' + getCleanPhone(selectedConsultation.phone) + '?text=' + encodeURIComponent('Halo ' + selectedConsultation.full_name + ', saya mengonfirmasi jadwal konsultasi Anda...')"
                            target="_blank"
                            class="inline-flex items-center justify-center h-8 px-3 rounded-lg text-xs font-semibold bg-emerald-600 hover:bg-emerald-500 text-white shadow transition-colors"
                        >
                            <Phone class="w-3.5 h-3.5 mr-1.5" />
                            Hubungi via WhatsApp
                        </a>
                    </div>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- ============================================== -->
        <!-- DIALOG: KONFIRMASI HAPUS PESAN                 -->
        <!-- ============================================== -->
        <Dialog :open="isDeleteMessageModalOpen" @update:open="isDeleteMessageModalOpen = $event">
            <DialogContent class="sm:max-w-md bg-card border-border text-foreground">
                <DialogHeader>
                    <DialogTitle class="text-base font-bold text-foreground flex items-center gap-2">
                        <AlertCircle class="w-5 h-5 text-destructive shrink-0" />
                        <span>Hapus Pesan Kontak?</span>
                    </DialogTitle>
                    <DialogDescription class="text-muted-foreground text-xs pt-1">
                        Apakah Anda yakin ingin menghapus pesan dari <strong class="text-foreground">{{ messageToDelete?.name }}</strong>? Tindakan ini tidak dapat dibatalkan.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="flex items-center justify-end gap-2 pt-4 border-t border-border">
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        class="h-8 text-xs bg-card border-border"
                        @click="isDeleteMessageModalOpen = false"
                    >
                        Batal
                    </Button>
                    <Button
                        type="button"
                        variant="destructive"
                        size="sm"
                        class="h-8 text-xs"
                        @click="executeDeleteMessage"
                    >
                        Hapus Pesan
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- ============================================== -->
        <!-- DIALOG: KONFIRMASI HAPUS KONSULTASI            -->
        <!-- ============================================== -->
        <Dialog :open="isDeleteConsultationModalOpen" @update:open="isDeleteConsultationModalOpen = $event">
            <DialogContent class="sm:max-w-md bg-card border-border text-foreground">
                <DialogHeader>
                    <DialogTitle class="text-base font-bold text-foreground flex items-center gap-2">
                        <AlertCircle class="w-5 h-5 text-destructive shrink-0" />
                        <span>Hapus Jadwal Konsultasi?</span>
                    </DialogTitle>
                    <DialogDescription class="text-muted-foreground text-xs pt-1">
                        Apakah Anda yakin ingin menghapus reservasi konsultasi dari <strong class="text-foreground">{{ consultationToDelete?.full_name }}</strong>? Tindakan ini permanen.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="flex items-center justify-end gap-2 pt-4 border-t border-border">
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        class="h-8 text-xs bg-card border-border"
                        @click="isDeleteConsultationModalOpen = false"
                    >
                        Batal
                    </Button>
                    <Button
                        type="button"
                        variant="destructive"
                        size="sm"
                        class="h-8 text-xs"
                        @click="executeDeleteConsultation"
                    >
                        Hapus Jadwal
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
