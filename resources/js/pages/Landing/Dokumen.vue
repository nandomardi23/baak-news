<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import LandingLayout from '@/layouts/LandingLayout.vue';
import SeoHead from '@/components/SeoHead.vue';
import { useStatusBadge } from '@/composables/useStatusBadge';
import { Button } from '@/components/ui/button';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from '@/components/ui/command';
import { Check, ChevronsUpDown, UserCheck } from 'lucide-vue-next';
import { cn } from '@/lib/utils';
import InputError from '@/components/InputError.vue';

interface Mahasiswa {
    id: number;
    nim: string;
    nama: string;
    prodi: string;
    angkatan: string;
    ipk: string;
    sks_tempuh: number;
    dosen_wali_id?: string;
    dosen_wali_nama?: string;
}

interface Semester {
    id: number;
    nama: string;
    has_krs: boolean;
    has_nilai: boolean;
}

interface Pengajuan {
    id: number;
    jenis_surat: string;
    status: string;
    status_label: string;
    status_badge: string;
    created_at: string;
}

const props = defineProps<{
    mahasiswa: Mahasiswa;
    semesters: Semester[];
    existingPending: boolean;
    recentPengajuan: Pengajuan[];
    dosens: { id: string; nama: string }[];
}>();

const { getBadgeClass } = useStatusBadge();

const dosenOpen = ref(false);
const form = useForm({
    dosen_wali_id: props.mahasiswa.dosen_wali_id || '',
});

const selectedDosenName = computed(() => {
    if (!form.dosen_wali_id) return '';
    const found = props.dosens?.find(d => String(d.id) === String(form.dosen_wali_id));
    return found?.nama || '';
});

const saveDosenWali = () => {
    form.post(`/dokumen/${props.mahasiswa.id}/dosen-wali`, {
        preserveScroll: true,
        onSuccess: () => {
            dosenOpen.value = false;
        }
    });
};
</script>

