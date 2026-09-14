<script setup lang="ts">
import LandingLayout from '../layouts/LandingLayout.vue';
import ContactSection from './ContactSection.vue';

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
        title="Contact & Inquiries"
    >
        <template #default="{ openAppointment }">
            <ContactSection
                :profile="profile"
                :flash="flash"
                @open-appointment="openAppointment"
            />
        </template>
    </LandingLayout>
</template>
