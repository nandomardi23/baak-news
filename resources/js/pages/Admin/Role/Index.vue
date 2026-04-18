<script setup lang="ts">
import { useBreadcrumbs } from '@/composables/useBreadcrumbs';

defineOptions({ layout: AppLayout });
const { setBreadcrumbs } = useBreadcrumbs();
import AppLayout from '@/layouts/AppLayout.vue';

import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Pencil, Trash2, Plus, ShieldCheck, Upload, Users } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import Swal from 'sweetalert2';

interface Role {
    id: number;
    name: string;
    users_count: number;
}

const props = defineProps<{
    roles: Role[];
}>();

setBreadcrumbs([
    { title: 'Dashboard', href: '/admin' },
    { title: 'User Management', href: '/admin/user' },
    { title: 'Roles', href: '/admin/role' },
]);

import { useConfirmDelete } from '@/composables/useConfirmDelete';
const { confirmDelete } = useConfirmDelete();

const deleteRole = (role: Role) => {
    confirmDelete({
        url: `/admin/role/${role.id}`,
        entityName: 'Role',
        text: `Tindakan ini tidak dapat dibatalkan. Data role "${formatRoleName(role.name)}" akan dihapus permanen.`,
        isRestricted: role.users_count > 0,
        restrictedMessage: `Role "${formatRoleName(role.name)}" sedang digunakan oleh ${role.users_count} pengguna. Data tidak dapat dihapus karena berelasi dengan data pengguna lain.`
    });
};

const formatRoleName = (name: string) => {
    return name
        .split('_')
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
};
</script>

<template>
    <Head title="Manajemen Role" />

    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Manajemen Role</h1>
                <p class="text-muted-foreground">Kelola role dan hak akses fitur pengguna</p>
            </div>
            <div class="flex gap-2">
                <Link
                    href="/admin/role/import-neo"
                    method="post"
                    as="button"
                >
                    <Button variant="outline" class="border-indigo-200 text-indigo-700 hover:bg-indigo-50">
                        <Upload class="w-4 h-4 mr-2" />
                        Import Neo Roles
                    </Button>
                </Link>
                <Link href="/admin/role/create">
                    <Button class="bg-primary text-primary-foreground hover:bg-primary/90">
                        <Plus class="w-4 h-4 mr-2" />
                        Buat Role Baru
                    </Button>
                </Link>
            </div>
        </div>

        <!-- Table -->
        <div class="rounded-xl border bg-card shadow-sm">
            <Table>
                <TableHeader>
                    <TableRow class="bg-slate-50/50">
                        <TableHead class="w-12 text-center">#</TableHead>
                        <TableHead>Nama Role</TableHead>
                        <TableHead class="text-center">Jumlah Pengguna</TableHead>
                        <TableHead class="text-right">Aksi</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="(role, index) in roles" :key="role.id" class="hover:bg-slate-50/50">
                        <TableCell class="text-center text-muted-foreground font-medium">
                            {{ index + 1 }}
                        </TableCell>
                        <TableCell>
                            <div class="flex items-center gap-3">
                                <div class="p-2 rounded-lg" :class="role.name === 'admin' ? 'bg-purple-100' : 'bg-blue-100'">
                                    <ShieldCheck class="w-4 h-4" :class="role.name === 'admin' ? 'text-purple-600' : 'text-blue-600'" />
                                </div>
                                <span class="font-semibold text-slate-800">{{ formatRoleName(role.name) }}</span>
                            </div>
                        </TableCell>
                        <TableCell class="text-center">
                            <Badge variant="secondary" class="gap-1.5 font-medium">
                                <Users class="w-3 h-3" />
                                {{ role.users_count }} Pengguna
                            </Badge>
                        </TableCell>
                        <TableCell class="text-right">
                            <div class="flex items-center justify-end gap-1">
                                <Link :href="`/admin/role/${role.id}/edit`">
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="text-blue-600 hover:text-blue-700 hover:bg-blue-50 h-8 w-8"
                                        title="Edit"
                                    >
                                        <Pencil class="w-4 h-4" />
                                    </Button>
                                </Link>
                                <Button
                                    v-if="role.name !== 'admin'"
                                    variant="ghost"
                                    size="icon"
                                    @click="deleteRole(role)"
                                    class="text-red-600 hover:text-red-700 hover:bg-red-50 h-8 w-8"
                                    title="Hapus"
                                >
                                    <Trash2 class="w-4 h-4" />
                                </Button>
                            </div>
                        </TableCell>
                    </TableRow>
                    <TableRow v-if="roles.length === 0">
                        <TableCell :colspan="4" class="text-center py-12 text-muted-foreground">
                            Belum ada role yang terdaftar
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>
    </div>
</template>
