<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref, onBeforeUnmount, nextTick } from 'vue';
import axios from 'axios';
import * as faceapi from 'face-api.js';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { type BreadcrumbItem } from '@/types';
import {
    Shield,
    ShieldCheck,
    ShieldAlert,
    ScanFace,
    Camera,
    CameraOff,
    CheckCircle2,
    AlertCircle,
    Trash2,
    Sparkles,
    KeyRound,
    Cpu,
    RefreshCw,
    LoaderCircle
} from 'lucide-vue-next';

interface Props {
    adminEmail: string;
    faceRecognitionEnabled: boolean;
    faceEnrolled: boolean;
    faceEnrolledAt?: string | null;
    status?: string | null;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Security & Face ID',
        href: '/settings/security',
    },
];

// Reactive States
const isToggling = ref(false);
const isCameraActive = ref(false);
const isModelsLoading = ref(false);
const isCapturing = ref(false);
const isDeleting = ref(false);

const cameraMessage = ref('Mempersiapkan modul biometrik...');
const faceDetected = ref(false);
const detectionConfidence = ref<number | null>(null);

const videoRef = ref<HTMLVideoElement | null>(null);
let videoStream: MediaStream | null = null;
let detectionInterval: any = null;
let latestDescriptor: Float32Array | null = null;

const toastMessage = ref<string | null>(props.status || null);
const toastType = ref<'success' | 'error' | 'info'>('success');

const showToast = (msg: string, type: 'success' | 'error' | 'info' = 'success') => {
    toastMessage.value = msg;
    toastType.value = type;
    setTimeout(() => {
        if (toastMessage.value === msg) {
            toastMessage.value = null;
        }
    }, 6000);
};

// 1. Toggle Face Recognition (On / Off)
const toggleFaceRecognition = () => {
    isToggling.value = true;
    const targetState = !props.faceRecognitionEnabled;

    router.post(
        route('settings.security.face-toggle'),
        { enabled: targetState },
        {
            preserveScroll: true,
            onSuccess: () => {
                showToast(
                    targetState
                        ? 'Face Recognition aktif. Verifikasi wajah diwajibkan saat login.'
                        : 'Face Recognition dinonaktifkan. Anda kini login hanya menggunakan OTP email.'
                );
            },
            onError: () => {
                showToast('Gagal mengubah pengaturan Face Recognition.', 'error');
            },
            onFinish: () => {
                isToggling.value = false;
            },
        }
    );
};

// 2. Start Biometric Camera Studio
const startCamera = async () => {
    isCameraActive.value = true;
    isModelsLoading.value = true;
    cameraMessage.value = 'Memuat model jaringan saraf AI (face-api.js)...';
    faceDetected.value = false;
    latestDescriptor = null;

    try {
        await Promise.all([
            faceapi.nets.ssdMobilenetv1.loadFromUri('/models'),
            faceapi.nets.faceLandmark68Net.loadFromUri('/models'),
            faceapi.nets.faceRecognitionNet.loadFromUri('/models'),
        ]);

        cameraMessage.value = 'Membuka kamera webcam...';
        videoStream = await navigator.mediaDevices.getUserMedia({
            video: {
                width: { ideal: 640 },
                height: { ideal: 480 },
                facingMode: 'user',
            },
        });

        await nextTick();
        if (videoRef.value) {
            videoRef.value.srcObject = videoStream;
        }
        setTimeout(() => {
            if (videoRef.value && videoStream && !videoRef.value.srcObject) {
                videoRef.value.srcObject = videoStream;
            }
        }, 150);
        isModelsLoading.value = false;
        cameraMessage.value = 'Posisikan wajah Anda tepat di tengah bingkai pemindai...';
    } catch (err: any) {
        isModelsLoading.value = false;
        stopCamera();
        showToast('Tidak dapat mengakses kamera: ' + (err.message || err), 'error');
    }
};

