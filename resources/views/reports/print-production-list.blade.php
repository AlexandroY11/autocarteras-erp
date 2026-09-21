<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Listado de producción</title>
    <style>
        @page {
            size: letter landscape;
            margin: 0.4in;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #111827;
            font-size: 10.5pt;
        }

        .generated-at {
            text-align: right;
            font-size: 8pt;
            color: #6b7280;
            margin: 0 0 4px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        thead th {
            background-color: #111827;
            color: #ffffff;
            text-transform: uppercase;
            font-size: 9.5pt;
            font-weight: bold;
            text-align: left;
            padding: 8px 6px;
            border: 1px solid #111827;
        }

        tbody td {
            padding: 7px 6px;
            border-bottom: 1px solid #d1d5db;
            font-size: 10pt;
            vertical-align: top;
            word-wrap: break-word;
        }

        .col-id { width: 4%; }
        .col-cliente { width: 12%; }
        .col-telefono { width: 9%; }
        .col-producto { width: 11%; }
        .col-color { width: 13%; }
        .col-ciudad { width: 11%; }
        .col-etapa { width: 9%; }
        .col-estado { width: 8%; }
        .col-entrega { width: 9%; }
        .col-tiempo { width: 14%; }

        /* El encabezado puede partirse en 2 líneas sin problema (más alto,
           una sola vez); el dato de la celda nunca debe partirse a la mitad
           de un número/palabra corta. */
        tbody td.col-id,
        tbody td.col-telefono,
        tbody td.col-estado,
        tbody td.col-entrega {
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <p class="generated-at">Generado el {{ $generatedAt->format('d/m/Y H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th class="col-id">ID</th>
                <th class="col-cliente">Cliente</th>
                <th class="col-telefono">Teléfono</th>
                <th class="col-producto">Producto</th>
                <th class="col-color">Color / Calcomanía</th>
                <th class="col-ciudad">Ciudad / Depto.</th>
                <th class="col-etapa">Etapa actual</th>
                <th class="col-estado">Estado</th>
                <th class="col-entrega">Fecha compromiso</th>
                <th class="col-tiempo">Estado de tiempo</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $order)
                @php
                    $colorSummary = $order->sticker
                        ? "{$order->color} / " . ($order->sticker_color ?: 'Sí')
                        : $order->color;

                    $cityDept = collect([
                        optional($order->client->city ?? null)->name,
                        optional($order->client->department ?? null)->name,
                    ])->filter()->implode(', ');
                @endphp
                <tr>
                    <td class="col-id">{{ $order->consecutive }}</td>
                    <td class="col-cliente">{{ optional($order->client)->full_name }}</td>
                    <td class="col-telefono">{{ optional($order->client)->phone }}</td>
                    <td class="col-producto">{{ optional($order->product)->name }}</td>
                    <td class="col-color">{{ $colorSummary }}</td>
                    <td class="col-ciudad">{{ $cityDept }}</td>
                    <td class="col-etapa">{{ optional($order->currentStage)->name ?? 'Sin etapa' }}</td>
                    <td class="col-estado">{{ $order->status_label }}</td>
                    <td class="col-entrega">{{ optional($order->due_date)->format('d/m/Y') }}</td>
                    <td class="col-tiempo">{{ $order->time_status_summary }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" style="text-align: center; padding: 20px;">
                        No hay pedidos en taller pendientes de despacho.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
