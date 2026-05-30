<x-app-layout title="Orden #{{ str_pad($order->consecutive, 3, '0', STR_PAD_LEFT) }}">

    <div class="flex items-center justify-between mt-4 mb-4">
        <div class="flex items-center gap-3">
            <a href="/dashboard" class="text-gray-500">← Volver</a>
            <h1 class="text-lg font-bold text-gray-800">
                Orden #{{ str_pad($order->consecutive, 3, '0', STR_PAD_LEFT) }}
            </h1>
        </div>
        @if(auth()->user()->isAdmin())
        <a href="/production-orders/{{ $order->id }}/edit"
            class="text-xs bg-gray-100 px-3 py-1 rounded-lg text-gray-600">Editar</a>
        @endif
    </div>

    {{-- Estado actual --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-3">
        <div class="flex justify-between items-center">
            <div>
                <div class="text-xs text-gray-400 mb-1">Estado actual</div>
                @if($order->currentStage)
                <span class="text-sm font-semibold text-white px-3 py-1 rounded-full"
                    style="background-color: {{ $order->currentStage->color }}">
                    {{ $order->currentStage->name }}
                </span>
                @else
                <span class="text-sm font-semibold px-3 py-1 rounded-full
                    {{ $order->status === 'done' ? 'bg-green-100 text-green-700' : '' }}
                    {{ $order->status === 'delivered' ? 'bg-gray-100 text-gray-700' : '' }}
                    {{ $order->status === 'cancelled' ? 'bg-red-100 text-red-700' : '' }}">
                    {{ match($order->status) {
                        'done'      => '✅ Terminada',
                        'delivered' => '📦 Entregada',
                        'cancelled' => '❌ Cancelada',
                        default     => $order->status
                    } }}
                </span>
                @endif
            </div>
            @if(!in_array($order->status, ['done', 'delivered', 'cancelled']))
            <form method="POST" action="/production-orders/{{ $order->id }}/advance-stage">
                @csrf
                <button type="submit"
                    class="bg-blue-700 text-white text-sm px-4 py-2 rounded-lg">
                    Avanzar etapa →
                </button>
            </form>
            @endif
        </div>
    </div>

    {{-- Info principal --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-3 space-y-3">
        <div class="grid grid-cols-2 gap-3">
            <div>
                <div class="text-xs text-gray-400">Cliente</div>
                <div class="text-sm font-semibold">{{ $order->client->full_name }}</div>
                <div class="text-xs text-gray-500">{{ $order->client->phone }}</div>
            </div>
            <div>
                <div class="text-xs text-gray-400">Producto</div>
                <div class="text-sm font-semibold">{{ $order->product->name }}</div>
            </div>
            <div>
                <div class="text-xs text-gray-400">Color</div>
                <div class="text-sm font-semibold">{{ $order->color }}</div>
            </div>
            <div>
                <div class="text-xs text-gray-400">Calcomanía</div>
                <div class="text-sm font-semibold">
                    {{ $order->sticker ? '✅ ' . ($order->sticker_color ?? 'Sí') : 'No' }}
                </div>
            </div>
            <div>
                <div class="text-xs text-gray-400">Fecha compromiso</div>
                <div class="text-sm font-semibold">
                    {{ $order->due_date->format('d/m/Y') }}
                </div>
            </div>
            <div>
                <div class="text-xs text-gray-400">Registrado por</div>
                <div class="text-sm font-semibold">{{ $order->createdBy->name }}</div>
            </div>
        </div>

        @if($order->observations)
        <div class="border-t pt-3">
            <div class="text-xs text-gray-400 mb-1">Observaciones</div>
            <div class="text-sm text-gray-700">{{ $order->observations }}</div>
        </div>
        @endif
    </div>

    {{-- Pagos --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-3">
        <div class="flex justify-between items-center mb-3">
            <h2 class="font-semibold text-gray-800">💰 Pagos</h2>
        </div>

        <div class="grid grid-cols-3 gap-2 mb-3 text-center">
            <div class="bg-gray-50 rounded-lg p-2">
                <div class="text-xs text-gray-400">Precio</div>
                <div class="text-sm font-bold">${{ number_format($order->price, 0, ',', '.') }}</div>
            </div>
            <div class="bg-green-50 rounded-lg p-2">
                <div class="text-xs text-gray-400">Pagado</div>
                <div class="text-sm font-bold text-green-700">${{ number_format($order->total_paid, 0, ',', '.') }}</div>
            </div>
            <div class="bg-red-50 rounded-lg p-2">
                <div class="text-xs text-gray-400">Saldo</div>
                <div class="text-sm font-bold text-red-700">${{ number_format($order->balance, 0, ',', '.') }}</div>
            </div>
        </div>

        {{-- Registrar pago --}}
        @if($order->balance > 0 && auth()->user()->isAdmin())
        <form method="POST" action="/payments"
            class="border border-gray-200 rounded-xl p-3 space-y-3 mb-3">
            @csrf
            <input type="hidden" name="production_order_id" value="{{ $order->id }}">
            <input type="hidden" name="paid_at" value="{{ now()->toDateString() }}">

            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="text-xs text-gray-500">Monto</label>
                    <input type="number" name="amount" min="1"
                        placeholder="0"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="text-xs text-gray-500">Tipo</label>
                    <select name="type"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="advance">Anticipo</option>
                        <option value="partial">Parcial</option>
                        <option value="final">Final</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="text-xs text-gray-500">Método de pago</label>
                <div class="grid grid-cols-2 gap-2 mt-1">
                    <label class="flex items-center gap-2 border border-gray-200 rounded-lg p-2 cursor-pointer has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50">
                        <input type="radio" name="payment_method" value="efectivo" checked class="text-blue-600">
                        <span class="text-sm">💵 Efectivo</span>
                    </label>
                    <label class="flex items-center gap-2 border border-gray-200 rounded-lg p-2 cursor-pointer has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50">
                        <input type="radio" name="payment_method" value="nequi" class="text-blue-600">
                        <span class="text-sm">📱 Nequi</span>
                    </label>
                </div>
            </div>

            <button type="submit"
                class="w-full bg-green-600 text-white text-sm font-semibold py-2 rounded-lg">
                Registrar pago
            </button>
        </form>
        @endif

        {{-- Historial de pagos --}}
        @forelse($order->payments as $payment)
        <div class="flex justify-between items-center py-2 border-t border-gray-100">
            <div>
                <div class="text-sm font-medium">${{ number_format($payment->amount, 0, ',', '.') }}</div>
                <div class="text-xs text-gray-400">
                    {{ match($payment->type) {
                        'advance' => 'Anticipo',
                        'partial' => 'Parcial',
                        'final'   => 'Final',
                    } }}
                    · {{ $payment->payment_method === 'nequi' ? '📱 Nequi' : '💵 Efectivo' }}
                    · {{ $payment->paid_at->format('d/m/Y') }}
                </div>
            </div>
            @if(auth()->user()->isAdmin())
            <form method="POST" action="/payments/{{ $payment->id }}"
                onsubmit="return confirm('¿Eliminar este pago?')">
                @csrf @method('DELETE')
                <button class="text-xs text-red-500">Eliminar</button>
            </form>
            @endif
        </div>
        @empty
        <div class="text-xs text-gray-400 text-center py-2">Sin pagos registrados</div>
        @endforelse
    </div>

    {{-- Trazabilidad --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-3">
        <h2 class="font-semibold text-gray-800 mb-3">📍 Trazabilidad</h2>
        <div class="space-y-3">
            @forelse($order->orderStages as $os)
            <div class="flex gap-3">
                <div class="flex flex-col items-center">
                    <div class="w-3 h-3 rounded-full mt-1"
                        style="background-color: {{ $os->stage->color }}"></div>
                    @if(!$loop->last)
                    <div class="w-0.5 bg-gray-200 flex-1 mt-1"></div>
                    @endif
                </div>
                <div class="pb-3">
                    <div class="text-sm font-semibold">{{ $os->stage->name }}</div>
                    <div class="text-xs text-gray-400">
                        Inicio: {{ $os->started_at?->format('d/m/Y H:i') ?? '—' }}
                    </div>
                    @if($os->completed_at)
                    <div class="text-xs text-gray-400">
                        Fin: {{ $os->completed_at->format('d/m/Y H:i') }}
                    </div>
                    @endif
                    @if($os->assignedTo)
                    <div class="text-xs text-blue-600">👷 {{ $os->assignedTo->name }}</div>
                    @endif
                    @if($os->notes)
                    <div class="text-xs text-gray-500 mt-1">{{ $os->notes }}</div>
                    @endif
                </div>
            </div>
            @empty
            <div class="text-xs text-gray-400">Sin historial de etapas</div>
            @endforelse
        </div>
    </div>

    {{-- Cancelar orden --}}
    @if(auth()->user()->isAdmin() && !in_array($order->status, ['delivered', 'cancelled']))
    <form method="POST" action="/production-orders/{{ $order->id }}/cancel"
        onsubmit="return confirm('¿Cancelar esta orden?')">
        @csrf
        <button class="w-full text-red-600 border border-red-200 py-2 rounded-xl text-sm mb-4">
            Cancelar orden
        </button>
    </form>
    @endif

</x-app-layout>