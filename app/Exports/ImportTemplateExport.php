<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ImportTemplateExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    protected string $type;

    public function __construct(string $type)
    {
        $this->type = $type;
    }

    public function headings(): array
    {
        return match ($this->type) {
            'stock' => [
                'nama_bahan *',
                'kategori_stok (Bahan Baku/WIP/dll)',
                'satuan (kg/g/liter/pcs/dll) *',
                'stok_awal',
                'min_stok',
                'reorder_point',
                'hpp_per_unit',
                'supplier_default',
                'masa_expired_hari',
            ],
            'product' => [
                'nama_produk *',
                'nama_varian *',
                'kategori',
                'harga_jual *',
                'hpp',
                'stok_awal',
                'min_stok',
                'masa_expired_hari',
            ],
            'supplier' => [
                'nama_supplier *',
                'contact_person',
                'telepon',
                'email',
                'alamat',
                'kota',
                'payment_terms (COD/NET30/NET60)',
                'nama_bank',
                'no_rekening',
                'catatan',
            ],
            'customer' => [
                'nama *',
                'nickname',
                'no_telepon',
                'email',
                'alamat',
                'gender (L/P)',
            ],
            'reseller' => [
                'nama *',
                'kode',
                'telepon',
                'payment_rate',
            ],
            default => ['kolom_1', 'kolom_2'],
        };
    }

    public function array(): array
    {
        // Example row for guidance
        return match ($this->type) {
            'stock' => [[
                'Tepung Terigu',
                'Bahan Baku',
                'kg',
                10,
                2,
                5,
                12000,
                'PT Bogasari',
                30,
            ]],
            'product' => [[
                'Roti Tawar',
                'Regular',
                'Roti',
                25000,
                15000,
                50,
                10,
                7,
            ]],
            'supplier' => [[
                'PT Bogasari',
                'Budi',
                '08123456789',
                'budi@bogasari.com',
                'Jl. Raya No. 1',
                'Surabaya',
                'NET30',
                'BCA',
                '1234567890',
                'Supplier tepung utama',
            ]],
            'customer' => [[
                'Ahmad Rizky',
                'Rizky',
                '081234567890',
                'rizky@email.com',
                'Jl. Kenanga No. 5',
                'L',
            ]],
            'reseller' => [[
                'Toko Berkah',
                'RSL001',
                '081234567890',
                85,
            ]],
            default => [['contoh1', 'contoh2']],
        };
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '0C9044'],
                ],
            ],
            2 => [
                'font' => ['italic' => true, 'color' => ['rgb' => '999999']],
            ],
        ];
    }
}
