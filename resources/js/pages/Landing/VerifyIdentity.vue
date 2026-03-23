<script setup>
import { useForm } from '@inertiajs/vue3';
import DatePicker from 'primevue/datepicker';

const props = defineProps({
    mahasiswa_id: Number,
})

const form = useForm({
    nim: '',
    tanggal_lahir: '',
})

const submit = () => {
    form.post(`/dokumen/${props.mahasiswa_id}/verify`)
}
</script>

<template>
    <div class="min-h-screen flex items-center justify-center bg-linear-to-br from-blue-50 to-indigo-100 px-4">
        <div class="w-full max-w-md bg-white rounded-2xl shadow-xl p-8">
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-800">Verifikasi Identitas</h1>
                <p class="text-gray-500 mt-2">Masukkan NIM dan tanggal lahir untuk mengakses dokumen</p>
            </div>

            <form @submit.prevent="submit" class="space-y-5">
                <div>
                    <label for="nim" class="block text-sm font-medium text-gray-700 mb-1.5">NIM</label>
                    <input
                        id="nim"
                        v-model="form.nim"
                        type="text"
                        placeholder="Masukkan NIM"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                    />
                </div>

                <div>
                    <label for="tanggal_lahir" class="block text-sm font-medium text-gray-700 mb-1.5">Tanggal Lahir</label>
                    <DatePicker
                        id="tanggal_lahir"
                        :model-value="form.tanggal_lahir ? new Date(form.tanggal_lahir) : null"
                        @update:model-value="(val) => {
                            if (val && val instanceof Date) {
                                // Format to YYYY-MM-DD ignoring timezone shifts
                                const year = val.getFullYear();
                                const month = String(val.getMonth() + 1).padStart(2, '0');
                                const day = String(val.getDate()).padStart(2, '0');
                                form.tanggal_lahir = `${year}-${month}-${day}`;
                            } else {
                                form.tanggal_lahir = '';
                            }
                        }"
                        dateFormat="yy-mm-dd"
                        showIcon
                        class="w-full"
                    />
                </div>

                <div v-if="form.errors.identity || $page.props.errors?.identity" class="bg-red-50 border border-red-200 rounded-lg p-3">
                    <p class="text-sm text-red-600">{{ form.errors.identity || $page.props.errors.identity }}</p>
                </div>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full py-3 px-4 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50"
                >
                    <span v-if="form.processing">Memverifikasi...</span>
                    <span v-else>Verifikasi</span>
                </button>
            </form>

            <p class="text-center text-sm text-gray-400 mt-6">
                Data Anda dijaga kerahasiaannya
            </p>
        </div>
    </div>
</template>
