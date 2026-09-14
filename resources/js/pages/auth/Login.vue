<script setup lang="ts">
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head } from '@inertiajs/vue3';
import { LoaderCircle, Fingerprint, Mail, Chrome } from 'lucide-vue-next';
import { ref, onMounted, onBeforeUnmount } from 'vue';
import axios from 'axios';
import * as faceapi from 'face-api.js';

const step = ref(1);
const otp = ref('');
const token = ref('');
const isLoading = ref(false);
const errorMsg = ref('');
const faceMessage = ref('Loading models...');
const videoRef = ref<HTMLVideoElement | null>(null);
let videoStream: MediaStream | null = null;
let faceMatcher: faceapi.FaceMatcher | null = null;
let recognitionInterval: any = null;

const requestOtp = async () => {
    isLoading.value = true;
    errorMsg.value = '';
    try {
        await axios.post(route('login.otp.send'));
        step.value = 2;
    } catch (e: any) {
        errorMsg.value = e.response?.data?.message || 'Failed to send OTP.';
    } finally {
        isLoading.value = false;
    }
};

const verifyOtp = async () => {
    if (otp.value.length !== 6) {
        errorMsg.value = 'OTP must be 6 digits.';
        return;
    }
    isLoading.value = true;
    errorMsg.value = '';
    try {
        const res = await axios.post(route('login.otp.verify'), { otp: otp.value });
        if (res.data.face_required) {
            token.value = res.data.token;
            step.value = 3;
            startBiometric(res.data.descriptor);
        } else {
            window.location.href = res.data.redirect;
        }
    } catch (e: any) {
        errorMsg.value = e.response?.data?.message || 'Invalid OTP.';
    } finally {
        isLoading.value = false;
    }
};

const startBiometric = async (enrolledDescriptor?: number[] | null) => {
    faceMessage.value = 'Loading face recognition neural network...';
    try {
        await Promise.all([
            faceapi.nets.ssdMobilenetv1.loadFromUri('/models'),
            faceapi.nets.faceLandmark68Net.loadFromUri('/models'),
            faceapi.nets.faceRecognitionNet.loadFromUri('/models'),
        ]);

        if (enrolledDescriptor && Array.isArray(enrolledDescriptor) && enrolledDescriptor.length > 0) {
            faceMessage.value = 'Biometric signature verified from database.';
            faceMatcher = new faceapi.FaceMatcher(new Float32Array(enrolledDescriptor), 0.6);
        } else {
            faceMessage.value = 'Loading reference image fallback...';
            // Load admin reference image fallback
            const referenceImage = await faceapi.fetchImage('/admin-face.jpg');
            const detection = await faceapi.detectSingleFace(referenceImage).withFaceLandmarks().withFaceDescriptor();
            
            if (!detection) {
                faceMessage.value = 'Reference face profile not found. Please enroll face in Settings.';
                return;
            }
            faceMatcher = new faceapi.FaceMatcher(detection.descriptor, 0.6);
        }

        faceMessage.value = 'Starting camera... Please look directly at the lens.';
        
        videoStream = await navigator.mediaDevices.getUserMedia({ video: true });
        if (videoRef.value) {
            videoRef.value.srcObject = videoStream;
        }
        
    } catch (e) {
        faceMessage.value = 'Camera/Biometric error: ' + e;
    }
};

const onPlay = async () => {
    if (!videoRef.value || !faceMatcher) return;

    recognitionInterval = setInterval(async () => {
        if (!videoRef.value) return;
        const detection = await faceapi.detectSingleFace(videoRef.value).withFaceLandmarks().withFaceDescriptor();
        
        if (detection) {
            const match = faceMatcher!.findBestMatch(detection.descriptor);
            if (match.label !== 'unknown' && match.distance < 0.5) {
                // Matched!
                clearInterval(recognitionInterval);
                stopWebcam();
                faceMessage.value = 'Face recognized! Authenticating...';
                
                try {
                    const res = await axios.post(route('login.biometric'), {
                        token: token.value,
                        confidence: match.distance
                    });
                    window.location.href = res.data.redirect;
                } catch (e: any) {
                    faceMessage.value = 'Authentication failed: ' + (e.response?.data?.message || 'Error');
                }
            } else {
                faceMessage.value = 'Face detected, matching biometric key...';
            }
        } else {
            faceMessage.value = 'Scanning for face...';
        }
    }, 1000);
};

const stopWebcam = () => {
    if (videoStream) {
        videoStream.getTracks().forEach(track => track.stop());
    }
    if (recognitionInterval) {
        clearInterval(recognitionInterval);
    }
};

onBeforeUnmount(() => {
    stopWebcam();
});

const goToGoogle = () => {
    window.location.href = route('login.google');
};
</script>

<template>
    <AuthBase title="Admin Login" description="Secure access for the portfolio owner">
        <Head title="Admin Login" />

        <div v-if="errorMsg" class="mb-4 text-center text-sm font-medium text-red-600">
            {{ errorMsg }}
        </div>

        <!-- STEP 1: Request OTP -->
        <div v-if="step === 1" class="flex flex-col gap-6">
            <Button @click="requestOtp" class="w-full flex items-center justify-center gap-2" :disabled="isLoading">
                <LoaderCircle v-if="isLoading" class="h-4 w-4 animate-spin" />
                <Mail v-else class="h-4 w-4" />
                Send Login OTP to Admin Email
            </Button>

            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <span class="w-full border-t" />
                </div>
                <div class="relative flex justify-center text-xs uppercase">
                    <span class="bg-background px-2 text-muted-foreground">Or continue with</span>
                </div>
            </div>

            <Button @click="goToGoogle" variant="outline" class="w-full flex items-center justify-center gap-2" type="button">
                <Chrome class="h-4 w-4" />
                Google SSO Bypass
            </Button>
        </div>

        <!-- STEP 2: Verify OTP -->
        <div v-if="step === 2" class="flex flex-col gap-6">
            <div class="text-center text-sm mb-2 text-muted-foreground">
                We've sent a 6-digit code to your email.
            </div>
            <div class="grid gap-2 text-center">
                <Label for="otp">Enter OTP</Label>
                <Input
                    id="otp"
                    type="text"
                    required
                    autofocus
                    maxlength="6"
                    class="text-center text-2xl tracking-widest"
                    v-model="otp"
                    placeholder="------"
                    @keyup.enter="verifyOtp"
                />
            </div>

            <Button @click="verifyOtp" class="mt-4 w-full" :disabled="isLoading || otp.length !== 6">
                <LoaderCircle v-if="isLoading" class="h-4 w-4 animate-spin" />
                Verify OTP
            </Button>
            
            <Button variant="ghost" @click="step = 1" class="w-full">
                Cancel
            </Button>
        </div>

        <!-- STEP 3: Face Recognition -->
        <div v-if="step === 3" class="flex flex-col items-center gap-6">
            <div class="text-center text-sm mb-2 text-muted-foreground">
                <Fingerprint class="h-10 w-10 mx-auto mb-2 text-primary" />
                {{ faceMessage }}
            </div>
            
            <div class="relative w-full overflow-hidden rounded-lg border-2 border-primary bg-black aspect-video flex items-center justify-center">
                <video 
                    ref="videoRef" 
                    autoplay 
                    muted 
                    playsinline 
                    class="object-cover w-full h-full"
                    @play="onPlay"
                ></video>
            </div>
        </div>

    </AuthBase>
</template>