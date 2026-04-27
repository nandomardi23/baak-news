<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Props {
    title: string;
    description?: string;
    image?: string;
    url?: string;
    type?: 'website' | 'article' | 'profile';
    keywords?: string;
}

const props = withDefaults(defineProps<Props>(), {
    description: 'BAAK STIKES Hang Tuah Tanjungpinang - Layanan administrasi akademik dan pengajuan surat serta dokumen akademik secara digital untuk mahasiswa.',
    image: '/images/logo.gif',
    url: '',
    type: 'website',
    keywords: 'BAAK, STIKES Hang Tuah, Tanjungpinang, administrasi akademik, pengajuan surat, dokumen akademik',
});

const currentUrl = computed(() => props.url || (typeof window !== 'undefined' ? window.location.href : ''));
const absoluteImage = computed(() => {
    if (props.image.startsWith('http')) return props.image;
    const origin = typeof window !== 'undefined' ? window.location.origin : '';
    return origin + props.image;
});
</script>

<template>
    <Head :title="title">
        <!-- Primary Meta Tags -->
        <meta name="title" :content="title" />
        <meta name="description" :content="description" />
        <meta name="keywords" :content="keywords" />
        <link rel="canonical" :href="currentUrl" />

        <!-- Open Graph / Facebook -->
        <meta property="og:type" :content="type" />
        <meta property="og:url" :content="currentUrl" />
        <meta property="og:title" :content="title" />
        <meta property="og:description" :content="description" />
        <meta property="og:image" :content="absoluteImage" />
        <meta property="og:locale" content="id_ID" />
        <meta property="og:site_name" content="BAAK STIKES Hang Tuah Tanjungpinang" />

        <!-- Twitter -->
        <meta property="twitter:card" content="summary_large_image" />
        <meta property="twitter:url" :content="currentUrl" />
        <meta property="twitter:title" :content="title" />
        <meta property="twitter:description" :content="description" />
        <meta property="twitter:image" :content="absoluteImage" />
        
        <slot />
    </Head>
</template>
