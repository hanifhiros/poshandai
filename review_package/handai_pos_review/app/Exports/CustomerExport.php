<?php

namespace App\Exports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CustomerExport implements FromQuery, WithChunkReading, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected int $storeId;

    public function __construct(int $storeId)
    {
        $this->storeId = $storeId;
    }

    public function query()
    {
        return Customer::where('store_id', $this->storeId)
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
            'Nickname',
            'No. Telepon',
            'Email',
            'Alamat',
            'Gender',
            'Qty Ordered',
            'Avg Qty Ordered',
            'Pernah Order',
            'Tgl Daftar',
        ];
    }

    public function map($customer): array
    {
        return [
            $customer->id,
            $customer->name,
            $customer->nickname ?? '-',
            $customer->contact_number ?? '-',
            $customer->email ?? '-',
            $customer->address ?? '-',
            $customer->gender ?? '-',
            $customer->qty_ordered ?? 0,
            $customer->qty_ordered_avg ?? 0,
            $customer->has_ordered ? 'Ya' : 'Belum',
            $customer->created_at?->format('Y-m-d') ?? '-',
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
