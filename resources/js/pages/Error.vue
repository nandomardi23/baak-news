<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const goBack = () => window.history.back();

const props = defineProps<{
    status: number;
}>();

const errorData = computed(() => {
    const errors: Record<number, { title: string; description: string; icon: string }> = {
        403: {
            title: 'Akses Ditolak',
            description: 'Maaf, Anda tidak memiliki izin untuk mengakses halaman ini.',
            icon: '🔒',
        },
        404: {
            title: 'Halaman Tidak Ditemukan',
            description: 'Maaf, halaman yang Anda cari tidak ditemukan atau telah dipindahkan.',
            icon: '🔍',
        },
        419: {
            title: 'Sesi Kedaluwarsa',
            description: 'Sesi Anda telah berakhir. Silakan muat ulang halaman dan coba lagi.',
            icon: '⏱️',
        },
        500: {
            title: 'Kesalahan Server',
            description: 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            icon: '⚙️',
        },
        503: {
            title: 'Layanan Tidak Tersedia',
            description: 'Sistem sedang dalam pemeliharaan. Silakan coba lagi dalam beberapa saat.',
            icon: '🔧',
        },
    };

    return (
        errors[props.status] ?? {
            title: 'Terjadi Kesalahan',
            description: 'Terjadi kesalahan yang tidak terduga. Silakan coba lagi.',
            icon: '⚠️',
        }
    );
});
</script>

<template>
    <Head :title="`${status} - ${errorData.title}`" />

    <div class="error-page">
        <!-- Animated background -->
        <div class="error-bg">
            <div class="error-bg-orb error-bg-orb--1"></div>
            <div class="error-bg-orb error-bg-orb--2"></div>
            <div class="error-bg-orb error-bg-orb--3"></div>
        </div>

        <div class="error-content">
            <!-- Icon -->
            <div class="error-icon">
                <span>{{ errorData.icon }}</span>
            </div>

            <!-- Status code -->
            <h1 class="error-code">{{ status }}</h1>

            <!-- Title -->
            <h2 class="error-title">{{ errorData.title }}</h2>

            <!-- Description -->
            <p class="error-description">{{ errorData.description }}</p>

            <!-- Actions -->
            <div class="error-actions">
                <button @click="goBack" class="error-btn error-btn--secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m12 19-7-7 7-7" />
                        <path d="M19 12H5" />
                    </svg>
                    Kembali
                </button>
                <Link href="/" class="error-btn error-btn--primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8" />
                        <path d="M3 10a2 2 0 0 1 .709-1.528l7-5.999a2 2 0 0 1 2.582 0l7 5.999A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                    </svg>
                    Beranda
                </Link>
            </div>
        </div>
    </div>
</template>

<style scoped>
.error-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
    background: var(--background);
    font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
}

/* Animated background orbs */
.error-bg {
    position: absolute;
    inset: 0;
    z-index: 0;
    pointer-events: none;
}

.error-bg-orb {
    position: absolute;
    border-radius: 50%;
    filter: blur(80px);
    opacity: 0.15;
    animation: float 20s ease-in-out infinite;
}

.dark .error-bg-orb {
    opacity: 0.08;
}

.error-bg-orb--1 {
    width: 500px;
    height: 500px;
    background: linear-gradient(135deg, hsl(220 70% 55%), hsl(280 65% 55%));
    top: -150px;
    right: -100px;
    animation-delay: 0s;
}

.error-bg-orb--2 {
    width: 400px;
    height: 400px;
    background: linear-gradient(135deg, hsl(160 60% 45%), hsl(200 70% 50%));
    bottom: -100px;
    left: -80px;
    animation-delay: -7s;
}

.error-bg-orb--3 {
    width: 300px;
    height: 300px;
    background: linear-gradient(135deg, hsl(30 80% 55%), hsl(340 75% 55%));
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    animation-delay: -14s;
}

@keyframes float {
    0%, 100% { transform: translate(0, 0) scale(1); }
    25% { transform: translate(30px, -40px) scale(1.05); }
    50% { transform: translate(-20px, 20px) scale(0.95); }
    75% { transform: translate(15px, 30px) scale(1.02); }
}

.error-bg-orb--3 {
    animation-name: float-center;
}

@keyframes float-center {
    0%, 100% { transform: translate(-50%, -50%) scale(1); }
    25% { transform: translate(calc(-50% + 30px), calc(-50% - 40px)) scale(1.05); }
    50% { transform: translate(calc(-50% - 20px), calc(-50% + 20px)) scale(0.95); }
    75% { transform: translate(calc(-50% + 15px), calc(-50% + 30px)) scale(1.02); }
}

/* Content */
.error-content {
    position: relative;
    z-index: 1;
    text-align: center;
    padding: 2rem;
    max-width: 480px;
    animation: fadeInUp 0.6s ease-out both;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Icon */
.error-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: var(--muted);
    margin-bottom: 1.5rem;
    animation: pulse-icon 3s ease-in-out infinite;
}

.error-icon span {
    font-size: 2.25rem;
    line-height: 1;
}

@keyframes pulse-icon {
    0%, 100% { box-shadow: 0 0 0 0 hsl(0 0% 50% / 0.1); }
    50% { box-shadow: 0 0 0 16px hsl(0 0% 50% / 0); }
}

/* Status code */
.error-code {
    font-size: 7rem;
    font-weight: 800;
    line-height: 1;
    letter-spacing: -0.04em;
    margin-bottom: 0.5rem;
    background: linear-gradient(135deg, var(--foreground) 0%, var(--muted-foreground) 100%);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
}

/* Title */
.error-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--foreground);
    margin-bottom: 0.75rem;
}

/* Description */
.error-description {
    font-size: 1rem;
    color: var(--muted-foreground);
    line-height: 1.6;
    margin-bottom: 2.5rem;
}

/* Actions */
.error-actions {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.error-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1.5rem;
    font-size: 0.875rem;
    font-weight: 500;
    border-radius: var(--radius);
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s ease;
    border: none;
    outline: none;
}

.error-btn--primary {
    background: var(--primary);
    color: var(--primary-foreground);
    box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
}

.error-btn--primary:hover {
    opacity: 0.9;
    box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
    transform: translateY(-1px);
}

.error-btn--secondary {
    background: var(--secondary);
    color: var(--secondary-foreground);
    font-family: inherit;
}

.error-btn--secondary:hover {
    background: var(--accent);
    transform: translateY(-1px);
}

/* Responsive */
@media (max-width: 480px) {
    .error-code {
        font-size: 5rem;
    }
    .error-title {
        font-size: 1.25rem;
    }
    .error-actions {
        flex-direction: column;
    }
    .error-btn {
        width: 100%;
        justify-content: center;
    }
}
</style>