// 3. Continuous Face Analysis on Webcam Feed
const onVideoPlay = () => {
    if (!videoRef.value) return;

    if (detectionInterval) clearInterval(detectionInterval);

    detectionInterval = setInterval(async () => {
        if (!videoRef.value || videoRef.value.paused || videoRef.value.ended) return;

        try {
            const detection = await faceapi
                .detectSingleFace(videoRef.value)
                .withFaceLandmarks()
                .withFaceDescriptor();

            if (detection) {
                faceDetected.value = true;
                latestDescriptor = detection.descriptor;
                detectionConfidence.value = Math.round(detection.detection.score * 100);
                cameraMessage.value = 'Wajah terdeteksi optimal! Siap direkam.';
            } else {
                faceDetected.value = false;
                latestDescriptor = null;
                detectionConfidence.value = null;
                cameraMessage.value = 'Wajah belum terdeteksi. Harap menghadap lurus ke kamera...';
            }
        } catch (e) {
            // Ignore minor frame cycle drops
        }
    }, 400);
};

// 4. Capture & Enroll Face
const captureAndEnroll = async () => {
    if (!faceDetected.value || !latestDescriptor || !videoRef.value) {
        showToast('Wajah belum terdeteksi secara optimal. Mohon hadap ke kamera.', 'error');
        return;
    }

    isCapturing.value = true;

    try {
        const video = videoRef.value;
        const canvas = document.createElement('canvas');
        canvas.width = video.videoWidth || 640;
        canvas.height = video.videoHeight || 480;
        const ctx = canvas.getContext('2d');
        if (ctx) {
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        }
        const dataUrl = canvas.toDataURL('image/jpeg', 0.85);

        const descriptorArray = Array.from(latestDescriptor);

        const res = await axios.post(route('settings.security.face-enroll'), {
            descriptor: descriptorArray,
            image: dataUrl,
        });

        if (res.data.success) {
            stopCamera();
            showToast('Biometrik wajah berhasil didaftarkan dan disimpan ke sistem!');
            router.reload({ only: ['faceEnrolled', 'faceEnrolledAt', 'faceRecognitionEnabled'] });
        } else {
            showToast(res.data.message || 'Gagal menyimpan data biometrik.', 'error');
        }
    } catch (err: any) {
        showToast(err.response?.data?.message || 'Terjadi kesalahan saat pendaftaran biometrik.', 'error');
    } finally {
        isCapturing.value = false;
    }
};

// 5. Stop Webcam Stream
const stopCamera = () => {
    if (videoStream) {
        videoStream.getTracks().forEach((track) => track.stop());
        videoStream = null;
    }
    if (detectionInterval) {
        clearInterval(detectionInterval);
        detectionInterval = null;
    }
    if (videoRef.value) {
        videoRef.value.srcObject = null;
    }
    isCameraActive.value = false;
    isModelsLoading.value = false;
    faceDetected.value = false;
    latestDescriptor = null;
    detectionConfidence.value = null;
};

// 6. Delete Face Biometric
const deleteFaceBiometric = () => {
    if (!confirm('Apakah Anda yakin ingin menghapus data biometrik wajah referensi ini? Anda perlu mendaftarkan wajah kembali jika Face Recognition diaktifkan.')) {
        return;
    }

    isDeleting.value = true;
    router.delete(route('settings.security.face-delete'), {
        preserveScroll: true,
        onSuccess: () => {
            showToast('Data biometrik wajah berhasil dihapus dari sistem.');
        },
        onError: () => {
            showToast('Gagal menghapus data biometrik.', 'error');
        },
        onFinish: () => {
            isDeleting.value = false;
        },
    });
};

onBeforeUnmount(() => {
    stopCamera();
});
</script>

