<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

interface KalenderItem {
    id: number;
    judul: string;
    deskripsi: string | null;
    tanggal_mulai: string;
    tanggal_selesai: string | null;
    tanggal_format: string;
    jenis: string;
    jenis_label: string;
    warna: string;
    tahun_akademik: string | null;
    duration_days: number;
}

interface JenisOption {
    value: string;
    label: string;
    color: string;
}

interface TahunAkademikOption {
    id: number;
    nama: string;
}

const props = defineProps<{
    kalender: KalenderItem[];
    filters: {
        tahun_akademik_id: number | null;
        jenis: string | null;
    };
    tahunAkademikOptions: TahunAkademikOption[];
    jenisOptions: JenisOption[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin' },
    { title: 'Kalender Akademik', href: '/admin/kalender' },
];

// Modal state
const showModal = ref(false);
const editingItem = ref<KalenderItem | null>(null);

const form = useForm({
    judul: '',
    deskripsi: '',
    tanggal_mulai: '',
    tanggal_selesai: '',
    jenis: 'lainnya',
    tahun_akademik_id: props.filters.tahun_akademik_id || (props.tahunAkademikOptions[0]?.id ?? ''),
    warna: '',
});

function openModal(item?: KalenderItem) {
    if (item) {
        editingItem.value = item;
        form.judul = item.judul;
        form.deskripsi = item.deskripsi || '';
        form.tanggal_mulai = item.tanggal_mulai;
        form.tanggal_selesai = item.tanggal_selesai || '';
        form.jenis = item.jenis;
        form.warna = item.warna;
    } else {
        editingItem.value = null;
        form.reset();
        form.tahun_akademik_id = props.filters.tahun_akademik_id || (props.tahunAkademikOptions[0]?.id ?? '');
    }
    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
    editingItem.value = null;
    form.reset();
}

function submitForm() {
    if (editingItem.value) {
        form.put(`/admin/kalender/${editingItem.value.id}`, {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        });
    } else {
        form.post('/admin/kalender', {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        });
    }
}

function deleteItem(item: KalenderItem) {
    if (confirm(`Hapus event "${item.judul}"?`)) {
        router.delete(`/admin/kalender/${item.id}`, {
            preserveScroll: true,
        });
    }
}

function filterByTahun(tahunId: number | string) {
    router.get('/admin/kalender', { tahun_akademik_id: tahunId }, { preserveState: true });
}

function filterByJenis(jenis: string | null) {
    router.get('/admin/kalender', { 
        tahun_akademik_id: props.filters.tahun_akademik_id,
        jenis: jenis 
    }, { preserveState: true });
}

// Group by month
const groupedByMonth = computed(() => {
    const groups: Record<string, KalenderItem[]> = {};
    
    props.kalender.forEach(item => {
        const date = new Date(item.tanggal_mulai);
        const monthKey = date.toLocaleDateString('id-ID', { year: 'numeric', month: 'long' });
        
        if (!groups[monthKey]) {
            groups[monthKey] = [];
        }
        groups[monthKey].push(item);
    });
    
    return groups;
});

const selectedJenisColor = computed(() => {
    const option = props.jenisOptions.find(o => o.value === form.jenis);
    return option?.color || '#6B7280';
});
</script>

<template>
    <Head title="Kalender Akademik" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h1 class="text-2xl font-bold">Kalender Akademik</h1>
                    <p class="text-muted-foreground">Kelola jadwal kegiatan akademik per semester</p>
                </div>
                <button
                    @click="openModal()"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium"
                >
                    + Tambah Event
                </button>
            </div>

            <!-- Filters -->
            <div class="flex flex-wrap gap-3">
                <select
                    :value="filters.tahun_akademik_id"
                    @change="filterByTahun(($event.target as HTMLSelectElement).value)"
                    class="px-4 py-2 rounded-lg border bg-card"
                >
                    <option v-for="ta in tahunAkademikOptions" :key="ta.id" :value="ta.id">
                        {{ ta.nama }}
                    </option>
                </select>

                <div class="flex gap-2">
                    <button
                        @click="filterByJenis(null)"
                        :class="[
                            'px-3 py-1.5 rounded-full text-sm font-medium transition-colors',
                            !filters.jenis ? 'bg-gray-800 text-white dark:bg-white dark:text-gray-900' : 'bg-gray-200 dark:bg-gray-700'
                        ]"
                    >
                        Semua
                    </button>
                    <button
                        v-for="jenis in jenisOptions"
                        :key="jenis.value"
                        @click="filterByJenis(jenis.value)"
                        :class="[
                            'px-3 py-1.5 rounded-full text-sm font-medium transition-colors',
                            filters.jenis === jenis.value ? 'text-white' : 'bg-gray-200 dark:bg-gray-700'
                        ]"
                        :style="filters.jenis === jenis.value ? { backgroundColor: jenis.color } : {}"
                    >
                        {{ jenis.label }}
                    </button>
                </div>
            </div>

            <!-- Timeline -->
            <div v-if="Object.keys(groupedByMonth).length > 0" class="space-y-8">
                <div v-for="(items, month) in groupedByMonth" :key="month">
                    <h2 class="text-lg font-semibold mb-4 sticky top-0 bg-background py-2">{{ month }}</h2>
                    
                    <div class="space-y-3 pl-4 border-l-2 border-gray-200 dark:border-gray-700">
                        <div
                            v-for="item in items"
                            :key="item.id"
                            class="relative pl-6 pb-4"
                        >
                            <!-- Timeline dot -->
                            <div
                                class="absolute -left-[9px] top-1 w-4 h-4 rounded-full border-2 border-background"
                                :style="{ backgroundColor: item.warna }"
                            ></div>

                            <!-- Card -->
                            <div class="rounded-xl border bg-card p-4 shadow-sm hover:shadow-md transition-shadow">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span
                                                class="px-2 py-0.5 rounded-full text-xs font-medium text-white"
                                                :style="{ backgroundColor: item.warna }"
                                            >
                                                {{ item.jenis_label }}
                                            </span>
                                            <span v-if="item.duration_days > 1" class="text-xs text-muted-foreground">
                                                {{ item.duration_days }} hari
                                            </span>
                                        </div>
                                        <h3 class="font-semibold text-lg">{{ item.judul }}</h3>
                                        <p class="text-sm text-muted-foreground">{{ item.tanggal_format }}</p>
                                        <p v-if="item.deskripsi" class="text-sm mt-2 text-muted-foreground">
                                            {{ item.deskripsi }}
                                        </p>
                                    </div>
                                    <div class="flex gap-2 flex-shrink-0">
                                        <button
                                            @click="openModal(item)"
                                            class="p-2 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors"
                                            title="Edit"
                                        >
                                            ✏️
                                        </button>
                                        <button
                                            @click="deleteItem(item)"
                                            class="p-2 hover:bg-red-100 dark:hover:bg-red-900/30 rounded-lg transition-colors text-red-600"
                                            title="Hapus"
                                        >
                                            🗑️
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="rounded-xl border bg-card p-12 text-center">
                <p class="text-muted-foreground mb-4">Belum ada event kalender untuk semester ini</p>
                <button
                    @click="openModal()"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                >
                    + Tambah Event Pertama
                </button>
            </div>
        </div>

        <!-- Modal -->
        <Teleport to="body">
            <div
                v-if="showModal"
                class="fixed inset-0 z-50 flex items-center justify-center p-4"
            >
                <!-- Backdrop -->
                <div class="absolute inset-0 bg-black/50" @click="closeModal"></div>

                <!-- Modal Content -->
                <div class="relative bg-background rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-auto">
                    <div class="p-6">
                        <h2 class="text-xl font-bold mb-6">
                            {{ editingItem ? 'Edit Event' : 'Tambah Event' }}
                        </h2>

                        <form @submit.prevent="submitForm" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium mb-1">Judul Event *</label>
                                <input
                                    v-model="form.judul"
                                    type="text"
                                    required
                                    class="w-full px-4 py-2 rounded-lg border bg-card focus:ring-2 focus:ring-blue-500 outline-none"
                                    placeholder="Contoh: UTS Semester Ganjil"
                                />
                                <p v-if="form.errors.judul" class="text-red-500 text-sm mt-1">{{ form.errors.judul }}</p>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium mb-1">Tanggal Mulai *</label>
                                    <input
                                        v-model="form.tanggal_mulai"
                                        type="date"
                                        required
                                        class="w-full px-4 py-2 rounded-lg border bg-card focus:ring-2 focus:ring-blue-500 outline-none"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">Tanggal Selesai</label>
                                    <input
                                        v-model="form.tanggal_selesai"
                                        type="date"
                                        class="w-full px-4 py-2 rounded-lg border bg-card focus:ring-2 focus:ring-blue-500 outline-none"
                                    />
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium mb-1">Jenis *</label>
                                    <select
                                        v-model="form.jenis"
                                        class="w-full px-4 py-2 rounded-lg border bg-card focus:ring-2 focus:ring-blue-500 outline-none"
                                    >
                                        <option v-for="opt in jenisOptions" :key="opt.value" :value="opt.value">
                                            {{ opt.label }}
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">Warna</label>
                                    <div class="flex items-center gap-2">
                                        <input
                                            v-model="form.warna"
                                            type="color"
                                            class="w-12 h-10 rounded-lg border cursor-pointer"
                                            :value="form.warna || selectedJenisColor"
                                        />
                                        <span class="text-sm text-muted-foreground">{{ form.warna || 'Default' }}</span>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1">Tahun Akademik *</label>
                                <select
                                    v-model="form.tahun_akademik_id"
                                    class="w-full px-4 py-2 rounded-lg border bg-card focus:ring-2 focus:ring-blue-500 outline-none"
                                >
                                    <option v-for="ta in tahunAkademikOptions" :key="ta.id" :value="ta.id">
                                        {{ ta.nama }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1">Deskripsi</label>
                                <textarea
                                    v-model="form.deskripsi"
                                    rows="3"
                                    class="w-full px-4 py-2 rounded-lg border bg-card focus:ring-2 focus:ring-blue-500 outline-none resize-none"
                                    placeholder="Keterangan tambahan..."
                                ></textarea>
                            </div>

                            <div class="flex justify-end gap-3 pt-4 border-t">
                                <button
                                    type="button"
                                    @click="closeModal"
                                    class="px-4 py-2 rounded-lg border hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50"
                                >
                                    {{ form.processing ? 'Menyimpan...' : (editingItem ? 'Simpan' : 'Tambah') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </Teleport>
    </AppLayout>
</template>
