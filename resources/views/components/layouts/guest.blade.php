<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <script>
        const theme = localStorage.getItem('theme')

        if (
            theme === 'dark' ||
            ((!theme || theme === 'system') &&
                window.matchMedia('(prefers-color-scheme: dark)').matches)
        ) {
            document.documentElement.classList.add('dark')
        }
    </script>

    <title>{{ $title ?? 'GDPS ERP' }}</title>

    @filamentStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="antialiased bg-gray-50 dark:bg-gray-950 transition-colors duration-300">
    {{ $slot }}

    @livewire('notifications')

    @stack('scripts')
    @filamentScripts
</body>

</html>
