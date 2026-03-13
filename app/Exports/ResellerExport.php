<?php

namespace App\Exports;

use App\Models\Reseller;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ResellerExport implements FromQuery, WithChunkReading, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected int $storeId;

    public function __construct(int $storeId)
    {
        $this->storeId = $storeId;
    }

    public function query()
    {
        return Reseller::whereHas('stores', fn($q) => $q->where('store_id', $this->storeId))
            ->with(['stores' => fn($q) => $q->where('store_id', $this->storeId)])
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
            'Nama',
            'Kode',
            'Telepon',
            'Status',
            'Payment Rate',
            'Qty Sold',
        ];
    }

    public function map($reseller): array
    {
        $pivot = $reseller->stores->first()?->pivot;

        return [
            $reseller->id,
            $reseller->name,
            $reseller->code ?? '-',
            $reseller->phone ?? '-',
            $reseller->status ?? '-',
            $pivot?->payment_rate ?? 0,
            $pivot?->qty_sold ?? 0,
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
