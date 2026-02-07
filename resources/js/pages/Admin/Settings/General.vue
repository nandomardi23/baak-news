<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';

const props = defineProps<{
    settings: {
        app_name: string;
        app_description: string;
        institute_name: string;
        institute_abbreviation: string;
        contact_email: string;
        contact_phone: string;
        contact_address: string;
    };
}>();

const form = useForm({
    app_name: props.settings.app_name,
    app_description: props.settings.app_description,
    institute_name: props.settings.institute_name,
    institute_abbreviation: props.settings.institute_abbreviation,
    contact_email: props.settings.contact_email,
    contact_phone: props.settings.contact_phone,
    contact_address: props.settings.contact_address,
});

const submit = () => {
    form.post(route('admin.settings.general.update'), {
        onSuccess: () => {
            toast.success('Settings updated successfully');
        },
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="[
        { title: 'Pengaturan Sistem', href: '#' },
        { title: 'Pengaturan Umum', href: '/admin/settings/general' },
    ]">
        <Head title="General Settings" />

        <div class="p-6 lg:p-10 max-w-5xl mx-auto">
            <!-- Header Section -->
            <div class="mb-10 text-center sm:text-left relative">
                 <div class="absolute -top-10 -left-10 w-32 h-32 bg-blue-500/10 rounded-full blur-3xl -z-10"></div>
                 <div class="absolute top-0 right-0 w-40 h-40 bg-indigo-500/10 rounded-full blur-3xl -z-10"></div>

                <h1 class="text-3xl font-bold tracking-tight text-slate-900 bg-clip-text text-transparent bg-gradient-to-r from-slate-900 to-slate-600">
                    Pengaturan Umum
                </h1>
                <p class="mt-2 text-lg text-slate-500 max-w-2xl">
                    Konfigurasi identitas aplikasi, informasi institusi, dan detail kontak Anda.
                </p>
            </div>

            <!-- Main Form Card -->
            <div class="bg-white rounded-[2rem] shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden relative">
                 <!-- Decorative Top Border -->
                <div class="h-2 bg-gradient-to-r from-blue-500 via-indigo-500 to-purple-500"></div>

                <form @submit.prevent="submit">
                    <div class="p-8 sm:p-10 space-y-12">
                        
                        <!-- Section 1: Application Identity -->
                        <div class="grid lg:grid-cols-3 gap-8 sm:gap-12">
                            <div class="lg:col-span-1">
                                <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                                    <span class="p-2 rounded-lg bg-blue-50 text-blue-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-app-window"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M10 4v4"/><path d="M2 8h20"/><path d="M6 4v4"/></svg>
                                    </span>
                                    Identitas Aplikasi
                                </h3>
                                <p class="mt-3 text-sm text-slate-500 leading-relaxed">
                                    Informasi ini akan ditampilkan pada judul halaman, footer, dan meta data website.
                                </p>
                            </div>

                            <div class="lg:col-span-2 space-y-6">
                                <div class="grid gap-2">
                                    <Label for="app_name" class="text-slate-700 font-medium">Nama Aplikasi</Label>
                                    <Input 
                                        id="app_name" 
                                        v-model="form.app_name" 
                                        placeholder="Contoh: BAAK Management System"
                                        class="h-12 rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500/20 bg-slate-50/50 focus:bg-white transition-all text-base"
                                    />
                                    <span class="text-xs text-red-500 font-medium" v-if="form.errors.app_name">{{ form.errors.app_name }}</span>
                                </div>

                                <div class="grid gap-2">
                                    <Label for="app_description" class="text-slate-700 font-medium">Deskripsi</Label>
                                    <Textarea 
                                        id="app_description" 
                                        v-model="form.app_description" 
                                        placeholder="Deskripsi singkat tentang aplikasi ini..."
                                        class="min-h-[100px] rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500/20 bg-slate-50/50 focus:bg-white transition-all text-base resize-none"
                                    />
                                    <span class="text-xs text-red-500 font-medium" v-if="form.errors.app_description">{{ form.errors.app_description }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="h-px bg-slate-100"></div>

                        <!-- Section 2: Institute Details -->
                        <div class="grid lg:grid-cols-3 gap-8 sm:gap-12">
                            <div class="lg:col-span-1">
                                <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                                    <span class="p-2 rounded-lg bg-indigo-50 text-indigo-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-building-2"><path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/><path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"/><path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"/><path d="M10 6h4"/><path d="M10 10h4"/><path d="M10 14h4"/><path d="M10 18h4"/></svg>
                                    </span>
                                    Detail Institusi
                                </h3>
                                <p class="mt-3 text-sm text-slate-500 leading-relaxed">
                                    Nama resmi institusi atau perusahaan yang mengelola sistem ini.
                                </p>
                            </div>

                            <div class="lg:col-span-2 grid sm:grid-cols-2 gap-6">
                                <div class="grid gap-2">
                                    <Label for="institute_name" class="text-slate-700 font-medium">Nama Institusi</Label>
                                    <Input 
                                        id="institute_name" 
                                        v-model="form.institute_name" 
                                        placeholder="Contoh: STIKES Hang Tuah"
                                        class="h-12 rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500/20 bg-slate-50/50 focus:bg-white transition-all text-base"
                                    />
                                    <span class="text-xs text-red-500 font-medium" v-if="form.errors.institute_name">{{ form.errors.institute_name }}</span>
                                </div>
                                <div class="grid gap-2">
                                    <Label for="institute_abbreviation" class="text-slate-700 font-medium">Singkatan</Label>
                                    <Input 
                                        id="institute_abbreviation" 
                                        v-model="form.institute_abbreviation" 
                                        placeholder="Contoh: STIKES-HT"
                                        class="h-12 rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500/20 bg-slate-50/50 focus:bg-white transition-all text-base"
                                    />
                                    <span class="text-xs text-red-500 font-medium" v-if="form.errors.institute_abbreviation">{{ form.errors.institute_abbreviation }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="h-px bg-slate-100"></div>

                        <!-- Section 3: Contact Information -->
                        <div class="grid lg:grid-cols-3 gap-8 sm:gap-12">
                            <div class="lg:col-span-1">
                                <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                                    <span class="p-2 rounded-lg bg-purple-50 text-purple-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-phone"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                    </span>
                                    Informasi Kontak
                                </h3>
                                <p class="mt-3 text-sm text-slate-500 leading-relaxed">
                                    Kontak publik yang dapat dihubungi oleh pengguna sistem.
                                </p>
                            </div>

                            <div class="lg:col-span-2 space-y-6">
                                <div class="grid sm:grid-cols-2 gap-6">
                                    <div class="grid gap-2">
                                        <Label for="contact_email" class="text-slate-700 font-medium">Email Address</Label>
                                        <Input 
                                            id="contact_email" 
                                            type="email" 
                                            v-model="form.contact_email" 
                                            placeholder="contact@institute.ac.id"
                                            class="h-12 rounded-xl border-slate-200 focus:border-purple-500 focus:ring-purple-500/20 bg-slate-50/50 focus:bg-white transition-all text-base"
                                        />
                                        <span class="text-xs text-red-500 font-medium" v-if="form.errors.contact_email">{{ form.errors.contact_email }}</span>
                                    </div>
                                    <div class="grid gap-2">
                                        <Label for="contact_phone" class="text-slate-700 font-medium">No. Telepon</Label>
                                        <Input 
                                            id="contact_phone" 
                                            v-model="form.contact_phone" 
                                            placeholder="+62 21 ..."
                                            class="h-12 rounded-xl border-slate-200 focus:border-purple-500 focus:ring-purple-500/20 bg-slate-50/50 focus:bg-white transition-all text-base"
                                        />
                                        <span class="text-xs text-red-500 font-medium" v-if="form.errors.contact_phone">{{ form.errors.contact_phone }}</span>
                                    </div>
                                </div>

                                <div class="grid gap-2">
                                    <Label for="contact_address" class="text-slate-700 font-medium">Alamat Lengkap</Label>
                                    <Textarea 
                                        id="contact_address" 
                                        v-model="form.contact_address" 
                                        placeholder="Alamat lengkap institusi..."
                                        class="min-h-[100px] rounded-xl border-slate-200 focus:border-purple-500 focus:ring-purple-500/20 bg-slate-50/50 focus:bg-white transition-all text-base resize-none"
                                    />
                                    <span class="text-xs text-red-500 font-medium" v-if="form.errors.contact_address">{{ form.errors.contact_address }}</span>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Footer Actions -->
                    <div class="px-8 sm:px-10 py-6 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-4">
                        <Button 
                            type="submit" 
                            :disabled="form.processing" 
                            class="bg-blue-600 hover:bg-blue-700 text-white rounded-xl px-8 py-6 h-auto text-base font-semibold shadow-lg shadow-blue-500/20 transition-all hover:scale-[1.02] active:scale-[0.98]"
                        >
                            <span v-if="form.processing" class="flex items-center gap-2">
                                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                Menyimpan...
                            </span>
                            <span v-else>Simpan Perubahan</span>
                        </Button>
                        
                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0 translate-x-2"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0 translate-x-2"
                        >
                            <p v-if="form.recentlySuccessful" class="text-sm text-green-600 font-medium bg-green-50 px-3 py-1 rounded-lg border border-green-100">
                                Berhasil disimpan!
                            </p>
                        </Transition>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