<template>
    <Head title="Keamanan & Face ID" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6 max-w-5xl mx-auto w-full">
            <!-- Header Banner -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-sidebar-border/70 dark:border-sidebar-border">
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-primary/10 text-primary border border-primary/20 shadow-sm">
                        <ShieldCheck class="h-6 w-6" />
                    </div>
                    <div>
                        <h1 class="text-xl md:text-2xl font-bold tracking-tight text-foreground">
                            Keamanan & Biometrik Wajah
                        </h1>
                        <p class="text-sm text-muted-foreground">
                            Kelola verifikasi 2-Faktor biometrik, preferensi login admin, dan pendaftaran wajah.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <span
                        :class="[
                            'inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold border',
                            faceRecognitionEnabled && faceEnrolled
                                ? 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border-emerald-500/20'
                                : faceRecognitionEnabled && !faceEnrolled
                                ? 'bg-amber-500/15 text-amber-600 dark:text-amber-400 border-amber-500/20'
                                : 'bg-muted text-muted-foreground border-sidebar-border'
                        ]"
                    >
                        <span
                            :class="[
                                'h-2 w-2 rounded-full',
                                faceRecognitionEnabled && faceEnrolled ? 'bg-emerald-500 animate-pulse' : 'bg-muted-foreground'
                            ]"
                        />
                        {{
                            faceRecognitionEnabled && faceEnrolled
                                ? 'Proteksi 2FA Aktif'
                                : faceRecognitionEnabled && !faceEnrolled
                                ? 'Face ID Belum Siap'
                                : 'Proteksi 1FA OTP'
                        }}
                    </span>
                </div>
            </div>

            <!-- Toast Notification Banner -->
            <div
                v-if="toastMessage"
                :class="[
                    'flex items-center gap-3 rounded-xl border p-4 text-sm transition-all duration-300 shadow-sm',
                    toastType === 'success'
                        ? 'border-emerald-500/30 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400'
                        : toastType === 'error'
                        ? 'border-rose-500/30 bg-rose-500/10 text-rose-600 dark:text-rose-400'
                        : 'border-blue-500/30 bg-blue-500/10 text-blue-600 dark:text-blue-400'
                ]"
            >
                <CheckCircle2 v-if="toastType === 'success'" class="h-5 w-5 shrink-0" />
                <AlertCircle v-else class="h-5 w-5 shrink-0" />
                <span class="flex-1 font-medium">{{ toastMessage }}</span>
                <button
                    @click="toastMessage = null"
                    class="text-xs opacity-70 hover:opacity-100 uppercase tracking-wider font-semibold"
                >
                    Tutup
                </button>
            </div>

            <!-- Section 1: Administrator Identity & Current Authentication Flow -->
            <Card class="border-sidebar-border/70 dark:border-sidebar-border overflow-hidden shadow-sm">
                <CardHeader class="pb-3">
                    <div class="flex items-center justify-between">
                        <div class="space-y-1">
                            <CardTitle class="text-base font-semibold flex items-center gap-2">
                                <KeyRound class="h-4 w-4 text-primary" />
                                Identitas Akun Pemilik Portofolio
                            </CardTitle>
                            <CardDescription>
                                Sistem portofolio ini menggunakan arsitektur Single-Admin yang diproteksi secara terenkripsi.
                            </CardDescription>
                        </div>
                        <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium bg-primary/10 text-primary border border-primary/20">
                            <span class="h-1.5 w-1.5 rounded-full bg-primary animate-pulse"></span>
                            Master Admin
                        </span>
                    </div>
                </CardHeader>
                <CardContent class="space-y-4 pt-1">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="rounded-xl border bg-muted/40 p-4 space-y-1">
                            <div class="text-xs text-muted-foreground font-medium">Email Terdaftar (Single-Admin)</div>
                            <div class="text-sm font-semibold font-mono tracking-tight text-foreground truncate">
                                {{ adminEmail || 'fatazikrillah007@gmail.com' }}
                            </div>
                            <div class="text-[11px] text-muted-foreground">
                                Semua token OTP login akan dialamatkan secara eksklusif ke email ini.
                            </div>
                        </div>

                        <div class="rounded-xl border bg-muted/40 p-4 space-y-1">
                            <div class="text-xs text-muted-foreground font-medium">Status Proteksi Login Saat Ini</div>
                            <div class="flex items-center gap-2">
                                <span
                                    :class="[
                                        'inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-xs font-semibold',
                                        faceRecognitionEnabled && faceEnrolled
                                            ? 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20'
                                            : faceRecognitionEnabled && !faceEnrolled
                                            ? 'bg-amber-500/15 text-amber-600 dark:text-amber-400 border border-amber-500/20'
                                            : 'bg-muted text-muted-foreground border'
                                    ]"
                                >
                                    {{
                                        faceRecognitionEnabled && faceEnrolled
                                            ? '2FA: OTP Email + AI Biometrik Wajah'
                                            : faceRecognitionEnabled && !faceEnrolled
                                            ? 'Face ID Aktif (Belum Mendaftar Wajah)'
                                            : '1FA: OTP Email Saja'
                                    }}
                                </span>
                            </div>
                            <div class="text-[11px] text-muted-foreground">
                                {{
                                    faceRecognitionEnabled
                                        ? 'Verifikasi biometrik wajib lolos setelah memasukkan OTP.'
                                        : 'Login instan setelah verifikasi kode OTP 6-digit berhasil.'
                                }}
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Section 2: Face Recognition Enforce Switch -->
            <Card class="border-sidebar-border/70 dark:border-sidebar-border shadow-sm">
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div class="space-y-1">
                            <CardTitle class="text-base font-semibold flex items-center gap-2">
                                <ScanFace class="h-4 w-4 text-primary" />
                                Autentikasi Biometrik Wajah (Face Recognition)
                            </CardTitle>
                            <CardDescription>
                                Aktifkan atau nonaktifkan langkah pemindaian wajah saat proses autentikasi masuk admin.
                            </CardDescription>
                        </div>

                        <!-- Custom Toggle Switch -->
                        <button
                            type="button"
                            @click="toggleFaceRecognition"
                            :disabled="isToggling"
                            :class="[
                                'relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2',
                                faceRecognitionEnabled ? 'bg-primary' : 'bg-muted-foreground/30',
                                isToggling ? 'opacity-60 cursor-not-allowed' : ''
                            ]"
                        >
                            <span
                                :class="[
                                    'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-lg ring-0 transition duration-200 ease-in-out',
                                    faceRecognitionEnabled ? 'translate-x-5' : 'translate-x-0'
                                ]"
                            />
                        </button>
                    </div>
                </CardHeader>
                <CardContent class="space-y-3">
                    <div class="text-sm text-muted-foreground">
                        Ketika fitur ini diaktifkan, setelah memasukkan 6-digit OTP email, webcam akan meminta Anda menghadap ke kamera untuk mencocokkan wajah dengan deskriptor referensi terdaftar.
                    </div>

                    <div
                        v-if="faceRecognitionEnabled && !faceEnrolled"
                        class="flex items-start gap-2.5 rounded-lg border border-amber-500/30 bg-amber-500/10 p-3 text-xs text-amber-700 dark:text-amber-400"
                    >
                        <AlertCircle class="h-4 w-4 shrink-0 mt-0.5" />
                        <div>
                            <span class="font-semibold">Peringatan:</span> Anda telah mengaktifkan Face Recognition namun belum mendaftarkan biometrik wajah referensi. Silakan lakukan enrollment pada panel di bawah ini agar login tidak terhambat.
                        </div>
                    </div>

                    <div
                        v-else-if="faceRecognitionEnabled && faceEnrolled"
                        class="flex items-center gap-2 text-xs text-emerald-600 dark:text-emerald-400 font-medium"
                    >
                        <CheckCircle2 class="h-4 w-4 shrink-0" />
                        Face Recognition aktif & biometrik referensi valid. Akun terlindungi dengan standar 2-Faktor.
                    </div>
                </CardContent>
            </Card>

            <!-- Section 3: Biometric Enrollment Studio -->
            <Card class="border-sidebar-border/70 dark:border-sidebar-border overflow-hidden shadow-sm">
                <CardHeader class="pb-3">
                    <div class="flex items-center justify-between">
                        <div class="space-y-1">
                            <CardTitle class="text-base font-semibold flex items-center gap-2">
                                <Camera class="h-4 w-4 text-primary" />
                                Studio Pendaftaran Biometrik Wajah
                            </CardTitle>
                            <CardDescription>
                                Daftarkan fitur wajah Anda secara presisi untuk dijadikan kunci otentikasi biometrik.
                            </CardDescription>
                        </div>

                        <span
                            :class="[
                                'inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium',
                                faceEnrolled
                                    ? 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20'
                                    : 'bg-muted text-muted-foreground border'
                            ]"
                        >
                            <span
                                :class="[
                                    'h-1.5 w-1.5 rounded-full',
                                    faceEnrolled ? 'bg-emerald-500' : 'bg-muted-foreground'
                                ]"
                            />
                            {{ faceEnrolled ? 'Wajah Terdaftar' : 'Belum Ada Biometrik' }}
                        </span>
                    </div>
                </CardHeader>

                <CardContent class="space-y-6">
                    <!-- Current Enrolled State Card -->
                    <div
                        v-if="!isCameraActive"
                        class="flex flex-col sm:flex-row items-center justify-between gap-4 rounded-xl border bg-card/60 p-4"
                    >
                        <div class="flex items-center gap-4">
                            <div class="relative flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-primary/10 border border-primary/20 text-primary shadow-sm">
                                <ScanFace class="h-7 w-7" />
                                <div
                                    v-if="faceEnrolled"
                                    class="absolute -bottom-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-emerald-500 text-white shadow-sm"
                                >
                                    <CheckCircle2 class="h-3.5 w-3.5" />
                                </div>
                            </div>

                            <div class="space-y-1 text-center sm:text-left">
                                <div class="text-sm font-semibold text-foreground">
                                    {{ faceEnrolled ? 'Data Biometrik Tersimpan di Database' : 'Biometrik Belum Didaftarkan' }}
                                </div>
                                <div class="text-xs text-muted-foreground">
                                    {{
                                        faceEnrolled
                                            ? `Terakhir diperbarui: ${faceEnrolledAt || 'Tersedia'}. Tersimpan sebagai 128-float embedding vector.`
                                            : 'Daftarkan wajah Anda menggunakan webcam sekarang untuk mengaktifkan login biometrik.'
                                    }}
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                            <Button
                                @click="startCamera"
                                class="gap-2 shadow-sm font-medium"
                            >
                                <Camera class="h-4 w-4" />
                                {{ faceEnrolled ? 'Daftarkan Ulang Wajah' : 'Mulai Pendaftaran Wajah' }}
                            </Button>

                            <Button
                                v-if="faceEnrolled"
                                variant="outline"
                                @click="deleteFaceBiometric"
                                :disabled="isDeleting"
                                class="text-rose-600 hover:text-rose-700 hover:bg-rose-50 dark:hover:bg-rose-950/30"
                                title="Hapus biometrik wajah"
                            >
                                <LoaderCircle v-if="isDeleting" class="h-4 w-4 animate-spin" />
                                <Trash2 v-else class="h-4 w-4" />
                            </Button>
                        </div>
                    </div>

                    <!-- Camera Studio Dialog Modal -->
                    <Dialog :open="isCameraActive" @update:open="(val: boolean) => { if (!val) stopCamera(); }">
                        <DialogContent class="sm:max-w-xl bg-zinc-950 border-zinc-800 text-white p-6 shadow-2xl">
                            <DialogHeader class="pb-2">
                                <div class="flex items-center justify-between text-xs pb-1 text-zinc-300">
                                    <div class="flex items-center gap-2">
                                        <span class="relative flex h-2.5 w-2.5">
                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-red-500"></span>
                                        </span>
                                        <span class="font-semibold tracking-wide text-white">STUDIO PENDAFTARAN BIOMETRIK WAJAH</span>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <span
                                            :class="[
                                                'px-2.5 py-0.5 rounded-full text-[11px] font-mono font-medium border',
                                                faceDetected
                                                    ? 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30'
                                                    : 'bg-amber-500/20 text-amber-300 border-amber-500/30'
                                            ]"
                                        >
                                            {{ faceDetected ? `Optimal (${detectionConfidence}%)` : 'Mencari Wajah...' }}
                                        </span>
                                    </div>
                                </div>
                                <DialogTitle class="text-base font-bold text-white">
                                    Studio Pendaftaran Biometrik Wajah
                                </DialogTitle>
                                <DialogDescription class="text-xs text-zinc-400">
                                    Posisikan wajah Anda tepat di dalam lingkaran pemindaian. Pastikan pencahayaan cukup dan wajah terlihat jelas.
                                </DialogDescription>
                            </DialogHeader>

                            <!-- Viewport -->
                            <div class="relative w-full aspect-video max-h-[360px] mx-auto rounded-xl overflow-hidden bg-zinc-950 flex items-center justify-center border border-zinc-800 shadow-inner my-2">
                                <!-- Loading State -->
                                <div v-if="isModelsLoading" class="flex flex-col items-center gap-2 text-zinc-400">
                                    <LoaderCircle class="h-8 w-8 animate-spin text-emerald-400" />
                                    <span class="text-xs">{{ cameraMessage }}</span>
                                </div>

                                <!-- Video Stream -->
                                <video
                                    ref="videoRef"
                                    autoplay
                                    muted
                                    playsinline
                                    class="w-full h-full object-cover"
                                    @play="onVideoPlay"
                                ></video>

                                <!-- Interactive Face Scanning Reticle Overlay -->
                                <div
                                    v-if="!isModelsLoading"
                                    class="pointer-events-none absolute inset-0 flex items-center justify-center"
                                >
                                    <div
                                        :class="[
                                            'relative w-48 h-48 sm:w-56 sm:h-56 rounded-full border-2 transition-all duration-300 flex items-center justify-center',
                                            faceDetected
                                                ? 'border-emerald-400 shadow-[0_0_25px_rgba(52,211,153,0.4)]'
                                                : 'border-amber-400/80 border-dashed shadow-[0_0_15px_rgba(251,191,36,0.2)]'
                                        ]"
                                    >
                                        <!-- Corner target markers -->
                                        <div class="absolute -top-2 -left-2 w-4 h-4 border-t-2 border-l-2 border-emerald-400"></div>
                                        <div class="absolute -top-2 -right-2 w-4 h-4 border-t-2 border-r-2 border-emerald-400"></div>
                                        <div class="absolute -bottom-2 -left-2 w-4 h-4 border-b-2 border-l-2 border-emerald-400"></div>
                                        <div class="absolute -bottom-2 -right-2 w-4 h-4 border-b-2 border-r-2 border-emerald-400"></div>

                                        <div class="text-[11px] text-zinc-300 font-mono tracking-wider bg-black/70 px-2.5 py-0.5 rounded-full border border-white/10 backdrop-blur-sm">
                                            {{ faceDetected ? 'FACE LOCKED' : 'ALIGN FACE' }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Live Feedback & Action Bar -->
                            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-2">
                                <div class="text-xs text-zinc-300 text-center sm:text-left">
                                    <span class="font-semibold text-white">Status:</span> {{ cameraMessage }}
                                </div>

                                <div class="flex items-center gap-2 w-full sm:w-auto justify-center sm:justify-end">
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        @click="stopCamera"
                                        class="text-zinc-400 hover:text-white hover:bg-zinc-800"
                                    >
                                        <CameraOff class="h-4 w-4 mr-1.5" />
                                        Batal
                                    </Button>

                                    <Button
                                        size="sm"
                                        @click="captureAndEnroll"
                                        :disabled="!faceDetected || isCapturing"
                                        class="bg-emerald-600 hover:bg-emerald-500 text-white font-medium shadow-sm transition-all active:scale-95"
                                    >
                                        <LoaderCircle v-if="isCapturing" class="h-4 w-4 animate-spin mr-1.5" />
                                        <Sparkles v-else class="h-4 w-4 mr-1.5" />
                                        {{ isCapturing ? 'Menyimpan...' : 'Ambil & Daftarkan Wajah' }}
                                    </Button>
                                </div>
                            </div>
                        </DialogContent>
                    </Dialog>
                </CardContent>
            </Card>

            <!-- Section 4: Security Specifications & Architecture Details -->
            <Card class="border-sidebar-border/70 dark:border-sidebar-border bg-muted/20 shadow-sm">
                <CardHeader class="pb-2">
                    <CardTitle class="text-sm font-semibold flex items-center gap-2 text-muted-foreground">
                        <Cpu class="h-4 w-4" />
                        Spesifikasi Enkripsi & Arsitektur Biometrik
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs text-muted-foreground">
                        <div class="space-y-1">
                            <div class="font-medium text-foreground">AI Neural Network</div>
                            <div>SSD MobileNet V1 + ResNet-34 Face Recognition, diproses secara client-side di browser.</div>
                        </div>
                        <div class="space-y-1">
                            <div class="font-medium text-foreground">Format Penyimpanan</div>
                            <div>128-dimensional Float embedding vector yang disimpan di database `app_settings`.</div>
                        </div>
                        <div class="space-y-1">
                            <div class="font-medium text-foreground">Toleransi Euclidean</div>
                            <div>Ambang batas kecocokan ketat (distance &lt; 0.50) untuk mencegah spoofing gambar/foto.</div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
