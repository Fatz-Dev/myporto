<script setup lang="ts">
import { computed } from 'vue';
import LandingLayout from '../layouts/LandingLayout.vue';
import SkillsSection from './SkillsSection.vue';

const props = defineProps<{
    profile?: any;
    landingSections?: any[];
    skillCategories?: any[];
    flash?: {
        success?: string;
        error?: string;
    };
}>();

const skillsSection = computed(() => {
    return props.landingSections?.find((s: any) => s.section_key === 'skills');
});
</script>

<template>
    <LandingLayout
        :profile="profile"
        :landing-sections="landingSections"
        title="Skills & Expertise"
    >
        <template #default>
            <SkillsSection
                :section="skillsSection"
                :skill-categories="skillCategories"
            />
        </template>
    </LandingLayout>
</template>
