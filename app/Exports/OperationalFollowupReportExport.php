<?php

namespace App\Exports;

use App\Exports\Concerns\EscapesFormulaInjection;
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
 * Reporte "Seguimiento operativo" — exclusivo Admin (ver ReportController).
 * Es un apoyo interno para que el Admin hable con cada trabajador, no algo
 * que el trabajador vea directamente — no hay nada que excluir por defecto.
 * General, no filtrado por habilidad (el Admin decide con quién hablar).
 */
class OperationalFollowupReportExport implements FromCollection, WithHeadings, WithMapping, WithColumnFormatting, WithStyles
{
    use HasDueDateTracking;
    use EscapesFormulaInjection;

    private const DATE_COLUMNS = ['O', 'P', 'Q'];
    private const DATETIME_COLUMN = 'L';

    public function collection(): Collection
    {
        return ProductionOrder::with(['client.city', 'client.department', 'product', 'currentStage', 'orderStages', 'orderDispatches'])
            ->whereNotIn('status', ['cancelled'])
            ->orderBy('due_date')
            ->get();
    }

    public function headings(): array
    {
        return [
            'ID', 'Cliente', 'Teléfono', 'Dirección', 'Ciudad', 'Departamento',
            'Producto', 'Color', 'Calcomanía', 'Color de calcomanía',
            'Etapa actual', 'En esta etapa desde',
            'Estado', 'Estado de despacho',
            'Fecha compromiso (fin de producción)', 'Fecha de creación', 'Fecha de entrega real',
            'Estado de tiempo', 'Días hábiles restantes',
        ];
    }

    public function map($order): array
    {
        // El dato de "desde cuándo está en la etapa actual" puede faltar si
        // hay un historial inconsistente (ej. una migración vieja sin
        // order_stage abierto) — nunca debe tumbar la generación completa.
        $inCurrentStageSince = null;
        try {
            $openStage = $order->orderStages->whereNull('completed_at')->sortByDesc('started_at')->first();
            $inCurrentStageSince = $this->toExcelDate($openStage?->started_at);
        } catch (\Throwable $e) {
            $inCurrentStageSince = null;
        }

        [$timeStatusLabel, $businessDaysRemaining] = $this->dueDateTrackingColumns($order);

        return [
            $order->consecutive,
            $this->escapeFormula(optional($order->client)->full_name),
            optional($order->client)->phone,
            $this->escapeFormula(optional($order->client)->address),
            optional(optional($order->client)->city)->name,
            optional(optional($order->client)->department)->name,
            $this->escapeFormula(optional($order->product)->name),
            $order->color,
            $order->sticker ? 'Sí' : 'No',
            $order->sticker_color,
            optional($order->currentStage)->name,
            $inCurrentStageSince,
            $order->status_label,
            $order->dispatch_status_label,
            $this->toExcelDate($order->due_date),
            $this->toExcelDate($order->created_at),
            $this->toExcelDate(optional($order->latestDispatch())->delivered_at),
            $timeStatusLabel,
            $businessDaysRemaining,
        ];
    }

    public function columnFormats(): array
    {
        return array_merge(
            array_fill_keys(self::DATE_COLUMNS, NumberFormat::FORMAT_DATE_DDMMYYYY),
            [self::DATETIME_COLUMN => 'dd/mm/yyyy hh:mm'],
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
