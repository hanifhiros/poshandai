<?php

namespace App\Exports;

use App\Models\ProductVariants;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductExport implements FromQuery, WithChunkReading, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected int $storeId;

    public function __construct(int $storeId)
    {
        $this->storeId = $storeId;
    }

    public function query()
    {
        return ProductVariants::with(['product.category'])
            ->where(function ($q) {
                $q->where('store_id', $this->storeId)
                  ->orWhereHas('product', fn($sub) => $sub->where('store_id', $this->storeId));
            })
            ->orderBy('product_name');
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nama Produk',
            'Varian',
            'Kategori',
            'Harga Jual',
            'HPP',
            'Margin %',
            'Stok',
            'Min Stok',
            'Status',
        ];
    }

    public function map($variant): array
    {
        return [
            $variant->id,
            $variant->product->name ?? $variant->product_name ?? '-',
            $variant->size ?? $variant->variant_option_summary ?? '-',
            $variant->product->category->category_name ?? '-',
            $variant->price ?? 0,
            $variant->hpp ?? 0,
            $variant->margin_percent ?? 0,
            $variant->quantity ?? 0,
            $variant->min_stock ?? 0,
            $variant->fg_status ?? 'Ready',
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
