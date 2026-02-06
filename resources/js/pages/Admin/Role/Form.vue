<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, Link } from '@inertiajs/vue3';

interface Permission {
    id: number;
    name: string;
    label: string;
}

interface Role {
    id: number;
    name: string;
}

const props = defineProps<{
    role?: Role;
    permissions: Record<string, Permission[]>;
    currentPermissions?: string[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin' },
    { title: 'User Management', href: '/admin/user' },
    { title: 'Roles', href: '/admin/role' },
    { title: props.role ? 'Edit Role' : 'Buat Role', href: '#' },
];

const form = useForm({
    name: props.role?.name || '',
    permissions: props.currentPermissions || [],
});

const submit = () => {
    if (props.role) {
        form.put(`/admin/role/${props.role.id}`);
    } else {
        form.post('/admin/role');
    }
};

// Check all in group helper
const toggleGroup = (groupName: string, checked: boolean) => {
    const groupPerms = props.permissions[groupName].map(p => p.name);
    if (checked) {
        // Add all distinct
        form.permissions = [...new Set([...form.permissions, ...groupPerms])];
    } else {
        // Remove all
        form.permissions = form.permissions.filter(p => !groupPerms.includes(p));
    }
};

const isGroupChecked = (groupName: string) => {
    const groupPerms = props.permissions[groupName].map(p => p.name);
    return groupPerms.every(p => form.permissions.includes(p));
};
</script>

<template>
    <Head :title="role ? 'Edit Role' : 'Buat Role'" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="max-w-4xl mx-auto w-full">
                <div class="bg-card border rounded-xl shadow-sm overflow-hidden">
                    <div class="p-6 border-b">
                        <h1 class="text-xl font-bold">{{ role ? 'Edit Role' : 'Buat Role Baru' }}</h1>
                        <p class="text-muted-foreground">Tentukan nama role dan fitur yang dapat diakses</p>
                    </div>
                    
                    <form @submit.prevent="submit" class="p-6 space-y-8">
                        <div>
                            <label class="block text-sm font-medium mb-1">Nama Role <span class="text-red-500">*</span></label>
                            <input
                                v-model="form.name"
                                type="text"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                                placeholder="Contoh: Staff Keuangan"
                                required
                            />
                            <p v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</p>
                        </div>

                        <div class="space-y-6">
                            <h3 class="text-lg font-medium border-b pb-2">Izin Akses (Permissions)</h3>
                            
                            <div v-for="(groupPerms, groupName) in permissions" :key="groupName" class="space-y-3">
                                <div class="flex items-center gap-2">
                                    <input 
                                        type="checkbox" 
                                        :checked="isGroupChecked(groupName)"
                                        @change="(e) => toggleGroup(groupName, (e.target as HTMLInputElement).checked)"
                                        class="w-4 h-4 rounded text-primary focus:ring-primary"
                                    >
                                    <h4 class="font-bold text-base">{{ groupName }}</h4>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 pl-6">
                                    <label 
                                        v-for="perm in groupPerms" 
                                        :key="perm.id"
                                        class="flex items-center gap-2 p-2 border rounded-lg hover:bg-muted/50 cursor-pointer transition"
                                        :class="{'border-primary bg-primary/5': form.permissions.includes(perm.name)}"
                                    >
                                        <input
                                            type="checkbox"
                                            v-model="form.permissions"
                                            :value="perm.name"
                                            class="w-4 h-4 rounded text-primary focus:ring-primary"
                                        />
                                        <span class="text-sm font-medium">{{ perm.label }}</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-4 border-t">
                            <Link href="/admin/role" class="px-4 py-2 border rounded-lg hover:bg-muted">
                                Batal
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-6 py-2 bg-primary text-primary-foreground font-medium rounded-lg hover:bg-primary/90 transition disabled:opacity-50"
                            >
                                {{ form.processing ? 'Menyimpan...' : 'Simpan Role' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
