<x-app-layout title="Calendario de Producción">

    <div class="pt-4 pb-10 space-y-4">
        {{-- HEADER --}}
        <div class="flex items-center justify-between px-4">
            <h1 class="text-2xl font-black text-gray-900">Calendario</h1>
            <a href="/orders" class="text-sm font-bold text-blue-600 bg-blue-50 px-3 py-1.5 rounded-xl">
                Volver a Lista
            </a>
        </div>

        {{-- CONTENEDOR DEL CALENDARIO --}}
        <div class="bg-white p-2 sm:p-4 rounded-[2.5rem] border border-gray-100 shadow-sm overflow-hidden">
            <div id='calendar'></div>
        </div>
    </div>

    <style>

        /* Festivos en Rojo */
        .holiday-cell { 
            background-color: #fee2e2 !important; /* Rojo muy suave de fondo */
        }
        .holiday-cell .fc-daygrid-day-number { 
            color: #dc2626 !important; /* Número en rojo intenso */
            font-weight: 900 !important;
        }

        /* Estilos para que combine con tu CRM */
        .fc { --fc-border-color: #f3f4f6; --fc-today-bg-color: #eff6ff; font-family: inherit; }
        .fc .fc-toolbar-title { font-size: 1.1rem; font-weight: 900; text-transform: capitalize; color: #111827; }
        .fc .fc-button-primary { background-color: #1d4ed8; border-color: #1d4ed8; font-weight: bold; border-radius: 12px; text-transform: capitalize; }
        .fc .fc-button-primary:hover { background-color: #1e40af; }
        .fc .fc-col-header-cell { background: #f9fafb; padding: 10px 0; }
        .fc .fc-col-header-cell-cushion { font-size: 0.75rem; font-weight: 800; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; }
        .fc .fc-daygrid-day-number { font-weight: 800; color: #374151; font-size: 0.85rem; padding: 8px; }

        /* Ajuste de altura de las cards */
        .fc-daygrid-event {
            margin-top: 1px !important;
            margin-bottom: 1px !important;
            cursor: pointer !important;
        }

        /* Evitar que el texto se salga */
        .fc-event-main {
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Hacer que el contenedor del día sea más alto en móvil para que quepan más cards */
        @media (max-width: 640px) {
            .fc .fc-daygrid-day-frame {
                min-height: 80px !important;
            }
        }

        
        /* Eventos (Órdenes ) */
        .fc-event { border: none !important; padding: 2px 4px; border-radius: 8px !important; margin: 1px 2px !important; shadow: 0 1px 2px rgba(0,0,0,0.05); }
        .fc-event-title { font-weight: 800 !important; font-size: 9px !important; white-space: normal !important; }
        
        /* Ajuste para móviles */
        @media (max-width: 640px) {
            .fc .fc-toolbar { flex-direction: column; gap: 8px; }
            #calendar { min-height: 500px; }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const holidays = @json($holidays);

            const ordersData = {
                @foreach($orders as $date => $dateOrders)
                    '{{ $date }}': [
                        @foreach($dateOrders as $order)
                        {
                            id: '{{ $order->id }}',
                            consecutive: '{{ str_pad($order->consecutive, 3, "0", STR_PAD_LEFT) }}',
                            product: '{{ addslashes($order->product->name) }}',
                            client: '{{ addslashes($order->client->full_name) }}',
                            stage: '{{ $order->currentStage->name ?? "Sin etapa" }}',
                            color: '{{ $order->currentStage->color ?? "#3b82f6" }}'
                        },
                        @endforeach
                    ],
                @endforeach
            };

            const calendarEl = document.getElementById('calendar');
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'es',
                firstDay: 1,
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth'
                },

                // CLIC EN EL CUADRO DEL DÍA
                dateClick: function(info) {
                    window.location.href = '/production-orders/calendar/' + info.dateStr;
                },

                // CLIC EN LA CARD (Evento) - Redirige al mismo día
                eventClick: function(info) {
                    const dateStr = info.event.startStr;
                    window.location.href = '/production-orders/calendar/' + dateStr;
                },

                events: [
                    @foreach($orders as $date => $dateOrders)
                        @foreach($dateOrders as $order)
                        {
                            title: '{{ addslashes($order->product->name) }}',
                            start: '{{ $order->due_date->format("Y-m-d") }}',
                            backgroundColor: '{{ $order->currentStage->color ?? "#3b82f6" }}',
                            extendedProps: { 
                                consecutive: '{{ str_pad($order->consecutive, 3, "0", STR_PAD_LEFT) }}',
                                stage: '{{ $order->currentStage->name ?? "" }}',
                                timeStatus: '{{ $order->time_status }}'
                            }
                        },
                        @endforeach
                    @endforeach
                ],

                // PERSONALIZACIÓN VISUAL DE LA CARD
                eventContent: function(arg) {
                    const status = arg.event.extendedProps.timeStatus;
                    const consecutive = arg.event.extendedProps.consecutive;
                    
                    // Color del borde según estado de tiempo
                    let borderColor = 'transparent';
                    if (status === 'overdue' || status === 'critical') borderColor = '#dc2626';
                    else if (status === 'warning') borderColor = '#f59e0b';

                    return {
                        html: `
                            <div class="flex flex-col p-1 overflow-hidden" style="border-left: 3px solid ${borderColor}">
                                <div class="flex items-center gap-1">
                                    <span class="text-[8px] font-black opacity-70">#${consecutive}</span>
                                    <span class="text-[9px] font-bold truncate">${arg.event.title}</span>
                                </div>
                                <div class="text-[7px] font-black uppercase opacity-80 truncate">
                                    ${arg.event.extendedProps.stage}
                                </div>
                            </div>
                        `
                    };
                },

                dayCellDidMount: function(info) {
                    const holidays = @json($holidays ?? []);
                    const dateStr = info.date.toISOString().split('T')[0];
                    if (holidays.includes(dateStr)) {
                        info.el.classList.add('holiday-cell');
                    }
                    info.el.style.cursor = 'pointer';
                }
            });

            calendar.render();
        });
    </script>
</x-app-layout>