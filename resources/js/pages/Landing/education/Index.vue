<script setup lang="ts">
import { computed } from 'vue';
import LandingLayout from '../layouts/LandingLayout.vue';
import EducationSection from './EducationSection.vue';

const props = defineProps<{
    profile?: any;
    landingSections?: any[];
    educations?: any[];
    flash?: {
        success?: string;
        error?: string;
    };
}>();

const educationSection = computed(() => {
    return props.landingSections?.find((s: any) => s.section_key === 'education');
});
</script>

<template>
    <LandingLayout
        :profile="profile"
        :landing-sections="landingSections"
        title="Education & Background"
    >
        <template #default>
            <EducationSection
                :section="educationSection"
                :educations="educations"
            />
        </template>
    </LandingLayout>
</template>
