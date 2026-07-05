<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
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
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot ?? '' }}
                @yield('content')
            </main>
        </div>

        <script>
            // ── Tauri Interceptor for Downloads and PDF links ──
            if (window.__TAURI__ && window.__TAURI__.core) {
                console.log("[Tauri] Interceptor Active! IPC is available.");

                document.addEventListener('click', function(e) {
                    var a = e.target.closest('a');
                    if (a && a.href && (a.target === '_blank' || a.hasAttribute('download'))) {
                        e.preventDefault();
                        console.log("[Tauri] Intercepted link click: " + a.href);
                        window.__TAURI__.core.invoke('plugin:opener|open_url', { url: a.href })
                            .catch(function(err1) {
                                window.__TAURI__.core.invoke('plugin:opener|open', { path: a.href })
                                    .catch(function(err2) {
                                        console.error("[Tauri] Opener failed:", err1, err2);
                                        window.location.assign(a.href);
                                    });
                            });
                    }
                });

                var originalWindowOpen = window.open;
                window.open = function(url, target, features) {
                    if (target === '_blank' || target == null || target === '') {
                        console.log("[Tauri] Intercepted window.open: " + url);
                        var fullUrl = new URL(url, window.location.href).href;
                        window.__TAURI__.core.invoke('plugin:opener|open_url', { url: fullUrl })
                            .catch(function() {
                                window.__TAURI__.core.invoke('plugin:opener|open', { path: fullUrl })
                                    .catch(function() {
                                        originalWindowOpen(url, target, features);
                                    });
                            });
                        return null;
                    }
                    return originalWindowOpen(url, target, features);
                };
            } else {
                console.warn("[Tauri] Interceptor inactive. IPC is not available.");
            }
        </script>
    </body>
</html>
