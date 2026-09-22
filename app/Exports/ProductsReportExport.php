<?php

namespace App\Exports;

use App\Models\Product;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Reporte de catálogo de productos — exclusivo Admin (ver ReportController).
 * Incluye base_price/shipping_price (financiero), sin filtros por ahora.
 */
class ProductsReportExport implements FromCollection, WithHeadings, WithMapping, WithColumnFormatting, WithStyles
{
    private const MONEY_COLUMNS = ['E', 'F'];

    public function collection(): Collection
    {
        return Product::orderBy('name')->get();
    }

    public function headings(): array
    {
        return [
            'Nombre', 'Descripción', 'Piezas', 'Días promedio de producción',
            'Precio base', 'Precio de envío', 'Activo',
        ];
    }

    public function map($product): array
    {
        return [
            $product->name,
            $product->description,
            $product->pieces,
            $product->avg_production_days,
            (float) $product->base_price,
            (float) $product->shipping_price,
            $product->active ? 'Sí' : 'No',
        ];
    }

    public function columnFormats(): array
    {
        return array_fill_keys(self::MONEY_COLUMNS, NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
    }

    public function styles(Worksheet $sheet): ?array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => '1D4ED8']]],
        ];
    }
}
