<script setup lang="ts">
import { computed } from 'vue';
import LandingLayout from '../layouts/LandingLayout.vue';
import PortfolioSection from './PortfolioSection.vue';

const props = defineProps<{
    profile?: any;
    landingSections?: any[];
    projects?: any[];
    flash?: {
        success?: string;
        error?: string;
    };
}>();

const portfolioSection = computed(() => {
    return props.landingSections?.find((s: any) => s.section_key === 'portfolio');
});
</script>

<template>
    <LandingLayout
        :profile="profile"
        :landing-sections="landingSections"
        title="Selected Work & Projects"
    >
        <template #default>
            <PortfolioSection
                :section="portfolioSection"
                :projects="projects"
            />
        </template>
    </LandingLayout>
</template>
