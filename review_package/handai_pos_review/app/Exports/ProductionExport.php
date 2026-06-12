<?php

namespace App\Exports;

use App\Models\ProductionHistory;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductionExport implements FromQuery, WithChunkReading, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected int $storeId;

    public function __construct(int $storeId)
    {
        $this->storeId = $storeId;
    }

    public function query()
    {
        return ProductionHistory::with(['productVariants.product', 'pic', 'usages.stock'])
            ->where('store_id', $this->storeId)
            ->orderByDesc('production_date');
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Tanggal Produksi',
            'Produk',
            'Varian',
            'Qty Diproduksi',
            'PIC',
            'Bahan yang Dipakai',
        ];
    }

    public function map($history): array
    {
        $variant = $history->productVariants;
        $usages = $history->usages->map(function ($u) {
            return ($u->stock->name ?? '?') . ' (' . $u->quantity_used . ')';
        })->implode(', ');

        return [
            $history->id,
            $history->production_date?->format('Y-m-d') ?? '-',
            $variant?->product?->name ?? $history->product_name ?? '-',
            $variant?->size ?? $history->variant_option_summary ?? '-',
            $history->quantity_produced ?? 0,
            $history->pic?->name ?? '-',
            $usages ?: '-',
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
