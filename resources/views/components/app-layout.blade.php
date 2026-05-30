<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#1d4ed8">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>{{ $title ?? 'AutoCarteras Cali' }}</title>
    <link rel="manifest" href="/manifest.json">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { -webkit-tap-highlight-color: transparent; }
        body { font-family: 'system-ui', sans-serif; }
        .nav-item { transition: all 0.15s ease; }
        .nav-item.active { color: #1d4ed8; }
        .nav-item.active .nav-icon { background: #eff6ff; border-radius: 12px; }
        .card { border-radius: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.08), 0 1px 2px rgba(0,0,0,0.06); }
        .btn-primary { background: #1d4ed8; color: white; border-radius: 14px; font-weight: 700; font-size: 16px; padding: 14px 20px; border: none; width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px; transition: background 0.15s; }
        .btn-primary:active { background: #1e40af; transform: scale(0.98); }
        .status-badge { border-radius: 20px; font-size: 12px; font-weight: 700; padding: 4px 12px; }
        .input-field { border: 2px solid #e5e7eb; border-radius: 12px; padding: 14px 16px; font-size: 16px; width: 100%; transition: border-color 0.15s; background: white; }
        .input-field:focus { outline: none; border-color: #1d4ed8; }
        .section-title { font-size: 13px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; }
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
                <div class="text-xs text-gray-400">{{ auth()->user()->isAdmin() ? 'Administrador' : 'Trabajador' }}</div>
            </div>
            <form method="POST" action="/logout">
                @csrf
                <button class="w-9 h-9 bg-gray-100 rounded-full flex items-center justify-center text-gray-500 active:bg-gray-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
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
    <nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-100 z-40 safe-area-pb"
        style="box-shadow: 0 -1px 3px rgba(0,0,0,0.06); padding-bottom: env(safe-area-inset-bottom);">
        <div class="flex justify-around items-center px-2 py-1 max-w-2xl mx-auto">
            @if(auth()->user()->isAdmin())
                {{-- Nav completo para admin --}}
                <a href="/dashboard" class="nav-item flex flex-col items-center py-2 px-3 {{ request()->is('dashboard') ? 'active' : '' }}">
                    <div class="nav-icon w-10 h-8 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </div>
                    <span class="text-xs font-semibold mt-0.5">Métricas</span>
                </a>
                <a href="/orders" class="nav-item flex flex-col items-center py-2 px-3 {{ request()->is('orders*') || request()->is('production-orders*') ? 'active' : '' }}">
                    <div class="nav-icon w-10 h-8 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                    <span class="text-xs font-semibold mt-0.5">Órdenes</span>
                </a>
                <a href="/clients" class="nav-item flex flex-col items-center py-2 px-3 {{ request()->is('clients*') ? 'active' : '' }}">
                    <div class="nav-icon w-10 h-8 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <span class="text-xs font-semibold mt-0.5">Clientes</span>
                </a>
                <a href="/products" class="nav-item flex flex-col items-center py-2 px-3 {{ request()->is('products*') ? 'active' : '' }}">
                    <div class="nav-icon w-10 h-8 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    <span class="text-xs font-semibold mt-0.5">Productos</span>
                </a>
                <a href="/users" class="nav-item flex flex-col items-center py-2 px-3 {{ request()->is('users*') || request()->is('stages*') ? 'active' : '' }}"
                    x-data="{ open: false }" @click.prevent="open = !open">
                    <div class="nav-icon w-10 h-8 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <span class="text-xs font-semibold mt-0.5">Config</span>
                    <div x-show="open" @click.outside="open = false"
                        class="absolute bottom-16 right-2 bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden w-44" x-transition>
                        <a href="/users" class="flex items-center gap-3 px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 border-b border-gray-100">👥 Usuarios</a>
                        <a href="/stages" class="flex items-center gap-3 px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50">⚙️ Etapas</a>
                    </div>
                </a>
            @else
                {{-- Nav simple para operativos --}}
                <a href="/orders" class="nav-item flex flex-col items-center py-2 px-4 {{ request()->is('orders*') ? 'active' : '' }}">
                    <div class="nav-icon w-14 h-10 flex items-center justify-center">
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                    <span class="text-sm font-bold mt-0.5">Mis tareas</span>
                </a>
            @endif
        </div>
    </nav>

    @if(app()->isProduction())
    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js').catch(e => console.log('SW error', e));
        }
    </script>
    @else
    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.getRegistrations().then(regs => regs.forEach(r => r.unregister()));
        }
    </script>
    @endif
</body>
</html>