<script setup lang="ts">
import { useBreadcrumbs } from '@/composables/useBreadcrumbs';

defineOptions({ layout: AppLayout });
const { setBreadcrumbs } = useBreadcrumbs();
import AppLayout from '@/layouts/AppLayout.vue';

import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Download,
    Calendar,
    FileText,
    ArrowDownToLine,
    ArrowUpFromLine,
    User,
    Clock,
    Hash,
    MapPin,
    Info,
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';

interface ArsipDetail {
    id: number;
    jenis: 'masuk' | 'keluar';
    jenis_label: string;
    jenis_badge: string;
    nomor_surat: string;
    tanggal_surat: string;
    tanggal_surat_formatted: string;
    tanggal_diterima: string | null;
    tanggal_diterima_formatted: string | null;
    asal_surat: string | null;
    tujuan_surat: string | null;
    perihal: string;
    keterangan: string | null;
    file_url: string;
    file_extension: string;
    is_pdf: boolean;
    is_image: boolean;
    created_by: string;
    created_at: string;
    updated_at: string;
}

const props = defineProps<{
    arsip: ArsipDetail;
}>();

setBreadcrumbs([
    { title: 'Dashboard', href: '/admin' },
    { title: 'Arsip Surat', href: '/admin/arsip-surat' },
    { title: 'Detail', href: '#' },
]);

const getJenisBadgeClass = (jenis: string) => {
    return jenis === 'masuk'
        ? 'bg-sky-100 text-sky-800 border-sky-200'
        : 'bg-emerald-100 text-emerald-800 border-emerald-200';
};
</script>

