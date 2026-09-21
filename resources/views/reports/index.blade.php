<x-app-layout title="Reportes">
<div class="pt-4 space-y-6">

    <div>
        <h1 class="text-2xl font-black text-gray-900 tracking-tight">Reportes</h1>
        <p class="text-sm text-gray-400">Descargas en Excel — solo administradores.</p>
    </div>

    {{-- REPORTE FINANCIERO COMPLETO --}}
    <div class="bg-white border border-gray-100 rounded-2xl p-6 space-y-4">
        <h2 class="font-semibold text-gray-900">Financiero completo</h2>
        <p class="text-xs text-gray-500">
            Cartera/saldos, pendientes y órdenes "hechas" en el periodo, con datos de contacto del cliente.
        </p>

        <form method="GET" action="/reports/financial" class="space-y-4">
            <div>
                <label class="text-[10px] font-black text-gray-400 uppercase">Filtrar por</label>
                <select name="date_field" required
                        class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm mt-1 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option value="" disabled selected>Selecciona una fecha para filtrar...</option>
                    <option value="created_at">Fecha de creación del pedido</option>
                    <option value="delivered_at">Fecha de entrega</option>
                    <option value="production_completed_at">Fecha de fin de producción</option>
                </select>
                <p class="text-[11px] text-gray-400 mt-1">
                    Las 3 fechas se incluyen igual como columnas en el Excel, independientemente de cuál elijas aquí.
                </p>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-[10px] font-black text-gray-400 uppercase">Desde</label>
                    <input type="date" name="from"
                           class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm mt-1 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="text-[10px] font-black text-gray-400 uppercase">Hasta</label>
                    <input type="date" name="to"
                           class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm mt-1 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
            </div>
            <p class="text-[11px] text-gray-400">Si dejas "Desde"/"Hasta" vacíos, se incluyen todas las fechas.</p>

            <button type="submit" class="w-full bg-blue-700 text-white text-sm py-3 rounded-xl font-semibold cursor-pointer">
                Descargar Excel
            </button>
        </form>
    </div>

    {{-- REPORTE SEGUIMIENTO OPERATIVO --}}
    <div class="bg-white border border-gray-100 rounded-2xl p-6 space-y-4">
        <h2 class="font-semibold text-gray-900">Seguimiento operativo</h2>
        <p class="text-xs text-gray-500">
            Apoyo interno para hablar con cada trabajador: etapa actual, hace cuánto está ahí, y contexto del cliente.
            No es financiero. General — no filtrado por trabajador.
        </p>
        <a href="/reports/operational"
           class="block text-center w-full bg-gray-800 text-white text-sm py-3 rounded-xl font-semibold">
            Descargar Excel
        </a>
    </div>

    {{-- LISTADO DE PRODUCCIÓN PARA IMPRIMIR --}}
    <div class="bg-white border border-gray-100 rounded-2xl p-6 space-y-4">
        <h2 class="font-semibold text-gray-900">Listado de producción (PDF para imprimir)</h2>
        <p class="text-xs text-gray-500">
            Solo lo que sigue en el taller — excluye pedidos terminados/cancelados y los que ya salieron hacia el cliente.
        </p>
        <a href="/reports/print-production-list" target="_blank"
           class="block text-center w-full bg-gray-800 text-white text-sm py-3 rounded-xl font-semibold">
            Ver / Imprimir PDF
        </a>
    </div>

</div>
</x-app-layout>
