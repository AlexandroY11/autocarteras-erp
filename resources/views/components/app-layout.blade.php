<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="theme-color" content="#1d4ed8">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $title ?? 'AutoCarteras Cali' }}</title>
    {{-- <link rel="manifest" href="/manifest.json"> --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { -webkit-tap-highlight-color: transparent; }
        body { font-family: system-ui, -apple-system, sans-serif; }
        .nav-item { transition: all 0.15s ease; color: #9ca3af; }
        .nav-item.active { color: #1d4ed8; }
        .nav-item.active .nav-icon { background: #eff6ff; border-radius: 12px; }
        .card { border-radius: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.08), 0 1px 2px rgba(0,0,0,0.06); }
        .btn-primary { background: #1d4ed8; color: white; border-radius: 14px; font-weight: 700; font-size: 16px; padding: 14px 20px; border: none; width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px; transition: background 0.15s; cursor: pointer; }
        .btn-primary:active { background: #1e40af; transform: scale(0.98); }
        .status-badge { border-radius: 20px; font-size: 12px; font-weight: 700; padding: 4px 12px; }
        .input-field { border: 2px solid #e5e7eb; border-radius: 12px; padding: 14px 16px; font-size: 16px; width: 100%; transition: border-color 0.15s; background: white; box-sizing: border-box; }
        .input-field:focus { outline: none; border-color: #1d4ed8; }
        .section-title { font-size: 13px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; }
        select.input-field { appearance: auto; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>
<body class="bg-gray-50 min-h-screen">

    {{-- Top navbar --}}
    <header class="bg-white border-b border-gray-100 px-4 py-3 flex items-center justify-between sticky top-0 z-40"
        style="box-shadow: 0 1px 3px rgba(0,0,0,0.06);">
        <div class="flex items-center gap-3">
            <img src="/logo.png" alt="AutoCarteras" class="h-8 w-auto" onerror="this.style.display='none'">
            <div>
                <div class="font-bold text-gray-900 text-base leading-tight">AutoCarteras</div>
                <div class="text-xs text-gray-400 leading-tight">Sistema de Producción</div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <div class="text-right">
                <div class="text-sm font-semibold text-gray-700">{{ auth()->user()->name }}</div>
                <div class="text-xs text-gray-400">
                    {{ match(auth()->user()->role) {
                        'admin'    => 'Administrador',
                        'director' => 'Director',
                        default    => 'Trabajador',
                    } }}
                </div>
            </div>
            <form method="POST" action="/logout">
                @csrf
                <button type="submit" class="w-9 h-9 bg-gray-100 rounded-full flex items-center justify-center text-gray-500 active:bg-gray-200" title="Cerrar sesión">
                    {{-- Heroicon: arrow-right-on-rectangle --}}
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                    </svg>
                </button>
            </form>
        </div>
    </header>

    {{-- Contenido --}}
    <main class="pb-24 px-4 max-w-2xl mx-auto">
        @if(session('success'))
        <div class="mt-3 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-2xl flex items-center gap-2">
            <svg class="w-5 h-5 text-green-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            {{ session('success') }}
        </div>
        @endif

        @if($errors->any())
        <div class="mt-3 bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-2xl flex items-center gap-2">
            <svg class="w-5 h-5 text-red-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            {{ $errors->first() }}
        </div>
        @endif

        {{ $slot }}
    </main>

    {{-- NAV INFERIOR --}}
    <div class="fixed bottom-0 left-0 right-0 bg-white/90 backdrop-blur-lg border-t border-gray-100 z-50 shadow-[0_-10px_30px_rgba(0,0,0,0.05)]">
        <nav class="flex items-center overflow-x-auto no-scrollbar py-3 px-6 scroll-smooth">
            <div class="flex items-center gap-8 mx-auto min-w-max">

                {{-- Inicio — siempre visible --}}
                <a href="/dashboard" class="flex flex-col items-center gap-1 shrink-0 min-w-[60px] {{ request()->is('dashboard') ? 'text-blue-600' : 'text-gray-400' }}">
                    <div class="p-1 rounded-xl {{ request()->is('dashboard') ? 'bg-blue-50' : '' }}">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25a2.25 2.25 0 01-2.25-2.25v-2.25z" />
                        </svg>
                    </div>
                    <span class="text-[10px] font-black uppercase tracking-tighter">Inicio</span>
                </a>

                {{-- Órdenes — siempre visible --}}
                <a href="/orders" class="flex flex-col items-center gap-1 shrink-0 min-w-[60px] {{ request()->is('orders*') ? 'text-blue-600' : 'text-gray-400' }}">
                    <div class="p-1 rounded-xl {{ request()->is('orders*') ? 'bg-blue-50' : '' }}">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                        </svg>
                    </div>
                    <span class="text-[10px] font-black uppercase tracking-tighter">Órdenes</span>
                </a>

                {{-- Solo admin --}}
                @if(auth()->user()->isAdmin())

                    {{-- Clientes --}}
                    <a href="/clients" class="flex flex-col items-center gap-1 shrink-0 min-w-[60px] {{ request()->is('clients*') ? 'text-blue-600' : 'text-gray-400' }}">
                        <div class="p-1 rounded-xl {{ request()->is('clients*') ? 'bg-blue-50' : '' }}">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                            </svg>
                        </div>
                        <span class="text-[10px] font-black uppercase tracking-tighter">Clientes</span>
                    </a>

                    {{-- Config (productos, equipo, etapas) --}}
                    <div class="relative shrink-0 min-w-[60px]" x-data="{ open: false }" @click.outside="open = false">
                        <button @click="open = !open"
                            class="flex flex-col items-center gap-1 w-full cursor-pointer {{ request()->is('products*') || request()->is('users*') || request()->is('stages*') ? 'text-blue-600' : 'text-gray-400' }}">
                            <div class="p-1 rounded-xl {{ request()->is('products*') || request()->is('users*') || request()->is('stages*') ? 'bg-blue-50' : '' }}">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.28c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                            </div>
                            <span class="text-[10px] font-black uppercase tracking-tighter">Config</span>
                        </button>

                        <template x-teleport="body">
                            <div
                                x-show="open"
                                @click.outside="open = false"
                                x-transition:enter="transition ease-out duration-150"
                                x-transition:enter-start="opacity-0 translate-y-2"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-100"
                                x-transition:leave-start="opacity-100 translate-y-0"
                                x-transition:leave-end="opacity-0 translate-y-2"
                                class="fixed bottom-20 left-1/2 -translate-x-1/2 bg-white border border-gray-100 rounded-2xl shadow-xl py-1.5 min-w-[160px] z-[9999]">

                                <a href="/products" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium {{ request()->is('products*') ? 'text-blue-600' : 'text-gray-700' }} hover:bg-gray-50 rounded-xl mx-1">
                                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                                    </svg>
                                    Productos
                                </a>

                                <a href="/users" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium {{ request()->is('users*') ? 'text-blue-600' : 'text-gray-700' }} hover:bg-gray-50 rounded-xl mx-1">
                                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                                    </svg>
                                    Equipo
                                </a>

                                <a href="/stages" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium {{ request()->is('stages*') ? 'text-blue-600' : 'text-gray-700' }} hover:bg-gray-50 rounded-xl mx-1">
                                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                                    </svg>
                                    Etapas
                                </a>
                            </div>
                        </template>
                    </div>

                @endif {{-- fin @if isAdmin --}}

                {{-- Perfil — siempre visible --}}
                <a href="/profile" class="flex flex-col items-center gap-1 shrink-0 min-w-[60px] {{ request()->is('profile') ? 'text-blue-600' : 'text-gray-400' }}">
                    <div class="p-1 rounded-xl {{ request()->is('profile') ? 'bg-blue-50' : '' }}">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                    </div>
                    <span class="text-[10px] font-black uppercase tracking-tighter">Perfil</span>
                </a>

                {{-- Salir — siempre visible --}}
                <form method="POST" action="{{ route('logout') }}" class="shrink-0 min-w-[60px]">
                    @csrf
                    <button class="flex flex-col items-center gap-1 text-red-400 w-full">
                        <div class="p-1">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                            </svg>
                        </div>
                        <span class="text-[10px] font-black uppercase tracking-tighter">Salir</span>
                    </button>
                </form>

            </div>
        </nav>
    </div>


    <script>
    // Limpieza de Service Workers
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.getRegistrations().then(regs => regs.forEach(reg => reg.unregister()));
    }

    // Escuchar mensajes de sesión de Laravel
    document.addEventListener('DOMContentLoaded', () => {
        @if(session('success'))
            window.showAlert.success("{{ session('success') }}");
        @endif

        @if(session('error'))
            window.showAlert.error("{{ session('error') }}");
        @endif
    });
</script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.8/locales/es.global.min.js'></script>
</body>
</html>