<template>
    <SeoHead 
        :title="`Portal Dokumen | ${mahasiswa.nama}`" 
        :description="`Portal cetak KRS, KHS, Transkrip dan pengajuan surat keterangan untuk ${mahasiswa.nama}.`"
    />

    <LandingLayout variant="simple">
        <div class="max-w-5xl mx-auto py-8 px-4 sm:py-12">
            <Link href="/" class="inline-flex items-center text-slate-500 hover:text-blue-600 mb-6 transition font-medium group">
                <svg class="w-5 h-5 mr-2 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali ke Beranda
            </Link>

            <!-- Student Info Card -->
            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/60 shadow-xl shadow-slate-200/50 mb-6">
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                    <div class="w-16 h-16 bg-linear-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center text-white text-2xl font-bold shadow-lg shadow-blue-500/30">
                        {{ mahasiswa.nama.charAt(0) }}
                    </div>
                    <div class="flex-1">
                        <h1 class="text-2xl font-bold text-slate-900">{{ mahasiswa.nama }}</h1>
                        <p class="text-slate-500">{{ mahasiswa.nim }} • {{ mahasiswa.prodi }}</p>
                    </div>
                    <div class="flex gap-4 text-center mt-4 sm:mt-0">
                        <div class="px-4 py-2 bg-blue-50 rounded-xl">
                            <p class="text-2xl font-bold text-blue-600">{{ mahasiswa.ipk }}</p>
                            <p class="text-xs text-slate-500">IPK</p>
                        </div>
                        <div class="px-4 py-2 bg-emerald-50 rounded-xl">
                            <p class="text-2xl font-bold text-emerald-600">{{ mahasiswa.sks_tempuh }}</p>
                            <p class="text-xs text-slate-500">SKS</p>
                        </div>
                    </div>
                </div>

                <!-- Dosen Wali Selector -->
                <div class="mt-6 pt-6 border-t border-slate-100">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <h3 class="font-semibold text-slate-800 flex items-center gap-2">
                                <UserCheck class="w-5 h-5 text-blue-500" />
                                Pembimbing Akademik (Dosen Wali)
                            </h3>
                            <p class="text-xs text-slate-500 mt-1 max-w-md">Pilih dosen wali Anda untuk dicantumkan pada dokumen KRS yang akan dicetak.</p>
                        </div>
                        
                        <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
                            <div class="w-full sm:w-75">
                                <Popover v-model:open="dosenOpen">
                                    <PopoverTrigger as-child>
                                        <Button
                                            variant="outline"
                                            role="combobox"
                                            :aria-expanded="dosenOpen"
                                            class="w-full justify-between font-normal bg-white"
                                        >
                                            <span :class="selectedDosenName ? 'text-foreground truncate' : 'text-muted-foreground'">
                                                {{ selectedDosenName || 'Cari nama dosen...' }}
                                            </span>
                                            <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                        </Button>
                                    </PopoverTrigger>
                                    <PopoverContent class="w-[--reka-popover-trigger-width] p-0" align="start">
                                        <Command>
                                            <CommandInput placeholder="Ketik nama dosen..." />
                                            <CommandEmpty>Dosen tidak ditemukan.</CommandEmpty>
                                            <CommandList>
                                                <CommandGroup>
                                                    <CommandItem
                                                        v-for="d in dosens"
                                                        :key="d.id"
                                                        :value="d.nama"
                                                        @select="() => { form.dosen_wali_id = String(d.id); dosenOpen = false; }"
                                                    >
                                                        <Check :class="cn('mr-2 h-4 w-4', String(form.dosen_wali_id) === String(d.id) ? 'opacity-100' : 'opacity-0')" />
                                                        {{ d.nama }}
                                                    </CommandItem>
                                                </CommandGroup>
                                            </CommandList>
                                        </Command>
                                    </PopoverContent>
                                </Popover>
                                <InputError :message="form.errors.dosen_wali_id" class="mt-1" />
                            </div>
                            
                            <Button 
                                v-if="form.isDirty" 
                                @click="saveDosenWali" 
                                :disabled="form.processing"
                                class="bg-blue-600 hover:bg-blue-700 text-white min-w-25"
                            >
                                <span v-if="form.processing">Menyimpan...</span>
                                <span v-else>Simpan</span>
                            </Button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-6">
                    <!-- Semester Documents -->
                    <div class="bg-white rounded-2xl p-6 border border-slate-200/60 shadow-lg">
                        <div class="flex items-center gap-3 mb-5">
                            <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center text-blue-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-slate-900">Dokumen Per Semester</h2>
                                <p class="text-sm text-slate-500">Cetak KRS &amp; KHS</p>
                            </div>
                        </div>

                        <div v-if="semesters.length > 0" class="space-y-3">
                            <div v-for="sem in semesters" :key="sem.id" class="flex items-center justify-between p-4 bg-slate-50 rounded-xl hover:bg-slate-100 transition">
                                <div>
                                    <p class="font-semibold text-slate-800">{{ sem.nama }}</p>
                                    <div class="flex gap-2 mt-1">
                                        <span v-if="sem.has_krs" class="text-xs px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full">KRS</span>
                                        <span v-if="sem.has_nilai" class="text-xs px-2 py-0.5 bg-emerald-100 text-emerald-700 rounded-full">Nilai</span>
                                    </div>
                                </div>
                                <div class="flex gap-2 flex-wrap">
                                    <a v-if="sem.has_krs" :href="`/dokumen/${mahasiswa.id}/krs/${sem.id}/print`" target="_blank"
                                        class="px-3 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                        KRS
                                    </a>
                                    <a v-if="sem.has_nilai" :href="`/dokumen/${mahasiswa.id}/khs/${sem.id}/print`" target="_blank"
                                        class="px-3 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                        KHS
                                    </a>
                                    <a v-if="sem.has_krs" :href="`/dokumen/${mahasiswa.id}/kartu-ujian/${sem.id}/print?jenis=uts`" target="_blank"
                                        class="px-3 py-2 bg-amber-600 text-white text-sm font-medium rounded-lg hover:bg-amber-700 transition flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                        Kartu UTS
                                    </a>
                                    <a v-if="sem.has_krs" :href="`/dokumen/${mahasiswa.id}/kartu-ujian/${sem.id}/print?jenis=uas`" target="_blank"
                                        class="px-3 py-2 bg-orange-600 text-white text-sm font-medium rounded-lg hover:bg-orange-700 transition flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                        Kartu UAS
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-8 text-slate-400">
                            <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <p>Belum ada data semester</p>
                        </div>
                    </div>

                    <!-- Transkrip - Coming Soon -->
                    <div class="bg-white rounded-2xl p-6 border border-slate-200/60 shadow-lg relative overflow-hidden">
                        <!-- Coming Soon Badge -->
                        <div class="absolute top-4 right-4 z-10">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-100 text-amber-700 text-xs font-bold rounded-full border border-amber-200 animate-pulse">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Segera Hadir
                            </span>
                        </div>

                        <div class="flex items-center gap-3 mb-5 opacity-50">
                            <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center text-purple-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-slate-900">Transkrip Nilai</h2>
                                <p class="text-sm text-slate-500">Cetak transkrip lengkap</p>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <button disabled class="flex-1 py-3.5 bg-slate-300 text-slate-500 font-bold rounded-xl cursor-not-allowed text-center flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                Cetak Transkrip Nilai
                            </button>
                        </div>
                        <p class="text-xs text-slate-400 mt-3 text-center">Fitur ini sedang dalam tahap pengembangan</p>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <div class="bg-white rounded-2xl p-6 border border-slate-200/60 shadow-lg">
                        <div class="flex items-center gap-3 mb-5">
                            <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center text-amber-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-slate-900">Surat Keterangan</h2>
                                <p class="text-sm text-slate-500">Perlu approval admin</p>
                            </div>
                        </div>
                        <Link :href="`/pengajuan/${mahasiswa.id}`" class="block w-full py-3 bg-linear-to-r from-amber-500 to-orange-500 text-white font-medium rounded-xl hover:from-amber-600 hover:to-orange-600 transition text-center shadow-lg shadow-amber-500/20">Ajukan Surat Keterangan</Link>
                        <p class="text-xs text-slate-400 mt-3 text-center">Untuk surat yang memerlukan tanda tangan resmi</p>
                    </div>

                    <div class="bg-white rounded-2xl p-6 border border-slate-200/60 shadow-lg">
                        <div class="flex items-center gap-3 mb-5">
                            <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center text-emerald-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path></svg>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-slate-900">Yudisium</h2>
                                <p class="text-sm text-slate-500">Persyaratan Kelulusan</p>
                            </div>
                        </div>
                        <Link :href="`/yudisium/${mahasiswa.id}`" class="block w-full py-3 bg-linear-to-r from-emerald-500 to-teal-500 text-white font-medium rounded-xl hover:from-emerald-600 hover:to-teal-600 transition text-center shadow-lg shadow-emerald-500/20">Cek Status Yudisium</Link>
                        <p class="text-xs text-slate-400 mt-3 text-center">Lengkapi syarat yudisium Anda</p>
                    </div>

                    <div v-if="recentPengajuan.length > 0" class="bg-white rounded-2xl p-6 border border-slate-200/60 shadow-lg">
                        <h3 class="font-bold text-slate-900 mb-4">Pengajuan Terakhir</h3>
                        <div class="space-y-3">
                            <div v-for="p in recentPengajuan" :key="p.id" class="flex items-center justify-between text-sm">
                                <div>
                                    <p class="font-medium text-slate-700">{{ p.jenis_surat }}</p>
                                    <p class="text-xs text-slate-400">{{ p.created_at }}</p>
                                </div>
                                <span :class="getBadgeClass(p.status_badge)" class="px-2 py-1 rounded-full text-xs font-medium">{{ p.status_label }}</span>
                            </div>
                        </div>
                        <Link :href="`/status/${mahasiswa.id}`" class="block mt-4 text-center text-sm text-blue-600 hover:underline">Lihat Semua →</Link>
                    </div>
                </div>
            </div>
        </div>
    </LandingLayout>
</template>
