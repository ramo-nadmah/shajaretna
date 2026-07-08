<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'شجرتنا' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen">

    <nav class="border-b border-white/7 bg-ground-raised/60 backdrop-blur-sm sticky top-0 z-50">
        <div class="max-w-4xl mx-auto px-5 py-3 flex items-center justify-between gap-3">

            <a href="{{ route('people.index') }}" class="site-brand shrink-0">
                شجرتنا
            </a>

            <div class="flex items-center gap-1 sm:gap-2">
                @auth
                    {{-- Primary link visible on all screen sizes --}}
                    <a href="{{ route('tree') }}"
                       class="nav-link {{ request()->routeIs('tree') ? 'active' : '' }}">
                        الشجرة
                    </a>

                    {{-- Secondary links hidden on small screens --}}
                    <a href="{{ route('kinship') }}"
                       class="nav-link hidden md:inline-flex {{ request()->routeIs('kinship') ? 'active' : '' }}">
                        القرابة
                    </a>
                    <a href="{{ route('marriages') }}"
                       class="nav-link hidden md:inline-flex {{ request()->routeIs('marriages') ? 'active' : '' }}">
                        الزواجات
                    </a>
                    <a href="{{ route('people.index') }}"
                       class="nav-link hidden md:inline-flex {{ request()->routeIs('people.*') ? 'active' : '' }}">
                        الأشخاص
                    </a>

                    <a href="{{ route('people.create') }}" class="btn-primary text-sm hidden md:inline-flex">
                        + إضافة
                    </a>

                    <span class="text-white/20 text-sm hidden md:inline mx-1">|</span>

                    <span class="text-muted text-sm hidden md:inline truncate max-w-32"
                          title="{{ auth()->user()->first_name }}">
                        {{ auth()->user()->first_name }}
                    </span>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="nav-logout">
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

    <main class="max-w-4xl mx-auto px-5 py-10">
        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>
