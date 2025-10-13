<!DOCTYPE html>
@php
  $theme = auth()->user()->theme ?? null;
@endphp
<html lang="ja" @if($theme) data-theme="{{ $theme }}" @endif>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-base-200 text-base-content">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-base-100 border-b border-base-300">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
        @livewireScripts
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
        <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/fr.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ja.js"></script>
        <script>
          window.initDatepicker = (el, value, locale, onChange) => {
            const loc = locale === 'ja' ? flatpickr.l10ns.ja
                     : locale === 'fr' ? flatpickr.l10ns.fr
                     : flatpickr.l10ns.default;
            const fp = flatpickr(el, {
              dateFormat: 'Y-m-d',
              defaultDate: value || null,
              locale: loc,
              allowInput: true,
              onChange: onChange || (()=>{}),
            });
            return fp;
          };
        </script>
    </body>
</html>
