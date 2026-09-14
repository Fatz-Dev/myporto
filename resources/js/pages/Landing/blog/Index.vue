<script setup lang="ts">
import LandingLayout from '../layouts/LandingLayout.vue';
import BlogSection from './BlogSection.vue';

defineProps<{
    profile?: any;
    landingSections?: any[];
    blogs?: any[];
    categories?: string[];
    currentCategory?: string;
    search?: string;
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
        title="Blog & Engineering Insights"
    >
        <template #default>
            <BlogSection
                :blogs="blogs"
                :categories="categories"
                :current-category="currentCategory"
                :initial-search="search"
            />
        </template>
    </LandingLayout>
</template>