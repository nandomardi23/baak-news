<script setup lang="ts">
import { useBreadcrumbs } from '@/composables/useBreadcrumbs';

defineOptions({ layout: AppLayout });
const { setBreadcrumbs } = useBreadcrumbs();
import AppLayout from '@/layouts/AppLayout.vue';

import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import SmartTable from '@/components/ui/datatable/SmartTable.vue';
import ComboboxFilter from '@/components/ui/datatable/ComboboxFilter.vue';
import { Eye, Trash2, Plus, Download, FileText, Upload, X, ArrowDownToLine, ArrowUpFromLine, Calendar } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { useConfirmDelete } from '@/composables/useConfirmDelete';
import { useStatusBadge } from '@/composables/useStatusBadge';
import { Link } from '@inertiajs/vue3';

interface ArsipSurat {
    id: number;
    jenis: 'masuk' | 'keluar';
    jenis_label: string;
    jenis_badge: string;
    nomor_surat: string;
    tanggal_surat: string;
    tanggal_diterima: string | null;
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
}

const props = defineProps<{
    arsipSurat: any;
    filters: Record<string, any>;
}>();

setBreadcrumbs([
    { title: 'Dashboard', href: '/admin' },
    { title: 'Surat & Dokumen', href: '#' },
    { title: 'Arsip Surat', href: '/admin/arsip-surat' },
]);

// Table columns
const columns = [
    { key: 'tanggal_surat', label: 'Tanggal', sortable: true },
    { key: 'jenis', label: 'Jenis', sortable: true, align: 'center' as const },
    { key: 'nomor_surat', label: 'Nomor Surat', sortable: true },
    { key: 'asal_tujuan', label: 'Asal / Tujuan' },
    { key: 'perihal', label: 'Perihal' },
    { key: 'created_by', label: 'Diinput Oleh' },
    { key: 'aksi', label: 'Aksi', align: 'center' as const },
];

// ─── Filters ─────────────────────────────────────────────
const selectedJenis = ref(props.filters.jenis || 'all');
const dariTanggal = ref(props.filters.dari_tanggal || '');
const sampaiTanggal = ref(props.filters.sampai_tanggal || '');

const jenisOptions = [
    { label: 'Semua Jenis', value: 'all' },
    { label: 'Surat Masuk', value: 'masuk' },
    { label: 'Surat Keluar', value: 'keluar' },
];

const updateFilter = () => {
    router.get('/admin/arsip-surat', {
        jenis: selectedJenis.value === 'all' ? undefined : selectedJenis.value,
        dari_tanggal: dariTanggal.value || undefined,
        sampai_tanggal: sampaiTanggal.value || undefined,
        search: props.filters.search,
    }, { preserveState: true, preserveScroll: true });
};

const activeFilterChips = computed(() => {
    const chips = [];
    if (props.filters.jenis && props.filters.jenis !== 'all') {
        const label = jenisOptions.find(j => j.value === props.filters.jenis)?.label;
        if (label) chips.push({ key: 'jenis', label: 'Jenis', valueLabel: String(label) });
    }
    if (props.filters.dari_tanggal) {
        chips.push({ key: 'dari_tanggal', label: 'Dari', valueLabel: props.filters.dari_tanggal });
    }
    if (props.filters.sampai_tanggal) {
        chips.push({ key: 'sampai_tanggal', label: 'Sampai', valueLabel: props.filters.sampai_tanggal });
    }
    return chips;
});

// ─── Modal Form ──────────────────────────────────────────
const showModal = ref(false);
const editingItem = ref<ArsipSurat | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);
const dragOver = ref(false);
const selectedFileName = ref('');

const form = useForm({
    jenis: 'masuk' as 'masuk' | 'keluar',
    nomor_surat: '',
    tanggal_surat: '',
    tanggal_diterima: '',
    asal_surat: '',
    tujuan_surat: '',
    perihal: '',
    keterangan: '',
    file: null as File | null,
    _method: 'POST',
});

const resetForm = () => {
    form.clearErrors();
    form.jenis = 'masuk';
    form.nomor_surat = '';
    form.tanggal_surat = '';
    form.tanggal_diterima = '';
    form.asal_surat = '';
    form.tujuan_surat = '';
    form.perihal = '';
    form.keterangan = '';
    form.file = null;
    form._method = 'POST';
    selectedFileName.value = '';
};

const openCreateModal = () => {
    editingItem.value = null;
    resetForm();
    showModal.value = true;
};

