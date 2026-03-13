<?php

namespace App\Exports;

use App\Models\StockMovement;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StockMovementExport implements FromQuery, WithChunkReading, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected int $storeId;

    public function __construct(int $storeId)
    {
        $this->storeId = $storeId;
    }

    public function query()
    {
        return StockMovement::with(['stock', 'productVariant.product', 'unit'])
            ->where('store_id', $this->storeId)
            ->orderByDesc('created_at');
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Tanggal',
            'Tipe Gerakan',
            'Bahan/Produk',
            'Qty',
            'Satuan',
            'Biaya/Unit',
            'Total Biaya',
            'Referensi',
            'Catatan',
        ];
    }

    public function map($movement): array
    {
        $itemName = $movement->stock->name
            ?? ($movement->productVariant?->product?->name ?? '-');

        return [
            $movement->id,
            $movement->created_at?->format('Y-m-d H:i') ?? '-',
            $movement->movement_type ?? '-',
            $itemName,
            $movement->quantity ?? 0,
            $movement->unit?->symbol ?? '-',
            $movement->cost_per_unit ?? 0,
            $movement->total_cost ?? 0,
            $movement->reference_type ?? '-',
            $movement->notes ?? '-',
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
