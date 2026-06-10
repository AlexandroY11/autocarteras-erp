<x-app-layout title="Órdenes">
    <div class="pt-4 space-y-5">

        {{-- HEADER --}}
        <div class="flex justify-between items-end">
            <div>
                <h1 class="text-3xl font-black text-gray-900 tracking-tight">Órdenes</h1>
                <p class="text-sm font-medium text-blue-600 bg-blue-50 inline-block px-2 py-0.5 rounded-lg mt-1">
                    {{ $orders->total() }} órdenes registradas
                </p>
            </div>

            <div class="flex items-center gap-2">
                {{-- Botón Calendario --}}
                <a href="/production-orders/calendar"
                class="flex items-center gap-2 bg-indigo-600 text-white font-bold px-4 py-2.5 rounded-2xl text-sm shadow-md shadow-indigo-100 active:scale-95 transition-all">
                    
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 9v7.5m-9 3.75h.008v.008H12v-.008Z" />
                    </svg>

                    Calendario
                </a>

                {{-- Tu botón Nueva (ya existente) --}}
                <a href="/production-orders/create"
                class="flex items-center gap-2 bg-blue-700 text-white font-bold px-4 py-2.5 rounded-2xl text-sm shadow-md shadow-blue-100 active:scale-95 transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nueva
                </a>
            </div>

        </div>

        {{-- SEARCH + FILTER --}}
        <div class="bg-gray-50 border border-gray-200 rounded-[2rem] p-4 shadow-inner">

            <form method="GET" action="/orders" class="flex flex-col md:flex-row gap-3">

                {{-- SEARCH --}}
                <div class="flex-1 relative">

                    <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>

                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Buscar cliente o número de orden..."
                           class="w-full border-none rounded-2xl px-5 py-3.5 pl-12 text-sm focus:ring-2 focus:ring-blue-500 shadow-sm bg-white">
                </div>

                {{-- STAGE FILTER --}}
                <select name="stage"
                        class="w-full md:w-64 border-none rounded-2xl px-4 py-3.5 text-sm bg-white focus:ring-2 focus:ring-blue-500 shadow-sm">

                    <option value="">Todas las etapas</option>

                    @foreach($stages as $stage)
                        <option value="{{ $stage->id }}" @selected(request('stage') == $stage->id)>
                            {{ $stage->name }}
                        </option>
                    @endforeach

                </select>

                <button class="bg-blue-700 text-white px-8 rounded-2xl font-black text-sm shadow-md shadow-blue-100">
                    Buscar
                </button>

            </form>
        </div>

        <div class="flex gap-2 overflow-x-auto pb-4 no-scrollbar">
            @php
                $currentTimeStatus = request('time_status', 'all');
                $tabs = [
                    'all'      => ['label' => 'Todos', 'color' => 'bg-blue-600 text-white', 'inactive' => 'bg-gray-100 text-gray-500'],
                    'overdue'  => ['label' => 'Vencidos', 'color' => 'bg-red-600 text-white', 'inactive' => 'bg-red-50 text-red-600'],
                    'critical' => ['label' => 'Críticos', 'color' => 'bg-orange-500 text-white', 'inactive' => 'bg-orange-50 text-orange-600'],
                ];
            @endphp

            @foreach($tabs as $key => $tab)
                <a href="{{ request()->fullUrlWithQuery(['time_status' => $key]) }}" 
                class="px-5 py-2 rounded-full text-xs font-black whitespace-nowrap transition-all {{ $currentTimeStatus == $key ? $tab['color'] : $tab['inactive'] }}">
                    {{ $tab['label'] }}
                </a>
            @endforeach
        </div>

        {{-- LIST --}}
        <div class="space-y-3">
            @php 
                $lastGroup = null; 
                $groupData = [
                    'overdue'  => ['label' => 'Órdenes Vencidas', 'color' => 'text-red-600', 'icon' => 'M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z'],
                    'critical' => ['label' => 'Producción Crítica', 'color' => 'text-orange-600', 'icon' => 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z'],
                    'warning'  => ['label' => 'Tiempo Justo', 'color' => 'text-amber-600', 'icon' => 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
                    'on_time'  => ['label' => 'A Tiempo', 'color' => 'text-green-600', 'icon' => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z'],
                    'completed'=> ['label' => 'Completadas', 'color' => 'text-blue-600', 'icon' => 'M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z'],
                ];
            @endphp
            @forelse($orders as $order)
            @php $currentGroup = $order->time_status; @endphp

            {{-- DIVISOR DE GRUPO --}}
            @if($lastGroup !== $currentGroup)
                <div class="pt-8 pb-3 flex items-center gap-3">
                    <div class="flex items-center gap-2 shrink-0">
                        <svg class="w-4 h-4 {{ $groupData[$currentGroup]['color'] ?? 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $groupData[$currentGroup]['icon'] ?? '' }}"/>
                        </svg>
                        <span class="text-[11px] font-black uppercase tracking-[0.15em] {{ $groupData[$currentGroup]['color'] ?? 'text-gray-400' }}">
                            {{ $groupData[$currentGroup]['label'] ?? 'Otras Órdenes' }}
                        </span>
                    </div>
                    <div class="h-[1px] flex-1 bg-gradient-to-r from-gray-200 to-transparent"></div>
                </div>
                @php $lastGroup = $currentGroup; @endphp
            @endif
            <div
                data-swipeable
                data-can-advance="{{ $order->current_stage_id != 8 ? '1' : '0' }}"
                class="relative overflow-hidden rounded-2xl
                    {{ $order->current_stage_id == 8 ? 'opacity-90' : '' }}"
            >
                {{-- FONDO VERDE --}}
                <div data-bg class="absolute inset-0 flex items-center gap-3 pl-6 rounded-2xl transition-colors duration-300"
                    style="background: #3B6D11;">
                    <svg class="w-5 h-5 text-green-300" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                    </svg>
                    @if($order->current_stage_id == 8)
                        <span class="text-sm font-medium text-green-200">
                            Última etapa alcanzada
                        </span>
                    @else
                        <span class="text-sm font-medium text-green-200">
                            Avanzar etapa
                        </span>
                    @endif               
                </div>

                {{-- FORM OCULTO --}}
                @if(!in_array($order->status, ['done', 'delivered', 'cancelled']))
                <form data-form method="POST"
                    action="/production-orders/{{ $order->id }}/advance-stage"
                    class="hidden">
                    @csrf
                </form>
                @endif

                {{-- CARD (mismo contenido que tenías, solo cambia el wrapper de <a> a <div>) --}}
                <a data-card href="/production-orders/{{ $order->id }}"
                class="block bg-white rounded-2xl border border-gray-100 overflow-hidden"
                style="will-change: transform;">

                {{-- FILA SUPERIOR --}}
                    <div class="flex items-stretch border-b border-gray-100">

                        {{-- NÚMERO DE ORDEN --}}
                        <div class="flex flex-col items-center justify-center px-5 py-4 bg-gray-50 border-r border-gray-100 min-w-[88px]">
                            <span class="text-[11px] text-gray-400 uppercase tracking-widest">Ord.</span>
                            <span class="text-2xl font-medium text-gray-900 leading-tight">
                                #{{ str_pad($order->consecutive, 3, '0', STR_PAD_LEFT) }}
                            </span>
                        </div>

                        {{-- PRODUCTO Y CLIENTE --}}
                        <div class="flex flex-col justify-center px-5 py-4 flex-1 gap-0.5">
                            <p class="text-lg font-medium text-gray-900">{{ $order->product->name }}</p>
                            <p class="text-sm text-gray-400 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                                </svg>
                                {{ $order->client->full_name }}
                                <span class="text-gray-300">·</span>
                                {{ $order->client->phone }}
                            </p>
                        </div>

                        {{-- ETAPA --}}
                        <div class="flex items-center gap-2 px-3 py-4 border-l border-gray-100 shrink-0 max-w-[120px]">
                            @if($order->currentStage)
                                <span class="w-2.5 h-2.5 rounded-full shrink-0"
                                    style="background: {{ $order->currentStage->color }}"></span>
                                <span class="text-sm font-medium truncate" style="color: {{ $order->currentStage->color }}">
                                    {{ $order->currentStage->name }}
                                </span>
                            @else
                                <span class="w-2.5 h-2.5 rounded-full bg-gray-300 shrink-0"></span>
                                <span class="text-sm font-medium text-gray-400">Sin etapa</span>
                            @endif
                        </div>

                    </div>

                    {{-- FILA INFERIOR --}}
                    <div class="flex items-center flex-wrap border-t border-gray-100">

                        {{-- COLOR --}}
                        <div class="flex items-center gap-2 px-[18px] py-3 text-sm text-gray-600 border-r border-gray-100">
                            <span class="w-3 h-3 rounded-full border border-gray-300 shrink-0"
                                style="background: {{ $order->color }}"></span>
                            {{ $order->color }}
                        </div>

                        {{-- CALCOMANÍAS --}}
                        @if($order->sticker_color)
                            <div class="flex items-center gap-2 px-[18px] py-3 text-sm font-medium text-amber-700 bg-amber-50 border-r border-amber-200">
                                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L9.568 3z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z"/>
                                </svg>
                                Calcomanía {{ $order->sticker_color }}
                            </div>
                        @else
                            <div class="flex items-center gap-2 px-[18px] py-3 text-sm text-gray-400 border-r border-gray-100">
                                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L9.568 3z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z"/>
                                </svg>
                                Sin calcomanías
                            </div>
                        @endif

                        {{-- PIEZAS --}}
                        <div class="flex items-center gap-2 px-[18px] py-3 text-sm text-gray-600 border-r border-gray-100">
                            <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/>
                            </svg>
                            {{ $order->product->pieces ?? 0 }} piezas
                        </div>

                        {{-- INDICADOR DE TIEMPO Y FECHA --}}
                        <div class="flex flex-col items-end gap-1 px-[18px] py-3 border-r border-gray-100">
                            
                            {{-- Badge de Estado --}}
                            @php
                                $statusColors = [
                                    'completed' => 'bg-gray-100 text-gray-600',
                                    'overdue'   => 'bg-red-100 text-red-700 border border-red-200',
                                    'critical'  => 'bg-orange-100 text-orange-700 border border-orange-200',
                                    'warning'   => 'bg-yellow-100 text-yellow-700 border border-yellow-200',
                                    'on_time'   => 'bg-green-100 text-green-700 border border-green-200',
                                    'no_date'   => 'bg-gray-50 text-gray-400',
                                    'unknown'   => 'bg-gray-50 text-gray-400',
                                ];
                                
                                $statusLabels = [
                                    'completed' => 'Completado',
                                    'overdue'   => 'Retrasado',
                                    'critical'  => 'Crítico',
                                    'warning'   => 'Atención',
                                    'on_time'   => 'A tiempo',
                                    'no_date'   => 'Sin fecha',
                                    'unknown'   => 'Desconocido',
                                ];

                                $timeStatus = $order->time_status;
                            @endphp

                            <span class="px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider rounded-md {{ $statusColors[$timeStatus] ?? '' }}">
                                {{ $statusLabels[$timeStatus] ?? 'N/A' }}
                            </span>

                            {{-- Fecha --}}
                            @if($order->due_date)
                                <div class="flex items-center gap-1.5 text-sm {{ $timeStatus === 'overdue' ? 'text-red-600 font-medium' : 'text-gray-600' }}">
                                    <svg class="w-4 h-4 shrink-0 {{ $timeStatus === 'overdue' ? 'text-red-400' : 'text-gray-400' }}"
                                        fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5"/>
                                    </svg>
                                    {{ $order->due_date->format('d/m/Y') }}
                                </div>
                                
                                {{-- Mostrar días de holgura si está en proceso --}}
                                @if(!in_array($timeStatus, ['completed', 'overdue', 'no_date']))
                                    <span class="text-[11px] text-gray-400">
                                        Margen: {{ $order->slack }} días
                                    </span>
                                @endif
                            @endif
                        </div>

                        {{-- PRECIO Y SALDO (derecha) --}}
                        <div class="w-full flex flex-row items-center justify-between px-[18px] py-3 border-t border-gray-100 sm:w-auto sm:ml-auto sm:flex-col sm:items-end sm:border-t-0">
                            @php $balance = $order->price - $order->payments->sum('amount'); @endphp

                            <span class="text-base font-medium text-gray-900">
                                ${{ number_format($balance > 0 ? $order->price : $order->price, 0, ',', '.') }}
                            </span>

                            @if($balance > 0)
                                <span class="text-xs text-red-600">
                                    Debe ${{ number_format($balance, 0, ',', '.') }}
                                </span>
                            @else
                                <span class="text-xs text-green-700 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Pagado
                                </span>
                            @endif
                        </div>

                    </div>
                </a>
            </div>

            @empty

            <div class="text-center py-20 bg-gray-50 rounded-[2.5rem] border border-dashed border-gray-200">
                <h3 class="text-xl font-bold text-gray-400">No hay órdenes</h3>
                <p class="text-sm text-gray-400 mt-1">Crea la primera orden para comenzar</p>
            </div>

            @endforelse

        </div>

        {{-- PAGINATION --}}
        <div class="mt-4">
            {{ $orders->links() }}
        </div>

    </div>

    {{-- SweetAlert2 CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        let activeSwipe = null;
        document.querySelectorAll('[data-swipeable]' ).forEach(wrap => {
            const card = wrap.querySelector('[data-card]');
            const canAdvance = wrap.dataset.canAdvance === '1';
            const bg   = wrap.querySelector('[data-bg]');
            const form = wrap.querySelector('[data-form]');
            let startX = 0, curX = 0, dragging = false, movedDistance = 0;
            const CLICK_THRESHOLD = 10; // Pixeles para diferenciar un click de un swipe

            bg.style.opacity = 0;
            bg.style.transition = 'background-color 0.3s ease-in-out, opacity 0.3s ease-in-out';

            const THRESHOLD = 0.38;

            const handleMouseMove = (e) => onMove(e.clientX);
            const handleMouseUp = () => onEnd();

            function onStart(x, event) {

                if (!canAdvance) {
                    return;
                }

                if (activeSwipe && activeSwipe !== wrap) {
                    return;
                }

                activeSwipe = wrap;

                startX = x;
                curX = 0;
                movedDistance = 0;
                dragging = true;

                card.style.transition = 'none';

                if (event?.preventDefault) {
                    event.preventDefault();
                }

                document.addEventListener('mousemove', handleMouseMove);
                document.addEventListener('mouseup', handleMouseUp);
            }

            function onMove(x) {
                if (activeSwipe !== wrap) return;
                if (!dragging) return;
                const deltaX = x - startX;
                curX = Math.max(0, deltaX);
                movedDistance = Math.abs(deltaX); // Actualizar la distancia movida

                const ratio = Math.min(curX / wrap.offsetWidth, 1);
                card.style.transform = `translateX(${curX}px)`;
                bg.style.opacity = Math.min(ratio * 2.5, 1);
                bg.style.backgroundColor = ratio >= THRESHOLD ? '#27500A' : '#3B6D11';
            }

            function onEnd() {
                if (activeSwipe !== wrap) return;

                activeSwipe = null;

                if (!dragging) return;
                dragging = false;

                // Eliminar listeners de mousemove y mouseup del document
                document.removeEventListener('mousemove', handleMouseMove);
                document.removeEventListener('mouseup', handleMouseUp);

                const ratio = curX / wrap.offsetWidth;
                card.style.transition = 'transform 0.35s cubic-bezier(.25,.46,.45,.94)';

                if (movedDistance < CLICK_THRESHOLD) {
                    // Si la distancia movida es menor que el umbral, se considera un click
                    // No hacemos nada aquí, permitimos que el evento click del <a> se propague
                    card.style.transform = 'translateX(0)';
                    bg.style.opacity = 0;
                    bg.style.backgroundColor = '#3B6D11';
                    return;
                }

                // Si es un swipe, prevenimos la navegación del <a>
                // Esto se maneja con un listener en el <a> que previene el default si isSwiping es true

                if (ratio >= THRESHOLD) {
                    if (navigator.vibrate) navigator.vibrate(40);
                    card.style.transform = `translateX(${wrap.offsetWidth}px)`;

                    Swal.fire({
                        title: '¿Avanzar etapa?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#1d4ed8',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: 'Sí, avanzar',
                        cancelButtonText: 'Cancelar',
                        reverseButtons: true
                    }).then(result => {
                        if (result.isConfirmed) {
                            if (form) {
                                form.submit();
                            } else {
                                card.style.transform = 'translateX(0)';
                                bg.style.opacity = 0;
                                bg.style.backgroundColor = '#3B6D11';
                            }
                        } else {
                            card.style.transform = 'translateX(0)';
                            bg.style.opacity = 0;
                            bg.style.backgroundColor = '#3B6D11';
                        }
                    });
                } else {
                    card.style.transform = 'translateX(0)';
                    bg.style.opacity = 0;
                    bg.style.backgroundColor = '#3B6D11';
                }
                curX = 0;
            }

            // Eventos de ratón
            wrap.addEventListener('mousedown',  e => onStart(e.clientX, e));

            // Eventos táctiles
            wrap.addEventListener('touchstart', e => onStart(e.touches[0].clientX, e), { passive: true });
            wrap.addEventListener('touchmove',  e => onMove(e.touches[0].clientX),  { passive: true });
            wrap.addEventListener('touchend',   () => onEnd());

            // Prevenir la navegación del <a> si se detectó un swipe
            card.addEventListener('click', e => {
                if (movedDistance >= CLICK_THRESHOLD) {
                    e.preventDefault();
                }
            });
        });
    </script>
</x-app-layout>