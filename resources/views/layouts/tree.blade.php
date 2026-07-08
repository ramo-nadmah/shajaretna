<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'شجرتنا' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        /* Livewire wraps the component in an anonymous div — make it fill <main> */
        main > div:first-child { height: 100%; display: flex; flex-direction: column; }
    </style>
</head>
<body class="overflow-hidden" style="height: 100dvh; display: flex; flex-direction: column;">

    <nav class="border-b border-white/7 bg-ground-raised/60 backdrop-blur-sm shrink-0 z-50" id="app-nav">
        <div class="px-4 py-3 flex items-center justify-between">
            <a href="{{ route('people.index') }}" class="text-gold text-lg font-semibold tracking-wide" style="font-family: 'Segoe UI', sans-serif;">
                شجرتنا
            </a>
            <div class="flex items-center gap-2 sm:gap-3">
                @auth
                    <a href="{{ route('tree') }}"
                       class="text-sm text-muted hover:text-parchment transition-colors px-2.5 py-1.5 rounded-lg hover:bg-white/5 {{ request()->routeIs('tree') ? 'text-gold' : '' }}">
                        الشجرة
                    </a>
                    <a href="{{ route('kinship') }}"
                       class="text-sm text-muted hover:text-parchment transition-colors px-2.5 py-1.5 rounded-lg hover:bg-white/5 {{ request()->routeIs('kinship') ? 'text-gold' : '' }}">
                        القرابة
                    </a>
                    <a href="{{ route('marriages') }}"
                       class="text-sm text-muted hover:text-parchment transition-colors px-2.5 py-1.5 rounded-lg hover:bg-white/5 {{ request()->routeIs('marriages') ? 'text-gold' : '' }}">
                        الزواجات
                    </a>
                    <a href="{{ route('people.index') }}"
                       class="text-sm text-muted hover:text-parchment transition-colors px-2.5 py-1.5 rounded-lg hover:bg-white/5 hidden sm:inline-flex {{ request()->routeIs('people.*') ? 'text-gold' : '' }}">
                        الأشخاص
                    </a>
                    <a href="{{ route('people.create') }}" class="btn-primary text-sm hidden sm:inline-flex">
                        + إضافة
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-muted text-xs hover:text-parchment transition-colors">
                            خروج
                        </button>
                    </form>
                @endauth
                @guest
                    <a href="{{ route('login') }}" class="btn-primary text-sm">دخول</a>
                @endguest
            </div>
        </div>
    </nav>

    <main class="flex-1 overflow-hidden">
        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>
