<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Monitoring Hujan' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        ink: '#10243f',
                        aqua: '#22d3ee',
                        mint: '#34d399',
                        sand: '#f4f4e8'
                    },
                    fontFamily: {
                        sans: ['Poppins', 'ui-sans-serif', 'system-ui'],
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            background: radial-gradient(circle at top left, #d6fff5 0%, #f5fbff 42%, #f5f2e8 100%);
        }

        .mobile-drawer-open {
            overflow: hidden;
        }
    </style>
    @stack('head')
</head>
<body class="text-slate-800">
    <div class="min-h-screen lg:grid lg:grid-cols-[260px_1fr]">
        <aside class="hidden lg:flex flex-col border-r border-white/60 bg-ink text-white">
            <div class="px-6 py-5 border-b border-white/20">
                <p class="text-xs uppercase tracking-[0.2em] text-aqua">Rain Monitor</p>
                <h1 class="text-lg font-semibold">Sistem Monitoring</h1>
            </div>
            <nav class="flex-1 px-4 py-5 space-y-2 text-sm">
                <a href="{{ route('dashboard') }}" class="block px-3 py-2 rounded-lg hover:bg-white/10">Dashboard</a>
                <a href="{{ route('logs.index') }}" class="block px-3 py-2 rounded-lg hover:bg-white/10">Log Data</a>
                <a href="{{ route('audio.index') }}" class="block px-3 py-2 rounded-lg hover:bg-white/10">Audio</a>
                @if(auth()->user()?->isAdmin())
                    <a href="{{ route('settings.index') }}" class="block px-3 py-2 rounded-lg hover:bg-white/10">Settings</a>
                    <a href="{{ route('users.index') }}" class="block px-3 py-2 rounded-lg hover:bg-white/10">Manajemen Akun</a>
                @endif
            </nav>
            <div class="px-5 py-4 border-t border-white/20 text-xs">
                Login sebagai {{ auth()->user()?->role }}
            </div>
        </aside>

        <main class="p-4 md:p-6 lg:p-8">
            <header class="mb-6 flex flex-wrap gap-3 items-center justify-between">
                <div>
                    <button
                        type="button"
                        id="mobileMenuButton"
                        class="mb-3 inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 lg:hidden"
                        aria-controls="mobileMenu"
                        aria-expanded="false"
                        aria-label="Buka menu navigasi"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                        Menu
                    </button>
                    <h2 class="text-2xl font-semibold text-ink">{{ $heading ?? 'Monitoring Hujan' }}</h2>
                    <p class="text-sm text-slate-500">{{ $subheading ?? 'Pantau data sensor, lokasi alat, dan rekaman audio.' }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="hidden sm:block">
                    @csrf
                    <button class="rounded-xl bg-ink px-4 py-2 text-sm font-medium text-white hover:bg-slate-900">Logout</button>
                </form>
            </header>

            <div id="mobileMenuOverlay" class="fixed inset-0 z-40 hidden bg-slate-900/45 lg:hidden"></div>
            <aside
                id="mobileMenu"
                class="fixed inset-y-0 left-0 z-50 w-72 max-w-[86vw] -translate-x-full border-r border-slate-200 bg-white shadow-2xl transition-transform duration-200 ease-out lg:hidden"
                aria-hidden="true"
            >
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.2em] text-cyan-600">Rain Monitor</p>
                        <h3 class="text-base font-semibold text-ink">Navigasi</h3>
                    </div>
                    <button
                        type="button"
                        id="mobileMenuClose"
                        class="rounded-lg border border-slate-200 bg-white p-2 text-slate-600 hover:bg-slate-50"
                        aria-label="Tutup menu navigasi"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <nav class="space-y-2 px-4 py-4 text-sm">
                    <a href="{{ route('dashboard') }}" class="mobile-menu-link block rounded-lg px-3 py-2.5 text-slate-700 hover:bg-sky-50 hover:text-sky-700 {{ request()->routeIs('dashboard') ? 'bg-sky-100 font-semibold text-sky-700' : '' }}">Dashboard</a>
                    <a href="{{ route('logs.index') }}" class="mobile-menu-link block rounded-lg px-3 py-2.5 text-slate-700 hover:bg-sky-50 hover:text-sky-700 {{ request()->routeIs('logs.*') ? 'bg-sky-100 font-semibold text-sky-700' : '' }}">Log Data</a>
                    <a href="{{ route('audio.index') }}" class="mobile-menu-link block rounded-lg px-3 py-2.5 text-slate-700 hover:bg-sky-50 hover:text-sky-700 {{ request()->routeIs('audio.*') ? 'bg-sky-100 font-semibold text-sky-700' : '' }}">Audio</a>
                    @if(auth()->user()?->isAdmin())
                        <a href="{{ route('settings.index') }}" class="mobile-menu-link block rounded-lg px-3 py-2.5 text-slate-700 hover:bg-sky-50 hover:text-sky-700 {{ request()->routeIs('settings.*') ? 'bg-sky-100 font-semibold text-sky-700' : '' }}">Settings</a>
                        <a href="{{ route('users.index') }}" class="mobile-menu-link block rounded-lg px-3 py-2.5 text-slate-700 hover:bg-sky-50 hover:text-sky-700 {{ request()->routeIs('users.*') ? 'bg-sky-100 font-semibold text-sky-700' : '' }}">Manajemen Akun</a>
                    @endif
                </nav>

                <div class="mt-auto border-t border-slate-100 px-5 py-4">
                    <p class="mb-3 text-xs text-slate-500">Login sebagai {{ auth()->user()?->role }}</p>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="w-full rounded-lg bg-ink px-4 py-2 text-sm font-medium text-white hover:bg-slate-900">Logout</button>
                    </form>
                </div>
            </aside>

            @if(session('status'))
                <div class="mb-4 rounded-xl border border-mint/50 bg-mint/10 px-4 py-3 text-sm text-green-900">
                    {{ session('status') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 rounded-xl border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <ul class="list-disc ml-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{ $slot }}
        </main>
    </div>

    <script>
        (function () {
            const menu = document.getElementById('mobileMenu');
            const overlay = document.getElementById('mobileMenuOverlay');
            const openButton = document.getElementById('mobileMenuButton');
            const closeButton = document.getElementById('mobileMenuClose');

            if (!menu || !overlay || !openButton || !closeButton) {
                return;
            }

            const menuLinks = menu.querySelectorAll('.mobile-menu-link');

            function openMenu() {
                menu.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
                document.body.classList.add('mobile-drawer-open');
                menu.setAttribute('aria-hidden', 'false');
                openButton.setAttribute('aria-expanded', 'true');
            }

            function closeMenu() {
                menu.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
                document.body.classList.remove('mobile-drawer-open');
                menu.setAttribute('aria-hidden', 'true');
                openButton.setAttribute('aria-expanded', 'false');
            }

            openButton.addEventListener('click', openMenu);
            closeButton.addEventListener('click', closeMenu);
            overlay.addEventListener('click', closeMenu);

            menuLinks.forEach((link) => {
                link.addEventListener('click', closeMenu);
            });

            window.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    closeMenu();
                }
            });

            window.addEventListener('resize', () => {
                if (window.innerWidth >= 1024) {
                    closeMenu();
                }
            });
        })();
    </script>

    @stack('scripts')
</body>
</html>
