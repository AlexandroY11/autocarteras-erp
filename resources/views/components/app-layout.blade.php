<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#1e40af">
    <title>{{ $title ?? 'AutoCarteras Cali' }}</title>
    <link rel="manifest" href="/manifest.json">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen">

    {{-- Navbar --}}
    <nav class="bg-blue-800 text-white px-4 py-3 flex items-center justify-between sticky top-0 z-50">
        <span class="font-bold text-lg">🏭 AutoCarteras</span>
        <div class="flex items-center gap-3">
            <span class="text-sm text-blue-200">{{ auth()->user()->name ?? '' }}</span>
            <form method="POST" action="/logout">
                @csrf
                <button class="text-sm bg-blue-700 px-3 py-1 rounded">Salir</button>
            </form>
        </div>
    </nav>

    {{-- Menú inferior mobile --}}
    <nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 flex justify-around py-2 z-50">
        <a href="/dashboard"
            class="flex flex-col items-center text-xs {{ request()->is('dashboard') ? 'text-blue-700' : 'text-gray-500' }}">
            <span class="text-xl">📋</span> Órdenes
        </a>
        <a href="/products"
            class="flex flex-col items-center text-xs {{ request()->is('products*') ? 'text-blue-700' : 'text-gray-500' }}">
            <span class="text-xl">📦</span> Productos
        </a>
        <a href="/clients"
            class="flex flex-col items-center text-xs {{ request()->is('clients*') ? 'text-blue-700' : 'text-gray-500' }}">
            <span class="text-xl">👤</span> Clientes
        </a>
        @if(auth()->user()->isAdmin())
        <a href="/users"
            class="flex flex-col items-center text-xs {{ request()->is('users*') ? 'text-blue-700' : 'text-gray-500' }}">
            <span class="text-xl">👥</span> Usuarios
        </a>
        <a href="/stages"
            class="flex flex-col items-center text-xs {{ request()->is('stages*') ? 'text-blue-700' : 'text-gray-500' }}">
            <span class="text-xl">⚙️</span> Etapas
        </a>
        @endif
    </nav>

    {{-- Contenido --}}
    <main class="pb-20 pt-2 px-4 max-w-2xl mx-auto">
        {{ $slot }}
    </main>


    <script>
        @if(app()->isProduction())
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js')
                .then(() => console.log('SW registrado'))
                .catch(e => console.log('SW error', e));
        }
        @else
        // Desregistrar SW en desarrollo
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.getRegistrations().then(regs => {
                regs.forEach(reg => reg.unregister());
            });
        }
        @endif
    </script>
</body>
</html>