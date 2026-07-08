@props([
    'title' => 'Weesia FibPath Analyzer',
    'withCsrf' => true,
])

<!doctype html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @if ($withCsrf)
            <meta name="csrf-token" content="{{ csrf_token() }}">
        @endif
        <title>{{ $title }}</title>
        <link rel="icon" type="image/svg+xml" href="{{ asset('brand/weesia-mark.svg') }}">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;600&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-ink-900 text-ink-100 antialiased">
        {{ $slot }}
    </body>
</html>
