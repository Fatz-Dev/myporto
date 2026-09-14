<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';

interface Props {
    class?: string;
}

defineProps<Props>();
</script>

<template>
    <div class="flex aspect-square size-8 items-center justify-center rounded-md overflow-hidden text-sidebar-primary-foreground">
        <img src="/images/logo.jpg" alt="Logo" class="size-6 rounded object-cover" />
        <!-- <AppLogoIcon class="size-5 fill-current text-white dark:text-black" /> -->
    </div>
    <div class="ml-1 grid flex-1 text-left text-sm">
        <span class="mb-0.5 truncate font-semibold leading-none">Dashboard</span>
    </div>
</template>
