<x-app-layout title="Mis Tareas">

<div class="pt-4 space-y-5">

    {{-- HEADER --}}
    <div>
        <h1 class="text-3xl font-black text-gray-900 tracking-tight">Mis tareas</h1>
        <p class="text-sm text-gray-500">Órdenes asignadas para producción</p>
    </div>

    {{-- EMPTY STATE --}}
    @if($myOrders->count() === 0)

        <div class="text-center py-24 bg-gray-50 rounded-[2rem] border border-dashed border-gray-200">
            <div class="text-5xl mb-3">✓</div>
            <div class="text-xl font-bold text-gray-400">Todo al día</div>
            <div class="text-sm text-gray-400 mt-1">No tienes tareas pendientes</div>
        </div>

    @else

        <div class="space-y-3">

            @foreach($myOrders as $order)

            <div class="bg-white rounded-[2rem] border border-gray-100 shadow-sm p-5 space-y-4">

                {{-- HEADER --}}
                <div class="flex justify-between items-start">

                    <div class="min-w-0">

                        <p class="text-xs font-bold text-gray-400">
                            #{{ str_pad($order->consecutive, 3, '0', STR_PAD_LEFT) }}
                        </p>

                        <h2 class="text-lg font-black text-gray-900 truncate">
                            {{ $order->client->full_name }}
                        </h2>

                        <p class="text-sm text-gray-500 truncate">
                            {{ $order->product->name }}
                        </p>

                    </div>

                    {{-- ESTADO FECHA --}}
                    <div class="text-right shrink-0">

                        @if($order->due_date->isPast())
                            <span class="text-xs font-bold text-red-600">
                                Vencida
                            </span>
                        @else
                            <span class="text-xs text-gray-500">
                                {{ $order->due_date->diffForHumans() }}
                            </span>
                        @endif

                    </div>

                </div>

                {{-- INFO RÁPIDA --}}
                <div class="flex items-center gap-4 text-xs text-gray-500">

                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/>
                        </svg>
                        {{ $order->color }}
                    </span>

                    @if($order->sticker)
                        <span class="flex items-center gap-1">
                            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M20 12H4"/>
                            </svg>
                            Calcomanía {{ $order->sticker_color }}
                        </span>
                    @endif

                </div>

                {{-- OBSERVACIONES --}}
                @if($order->observations)
                <div class="bg-gray-50 rounded-xl p-3">
                    <p class="text-xs font-bold text-gray-400 uppercase mb-1">
                        Observaciones
                    </p>
                    <p class="text-sm text-gray-700">
                        {{ $order->observations }}
                    </p>
                </div>
                @endif

                {{-- ETAPA ACTUAL --}}
                <div class="flex items-center justify-between">

                    <p class="text-xs font-bold text-gray-400 uppercase">
                        Etapa actual
                    </p>

                    @if($order->currentStage)
                        <span class="text-xs font-bold text-white px-3 py-1 rounded-full"
                              style="background: {{ $order->currentStage->color }}">
                            {{ $order->currentStage->name }}
                        </span>
                    @else
                        <span class="text-xs bg-gray-100 text-gray-600 px-3 py-1 rounded-full">
                            Sin etapa
                        </span>
                    @endif

                </div>

                {{-- ACCIÓN PRINCIPAL --}}
                @if($order->currentStage && auth()->user()->canAdvanceStage($order->currentStage->id))

                    <form method="POST"
                          action="/production-orders/{{ $order->id }}/advance-stage"
                          x-data="{ loading: false }"
                          @submit="loading = true">

                        @csrf

                        <button type="submit"
                                :disabled="loading"
                                class="w-full bg-blue-700 text-white font-black py-4 rounded-2xl shadow-md active:scale-95 transition-all flex items-center justify-center gap-2">

                            <svg x-show="!loading"
                                 class="w-5 h-5"
                                 fill="none"
                                 viewBox="0 0 24 24"
                                 stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M5 13l4 4L19 7"/>
                            </svg>

                            <span x-show="!loading">Marcar como completada</span>
                            <span x-show="loading">Guardando...</span>

                        </button>

                    </form>

                @endif

            </div>

            @endforeach

        </div>

    @endif

</div>

</x-app-layout>