const openEditModal = (item: ArsipSurat) => {
    editingItem.value = item;
    form.clearErrors();
    form.jenis = item.jenis;
    form.nomor_surat = item.nomor_surat;
    form.tanggal_surat = item.tanggal_surat;
    form.tanggal_diterima = item.tanggal_diterima || '';
    form.asal_surat = item.asal_surat || '';
    form.tujuan_surat = item.tujuan_surat || '';
    form.perihal = item.perihal;
    form.keterangan = item.keterangan || '';
    form.file = null;
    form._method = 'PUT';
    selectedFileName.value = '';
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editingItem.value = null;
    resetForm();
};

const handleFileSelect = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        form.file = target.files[0];
        selectedFileName.value = target.files[0].name;
    }
};

const handleDrop = (e: DragEvent) => {
    dragOver.value = false;
    if (e.dataTransfer?.files && e.dataTransfer.files[0]) {
        form.file = e.dataTransfer.files[0];
        selectedFileName.value = e.dataTransfer.files[0].name;
    }
};

const removeFile = () => {
    form.file = null;
    selectedFileName.value = '';
    if (fileInput.value) fileInput.value.value = '';
};

const submit = () => {
    if (editingItem.value) {
        // For update, use POST with _method: PUT (multipart form workaround)
        router.post(`/admin/arsip-surat/${editingItem.value.id}`, {
            _method: 'PUT',
            jenis: form.jenis,
            nomor_surat: form.nomor_surat,
            tanggal_surat: form.tanggal_surat,
            tanggal_diterima: form.tanggal_diterima || null,
            asal_surat: form.asal_surat || null,
            tujuan_surat: form.tujuan_surat || null,
            perihal: form.perihal,
            keterangan: form.keterangan || null,
            file: form.file,
        }, {
            forceFormData: true,
            onSuccess: () => closeModal(),
            onError: (errors) => {
                // Map errors back to form
                Object.entries(errors).forEach(([key, value]) => {
                    (form.errors as any)[key] = value;
                });
            },
        });
    } else {
        router.post('/admin/arsip-surat', {
            jenis: form.jenis,
            nomor_surat: form.nomor_surat,
            tanggal_surat: form.tanggal_surat,
            tanggal_diterima: form.tanggal_diterima || null,
            asal_surat: form.asal_surat || null,
            tujuan_surat: form.tujuan_surat || null,
            perihal: form.perihal,
            keterangan: form.keterangan || null,
            file: form.file,
        }, {
            forceFormData: true,
            onSuccess: () => closeModal(),
            onError: (errors) => {
                Object.entries(errors).forEach(([key, value]) => {
                    (form.errors as any)[key] = value;
                });
            },
        });
    }
};

// ─── Delete ──────────────────────────────────────────────
const { confirmDelete } = useConfirmDelete();

const deleteItem = (id: number) => {
    confirmDelete({
        url: `/admin/arsip-surat/${id}`,
        entityName: 'Arsip Surat',
        text: 'Hapus arsip surat ini beserta file scan-nya? Tindakan ini tidak dapat dibatalkan.',
    });
};

// ─── Badge ───────────────────────────────────────────────
const getJenisBadgeClass = (jenis: string) => {
    return jenis === 'masuk'
        ? 'bg-sky-100 text-sky-800 border-sky-200'
        : 'bg-emerald-100 text-emerald-800 border-emerald-200';
};
</script>

