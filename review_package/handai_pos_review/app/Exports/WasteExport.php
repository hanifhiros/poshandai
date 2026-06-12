<?php

namespace App\Exports;

use App\Models\WasteLog;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class WasteExport implements FromQuery, WithChunkReading, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected int $storeId;

    public function __construct(int $storeId)
    {
        $this->storeId = $storeId;
    }

    public function query()
    {
        return WasteLog::with(['stock', 'productVariant.product', 'unit', 'pic'])
            ->where('store_id', $this->storeId)
            ->orderByDesc('waste_date');
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
            'Tipe Item',
            'Nama Item',
            'Qty',
            'Satuan',
            'Biaya/Unit',
            'Total Biaya',
            'Alasan',
            'PIC',
            'Catatan',
        ];
    }

    public function map($waste): array
    {
        $reasons = WasteLog::reasons();

        return [
            $waste->id,
            $waste->waste_date?->format('Y-m-d') ?? '-',
            $waste->item_type === 'stock' ? 'Bahan Baku' : 'Produk Jadi',
            $waste->item_name ?? ($waste->stock->name ?? ($waste->productVariant?->product?->name ?? '-')),
            $waste->quantity ?? 0,
            $waste->unit?->symbol ?? '-',
            $waste->cost_per_unit ?? 0,
            $waste->total_cost ?? 0,
            $reasons[$waste->reason] ?? $waste->reason ?? '-',
            $waste->pic?->name ?? '-',
            $waste->notes ?? '-',
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
