<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

interface User {
    id: number;
    name: string;
    email: string;
    role: string | null;
}

const props = defineProps<{
    user?: User;
    roles: string[];
}>();

const isEditing = computed(() => !!props.user);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin' },
    { title: 'User Management', href: '/admin/user' },
    { title: isEditing.value ? 'Edit User' : 'Tambah User', href: '#' },
];

const form = useForm({
    name: props.user?.name || '',
    email: props.user?.email || '',
    password: '',
    password_confirmation: '',
    role: props.user?.role || '',
});

const submit = () => {
    if (isEditing.value) {
        form.put(`/admin/user/${props.user!.id}`);
    } else {
        form.post('/admin/user');
    }
};
</script>

<template>
    <Head :title="isEditing ? 'Edit User' : 'Tambah User'" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div>
                <h1 class="text-2xl font-bold">{{ isEditing ? 'Edit User' : 'Tambah User' }}</h1>
                <p class="text-muted-foreground">{{ isEditing ? 'Perbarui data user' : 'Buat user baru' }}</p>
            </div>

            <div class="max-w-2xl">
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="rounded-xl border bg-card p-6 shadow-sm space-y-4">
                        <!-- Name -->
                        <div>
                            <label class="block text-sm font-medium mb-2">Nama <span class="text-red-500">*</span></label>
                            <input
                                v-model="form.name"
                                type="text"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                                placeholder="Nama lengkap"
                                required
                            />
                            <p v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</p>
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-sm font-medium mb-2">Email <span class="text-red-500">*</span></label>
                            <input
                                v-model="form.email"
                                type="email"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                                placeholder="email@contoh.com"
                                required
                            />
                            <p v-if="form.errors.email" class="text-red-500 text-sm mt-1">{{ form.errors.email }}</p>
                        </div>

                        <!-- Password -->
                        <div>
                            <label class="block text-sm font-medium mb-2">
                                Password
                                <span v-if="!isEditing" class="text-red-500">*</span>
                                <span v-else class="text-muted-foreground text-xs">(kosongkan jika tidak ingin mengubah)</span>
                            </label>
                            <input
                                v-model="form.password"
                                type="password"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                                placeholder="••••••••"
                                :required="!isEditing"
                            />
                            <p v-if="form.errors.password" class="text-red-500 text-sm mt-1">{{ form.errors.password }}</p>
                        </div>

                        <!-- Password Confirmation -->
                        <div>
                            <label class="block text-sm font-medium mb-2">Konfirmasi Password</label>
                            <input
                                v-model="form.password_confirmation"
                                type="password"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                                placeholder="••••••••"
                            />
                        </div>

                        <!-- Role -->
                        <div>
                            <label class="block text-sm font-medium mb-2">Role <span class="text-red-500">*</span></label>
                            <select
                                v-model="form.role"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                                required
                            >
                                <option value="" disabled>Pilih role</option>
                                <option v-for="role in roles" :key="role" :value="role">
                                    {{ role }}
                                </option>
                            </select>
                            <p v-if="form.errors.role" class="text-red-500 text-sm mt-1">{{ form.errors.role }}</p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-4">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-2 bg-primary text-primary-foreground font-medium rounded-lg hover:bg-primary/90 transition disabled:opacity-50"
                        >
                            <span v-if="form.processing">Menyimpan...</span>
                            <span v-else>{{ isEditing ? 'Perbarui' : 'Simpan' }}</span>
                        </button>
                        <Link
                            href="/admin/user"
                            class="px-6 py-2 border rounded-lg hover:bg-muted transition"
                        >
                            Batal
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