<template>
    <Head title="Arsip Surat" />

    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <!-- Page Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Arsip Surat</h1>
                <p class="text-muted-foreground">Digitalisasi arsip surat masuk (disposisi) dan surat keluar BAAK</p>
            </div>
        </div>

        <!-- Data Table -->
        <SmartTable
            :data="arsipSurat"
            :columns="columns"
            :search="filters.search"
            :filters="{ jenis: filters.jenis, dari_tanggal: filters.dari_tanggal, sampai_tanggal: filters.sampai_tanggal }"
            :active-filters="activeFilterChips"
            :sort-field="filters.sort_field"
            :sort-direction="filters.sort_direction"
            title="Arsip Surat"
        >
            <!-- Action: Tambah -->
            <template #actions>
                <Button @click="openCreateModal" class="bg-indigo-600 hover:bg-indigo-700 text-white gap-2 rounded-xl shadow-md shadow-indigo-200 transition-all hover:shadow-lg hover:scale-[1.02] active:scale-95">
                    <Plus class="w-4 h-4" />
                    Tambah Arsip
                </Button>
            </template>

            <!-- Filter Sheet Content -->
            <template #filters>
                <div class="flex flex-col gap-4 w-full">
                    <div class="space-y-2">
                        <Label>Jenis Surat</Label>
                        <ComboboxFilter
                            v-model="selectedJenis"
                            :options="jenisOptions"
                            placeholder="Semua Jenis"
                            searchPlaceholder="Cari Jenis..."
                            widthClass="w-full h-10"
                            @update:modelValue="updateFilter"
                        />
                    </div>
                    <div class="space-y-2">
                        <Label>Dari Tanggal</Label>
                        <input
                            v-model="dariTanggal"
                            type="date"
                            class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition bg-white"
                            @change="updateFilter"
                        />
                    </div>
                    <div class="space-y-2">
                        <Label>Sampai Tanggal</Label>
                        <input
                            v-model="sampaiTanggal"
                            type="date"
                            class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition bg-white"
                            @change="updateFilter"
                        />
                    </div>
                </div>
            </template>

            <!-- Cell: Tanggal Surat -->
            <template #cell-tanggal_surat="{ row }">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center shrink-0">
                        <Calendar class="w-3.5 h-3.5 text-slate-500" />
                    </div>
                    <span class="text-sm text-slate-700 font-medium">{{ row.tanggal_surat }}</span>
                </div>
            </template>

            <!-- Cell: Jenis -->
            <template #cell-jenis="{ row }">
                <div class="flex justify-center">
                    <span
                        :class="getJenisBadgeClass(row.jenis)"
                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide border shadow-sm"
                    >
                        <ArrowDownToLine v-if="row.jenis === 'masuk'" class="w-3 h-3" />
                        <ArrowUpFromLine v-else class="w-3 h-3" />
                        {{ row.jenis_label }}
                    </span>
                </div>
            </template>

            <!-- Cell: Nomor Surat -->
            <template #cell-nomor_surat="{ row }">
                <div class="inline-flex items-center px-2 py-1 rounded-md bg-slate-50 border border-slate-200 font-mono text-xs font-medium text-slate-700">
                    {{ row.nomor_surat }}
                </div>
            </template>

            <!-- Cell: Asal / Tujuan -->
            <template #cell-asal_tujuan="{ row }">
                <div class="flex flex-col">
                    <span class="text-sm font-semibold text-slate-800">
                        {{ row.jenis === 'masuk' ? row.asal_surat : row.tujuan_surat }}
                    </span>
                    <span class="text-[10px] text-slate-400 font-medium uppercase tracking-wider">
                        {{ row.jenis === 'masuk' ? 'Asal Surat' : 'Tujuan Surat' }}
                    </span>
                </div>
            </template>

            <!-- Cell: Perihal -->
            <template #cell-perihal="{ row }">
                <span class="text-sm text-slate-700 line-clamp-2">{{ row.perihal }}</span>
            </template>

            <!-- Cell: Created By -->
            <template #cell-created_by="{ row }">
                <span class="text-xs text-slate-500">{{ row.created_by }}</span>
            </template>

            <!-- Cell: Aksi -->
            <template #cell-aksi="{ row }">
                <div class="flex items-center justify-center gap-1">
                    <Link :href="`/admin/arsip-surat/${row.id}`">
                        <Button
                            variant="ghost"
                            size="icon"
                            class="text-slate-500 hover:text-blue-600 hover:bg-blue-50 h-8 w-8"
                            title="Detail"
                        >
                            <Eye class="w-4 h-4" />
                        </Button>
                    </Link>
                    <a :href="`/admin/arsip-surat/${row.id}/download`" target="_blank">
                        <Button
                            variant="ghost"
                            size="icon"
                            class="text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 h-8 w-8"
                            title="Download"
                        >
                            <Download class="w-4 h-4" />
                        </Button>
                    </a>
                    <Button
                        variant="ghost"
                        size="icon"
                        @click="openEditModal(row)"
                        class="text-slate-500 hover:text-amber-600 hover:bg-amber-50 h-8 w-8"
                        title="Edit"
                    >
                        <FileText class="w-4 h-4" />
                    </Button>
                    <Button
                        variant="ghost"
                        size="icon"
                        @click="deleteItem(row.id)"
                        class="text-slate-500 hover:text-red-600 hover:bg-red-50 h-8 w-8"
                        title="Hapus"
                    >
                        <Trash2 class="w-4 h-4" />
                    </Button>
                </div>
            </template>
        </SmartTable>
    </div>

    <!-- Create/Edit Modal -->
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="showModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4" @click.self="closeModal">
                <Transition
                    enter-active-class="transition duration-300 ease-out"
                    enter-from-class="opacity-0 scale-95 translate-y-4"
                    enter-to-class="opacity-100 scale-100 translate-y-0"
                    leave-active-class="transition duration-200 ease-in"
                    leave-from-class="opacity-100 scale-100"
                    leave-to-class="opacity-0 scale-95"
                >
                    <div v-if="showModal" class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden max-h-[90vh] flex flex-col">
                        <!-- Modal Header -->
                        <div class="px-6 py-4 border-b bg-linear-to-r from-indigo-50 to-blue-50 flex items-center justify-between shrink-0">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center shadow-sm"
                                    :class="editingItem ? 'bg-amber-100 text-amber-600' : 'bg-indigo-100 text-indigo-600'">
                                    <FileText class="w-5 h-5" />
                                </div>
                                <div>
                                    <h2 class="text-lg font-bold text-slate-900">{{ editingItem ? 'Edit Arsip' : 'Tambah Arsip Surat' }}</h2>
                                    <p class="text-xs text-slate-500">{{ editingItem ? 'Perbarui data arsip surat' : 'Upload dan arsipkan surat baru' }}</p>
                                </div>
                            </div>
                            <button @click="closeModal" class="p-2 rounded-xl hover:bg-white/80 transition">
                                <X class="w-4 h-4 text-slate-400" />
                            </button>
                        </div>

                        <!-- Modal Body -->
                        <form @submit.prevent="submit" class="p-6 space-y-5 overflow-y-auto flex-1">
                            <!-- Jenis Surat Toggle -->
                            <div>
                                <label class="block text-sm font-semibold mb-2 text-slate-700">Jenis Surat <span class="text-red-500">*</span></label>
                                <div class="grid grid-cols-2 gap-2">
                                    <button
                                        type="button"
                                        @click="form.jenis = 'masuk'"
                                        :class="form.jenis === 'masuk'
                                            ? 'bg-sky-50 border-sky-300 text-sky-700 ring-2 ring-sky-200'
                                            : 'bg-white border-slate-200 text-slate-500 hover:bg-slate-50'"
                                        class="flex items-center justify-center gap-2 px-4 py-3 border rounded-xl font-semibold text-sm transition-all"
                                    >
                                        <ArrowDownToLine class="w-4 h-4" />
                                        Surat Masuk
                                    </button>
                                    <button
                                        type="button"
                                        @click="form.jenis = 'keluar'"
                                        :class="form.jenis === 'keluar'
                                            ? 'bg-emerald-50 border-emerald-300 text-emerald-700 ring-2 ring-emerald-200'
                                            : 'bg-white border-slate-200 text-slate-500 hover:bg-slate-50'"
                                        class="flex items-center justify-center gap-2 px-4 py-3 border rounded-xl font-semibold text-sm transition-all"
                                    >
                                        <ArrowUpFromLine class="w-4 h-4" />
                                        Surat Keluar
                                    </button>
                                </div>
                            </div>

                            <!-- Nomor Surat -->
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Nomor Surat <span class="text-red-500">*</span></label>
                                <input
                                    v-model="form.nomor_surat"
                                    type="text"
                                    class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition bg-white font-mono text-sm"
                                    placeholder="Contoh: 001/BAAK/VIII/2026"
                                    required
                                />
                                <p v-if="form.errors.nomor_surat" class="text-red-500 text-xs mt-1">{{ form.errors.nomor_surat }}</p>
                            </div>

                            <!-- Tanggal Surat -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold mb-1.5 text-slate-700">Tanggal Surat <span class="text-red-500">*</span></label>
                                    <input
                                        v-model="form.tanggal_surat"
                                        type="date"
                                        class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition bg-white text-sm"
                                        required
                                    />
                                    <p v-if="form.errors.tanggal_surat" class="text-red-500 text-xs mt-1">{{ form.errors.tanggal_surat }}</p>
                                </div>
                                <div v-if="form.jenis === 'masuk'">
                                    <label class="block text-sm font-semibold mb-1.5 text-slate-700">Tanggal Diterima <span class="text-red-500">*</span></label>
                                    <input
                                        v-model="form.tanggal_diterima"
                                        type="date"
                                        class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition bg-white text-sm"
                                    />
                                    <p v-if="form.errors.tanggal_diterima" class="text-red-500 text-xs mt-1">{{ form.errors.tanggal_diterima }}</p>
                                </div>
                            </div>

                            <!-- Asal Surat (Masuk) / Tujuan Surat (Keluar) -->
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">
                                    {{ form.jenis === 'masuk' ? 'Asal Surat' : 'Tujuan Surat' }} <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-if="form.jenis === 'masuk'"
                                    v-model="form.asal_surat"
                                    type="text"
                                    class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition bg-white text-sm"
                                    placeholder="Contoh: Rektorat Universitas XYZ"
                                />
                                <input
                                    v-else
                                    v-model="form.tujuan_surat"
                                    type="text"
                                    class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition bg-white text-sm"
                                    placeholder="Contoh: Dinas Pendidikan Kota"
                                />
                                <p v-if="form.errors.asal_surat" class="text-red-500 text-xs mt-1">{{ form.errors.asal_surat }}</p>
                                <p v-if="form.errors.tujuan_surat" class="text-red-500 text-xs mt-1">{{ form.errors.tujuan_surat }}</p>
                            </div>

                            <!-- Perihal -->
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Perihal <span class="text-red-500">*</span></label>
                                <input
                                    v-model="form.perihal"
                                    type="text"
                                    class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition bg-white text-sm"
                                    placeholder="Perihal surat"
                                    required
                                />
                                <p v-if="form.errors.perihal" class="text-red-500 text-xs mt-1">{{ form.errors.perihal }}</p>
                            </div>

                            <!-- Keterangan -->
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Keterangan (Opsional)</label>
                                <textarea
                                    v-model="form.keterangan"
                                    rows="2"
                                    class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition bg-white text-sm resize-none"
                                    placeholder="Catatan tambahan..."
                                ></textarea>
                            </div>

                            <!-- File Upload (Drag & Drop) -->
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">
                                    File Scan <span v-if="!editingItem" class="text-red-500">*</span>
                                    <span v-if="editingItem" class="font-normal text-slate-400">(kosongkan jika tidak diganti)</span>
                                </label>
                                <div
                                    @dragover.prevent="dragOver = true"
                                    @dragleave="dragOver = false"
                                    @drop.prevent="handleDrop"
                                    :class="dragOver ? 'border-indigo-400 bg-indigo-50' : 'border-slate-200 bg-slate-50 hover:bg-white hover:border-indigo-300'"
                                    class="border-2 border-dashed rounded-xl p-6 text-center transition-all cursor-pointer group"
                                    @click="fileInput?.click()"
                                >
                                    <input
                                        ref="fileInput"
                                        type="file"
                                        accept=".pdf,.jpg,.jpeg,.png,.webp"
                                        class="hidden"
                                        @change="handleFileSelect"
                                    />

                                    <div v-if="selectedFileName" class="flex items-center justify-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center">
                                            <FileText class="w-5 h-5 text-indigo-600" />
                                        </div>
                                        <div class="text-left">
                                            <p class="text-sm font-semibold text-slate-800 truncate max-w-50">{{ selectedFileName }}</p>
                                            <p class="text-xs text-slate-400">Klik untuk ganti file</p>
                                        </div>
                                        <button type="button" @click.stop="removeFile" class="p-1.5 rounded-lg hover:bg-red-50 text-slate-400 hover:text-red-500 transition">
                                            <X class="w-4 h-4" />
                                        </button>
                                    </div>
                                    <div v-else class="space-y-2">
                                        <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center mx-auto group-hover:bg-indigo-100 transition">
                                            <Upload class="w-5 h-5 text-slate-400 group-hover:text-indigo-500 transition" />
                                        </div>
                                        <p class="text-sm text-slate-500">
                                            <span class="font-semibold text-indigo-600">Klik untuk upload</span> atau drag & drop
                                        </p>
                                        <p class="text-xs text-slate-400">PDF, JPG, PNG, WebP (max 10MB)</p>
                                    </div>
                                </div>
                                <p v-if="form.errors.file" class="text-red-500 text-xs mt-1">{{ form.errors.file }}</p>
                            </div>

                            <!-- Submit -->
                            <div class="flex justify-end gap-3 pt-4 border-t mt-2">
                                <button
                                    type="button"
                                    @click="closeModal"
                                    class="px-5 py-2.5 border border-slate-200 rounded-xl hover:bg-slate-50 font-semibold text-sm text-slate-600 transition"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-semibold text-sm transition-all disabled:opacity-50 flex items-center gap-2 shadow-md shadow-indigo-200 hover:shadow-lg active:scale-95"
                                >
                                    <span v-if="form.processing" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                                    {{ form.processing ? 'Menyimpan...' : (editingItem ? 'Simpan Perubahan' : 'Simpan Arsip') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
