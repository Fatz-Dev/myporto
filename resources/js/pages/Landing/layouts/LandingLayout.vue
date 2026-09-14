<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import LandingFooter from '../components/LandingFooter.vue';
import LandingSidebar from '../components/LandingSidebar.vue';
import AppointmentModal from '../contact/AppointmentModal.vue';

defineProps<{
    profile?: any;
    landingSections?: any[];
    title?: string;
    description?: string;
}>();

const isAppointmentModalOpen = ref(false);

const openModal = () => {
    isAppointmentModalOpen.value = true;
};

const closeModal = () => {
    isAppointmentModalOpen.value = false;
};
</script>

<template>
    <Head :title="title || (profile?.professional_title || 'Full Stack Software Engineer')">
        <meta name="description" :content="description || (profile?.hero_subheadline || 'Full Stack Engineer crafting robust, scalable digital experiences.')" />
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="" />
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300..800;1,300..800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    </Head>

    <div class="min-h-screen bg-[#0a0c10] text-[#e2e8f0] font-sans antialiased selection:bg-[#d97736] selection:text-[#0a0c10] flex flex-col justify-between">
        <!-- Sidebar Navigation (Desktop & Mobile) -->
        <LandingSidebar
            :profile="profile"
            :sections="landingSections"
            @open-appointment="openModal"
        />

        <!-- Main Content Area: Renders the active single page -->
        <main class="lg:pl-[268px] w-full min-w-0 flex-1">
            <slot :open-appointment="openModal" />
        </main>

        <!-- Global Landing Footer -->
        <div class="lg:pl-[268px] w-full">
            <LandingFooter :profile="profile" />
        </div>

        <!-- Discovery Call Booking Modal Dialog -->
        <AppointmentModal
            :is-open="isAppointmentModalOpen"
            @close="closeModal"
        />
    </div>
</template>
