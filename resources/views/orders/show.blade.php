<x-app-layout title="Orden #{{ str_pad($order->consecutive, 3, '0', STR_PAD_LEFT) }}">
<div class="pt-4 space-y-4">

    {{-- ================= HEADER ================= --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="/orders" class="w-9 h-9 bg-gray-100 rounded-full flex items-center justify-center">
                <svg class="w-4 h-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-black text-gray-900 tracking-tight">
                    Orden #{{ str_pad($order->consecutive, 3, '0', STR_PAD_LEFT) }}
                </h1>
                @if($order->currentStage)
                    <span class="inline-flex text-xs font-bold text-white px-3 py-0.5 rounded-full"
                          style="background: {{ $order->currentStage->color }}">
                        {{ $order->currentStage->name }}
                    </span>
                @else
                    <span class="inline-flex text-xs font-bold bg-gray-100 text-gray-600 px-3 py-0.5 rounded-full">
                        {{ $order->status_label }}
                    </span>
                @endif
            </div>
        </div>

        <div class="flex gap-2">
            @if(auth()->user()->isAdmin())
                <a href="/production-orders/{{ $order->id }}/edit"
                   class="px-3 py-2 text-xs bg-gray-100 rounded-xl text-gray-600 font-semibold">
                    Editar
                </a>
            @endif
            @if(
                !in_array($order->status, ['done','cancelled'])
                && $order->current_stage_id !== \App\Models\Stage::enviadoId()
            )
                <form method="POST" action="/production-orders/{{ $order->id }}/advance-stage"
                    x-data="{}"
                    @submit="showAlert.confirm($event, '¿Avanzar a la siguiente etapa?', 'Sí, avanzar')">
                    @csrf
                    <button type="submit" class="bg-blue-700 text-white text-sm px-4 py-2 rounded-xl font-semibold cursor-pointer">
                        Avanzar etapa
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- ================= RESUMEN FINANCIERO ================= --}}
    @if(auth()->user()->isAdmin())
    <div class="grid grid-cols-3 gap-3">
        <div class="bg-white border border-gray-100 rounded-2xl p-4">
            <p class="text-[10px] text-gray-400 uppercase font-bold tracking-widest">Precio Producto</p>
            <p class="text-xl font-black text-blue-700">
                ${{ number_format($order->price, 0, ',', '.') }}
            </p>
        </div>
        <div class="bg-white border border-gray-100 rounded-2xl p-4">
            <p class="text-[10px] text-gray-400 uppercase font-bold tracking-widest">Precio Envío</p>
            <p class="text-xl font-black text-blue-700">
                ${{ number_format($order->shipping_price ?? 0, 0, ',', '.') }}
            </p>
        </div>
        <div class="bg-white border border-gray-100 rounded-2xl p-4">
            <p class="text-[10px] text-gray-400 uppercase font-bold tracking-widest">Total Recibido</p>
            <p class="text-xl font-black text-green-600">
                ${{ number_format($order->total_paid, 0, ',', '.') }}
            </p>
        </div>
        <div class="bg-white border border-gray-100 rounded-2xl p-4">
            <p class="text-[10px] text-gray-400 uppercase font-bold tracking-widest">Aplicado a Envío</p>
            <p class="text-xl font-black {{ $order->shipping_balance > 0 ? 'text-amber-600' : 'text-green-600' }}">
                ${{ number_format($order->shipping_paid, 0, ',', '.') }}
            </p>
            @if($order->shipping_balance > 0)
                <p class="text-[10px] text-amber-600 font-bold">Faltan ${{ number_format($order->shipping_balance, 0, ',', '.') }}</p>
            @endif
        </div>
        <div class="bg-white border border-gray-100 rounded-2xl p-4">
            <p class="text-[10px] text-gray-400 uppercase font-bold tracking-widest">Aplicado a Producto</p>
            <p class="text-xl font-black text-gray-700">
                ${{ number_format($order->product_paid, 0, ',', '.') }}
            </p>
        </div>
        <div class="bg-white border border-gray-100 rounded-2xl p-4">
            <p class="text-[10px] text-gray-400 uppercase font-bold tracking-widest">Saldo Producto</p>
            <p class="text-xl font-black {{ $order->product_balance > 0 ? 'text-red-600' : 'text-green-600' }}">
                ${{ number_format($order->product_balance, 0, ',', '.') }}
            </p>
        </div>
    </div>
    @endif

    {{-- ================= INFO ORDEN ================= --}}
    <div class="bg-white border border-gray-100 rounded-2xl p-5 space-y-4">
        <h2 class="flex items-center gap-2 font-semibold text-gray-700 text-sm uppercase tracking-wide">
            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
            </svg>
            Detalle de la orden
        </h2>

        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-[10px] text-gray-400 uppercase font-bold">Cliente</p>
                <p class="font-bold text-gray-900">{{ $order->client->full_name }}</p>
                <p class="text-xs text-gray-500">{{ $order->client->phone }}</p>
                <p class="text-xs text-gray-400">
                    {{ $order->client->address }}{{ $order->client->city ? ', ' . $order->client->city->name : '' }}
                </p>
            </div>
            <div>
                <p class="text-[10px] text-gray-400 uppercase font-bold">Producto</p>
                <p class="font-bold text-gray-900">{{ $order->product->name }}</p>
                <p class="text-xs text-gray-400">{{ $order->product->pieces ?? 0 }} piezas</p>
            </div>
            <div>
                <p class="text-[10px] text-gray-400 uppercase font-bold">Color</p>
                <p class="font-bold text-gray-900">{{ $order->color }}</p>
            </div>
            <div>
                <p class="text-[10px] text-gray-400 uppercase font-bold">Calcomanía</p>
                <p class="font-bold text-gray-900">
                    {{ $order->sticker ? ($order->sticker_color ?? 'Sí') : 'No' }}
                </p>
            </div>
            <div>
                <p class="text-[10px] text-gray-400 uppercase font-bold">Fecha compromiso</p>
                <p class="font-bold {{ $order->due_date->isPast() && $order->status !== 'done' ? 'text-red-600' : 'text-gray-900' }}">
                    {{ $order->due_date->format('d/m/Y') }}
                </p>
            </div>
            <div>
                <p class="text-[10px] text-gray-400 uppercase font-bold">Creado por</p>
                <p class="font-bold text-gray-900">{{ $order->createdBy->name }}</p>
            </div>
        </div>

        @if($order->observations)
            <div class="pt-3 border-t border-gray-100">
                <p class="text-[10px] text-gray-400 uppercase font-bold">Observaciones</p>
                <p class="text-sm text-gray-700 mt-1">{{ $order->observations }}</p>
            </div>
        @endif
    </div>

    {{-- ================= PAGOS ================= --}}
    @if(auth()->user()->isAdmin())
    <div class="bg-white border border-gray-100 rounded-2xl p-5 space-y-4">
        <h2 class="flex items-center gap-2 font-semibold text-gray-700 text-sm uppercase tracking-wide">
            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A2.25 2.25 0 006 7.5h12a2.25 2.25 0 002.25-2.25V4.5m-16.5 0h16.5"/>
            </svg>
            Pagos
        </h2>

        {{-- FORM PAGO --}}
        @if($order->total_balance > 0)
        <form method="POST" action="/payments"
            x-data="{ method: 'efectivo' }"
            @submit="showAlert.confirm($event, '¿Registrar este pago?', 'Sí, registrar')"
            class="space-y-3">
            @csrf
            <input type="hidden" name="production_order_id" value="{{ $order->id }}">

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-[10px] font-black text-gray-400 uppercase">Monto *</label>
                    <div class="relative mt-1">
                        <span class="absolute left-3 top-2.5 text-gray-400 text-sm">$</span>
                        <input type="number" name="amount" required placeholder="0"
                               class="w-full border border-gray-300 rounded-xl pl-7 pr-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                </div>
                <div>
                    <label class="text-[10px] font-black text-gray-400 uppercase">Tipo *</label>
                    <select name="type" required
                            class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm mt-1 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="advance">Anticipo</option>
                        <option value="partial">Parcial</option>
                        <option value="final">Final</option>
                    </select>
                </div>
            </div>

            {{-- MÉTODO DE PAGO --}}
            <div>
                <label class="text-[10px] font-black text-gray-400 uppercase block mb-2">Método de pago *</label>
                <div class="grid grid-cols-3 gap-2" x-data="{ method: 'efectivo' }">
                    <input type="hidden" name="payment_method" x-bind:value="method">

                    {{-- Efectivo --}}
                    <button type="button" @click="method = 'efectivo'"
                            :class="method === 'efectivo' ? 'border-green-500 bg-green-50 text-green-700' : 'border-gray-200 text-gray-500'"
                            class="flex flex-col items-center gap-1.5 border-2 rounded-xl py-3 px-2 text-xs font-bold transition-all cursor-pointer">

                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="w-6 h-6"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2 7h20v10H2V7z"/>
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 10a2 2 0 100 4 2 2 0 000-4z"/>
                        </svg>

                        Efectivo
                    </button>

                    {{-- Nequi --}}
                    <button type="button" @click="method = 'nequi'"
                            :class="method === 'nequi' ? 'border-fuchsia-500 bg-fuchsia-50 text-fuchsia-700' : 'border-gray-200 text-gray-500'"
                            class="flex flex-col items-center gap-1.5 border-2 rounded-xl py-3 px-2 text-xs font-bold transition-all cursor-pointer">

                        <img src="{{ asset('images/payments/nequi.svg') }}"
                            alt="Nequi"
                            class="h-6 object-contain">

                        Nequi
                    </button>

                    {{-- Nu --}}
                    <button type="button" @click="method = 'nu'"
                            :class="method === 'nu' ? 'border-violet-500 bg-violet-50 text-violet-700' : 'border-gray-200 text-gray-500'"
                            class="flex flex-col items-center gap-1.5 border-2 rounded-xl py-3 px-2 text-xs font-bold transition-all cursor-pointer">

                        <img src="{{ asset('images/payments/nubank.svg') }}"
                            alt="Nu"
                            class="h-6 object-contain">

                        Nu
                    </button>
                </div>
            </div>

            <div>
                <label class="text-[10px] font-black text-gray-400 uppercase">Notas</label>
                <input type="text" name="notes" placeholder="Ej: Transferencia desde Bancolombia..."
                       class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm mt-1 focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>

            <button type="submit"
                    class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-xl text-sm transition cursor-pointer">
                Registrar pago
            </button>
        </form>
        @endif

        {{-- LISTA PAGOS --}}
        <div class="space-y-2">
            @forelse($order->payments as $payment)
            <div class="flex justify-between items-center py-3 border-t border-gray-100">
                <div class="flex items-center gap-3">

                    {{-- Icono método --}}
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center
                        {{ $payment->payment_method === 'efectivo' ? 'bg-green-50 text-green-600' :
                           ($payment->payment_method === 'nequi'   ? 'bg-purple-50 text-purple-600' :
                                                                      'bg-violet-50 text-violet-600') }}">
                        @if($payment->payment_method === 'efectivo')
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A2.25 2.25 0 006 7.5h12a2.25 2.25 0 002.25-2.25V4.5m-16.5 0h16.5m-16.5 7.5h16.5"/>
                            </svg>
                        @elseif($payment->payment_method === 'nequi')
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2zm0 18c-4.418 0-8-3.582-8-8s3.582-8 8-8 8 3.582 8 8-3.582 8-8 8zm-1-13h2v6h-2zm0 8h2v2h-2z"/>
                            </svg>
                        @else
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14H9V8h2v8zm4 0h-2V8h2v8z"/>
                            </svg>
                        @endif
                    </div>

                    <div>
                        <p class="text-sm font-bold text-gray-900">
                            ${{ number_format($payment->amount, 0, ',', '.') }}
                        </p>
                        <p class="text-xs text-gray-400">
                            {{ ucfirst($payment->type) }} ·
                            {{ ucfirst($payment->payment_method) }} ·
                            {{ $payment->paid_at->format('d/m/Y') }}
                        </p>
                    </div>
                </div>
            </div>
            @empty
                <p class="text-xs text-gray-400 text-center py-4">Sin pagos registrados</p>
            @endforelse
        </div>
    </div>
    @endif

    {{-- ================= TRAZABILIDAD ================= --}}
    <div class="bg-white border border-gray-100 rounded-2xl p-5">
        <h2 class="flex items-center gap-2 font-semibold text-gray-700 text-sm uppercase tracking-wide mb-4">
            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z"/>
            </svg>
            Trazabilidad
        </h2>

        <div class="space-y-4">
            @forelse($order->orderStages as $os)
            <div class="flex gap-3">
                <div class="flex flex-col items-center">
                    <div class="w-3 h-3 mt-1 rounded-full shrink-0"
                         style="background: {{ $os->stage->color }}"></div>
                    @if(!$loop->last)
                        <div class="w-px flex-1 bg-gray-100 my-1"></div>
                    @endif
                </div>
                <div class="pb-4">
                    <p class="font-bold text-sm text-gray-900">{{ $os->stage->name }}</p>
                    <p class="text-xs text-gray-400">
                        {{ $os->started_at?->format('d/m H:i') ?? '—' }}
                        @if($os->completed_at)
                            → {{ $os->completed_at->format('d/m H:i') }}
                        @endif
                    </p>
                    @if($os->assignedTo)
                        <p class="text-xs text-blue-600 mt-0.5">{{ $os->assignedTo->name }}</p>
                    @endif
                    @if($os->notes)
                        <p class="text-xs text-gray-500 mt-0.5">{{ $os->notes }}</p>
                    @endif
                </div>
            </div>
            @empty
                <p class="text-xs text-gray-400 text-center py-4">Sin trazabilidad</p>
            @endforelse
        </div>
    </div>

    {{-- ================= DESPACHO / ENVÍO ================= --}}
    @if(auth()->user()->isAdmin() && $order->status === 'done')
    <div class="bg-white border border-gray-100 rounded-2xl p-5 space-y-4">
        <h2 class="flex items-center gap-2 font-semibold text-gray-700 text-sm uppercase tracking-wide">
            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5V14.25"/>
            </svg>
            Despacho / Envío
        </h2>

        @php $ds = $order->dispatch_status; $latestDispatch = $order->latestDispatch(); @endphp

        @if($latestDispatch)
            <div class="border border-gray-200 rounded-xl p-3 space-y-2">
                <div class="flex items-center justify-between">
                    <label class="text-[10px] font-black text-gray-400 uppercase">Número de guía</label>
                    @if($order->guide_number_missing)
                        <span class="text-[10px] font-black text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full uppercase">
                            Sin guía
                        </span>
                    @endif
                </div>
                <form method="POST" action="/production-orders/{{ $order->id }}/guide-number" class="flex gap-2">
                    @csrf
                    <input type="text" name="guide_number" value="{{ $latestDispatch->guide_number }}"
                           placeholder="Ej: 123456789"
                           class="flex-1 border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <button type="submit" class="bg-gray-800 text-white text-sm px-4 rounded-xl font-semibold cursor-pointer">
                        Guardar
                    </button>
                </form>
            </div>
        @endif

        @if($ds === 'pending_dispatch')
            @if($order->shipping_balance > 0)
                <p class="text-sm text-amber-700 bg-amber-50 rounded-xl px-4 py-3">
                    No se puede despachar: falta cubrir el envío (saldo de envío ${{ number_format($order->shipping_balance, 0, ',', '.') }}).
                </p>
            @else
                <form method="POST" action="/production-orders/{{ $order->id }}/dispatch"
                    x-data="{}"
                    @submit="showAlert.confirm($event, '¿Despachar esta orden?', 'Sí, despachar')"
                    class="space-y-3">
                    @csrf
                    @if($order->product_balance > 0)
                        <p class="text-xs text-gray-500">Recaudo a cobrar en la entrega: <strong>${{ number_format($order->product_balance, 0, ',', '.') }}</strong></p>
                    @else
                        <p class="text-xs text-gray-500">Producto pagado en su totalidad — se despacha sin recaudo.</p>
                    @endif
                    <p class="text-xs text-gray-400">El número de guía se asigna después, por separado.</p>
                    <button type="submit" class="w-full bg-blue-700 text-white text-sm py-3 rounded-xl font-semibold cursor-pointer">
                        Despachar
                    </button>
                </form>
            @endif
        @elseif($ds === 'dispatched')
            <form method="POST" action="/production-orders/{{ $order->id }}/mark-sent"
                x-data="{}"
                @submit="showAlert.confirm($event, '¿Marcar como en tránsito?', 'Sí, marcar')">
                @csrf
                <button type="submit" class="w-full bg-blue-700 text-white text-sm py-3 rounded-xl font-semibold cursor-pointer">
                    Marcar en tránsito
                </button>
            </form>
            <form method="POST" action="/production-orders/{{ $order->id }}/mark-returned"
                x-data="{ reason: '' }"
                @submit="showAlert.confirm($event, '¿Registrar devolución de esta guía?', 'Sí, devolver')"
                class="space-y-2">
                @csrf
                <input type="text" name="return_reason" x-model="reason" required placeholder="Motivo de la devolución"
                       class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <button type="submit" class="w-full border border-amber-300 text-amber-700 hover:bg-amber-50 text-sm py-2.5 rounded-xl font-semibold cursor-pointer">
                    Registrar devolución
                </button>
            </form>
        @elseif($ds === 'sent')
            <p class="text-sm text-blue-700 bg-blue-50 rounded-xl px-4 py-3">En tránsito hacia el cliente.</p>
            <form method="POST" action="/production-orders/{{ $order->id }}/mark-delivered"
                x-data="{}"
                @submit="showAlert.confirm($event, '¿Marcar como entregado?', 'Sí, entregado')">
                @csrf
                <button type="submit" class="w-full bg-green-600 text-white text-sm py-3 rounded-xl font-semibold cursor-pointer">
                    Marcar entregado
                </button>
            </form>
            <form method="POST" action="/production-orders/{{ $order->id }}/mark-returned"
                x-data="{ reason: '' }"
                @submit="showAlert.confirm($event, '¿Registrar devolución de esta guía?', 'Sí, devolver')"
                class="space-y-2">
                @csrf
                <input type="text" name="return_reason" x-model="reason" required placeholder="Motivo de la devolución"
                       class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <button type="submit" class="w-full border border-amber-300 text-amber-700 hover:bg-amber-50 text-sm py-2.5 rounded-xl font-semibold cursor-pointer">
                    Registrar devolución
                </button>
            </form>
        @elseif($ds === 'delivered')
            <p class="text-sm text-green-700 bg-green-50 rounded-xl px-4 py-3 font-semibold">Entregado.</p>
        @elseif($ds === 'returned')
            <p class="text-sm text-amber-700 bg-amber-50 rounded-xl px-4 py-3">Devuelto — requiere revisión antes de reintentar el envío.</p>
            <form method="POST" action="/production-orders/{{ $order->id }}/return-to-pending-dispatch"
                x-data="{}"
                @submit="showAlert.confirm($event, '¿Dejar la orden lista para un nuevo despacho?', 'Sí, listo')">
                @csrf
                <button type="submit" class="w-full bg-blue-700 text-white text-sm py-3 rounded-xl font-semibold cursor-pointer">
                    Listo para nuevo despacho
                </button>
            </form>
        @endif
    </div>
    @endif

    {{-- ================= CANCELAR ================= --}}
    @if(auth()->user()->isAdmin() && in_array($order->dispatch_status, [null, 'pending_dispatch'], true) && $order->status !== 'cancelled')
        <form method="POST"
            action="/production-orders/{{ $order->id }}/cancel"
            x-data="{}"
            @submit.prevent="
                Swal.fire({
                    title: '¿Cancelar esta orden?',
                    text: 'La orden será marcada como cancelada.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Sí, cancelar',
                    cancelButtonText: 'Volver',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        $el.submit();
                    }
                })
            ">
            @csrf

            <button
                class="w-full border border-red-200 text-red-500 hover:bg-red-50 py-3 rounded-xl text-sm font-semibold transition cursor-pointer">
                Cancelar orden
            </button>
        </form>
    @endif

</div>
</x-app-layout>