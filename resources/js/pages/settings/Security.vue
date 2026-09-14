<script setup lang="ts">
import SettingsView from '@/pages/Dashboard/setting/Settings.vue';

defineProps<{
    adminEmail: string;
    faceRecognitionEnabled: boolean;
    faceEnrolled: boolean;
    faceEnrolledAt?: string | null;
    status?: string | null;
}>();
</script>

<template>
    <SettingsView v-bind="$props" />
</template>