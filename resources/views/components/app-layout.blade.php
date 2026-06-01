<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#1d4ed8">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
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
    </style>
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

    {{-- Bottom nav --}}
    <nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-100 z-40"
        style="box-shadow: 0 -1px 3px rgba(0,0,0,0.06); padding-bottom: env(safe-area-inset-bottom);">
        <div class="flex justify-around items-center px-2 py-1 max-w-2xl mx-auto">

            @if(auth()->user()->isAdmin())

                {{-- Métricas --}}
                <a href="/dashboard" class="nav-item flex flex-col items-center py-2 px-3 {{ request()->is('dashboard') ? 'active' : '' }}">
                    <div class="nav-icon w-10 h-8 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>
                        </svg>
                    </div>
                    <span class="text-xs font-semibold mt-0.5">Métricas</span>
                </a>

                {{-- Órdenes --}}
                <a href="/orders" class="nav-item flex flex-col items-center py-2 px-3 {{ request()->is('orders*') || request()->is('production-orders*') ? 'active' : '' }}">
                    <div class="nav-icon w-10 h-8 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                    </div>
                    <span class="text-xs font-semibold mt-0.5">Órdenes</span>
                </a>

                {{-- Clientes --}}
                <a href="/clients" class="nav-item flex flex-col items-center py-2 px-3 {{ request()->is('clients*') ? 'active' : '' }}">
                    <div class="nav-icon w-10 h-8 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
                        </svg>
                    </div>
                    <span class="text-xs font-semibold mt-0.5">Clientes</span>
                </a>

                {{-- Productos --}}
                <a href="/products" class="nav-item flex flex-col items-center py-2 px-3 {{ request()->is('products*') ? 'active' : '' }}">
                    <div class="nav-icon w-10 h-8 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>
                        </svg>
                    </div>
                    <span class="text-xs font-semibold mt-0.5">Productos</span>
                </a>

                {{-- Materiales --}}
                <a href="/material-purchases" class="nav-item flex flex-col items-center py-2 px-3 {{ request()->is('material-purchases*') || request()->is('materials*') || request()->is('suppliers*') ? 'active' : '' }}">
                    <div class="nav-icon w-10 h-8 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.25-8.25 4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125"/>
                        </svg>
                    </div>
                    <span class="text-xs font-semibold mt-0.5">Materiales</span>
                </a>

                {{-- Config con dropdown funcional --}}
                <div class="relative" x-data="{ open: false }">
                    <button type="button"
                        @click="open = !open"
                        class="nav-item flex flex-col items-center py-2 px-3 {{ request()->is('users*') || request()->is('stages*') || request()->is('suppliers*') || request()->is('materials*') ? 'active' : '' }}">
                        <div class="nav-icon w-10 h-8 flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <span class="text-xs font-semibold mt-0.5">Config</span>
                    </button>

                    {{-- Dropdown hacia arriba --}}
                    <div x-show="open"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 translate-y-2"
                        @click.outside="open = false"
                        class="absolute bottom-full right-0 mb-2 bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden w-52"
                        style="display: none;">

                        <a href="/users" @click="open = false"
                            class="flex items-center gap-3 px-4 py-3.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 border-b border-gray-100">
                            <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
                            </svg>
                            Usuarios
                        </a>

                        <a href="/stages" @click="open = false"
                            class="flex items-center gap-3 px-4 py-3.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 border-b border-gray-100">
                            <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 5.25h16.5m-16.5 4.5h16.5m-16.5 4.5h16.5m-16.5 4.5h16.5"/>
                            </svg>
                            Etapas
                        </a>
                    </div>
                </div>

            @else
                {{-- Nav simple para operativos --}}
                <a href="/orders" class="nav-item flex flex-col items-center py-2 px-4 {{ request()->is('orders*') ? 'active' : '' }}">
                    <div class="nav-icon w-14 h-10 flex items-center justify-center">
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                    </div>
                    <span class="text-sm font-bold mt-0.5">Mis tareas</span>
                </a>
            @endif

        </div>
    </nav>

    <script>
        // Desregistrar cualquier service worker existente
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.getRegistrations().then(regs => {
                regs.forEach(reg => reg.unregister());
            });
            // Limpiar todo el caché
            if ('caches' in window) {
                caches.keys().then(keys => {
                    keys.forEach(key => caches.delete(key));
                });
            }
        }
    </script>
</body>
</html>