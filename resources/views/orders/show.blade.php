<x-app-layout title="Orden #{{ str_pad($order->consecutive, 3, '0', STR_PAD_LEFT) }}">

<div class="pt-4 space-y-5">

    {{-- ================= HEADER ================= --}}
    <div class="flex justify-between items-start">

        <div class="space-y-1">
            <a href="/dashboard" class="text-sm text-gray-500 hover:text-blue-600">
                ← Volver
            </a>

            <h1 class="text-3xl font-black text-gray-900 tracking-tight">
                Orden #{{ str_pad($order->consecutive, 3, '0', STR_PAD_LEFT) }}
            </h1>

            {{-- ESTADO --}}
            @if($order->currentStage)
                <span class="inline-flex text-xs font-bold text-white px-3 py-1 rounded-full"
                      style="background: {{ $order->currentStage->color }}">
                    {{ $order->currentStage->name }}
                </span>
            @else
                <span class="inline-flex text-xs font-bold bg-gray-100 text-gray-600 px-3 py-1 rounded-full">
                    {{ ucfirst($order->status) }}
                </span>
            @endif
        </div>

        {{-- ACCIÓN PRINCIPAL --}}
        <div class="flex gap-2">

            @if(auth()->user()->isAdmin())
                <a href="/production-orders/{{ $order->id }}/edit"
                   class="px-3 py-2 text-xs bg-gray-100 rounded-xl text-gray-600">
                    Editar
                </a>
            @endif

            @if(!in_array($order->status, ['done','delivered','cancelled']))
                <form method="POST" action="/production-orders/{{ $order->id }}/advance-stage">
                    @csrf
                    <button class="bg-blue-700 text-white text-sm px-4 py-2 rounded-xl font-semibold">
                        Avanzar etapa
                    </button>
                </form>
            @endif

        </div>
    </div>

    {{-- ================= RESUMEN ================= --}}
    <div class="grid grid-cols-3 gap-3">

        <div class="bg-white border border-gray-100 rounded-2xl p-4">
            <p class="text-[10px] text-gray-400 uppercase font-bold">Total</p>
            <p class="text-lg font-black text-blue-700">
                ${{ number_format($order->price, 0, ',', '.') }}
            </p>
        </div>

        <div class="bg-white border border-gray-100 rounded-2xl p-4">
            <p class="text-[10px] text-gray-400 uppercase font-bold">Pagado</p>
            <p class="text-lg font-black text-green-600">
                ${{ number_format($order->total_paid, 0, ',', '.') }}
            </p>
        </div>

        <div class="bg-white border border-gray-100 rounded-2xl p-4">
            <p class="text-[10px] text-gray-400 uppercase font-bold">Saldo</p>
            <p class="text-lg font-black text-red-600">
                ${{ number_format($order->balance, 0, ',', '.') }}
            </p>
        </div>

    </div>

    {{-- ================= INFO ================= --}}
    <div class="bg-white border border-gray-100 rounded-[2rem] p-5 space-y-4">

        <div class="grid grid-cols-2 gap-4 text-sm">

            <div>
                <p class="text-[10px] text-gray-400 uppercase">Cliente</p>
                <p class="font-bold text-gray-900">{{ $order->client->full_name }}</p>
                <p class="text-xs text-gray-500">{{ $order->client->phone }}</p>
            </div>

            <div>
                <p class="text-[10px] text-gray-400 uppercase">Producto</p>
                <p class="font-bold text-gray-900">{{ $order->product->name }}</p>
            </div>

            <div>
                <p class="text-[10px] text-gray-400 uppercase">Color</p>
                <p class="font-bold text-gray-900">{{ $order->color }}</p>
            </div>

            <div>
                <p class="text-[10px] text-gray-400 uppercase">Calcomanía</p>
                <p class="font-bold text-gray-900">
                    {{ $order->sticker ? $order->sticker_color : 'No' }}
                </p>
            </div>

            <div>
                <p class="text-[10px] text-gray-400 uppercase">Entrega</p>
                <p class="font-bold text-gray-900">
                    {{ $order->due_date->format('d/m/Y') }}
                </p>
            </div>

            <div>
                <p class="text-[10px] text-gray-400 uppercase">Creado por</p>
                <p class="font-bold text-gray-900">{{ $order->createdBy->name }}</p>
            </div>

        </div>

        @if($order->observations)
            <div class="pt-3 border-t border-gray-100">
                <p class="text-[10px] text-gray-400 uppercase">Observaciones</p>
                <p class="text-sm text-gray-700">{{ $order->observations }}</p>
            </div>
        @endif

    </div>

    {{-- ================= PAGOS ================= --}}
    <div class="bg-white border border-gray-100 rounded-[2rem] p-5 space-y-4">

        <h2 class="text-sm font-bold text-gray-800">Pagos</h2>

        {{-- FORM PAGO --}}
        @if($order->balance > 0 && auth()->user()->isAdmin())
        <form method="POST" action="/payments" class="space-y-3">
            @csrf
            <input type="hidden" name="production_order_id" value="{{ $order->id }}">

            <div class="grid grid-cols-2 gap-3">

                <input type="number" name="amount" placeholder="Monto"
                       class="w-full bg-gray-50 rounded-xl px-4 py-2 text-sm">

                <select name="type" class="w-full bg-gray-50 rounded-xl px-4 py-2 text-sm">
                    <option value="advance">Anticipo</option>
                    <option value="partial">Parcial</option>
                    <option value="final">Final</option>
                </select>

            </div>

            <button class="w-full bg-green-600 text-white font-bold py-2 rounded-xl">
                Registrar pago
            </button>
        </form>
        @endif

        {{-- LISTA PAGOS --}}
        <div class="space-y-2">

            @forelse($order->payments as $payment)

                <div class="flex justify-between items-center py-2 border-t border-gray-100">

                    <div>
                        <p class="text-sm font-semibold">
                            ${{ number_format($payment->amount, 0, ',', '.') }}
                        </p>
                        <p class="text-xs text-gray-400">
                            {{ ucfirst($payment->type) }} · {{ ucfirst($payment->payment_method) }} · {{ $payment->paid_at->format('d/m/Y') }}
                        </p>
                    </div>

                    @if(auth()->user()->isAdmin())
                        <form method="POST" action="/payments/{{ $payment->id }}">
                            @csrf @method('DELETE')
                            <button class="text-xs text-red-500">Eliminar</button>
                        </form>
                    @endif

                </div>

            @empty
                <p class="text-xs text-gray-400 text-center">Sin pagos registrados</p>
            @endforelse

        </div>

    </div>

    {{-- ================= TRAZABILIDAD ================= --}}
    <div class="bg-white border border-gray-100 rounded-[2rem] p-5">

        <h2 class="text-sm font-bold text-gray-800 mb-3">Trazabilidad</h2>

        <div class="space-y-4">

            @forelse($order->orderStages as $os)

                <div class="flex gap-3">

                    <div class="w-3 h-3 mt-1 rounded-full"
                         style="background: {{ $os->stage->color }}"></div>

                    <div>
                        <p class="font-semibold text-sm">{{ $os->stage->name }}</p>
                        <p class="text-xs text-gray-400">
                            {{ $os->started_at?->format('d/m H:i') ?? '—' }}
                            @if($os->completed_at)
                                → {{ $os->completed_at->format('d/m H:i') }}
                            @endif
                        </p>

                        @if($os->assignedTo)
                            <p class="text-xs text-blue-600">{{ $os->assignedTo->name }}</p>
                        @endif

                        @if($os->notes)
                            <p class="text-xs text-gray-500">{{ $os->notes }}</p>
                        @endif
                    </div>

                </div>

            @empty
                <p class="text-xs text-gray-400">Sin trazabilidad</p>
            @endforelse

        </div>

    </div>

    {{-- ================= CANCELAR ================= --}}
    @if(auth()->user()->isAdmin() && !in_array($order->status, ['delivered','cancelled']))

        <form method="POST"
              action="/production-orders/{{ $order->id }}/cancel"
              onsubmit="return confirmAction(event, '¿Cancelar esta orden?')">

            @csrf
            <button class="w-full border border-red-200 text-red-600 py-2 rounded-xl text-sm">
                Cancelar orden
            </button>

        </form>

    @endif

</div>
</x-app-layout>