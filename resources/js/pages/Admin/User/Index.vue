<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import SmartTable from '@/components/ui/datatable/SmartTable.vue';
import { Pencil, Trash2, Plus } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

interface User {
    id: number;
    name: string;
    email: string;
    roles: string[];
    created_at: string;
}

const props = defineProps<{
    users: any;
    roles: string[];
    filters: Record<string, any>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin' },
    { title: 'User Management', href: '/admin/user' },
];

const columns = [
    { key: 'name', label: 'Nama User', sortable: true },
    { key: 'email', label: 'Email', sortable: true },
    { key: 'roles', label: 'Role', sortable: false },
    { key: 'created_at', label: 'Terdaftar', sortable: true },
    { key: 'aksi', label: 'Aksi', align: 'right' as const },
];

// Role Filter State
const selectedRole = ref(props.filters.role || 'all');

const updateFilter = () => {
    router.get('/admin/user', {
        role: selectedRole.value === 'all' ? undefined : selectedRole.value,
        search: props.filters.search, // Preserve search
    }, { preserveState: true, preserveScroll: true });
};

const deleteUser = (id: number) => {
    if (confirm('Hapus user ini?')) {
        router.delete(`/admin/user/${id}`);
    }
};

const getRoleBadgeClass = (role: string) => {
    const classes: Record<string, string> = {
        admin: 'bg-purple-100 text-purple-800 border-purple-200',
        staff_baak: 'bg-blue-100 text-blue-800 border-blue-200',
        mahasiswa: 'bg-emerald-100 text-emerald-800 border-emerald-200',
        dosen: 'bg-amber-100 text-amber-800 border-amber-200',
    };
    return classes[role] || 'bg-gray-100 text-gray-800 border-gray-200';
};
</script>

<template>
    <Head title="User Management" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">User Management</h1>
                    <p class="text-muted-foreground">Total: {{ users.total }} user terdaftar</p>
                </div>
            </div>

            <SmartTable
                :data="users"
                :columns="columns"
                :search="filters.search"
                :filters="{ role: filters.role }"
                :sort-field="filters.sort_field"
                :sort-direction="filters.sort_direction"
                title="Data User"
            >
                <!-- Actions Toolbar -->
                <template #actions>
                    <Link href="/admin/user/create">
                        <Button class="bg-primary text-primary-foreground hover:bg-primary/90">
                            <Plus class="w-4 h-4 mr-2" />
                            Tambah User
                        </Button>
                    </Link>
                </template>

                <!-- Filters Slot -->
                <template #filters>
                    <div class="w-full sm:w-48">
                        <Select v-model="selectedRole" @update:modelValue="updateFilter">
                            <SelectTrigger class="h-9 w-full">
                                <SelectValue placeholder="Pilih Role" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Semua Role</SelectItem>
                                <SelectItem v-for="role in roles" :key="role" :value="role">
                                    {{ role }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </template>

                <!-- Custom Cell: Roles -->
                <template #cell-roles="{ value }">
                    <div class="flex flex-wrap gap-1">
                        <span
                            v-for="role in value"
                            :key="role"
                            :class="getRoleBadgeClass(role)"
                            class="px-2 py-0.5 rounded-full text-xs font-bold border capitalize"
                        >
                            {{ role }}
                        </span>
                    </div>
                </template>

                <!-- Custom Cell: Aksi -->
                <template #cell-aksi="{ row }">
                     <div class="flex items-center justify-end gap-2">
                        <Link :href="`/admin/user/${row.id}/edit`">
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
                            variant="ghost"
                            size="icon"
                            @click="deleteUser(row.id)"
                            class="text-red-600 hover:text-red-700 hover:bg-red-50 h-8 w-8"
                            title="Hapus"
                        >
                            <Trash2 class="w-4 h-4" />
                        </Button>
                    </div>
                </template>
            </SmartTable>
        </div>
    </AppLayout>
</template>
