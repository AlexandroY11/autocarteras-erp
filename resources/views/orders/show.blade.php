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
                        {{ ucfirst($order->status) }}
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
                !in_array($order->status, ['done','delivered','cancelled'])
                && $order->current_stage_id != 8
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
    <div class="grid grid-cols-3 gap-3">
        <div class="bg-white border border-gray-100 rounded-2xl p-4">
            <p class="text-[10px] text-gray-400 uppercase font-bold tracking-widest">Total</p>
            <p class="text-xl font-black text-blue-700">
                ${{ number_format($order->price, 0, ',', '.') }}
            </p>
        </div>
        <div class="bg-white border border-gray-100 rounded-2xl p-4">
            <p class="text-[10px] text-gray-400 uppercase font-bold tracking-widest">Pagado</p>
            <p class="text-xl font-black text-green-600">
                ${{ number_format($order->total_paid, 0, ',', '.') }}
            </p>
        </div>
        <div class="bg-white border border-gray-100 rounded-2xl p-4">
            <p class="text-[10px] text-gray-400 uppercase font-bold tracking-widest">Saldo</p>
            <p class="text-xl font-black {{ $order->balance > 0 ? 'text-red-600' : 'text-green-600' }}">
                ${{ number_format($order->balance, 0, ',', '.') }}
            </p>
        </div>
    </div>

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
                    {{ $order->client->address }}, {{ $order->client->city->name }}
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
                <p class="font-bold {{ $order->due_date->isPast() && !in_array($order->status, ['delivered','done']) ? 'text-red-600' : 'text-gray-900' }}">
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
    <div class="bg-white border border-gray-100 rounded-2xl p-5 space-y-4">
        <h2 class="flex items-center gap-2 font-semibold text-gray-700 text-sm uppercase tracking-wide">
            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A2.25 2.25 0 006 7.5h12a2.25 2.25 0 002.25-2.25V4.5m-16.5 0h16.5"/>
            </svg>
            Pagos
        </h2>

        {{-- FORM PAGO --}}
        @if($order->balance > 0 && auth()->user()->isAdmin())
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

                @if(auth()->user()->isAdmin())
                    <form method="POST" action="/payments/{{ $payment->id }}">
                        @csrf @method('DELETE')
                        <button class="text-xs text-red-400 hover:text-red-600 transition cursor-pointer">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                            </svg>
                        </button>
                    </form>
                @endif
            </div>
            @empty
                <p class="text-xs text-gray-400 text-center py-4">Sin pagos registrados</p>
            @endforelse
        </div>
    </div>

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

    {{-- ================= CANCELAR ================= --}}
    @if(auth()->user()->isAdmin() && !in_array($order->status, ['delivered','cancelled']))
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