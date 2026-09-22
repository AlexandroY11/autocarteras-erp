<?php

namespace App\Exports;

use App\Models\Client;
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
 * Reporte de clientes — exclusivo Admin (ver ReportController). El conteo de
 * órdenes es histórico completo (incluye canceladas), igual que
 * DashboardController::$monthlyOrders — "cuánto ha pedido" es un conteo de
 * creación, no de trabajo activo, así que no se excluye nada por defecto.
 * Se reporta en 2 columnas separadas (Total / Canceladas) en vez de un solo
 * número neto, para no esconder el dato.
 */
class ClientsReportExport implements FromCollection, WithHeadings, WithMapping, WithColumnFormatting, WithStyles
{
    private const DATE_COLUMN = 'H';

    public function __construct(
        private readonly ?int $departmentId = null,
        private readonly ?int $cityId = null,
        private readonly ?string $from = null,
        private readonly ?string $to = null,
        private readonly ?bool $active = null,
    ) {}

    public function collection(): Collection
    {
        return Client::with(['city', 'department'])
            ->withCount([
                'productionOrders as total_orders',
                'productionOrders as cancelled_orders' => fn ($q) => $q->where('status', 'cancelled'),
            ])
            ->when($this->departmentId, fn ($q) => $q->where('department_id', $this->departmentId))
            ->when($this->cityId, fn ($q) => $q->where('city_id', $this->cityId))
            ->when($this->from, fn ($q) => $q->whereDate('created_at', '>=', $this->from))
            ->when($this->to, fn ($q) => $q->whereDate('created_at', '<=', $this->to))
            ->when($this->active !== null, fn ($q) => $q->where('active', $this->active))
            ->orderBy('first_name')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Nombre', 'Teléfono', 'Correo', 'Dirección', 'Ciudad', 'Departamento',
            'Activo', 'Fecha de registro', 'Total de órdenes', 'Órdenes canceladas',
        ];
    }

    public function map($client): array
    {
        return [
            $client->full_name,
            $client->phone,
            $client->email,
            $client->address,
            optional($client->city)->name,
            optional($client->department)->name,
            $client->active ? 'Sí' : 'No',
            $client->created_at ? ExcelDate::dateTimeToExcel($client->created_at) : null,
            $client->total_orders,
            $client->cancelled_orders,
        ];
    }

    public function columnFormats(): array
    {
        return [self::DATE_COLUMN => NumberFormat::FORMAT_DATE_DDMMYYYY];
    }

    public function styles(Worksheet $sheet): ?array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => '1D4ED8']]],
        ];
    }
}
