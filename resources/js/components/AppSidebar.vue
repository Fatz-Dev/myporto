<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { Link } from '@inertiajs/vue3';
import { Briefcase, Code2, EyeOff, GraduationCap, LayoutGrid, Paperclip, PhoneCall, ShieldCheck, User } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const mainNavItems = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        url: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Biodata',
        href: '/dashboard/biodata',
        url: '/dashboard/biodata',
        icon: User,
    },
    {
        title: 'Skill',
        href: '/dashboard/skills',
        url: '/dashboard/skills',
        icon: Code2,
    },
    {
        title: 'Education',
        href: '/dashboard/educations',
        url: '/dashboard/educations',
        icon: GraduationCap,
    },
    {
        title: "Project",
        href: "/dashboard/projects",
        url: "/dashboard/projects",
        icon: Briefcase,
    },
    {
        title: "Experience",
        href: "/dashboard/experiences",
        url: "/dashboard/experiences",
        icon: Briefcase,
    },
    {
        title: "Blog",
        href: "/dashboard/blogs",
        url: "/dashboard/blogs",
        icon: Paperclip,
    },
    {
        title: "Contact",
        href: "/dashboard/contacts",
        url: "/dashboard/contacts",
        icon: PhoneCall
    },
    {
        title: 'Security & Face ID',
        href: '/settings/security',
        url: '/settings/security',
        icon: ShieldCheck,
    },
    {
        title: 'Control Pages',
        href: '/dashboard/control-pages',
        url: '/dashboard/control-pages',
        icon: EyeOff,
    },
];

// const footerNavItems = [
//     {
//         title: 'Github Repo',
//         href: 'https://github.com/laravel/vue-starter-kit',
//         icon: Folder,
//     },
//     {
//         title: 'Documentation',
//         href: 'https://laravel.com/docs/starter-kits',
//         icon: BookOpen,
//     },
// ];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <!-- <NavFooter :items="footerNavItems" /> -->
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>