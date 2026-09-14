<script setup lang="ts">
import { computed } from 'vue';
import LandingLayout from '../layouts/LandingLayout.vue';
import AboutSection from './AboutSection.vue';

const props = defineProps<{
    profile?: any;
    landingSections?: any[];
    flash?: {
        success?: string;
        error?: string;
    };
}>();

const aboutSection = computed(() => {
    return props.landingSections?.find((s: any) => s.section_key === 'about');
});
</script>

<template>
    <LandingLayout
        :profile="profile"
        :landing-sections="landingSections"
        title="About"
    >
        <template #default>
            <AboutSection :profile="profile" :section="aboutSection" />
        </template>
    </LandingLayout>
</template>