<template>
    <Head :title="`Arsip Surat - ${arsip.nomor_surat}`" />

    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <!-- Page Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <Link href="/admin/arsip-surat">
                    <Button variant="ghost" size="icon" class="rounded-xl hover:bg-slate-100 h-10 w-10">
                        <ArrowLeft class="w-5 h-5 text-slate-600" />
                    </Button>
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Detail Arsip Surat</h1>
                    <p class="text-muted-foreground text-sm">{{ arsip.nomor_surat }}</p>
                </div>
            </div>
            <a :href="`/admin/arsip-surat/${arsip.id}/download`" target="_blank">
                <Button class="bg-indigo-600 hover:bg-indigo-700 text-white gap-2 rounded-xl shadow-md shadow-indigo-200 transition-all hover:shadow-lg hover:scale-[1.02] active:scale-95">
                    <Download class="w-4 h-4" />
                    Download File
                </Button>
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left: Info Card -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Jenis & Nomor Card -->
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-50 bg-gradient-to-r from-slate-50 to-white">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500">Informasi Surat</h3>
                    </div>
                    <div class="p-6 space-y-5">
                        <!-- Jenis -->
                        <div class="flex items-start gap-3">
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
                                :class="arsip.jenis === 'masuk' ? 'bg-sky-100 text-sky-600' : 'bg-emerald-100 text-emerald-600'">
                                <ArrowDownToLine v-if="arsip.jenis === 'masuk'" class="w-4 h-4" />
                                <ArrowUpFromLine v-else class="w-4 h-4" />
                            </div>
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Jenis Surat</p>
                                <span
                                    :class="getJenisBadgeClass(arsip.jenis)"
                                    class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold border mt-1"
                                >
                                    {{ arsip.jenis_label }}
                                </span>
                            </div>
                        </div>

                        <!-- Nomor Surat -->
                        <div class="flex items-start gap-3">
                            <div class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center shrink-0">
                                <Hash class="w-4 h-4 text-slate-500" />
                            </div>
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Nomor Surat</p>
                                <p class="text-sm font-mono font-semibold text-slate-800 mt-0.5">{{ arsip.nomor_surat }}</p>
                            </div>
                        </div>

                        <!-- Tanggal Surat -->
                        <div class="flex items-start gap-3">
                            <div class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center shrink-0">
                                <Calendar class="w-4 h-4 text-slate-500" />
                            </div>
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Tanggal Surat</p>
                                <p class="text-sm font-semibold text-slate-800 mt-0.5">{{ arsip.tanggal_surat_formatted }}</p>
                            </div>
                        </div>

                        <!-- Tanggal Diterima (Surat Masuk only) -->
                        <div v-if="arsip.jenis === 'masuk' && arsip.tanggal_diterima_formatted" class="flex items-start gap-3">
                            <div class="w-9 h-9 rounded-xl bg-sky-50 flex items-center justify-center shrink-0">
                                <Clock class="w-4 h-4 text-sky-500" />
                            </div>
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Tanggal Diterima</p>
                                <p class="text-sm font-semibold text-slate-800 mt-0.5">{{ arsip.tanggal_diterima_formatted }}</p>
                            </div>
                        </div>

                        <!-- Asal / Tujuan -->
                        <div class="flex items-start gap-3">
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
                                :class="arsip.jenis === 'masuk' ? 'bg-sky-50 text-sky-500' : 'bg-emerald-50 text-emerald-500'">
                                <MapPin class="w-4 h-4" />
                            </div>
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                    {{ arsip.jenis === 'masuk' ? 'Asal Surat' : 'Tujuan Surat' }}
                                </p>
                                <p class="text-sm font-semibold text-slate-800 mt-0.5">
                                    {{ arsip.jenis === 'masuk' ? arsip.asal_surat : arsip.tujuan_surat }}
                                </p>
                            </div>
                        </div>

                        <!-- Perihal -->
                        <div class="flex items-start gap-3">
                            <div class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center shrink-0">
                                <FileText class="w-4 h-4 text-slate-500" />
                            </div>
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Perihal</p>
                                <p class="text-sm font-semibold text-slate-800 mt-0.5">{{ arsip.perihal }}</p>
                            </div>
                        </div>

                        <!-- Keterangan -->
                        <div v-if="arsip.keterangan" class="flex items-start gap-3">
                            <div class="w-9 h-9 rounded-xl bg-amber-50 flex items-center justify-center shrink-0">
                                <Info class="w-4 h-4 text-amber-500" />
                            </div>
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Keterangan</p>
                                <p class="text-sm text-slate-600 mt-0.5 whitespace-pre-wrap">{{ arsip.keterangan }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Metadata Card -->
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-50 bg-gradient-to-r from-slate-50 to-white">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500">Metadata</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex items-start gap-3">
                            <div class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center shrink-0">
                                <User class="w-4 h-4 text-slate-500" />
                            </div>
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Diinput Oleh</p>
                                <p class="text-sm font-semibold text-slate-800 mt-0.5">{{ arsip.created_by }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center shrink-0">
                                <Clock class="w-4 h-4 text-slate-500" />
                            </div>
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Tanggal Input</p>
                                <p class="text-sm text-slate-600 mt-0.5">{{ arsip.created_at }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center shrink-0">
                                <Clock class="w-4 h-4 text-slate-500" />
                            </div>
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Terakhir Diubah</p>
                                <p class="text-sm text-slate-600 mt-0.5">{{ arsip.updated_at }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Document Preview -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden h-full">
                    <div class="px-6 py-4 border-b border-slate-50 bg-gradient-to-r from-slate-50 to-white flex items-center justify-between">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500">Preview Dokumen</h3>
                        <span class="text-xs font-mono px-2 py-1 rounded-md bg-slate-100 text-slate-500 uppercase">
                            {{ arsip.file_extension }}
                        </span>
                    </div>
                    <div class="p-4">
                        <!-- PDF Preview -->
                        <div v-if="arsip.is_pdf" class="w-full h-[700px] rounded-xl overflow-hidden bg-slate-50">
                            <iframe
                                :src="arsip.file_url"
                                class="w-full h-full border-0"
                                title="PDF Preview"
                            ></iframe>
                        </div>

                        <!-- Image Preview -->
                        <div v-else-if="arsip.is_image" class="w-full flex items-center justify-center bg-slate-50 rounded-xl p-4 min-h-[400px]">
                            <img
                                :src="arsip.file_url"
                                :alt="arsip.perihal"
                                class="max-w-full max-h-[700px] object-contain rounded-lg shadow-lg"
                            />
                        </div>

                        <!-- Unsupported -->
                        <div v-else class="w-full h-[400px] flex flex-col items-center justify-center bg-slate-50 rounded-xl">
                            <div class="w-16 h-16 rounded-2xl bg-slate-100 flex items-center justify-center mb-4">
                                <FileText class="w-8 h-8 text-slate-400" />
                            </div>
                            <p class="text-slate-500 font-medium">Preview tidak tersedia untuk format ini</p>
                            <p class="text-sm text-slate-400 mt-1">Silakan download file untuk melihat isi dokumen</p>
                            <a :href="`/admin/arsip-surat/${arsip.id}/download`" target="_blank" class="mt-4">
                                <Button class="bg-indigo-600 hover:bg-indigo-700 text-white gap-2 rounded-xl">
                                    <Download class="w-4 h-4" />
                                    Download
                                </Button>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
