<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

interface User {
    id: number;
    name: string;
    email: string;
    roles: string[];
    created_at: string;
}

interface Pagination {
    data: User[];
    links: any[];
    current_page: number;
    last_page: number;
    total: number;
}

const props = defineProps<{
    users: Pagination;
    roles: string[];
    filters: {
        role?: string;
        search?: string;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin' },
    { title: 'User Management', href: '/admin/user' },
];

const search = ref(props.filters.search || '');
const selectedRole = ref(props.filters.role || '');

const applyFilters = () => {
    router.get('/admin/user', {
        search: search.value || undefined,
        role: selectedRole.value || undefined,
    }, { preserveState: true });
};

const deleteUser = (id: number) => {
    if (confirm('Hapus user ini?')) {
        router.delete(`/admin/user/${id}`);
    }
};

const getRoleBadgeClass = (role: string) => {
    const classes: Record<string, string> = {
        admin: 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400',
        staff_baak: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
    };
    return classes[role] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Head title="User Management" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">User Management</h1>
                    <p class="text-muted-foreground">Total: {{ users.total }} user</p>
                </div>
                <Link
                    href="/admin/user/create"
                    class="px-4 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition"
                >
                    + Tambah User
                </Link>
            </div>

            <!-- Filters -->
            <div class="flex flex-wrap gap-4 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium mb-1">Cari</label>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Nama atau email..."
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                        @keyup.enter="applyFilters"
                    />
                </div>
                <div class="w-40">
                    <label class="block text-sm font-medium mb-1">Role</label>
                    <select
                        v-model="selectedRole"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                    >
                        <option value="">Semua</option>
                        <option v-for="role in roles" :key="role" :value="role">
                            {{ role }}
                        </option>
                    </select>
                </div>
                <button
                    @click="applyFilters"
                    class="px-4 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90"
                >
                    Filter
                </button>
            </div>

            <!-- Table -->
            <div class="rounded-xl border bg-card shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-muted/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-medium text-muted-foreground">Nama</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-muted-foreground">Email</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-muted-foreground">Role</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-muted-foreground">Dibuat</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-muted-foreground">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="user in users.data" :key="user.id" class="hover:bg-muted/50">
                                <td class="px-6 py-4 font-medium">{{ user.name }}</td>
                                <td class="px-6 py-4 text-muted-foreground">{{ user.email }}</td>
                                <td class="px-6 py-4">
                                    <span
                                        v-for="role in user.roles"
                                        :key="role"
                                        :class="getRoleBadgeClass(role)"
                                        class="px-2 py-1 rounded-full text-xs font-medium mr-1"
                                    >
                                        {{ role }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-muted-foreground">{{ user.created_at }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex gap-2">
                                        <Link
                                            :href="`/admin/user/${user.id}/edit`"
                                            class="text-primary hover:underline text-sm"
                                        >
                                            Edit
                                        </Link>
                                        <button
                                            @click="deleteUser(user.id)"
                                            class="text-red-600 hover:underline text-sm"
                                        >
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="users.data.length === 0">
                                <td colspan="5" class="px-6 py-12 text-center text-muted-foreground">
                                    Tidak ada user
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="users.last_page > 1" class="px-6 py-4 border-t flex items-center justify-between">
                    <p class="text-sm text-muted-foreground">
                        Halaman {{ users.current_page }} dari {{ users.last_page }}
                    </p>
                    <div class="flex gap-2">
                        <Link
                            v-for="link in users.links"
                            :key="link.label"
                            :href="link.url || '#'"
                            :class="[
                                'px-3 py-1 rounded text-sm',
                                link.active ? 'bg-primary text-primary-foreground' : 'border hover:bg-muted',
                                !link.url ? 'opacity-50 cursor-not-allowed' : ''
                            ]"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
