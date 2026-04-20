<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"  @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- Inline script to detect system dark mode preference and apply it immediately --}}
        <script>
            (function() {
                const appearance = '{{ $appearance ?? "system" }}';

                if (appearance === 'system') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                    if (prefersDark) {
                        document.documentElement.classList.add('dark');
                    }
                }
            })();
        </script>

        {{-- Inline style to set the HTML background color based on our theme in app.css --}}
        <style>
            html {
                background-color: oklch(1 0 0);
            }

            html.dark {
                background-color: oklch(0.145 0 0);
            }
        </style>

        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="description" content="BAAK STIKES Hang Tuah Tanjungpinang - Layanan administrasi akademik dan pengajuan surat serta dokumen akademik secara digital untuk mahasiswa.">
        <meta name="keywords" content="BAAK, STIKES Hang Tuah, Tanjungpinang, administrasi akademik, pengajuan surat, dokumen akademik, KRS, KHS, transkrip nilai">
        <meta name="robots" content="index, follow">
        <meta name="author" content="BAAK STIKES Hang Tuah Tanjungpinang">
        <link rel="canonical" href="{{ url()->current() }}">

        <title inertia>{{ config('app.name', 'BAAK STIKES Hang Tuah Tanjungpinang - Layanan Administrasi Akademik') }}</title>

        {{-- Structured Data (JSON-LD) --}}
        <script type="application/ld+json">
        {
            "@@context": "https://schema.org",
            "@type": "EducationalOrganization",
            "name": "BAAK STIKES Hang Tuah Tanjungpinang",
            "alternateName": "Biro Administrasi Akademik Kemahasiswaan STIKES Hang Tuah",
            "url": "{{ config('app.url') }}",
            "logo": "{{ asset('images/logo.gif') }}",
            "description": "Biro Administrasi Akademik dan Kemahasiswaan STIKES Hang Tuah Tanjungpinang menyediakan layanan pengajuan surat dan dokumen akademik secara digital.",
            "address": {
                "@type": "PostalAddress",
                "streetAddress": "Jl. WR Supratman",
                "addressLocality": "Tanjungpinang Timur",
                "addressRegion": "Kepulauan Riau",
                "addressCountry": "ID"
            },
            "telephone": "(0771) 4440071",
            "email": "stikestpi@gmail.com",
            "sameAs": []
        }
        </script>
        <script type="application/ld+json">
        {
            "@@context": "https://schema.org",
            "@type": "WebSite",
            "name": "BAAK STIKES Hang Tuah Tanjungpinang",
            "url": "{{ config('app.url') }}",
            "potentialAction": {
                "@type": "SearchAction",
                "target": "{{ config('app.url') }}/search?search={search_term_string}",
                "query-input": "required name=search_term_string"
            }
        }
        </script>

        {{-- Google Analytics - Ganti UA-XXXXXXXXX dengan ID tracking Anda --}}
        {{-- 
        <script async src="https://www.googletagmanager.com/gtag/js?id=G-XXXXXXXXXX"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', 'G-XXXXXXXXXX');
        </script>
        --}}

        <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16.png">
        <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600&display=swap" rel="stylesheet" />
        
        @routes
        @vite(['resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
