<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavGrouped from '@/components/NavGrouped.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import {
    LayoutGrid,
    Users,
    FileText,
    UserCog,
    Shield,
    Settings,
    RefreshCw,
    BookOpen,
    Calendar,
    School,
    GraduationCap,
    Palette,
    ClipboardList,
    Briefcase,
    History,
    UserPlus,
    Cog,
    IdCard,
} from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const navGroups = [
    {
        label: 'Menu Utama',
        items: [
            { title: 'Dashboard', href: '/admin', icon: LayoutGrid },
        ],
    },
    {
        label: 'Akademik',
        items: [
            { title: 'Mahasiswa', href: '/admin/mahasiswa', icon: Users },
            { title: 'Dosen', href: '/admin/dosen', icon: GraduationCap },
            { title: 'Mata Kuliah', href: '/admin/akademik/matakuliah', icon: BookOpen },
            { title: 'Semester', href: '/admin/akademik/semester', icon: Calendar },
            { title: 'Program Studi', href: '/admin/akademik/prodi', icon: School },
        ],
    },
    {
        label: 'Surat & Dokumen',
        items: [
            { title: 'Pengajuan Surat', href: '/admin/surat', icon: FileText },
            { title: 'Batch Kartu Ujian', href: '/admin/mahasiswa/kartu-ujian/batch', icon: IdCard },
            { title: 'Template Designer', href: '/admin/templates', icon: Palette },
        ],
    },
    {
        label: 'Administrasi',
        items: [
            { title: 'Pejabat', href: '/admin/pejabat', icon: Briefcase },
            { title: 'Master Jabatan', href: '/admin/jabatan', icon: ClipboardList },
            { title: 'Manajemen Role', href: '/admin/role', icon: Shield },
            { title: 'User Management', href: '/admin/user', icon: UserPlus },
            { title: 'Log Aktivitas', href: '/admin/logs', icon: History },
        ],
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Sync Neo Feeder',
        href: '/admin/settings/neofeeder',
        icon: RefreshCw,
    },
    {
        title: 'Pengaturan',
        href: '/settings/profile',
        icon: Settings,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link href="/admin">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavGrouped :groups="navGroups" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
