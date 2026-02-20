<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

interface Mahasiswa {
    id: number;
    nama: string;
    nim: string;
    tempat_lahir: string | null;
    tanggal_lahir: string | null;
    jenis_kelamin: string | null;
    alamat: string | null;
    dusun: string | null;
    rt: string | null;
    rw: string | null;
    kelurahan: string | null;
    kode_pos: string | null;
    nama_ayah: string | null;
    nama_ibu: string | null;
    pekerjaan_ayah: string | null;
    pekerjaan_ibu: string | null;
    dosen_wali: string | null;
}

const props = defineProps<{
    mahasiswa: Mahasiswa;
    dosen: { id: number; nama: string }[];
}>();

const detailTab = ref('orang_tua');
const isEditingDosenWali = ref(false);

const dosenWaliForm = useForm({
    dosen_wali_id: '' as string | number,
});

const saveDosenWali = () => {
    dosenWaliForm.patch(`/admin/mahasiswa/${props.mahasiswa.id}`, {
        onSuccess: () => {
            isEditingDosenWali.value = false;
            dosenWaliForm.reset();
        },
    });
};
</script>

<template>
    <div class="space-y-6">
        <!-- Data Mahasiswa -->
        <div class="rounded bg-white border shadow-sm p-6">
            <h3 class="text-lg font-semibold text-blue-500 mb-6 border-b pb-2">Data Mahasiswa</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                <!-- Left Column -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Nama</label>
                        <div class="p-2 bg-indigo-50 border border-indigo-100 rounded text-gray-800 font-medium uppercase">
                            {{ mahasiswa.nama }}
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Jenis Kelamin</label>
                        <div class="p-2 bg-gray-50 border border-gray-200 rounded text-gray-700">
                            {{ mahasiswa.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Tanggal Lahir</label>
                        <div class="p-2 bg-gray-50 border border-gray-200 rounded text-gray-700 flex items-center justify-between">
                            <span>{{ mahasiswa.tanggal_lahir || '-' }}</span>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Tempat Lahir</label>
                        <div class="p-2 bg-gray-50 border border-gray-200 rounded text-gray-700 uppercase">
                            {{ mahasiswa.tempat_lahir || '-' }}
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Nama Ibu</label>
                        <div class="p-2 bg-gray-50 border border-gray-200 rounded text-gray-700 uppercase">
                            {{ mahasiswa.nama_ibu || '-' }}
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Agama</label>
                        <div class="p-2 bg-gray-50 border border-gray-200 rounded text-gray-700">
                            -
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Dosen Wali</label>
                        <div class="p-2 bg-blue-50 border border-blue-200 rounded text-blue-700 font-medium">
                            {{ mahasiswa.dosen_wali || '-' }}
                            <button @click="isEditingDosenWali = true" class="ml-2 text-blue-600 hover:text-blue-800 text-xs font-semibold">
                                (Edit)
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dosen Wali Edit Modal -->
        <div v-if="isEditingDosenWali" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                <h3 class="text-lg font-bold mb-4">Edit Dosen Wali</h3>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Dosen Wali</label>
                    <select v-model="dosenWaliForm.dosen_wali_id" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="" disabled>-- Pilih Dosen --</option>
                        <option v-for="d in dosen" :key="d.id" :value="d.id">
                            {{ d.nama }}
                        </option>
                    </select>
                </div>
                <div class="flex justify-end gap-2">
                    <button @click="isEditingDosenWali = false" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded">Batal</button>
                    <button @click="saveDosenWali" :disabled="dosenWaliForm.processing" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50">
                        {{ dosenWaliForm.processing ? 'Menyimpan...' : 'Simpan' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Informasi Detail Mahasiswa -->
        <div class="rounded bg-white border shadow-sm p-6 min-h-[400px]">
            <h3 class="text-lg font-semibold text-blue-500 mb-6 border-b pb-2">Informasi Detail Mahasiswa</h3>

            <!-- Nested Tabs -->
            <div class="flex justify-center mb-8">
                <div class="flex border rounded overflow-hidden divide-x">
                    <button 
                        @click="detailTab = 'alamat'"
                        :class="['px-6 py-2 text-sm font-medium transition', detailTab === 'alamat' ? 'bg-blue-500 text-white' : 'bg-white text-gray-600 hover:bg-gray-50']"
                    >
                        ALAMAT
                    </button>
                    <button 
                        @click="detailTab = 'orang_tua'"
                        :class="['px-6 py-2 text-sm font-medium transition', detailTab === 'orang_tua' ? 'bg-blue-500 text-white' : 'bg-white text-gray-600 hover:bg-gray-50']"
                    >
                        ORANG TUA
                    </button>
                    <button 
                        @click="detailTab = 'wali'"
                        :class="['px-6 py-2 text-sm font-medium transition', detailTab === 'wali' ? 'bg-blue-500 text-white' : 'bg-white text-gray-600 hover:bg-gray-50']"
                    >
                        WALI
                    </button>
                    <button 
                        @click="detailTab = 'kebutuhan_khusus'"
                        :class="['px-6 py-2 text-sm font-medium transition', detailTab === 'kebutuhan_khusus' ? 'bg-blue-500 text-white' : 'bg-white text-gray-600 hover:bg-gray-50']"
                    >
                        KEBUTUHAN KHUSUS
                    </button>
                </div>
            </div>

            <!-- Content: Alamat -->
            <div v-show="detailTab === 'alamat'">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Jalan</label>
                        <div class="p-2 bg-gray-50 border border-gray-200 rounded text-gray-700">{{ mahasiswa.alamat || '-' }}</div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Dusun</label>
                        <div class="p-2 bg-gray-50 border border-gray-200 rounded text-gray-700">{{ mahasiswa.dusun || '-' }}</div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">RT</label>
                            <div class="p-2 bg-gray-50 border border-gray-200 rounded text-gray-700">{{ mahasiswa.rt || '-' }}</div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">RW</label>
                            <div class="p-2 bg-gray-50 border border-gray-200 rounded text-gray-700">{{ mahasiswa.rw || '-' }}</div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Kelurahan</label>
                        <div class="p-2 bg-gray-50 border border-gray-200 rounded text-gray-700">{{ mahasiswa.kelurahan || '-' }}</div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Kode Pos</label>
                        <div class="p-2 bg-gray-50 border border-gray-200 rounded text-gray-700">{{ mahasiswa.kode_pos || '-' }}</div>
                    </div>
                </div>
                <div class="mt-6 p-4 bg-yellow-50 text-yellow-700 text-sm rounded border border-yellow-100 flex items-start gap-2">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p>Data lengkap alamat tersedia di menu edit atau setelah sinkronisasi detail.</p>
                </div>
            </div>

            <!-- Content: Orang Tua -->
            <div v-show="detailTab === 'orang_tua'">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                    <!-- Ayah -->
                    <div class="space-y-4">
                        <h4 class="text-center font-bold text-gray-700 text-lg mb-6">Ayah</h4>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">NIK</label>
                            <div class="p-2 bg-gray-50 border border-gray-200 rounded text-gray-700 min-h-[40px]">-</div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Nama Ayah</label>
                            <div class="p-2 bg-gray-50 border border-gray-200 rounded text-gray-700 min-h-[40px] uppercase">{{ mahasiswa.nama_ayah || '-' }}</div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Tanggal Lahir</label>
                            <div class="p-2 bg-gray-50 border border-gray-200 rounded text-gray-700 min-h-[40px]">-</div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Pendidikan</label>
                            <div class="p-2 bg-gray-50 border border-gray-200 rounded text-gray-700 min-h-[40px]">-</div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Pekerjaan</label>
                            <div class="p-2 bg-gray-50 border border-gray-200 rounded text-gray-700 min-h-[40px]">{{ mahasiswa.pekerjaan_ayah || '-' }}</div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Penghasilan</label>
                            <div class="p-2 bg-gray-50 border border-gray-200 rounded text-gray-700 min-h-[40px]">-</div>
                        </div>
                    </div>

                    <!-- Ibu -->
                    <div class="space-y-4">
                        <h4 class="text-center font-bold text-gray-700 text-lg mb-6">Ibu</h4>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">NIK</label>
                            <div class="p-2 bg-gray-50 border border-gray-200 rounded text-gray-700 min-h-[40px]">-</div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Nama Ibu</label>
                            <div class="p-2 bg-gray-50 border border-gray-200 rounded text-gray-700 min-h-[40px] uppercase">{{ mahasiswa.nama_ibu || '-' }}</div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Tanggal Lahir</label>
                            <div class="p-2 bg-gray-50 border border-gray-200 rounded text-gray-700 min-h-[40px]">-</div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Pendidikan</label>
                            <div class="p-2 bg-gray-50 border border-gray-200 rounded text-gray-700 min-h-[40px]">-</div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Pekerjaan</label>
                            <div class="p-2 bg-gray-50 border border-gray-200 rounded text-gray-700 min-h-[40px]">{{ mahasiswa.pekerjaan_ibu || '-' }}</div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Penghasilan</label>
                            <div class="p-2 bg-gray-50 border border-gray-200 rounded text-gray-700 min-h-[40px]">-</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content: Wali & Kebutuhan Khusus -->
            <div v-show="detailTab === 'wali'" class="p-8 text-center text-gray-400 italic">
                Data Wali belum tersedia
            </div>
            <div v-show="detailTab === 'kebutuhan_khusus'" class="p-8 text-center text-gray-400 italic">
                Data Kebutuhan Khusus belum tersedia
            </div>
        </div>
    </div>
</template>
