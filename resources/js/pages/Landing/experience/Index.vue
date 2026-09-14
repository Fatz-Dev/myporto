<script setup lang="ts">
import LandingLayout from '../layouts/LandingLayout.vue';
import ExperienceSection from './ExperienceSection.vue';

defineProps<{
    profile?: any;
    landingSections?: any[];
    experiences?: any[];
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
        title="Experience & Career"
    >
        <template #default>
            <ExperienceSection :experiences="experiences" />
        </template>
    </LandingLayout>
</template>
