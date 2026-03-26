<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, shrink-to-fit=no">
        <meta name="theme-color" content="#800000">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <meta name="apple-mobile-web-app-title" content="PSAU Parking">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <style>
            html, body { -webkit-overflow-scrolling: touch; -webkit-tap-highlight-color: transparent; }
        </style>

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        {{-- FIX 5 — BFCache Back-Button Handler --}}
        <script>
            // Handle BFCache: force server reload when Back is pressed
            window.addEventListener('pageshow', function(event) {
                if (event.persisted ||
                    (window.performance &&
                     window.performance.navigation &&
                     window.performance.navigation.type === 2))
                {
                    window.location.reload(true);
                }
            });

            // Prevent form resubmission on back button
            if (window.history.replaceState) {
                window.history.replaceState(null, null, window.location.href);
            }

            // Redirect unauthenticated back-button access
            @guest
                var path = window.location.pathname;
                var publicPaths = ['/', '/login', '/register', '/forgot-password'];
                if (publicPaths.indexOf(path) === -1) {
                    window.location.href = "{{ route('login') }}";
                }
            @endguest
        </script>
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-accent">
            @include('layouts.navigation')

            {{-- FIX 9 — Flash Messages --}}
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                @if(session('success'))
                    <div class="bg-green-50 border border-green-300 text-green-800 rounded-lg px-4 py-3 flex items-center gap-3 mb-4" role="alert">
                        <span>✅</span>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-red-50 border border-red-300 text-red-800 rounded-lg px-4 py-3 flex items-center gap-3 mb-4" role="alert">
                        <span>❌</span>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="bg-yellow-50 border border-yellow-300 text-yellow-800 rounded-lg px-4 py-3 flex items-center gap-3 mb-4" role="alert">
                        <span>⚠️</span>
                        <span>{{ session('warning') }}</span>
                    </div>
                @endif

                @if(session('status'))
                    <div class="bg-blue-50 border border-blue-300 text-blue-800 rounded-lg px-4 py-3 flex items-center gap-3 mb-4" role="alert">
                        <span>ℹ️</span>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif
            </div>

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow-sm border-b border-gray-200">
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
    </body>

    {{-- Fix 6: Idle Session Timeout (15 minutes) --}}
    @auth
    <script>
        (function () {
            const IDLE_MS = 15 * 60 * 1000; // 15 minutes
            const CSRF    = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            let idleTimer;

            function resetIdle() {
                clearTimeout(idleTimer);
                idleTimer = setTimeout(function () {
                    fetch('{{ route('logout') }}', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json' }
                    }).finally(function () {
                        window.location.href = '{{ route('login') }}';
                    });
                }, IDLE_MS);
            }

            ['mousemove','mousedown','keydown','touchstart','scroll','click']
                .forEach(evt => document.addEventListener(evt, resetIdle, true));

            resetIdle();
        })();
    </script>
    @endauth
</html>
