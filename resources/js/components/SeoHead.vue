<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

interface Props {
    title: string;
    description?: string;
    image?: string;
    url?: string;
    type?: 'website' | 'article' | 'profile';
}

withDefaults(defineProps<Props>(), {
    description: 'Biro Administrasi Akademik - Layanan pengajuan dokumen akademik online mahasiswa STIKES Hang Tuah Tanjungpinang.',
    image: '/images/logo.gif',
    url: '',
    type: 'website',
});
</script>

<template>
    <Head :title="title">
        <!-- Primary Meta Tags -->
        <meta name="title" :content="title" />
        <meta name="description" :content="description" />

        <!-- Open Graph / Facebook -->
        <meta property="og:type" :content="type" />
        <meta v-if="url" property="og:url" :content="url" />
        <meta property="og:title" :content="title" />
        <meta property="og:description" :content="description" />
        <meta property="og:image" :content="image" />

        <!-- Twitter -->
        <meta property="twitter:card" content="summary_large_image" />
        <meta v-if="url" property="twitter:url" :content="url" />
        <meta property="twitter:title" :content="title" />
        <meta property="twitter:description" :content="description" />
        <meta property="twitter:image" :content="image" />
        
        <slot />
    </Head>
</template>
