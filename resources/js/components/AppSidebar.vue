<script setup lang="ts">
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
    {
        label: 'Pengaturan Sistem',
        items: [
            { title: 'Pengaturan Umum', href: '/admin/settings/general', icon: Settings },
            { title: 'Sync Neo Feeder', href: '/admin/settings/neofeeder', icon: RefreshCw },
        ],
    },
];
</script>

<template>
    <Sidebar collapsible="icon" class="border-r border-slate-100 bg-white">
        <SidebarHeader class="p-4 pb-2">
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child class="hover:bg-transparent data-[state=open]:bg-transparent hover:no-underline p-0">
                        <Link href="/admin" class="flex items-center gap-2">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent class="py-2">
            <NavGrouped :groups="navGroups" />
        </SidebarContent>

        <SidebarFooter class="p-4 border-t border-slate-50">
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
