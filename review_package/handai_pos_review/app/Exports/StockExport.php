<?php

namespace App\Exports;

use App\Models\Stock;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StockExport implements FromQuery, WithChunkReading, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected int $storeId;

    public function __construct(int $storeId)
    {
        $this->storeId = $storeId;
    }

    /**
     * Use query builder so that Excel can chunk the results.
     */
    public function query()
    {
        return Stock::with(['unit', 'stockCategory', 'defaultSupplier'])
            ->where('store_id', $this->storeId)
            ->where('is_active', true)
            ->orderBy('name');
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nama Bahan',
            'Kategori',
            'Satuan',
            'Stok Saat Ini',
            'Min Stok',
            'Reorder Point',
            'HPP/Unit',
            'Nilai Inventori',
            'Supplier Default',
            'Masa Expired (hari)',
            'Status',
        ];
    }

    public function map($stock): array
    {
        return [
            $stock->id,
            $stock->name,
            $stock->stockCategory->name ?? '-',
            $stock->unit->symbol ?? '-',
            $stock->unit_qty,
            $stock->min_stock ?? 0,
            $stock->reorder_point ?? 0,
            $stock->price_per_unit ?? 0,
            $stock->inventory_value,
            $stock->defaultSupplier->name ?? '-',
            $stock->expired_duration ?? 0,
            $stock->status,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '0C9044'],
                ],
            ],
        ];
    }
}
