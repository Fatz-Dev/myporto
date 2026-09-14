<script setup lang="ts">
import LandingLayout from './layouts/LandingLayout.vue';
import HeroSection from './hero/HeroSection.vue';

defineProps<{
    profile?: any;
    landingSections?: any[];
    flash?: {
        success?: string;
        error?: string;
    };
}>();
</script>

<template>
    <LandingLayout
        :profile="profile"
        :landing-sections="landingSections"
        :title="profile?.professional_title || 'Full Stack Software Engineer'"
    >
        <template #default="{ openAppointment }">
            <HeroSection
                :profile="profile"
                @open-appointment="openAppointment"
            />
        </template>
    </LandingLayout>
</template>
