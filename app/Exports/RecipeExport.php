<?php

namespace App\Exports;

use App\Models\ProductVariants;
use App\Models\Bom;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RecipeExport implements FromQuery, WithChunkReading, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected int $storeId;

    public function __construct(int $storeId)
    {
        $this->storeId = $storeId;
    }

    public function query()
    {
        return Bom::with(['stock.unit', 'ProductVariants.product', 'unit'])
            ->where('store_id', $this->storeId);
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function headings(): array
    {
        return [
            'ID Resep',
            'Produk',
            'Varian',
            'Bahan Baku',
            'Qty Dibutuhkan',
            'Satuan',
            'HPP Bahan/Unit',
            'Biaya per Resep',
        ];
    }

    public function map($bom): array
    {
        $variant = $bom->ProductVariants;
        $stock = $bom->stock;
        $costPerRecipe = ($bom->quantity_required ?? 0) * ($stock->price_per_unit ?? 0);

        return [
            $bom->id,
            $variant?->product?->name ?? '-',
            $variant?->size ?? $variant?->variant_option_summary ?? '-',
            $stock?->name ?? '-',
            $bom->quantity_required ?? 0,
            $bom->unit?->symbol ?? $stock?->unit?->symbol ?? '-',
            $stock?->price_per_unit ?? 0,
            round($costPerRecipe, 2),
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
