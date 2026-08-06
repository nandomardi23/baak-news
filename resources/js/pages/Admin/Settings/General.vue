<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { toast } from 'vue-sonner';

defineOptions({ layout: AppLayout });

const props = defineProps<{
    settings: {
        app_name: string;
        app_description: string;
        institute_name: string;
        institute_abbreviation: string;
        contact_email: string;
        contact_phone: string;
        contact_address: string;
        hero_background_image: string | null;
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
    hero_background_image: null as File | null,
    remove_hero_background: false,
});

const imagePreview = ref<string | null>(props.settings.hero_background_image);
const fileInputRef = ref<HTMLInputElement | null>(null);

const hasCurrentImage = computed(() => {
    return imagePreview.value && !form.remove_hero_background;
});

const handleFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];
    if (file) {
        form.hero_background_image = file;
        form.remove_hero_background = false;
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    }
};

const removeImage = () => {
    form.hero_background_image = null;
    form.remove_hero_background = true;
    imagePreview.value = null;
    if (fileInputRef.value) {
        fileInputRef.value.value = '';
    }
};

const submit = () => {
    form.post(route('admin.settings.general.update'), {
        forceFormData: true,
        onSuccess: () => {
            toast.success('Settings updated successfully');
        },
    });
};
</script>

<template>
    
        <Head title="General Settings" />

        <div class="p-6 lg:p-10 max-w-5xl mx-auto">
            <!-- Header Section -->
            <div class="mb-10 text-center sm:text-left relative">
                 <div class="absolute -top-10 -left-10 w-32 h-32 bg-blue-500/10 rounded-full blur-3xl -z-10"></div>
                 <div class="absolute top-0 right-0 w-40 h-40 bg-indigo-500/10 rounded-full blur-3xl -z-10"></div>

                <h1 class="text-3xl font-bold tracking-tight text-transparent bg-clip-text bg-linear-to-r from-slate-900 to-slate-600">
                    Pengaturan Umum
                </h1>
                <p class="mt-2 text-lg text-slate-500 max-w-2xl">
                    Konfigurasi identitas aplikasi, informasi institusi, dan detail kontak Anda.
                </p>
            </div>

            <!-- Main Form Card -->
            <div class="bg-white rounded-4xl shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden relative">
                 <!-- Decorative Top Border -->
                <div class="h-2 bg-linear-to-r from-blue-500 via-indigo-500 to-purple-500"></div>

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

                        <div class="h-px bg-slate-100"></div>

                        <!-- Section 4: Background Hero Section -->
                        <div class="grid lg:grid-cols-3 gap-8 sm:gap-12">
                            <div class="lg:col-span-1">
                                <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                                    <span class="p-2 rounded-lg bg-emerald-50 text-emerald-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
                                    </span>
                                    Background Hero Section
                                </h3>
                                <p class="mt-3 text-sm text-slate-500 leading-relaxed">
                                    Gambar latar belakang untuk bagian atas (Hero Section) di halaman utama. Gunakan gambar landscape beresolusi tinggi (minimal 1920x800px).
                                </p>
                            </div>

                            <div class="lg:col-span-2 space-y-4">
                                <!-- Current Image Preview -->
                                <div v-if="hasCurrentImage" class="relative group rounded-xl overflow-hidden border border-slate-200 shadow-sm">
                                    <img :src="imagePreview!" alt="Background Hero Preview" class="w-full h-48 object-cover" />
                                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition-colors duration-300 flex items-center justify-center">
                                        <button
                                            type="button"
                                            @click="removeImage"
                                            class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg shadow-lg flex items-center gap-2"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            Hapus Gambar
                                        </button>
                                    </div>
                                </div>

                                <!-- Upload Area -->
                                <div class="relative">
                                    <input
                                        ref="fileInputRef"
                                        type="file"
                                        accept="image/jpeg,image/jpg,image/png,image/webp"
                                        @change="handleFileChange"
                                        class="hidden"
                                        id="hero_bg_input"
                                    />
                                    <label
                                        for="hero_bg_input"
                                        class="flex flex-col items-center justify-center w-full py-8 border-2 border-dashed border-slate-300 rounded-xl cursor-pointer hover:border-emerald-400 hover:bg-emerald-50/50 transition-colors duration-300 group"
                                    >
                                        <div class="w-12 h-12 rounded-full bg-slate-100 group-hover:bg-emerald-100 flex items-center justify-center mb-3 transition-colors">
                                            <svg class="w-6 h-6 text-slate-400 group-hover:text-emerald-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        </div>
                                        <span class="text-sm font-semibold text-slate-600 group-hover:text-emerald-700 transition-colors">
                                            {{ hasCurrentImage ? 'Ganti Gambar' : 'Upload Gambar Background' }}
                                        </span>
                                        <span class="text-xs text-slate-400 mt-1">JPG, PNG, atau WebP (maks. 2MB)</span>
                                    </label>
                                </div>
                                <span class="text-xs text-red-500 font-medium" v-if="form.errors.hero_background_image">{{ form.errors.hero_background_image }}</span>
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
    
</template>
