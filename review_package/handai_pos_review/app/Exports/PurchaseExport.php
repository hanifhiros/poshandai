<?php

namespace App\Exports;

use App\Models\StockBatch;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PurchaseExport implements FromQuery, WithChunkReading, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected int $storeId;

    public function __construct(int $storeId)
    {
        $this->storeId = $storeId;
    }

    public function query()
    {
        return StockBatch::with(['stock', 'unit', 'supplier'])
            ->where('store_id', $this->storeId)
            ->orderByDesc('buy_date');
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Tanggal Beli',
            'Nama Bahan',
            'Supplier',
            'Qty',
            'Satuan',
            'Biaya',
            'Diskon',
            'Pajak',
            'Metode Bayar',
            'No Invoice',
            'Catatan',
        ];
    }

    public function map($batch): array
    {
        return [
            $batch->id,
            $batch->buy_date?->format('Y-m-d') ?? '-',
            $batch->stock_name ?? ($batch->stock->name ?? '-'),
            $batch->supplier_name ?? ($batch->supplier->name ?? '-'),
            $batch->unit_qty ?? 0,
            $batch->unit?->symbol ?? '-',
            $batch->cost ?? 0,
            $batch->discount ?? 0,
            $batch->tax ?? 0,
            $batch->payment_method ?? '-',
            $batch->invoice_ref ?? '-',
            $batch->purchase_notes ?? '-',
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
