<?php

namespace App\Exports;

use App\Exports\Concerns\HasDueDateTracking;
use App\Models\ProductionOrder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Reporte "Financiero completo" — exclusivo Admin (ver ReportController).
 * El filtro de periodo es explícito: el Admin elige cuál de las 3 fechas
 * usar para filtrar, pero las 3 siempre se incluyen como columnas.
 */
class FinancialFullReportExport implements FromCollection, WithHeadings, WithMapping, WithColumnFormatting, WithStyles
{
    use HasDueDateTracking;

    private const MONEY_COLUMNS = ['M', 'N', 'O', 'P', 'Q', 'R', 'S'];
    private const DATE_COLUMNS = ['T', 'U', 'V'];

    public function __construct(
        private readonly string $dateField,
        private readonly ?string $from,
        private readonly ?string $to,
    ) {
    }

    public function collection(): Collection
    {
        $query = ProductionOrder::with(['client.city', 'client.department', 'product', 'orderStages', 'orderDispatches']);

        match ($this->dateField) {
            'delivered_at' => $query->whereHas('orderDispatches', function ($q) {
                $q->whereNotNull('delivered_at');
                if ($this->from) {
                    $q->where('delivered_at', '>=', $this->from);
                }
                if ($this->to) {
                    $q->where('delivered_at', '<=', $this->to);
                }
            }),
            'production_completed_at' => $query->whereHas('orderStages', function ($q) {
                $q->whereNotNull('completed_at');
                if ($this->from) {
                    $q->where('completed_at', '>=', $this->from);
                }
                if ($this->to) {
                    $q->where('completed_at', '<=', $this->to);
                }
            }),
            default => $query
                ->when($this->from, fn ($q) => $q->where('created_at', '>=', $this->from))
                ->when($this->to, fn ($q) => $q->where('created_at', '<=', $this->to)),
        };

        return $query->orderBy('consecutive')->get();
    }

    public function headings(): array
    {
        return [
            'ID', 'Cliente', 'Teléfono', 'Dirección', 'Ciudad', 'Departamento',
            'Producto', 'Color', 'Calcomanía', 'Color de calcomanía',
            'Estado', 'Estado de despacho',
            'Precio producto', 'Precio envío',
            'Pagado envío', 'Saldo envío', 'Pagado producto', 'Saldo producto', 'Saldo total',
            'Fecha de creación', 'Fecha de fin de producción', 'Fecha de entrega',
            'Estado de tiempo', 'Días hábiles restantes',
        ];
    }

    public function map($order): array
    {
        [$timeStatusLabel, $businessDaysRemaining] = $this->dueDateTrackingColumns($order);

        return [
            $order->consecutive,
            optional($order->client)->full_name,
            optional($order->client)->phone,
            optional($order->client)->address,
            optional(optional($order->client)->city)->name,
            optional(optional($order->client)->department)->name,
            optional($order->product)->name,
            $order->color,
            $order->sticker ? 'Sí' : 'No',
            $order->sticker_color,
            $order->status_label,
            $order->dispatch_status_label,
            (float) $order->price,
            (float) $order->shipping_price,
            (float) $order->shipping_paid,
            (float) $order->shipping_balance,
            (float) $order->product_paid,
            (float) $order->product_balance,
            (float) $order->total_balance,
            $this->toExcelDate($order->created_at),
            $this->toExcelDate($order->production_completed_at),
            $this->toExcelDate(optional($order->latestDispatch())->delivered_at),
            $timeStatusLabel,
            $businessDaysRemaining,
        ];
    }

    public function columnFormats(): array
    {
        return array_merge(
            array_fill_keys(self::MONEY_COLUMNS, '"$"#,##0'),
            array_fill_keys(self::DATE_COLUMNS, NumberFormat::FORMAT_DATE_DDMMYYYY),
        );
    }

    public function styles(Worksheet $sheet): ?array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => '1D4ED8']]],
        ];
    }

    private function toExcelDate(?\DateTimeInterface $date): ?float
    {
        return $date ? ExcelDate::dateTimeToExcel($date) : null;
    }
}
