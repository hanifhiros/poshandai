<?php

namespace Database\Seeders;

use App\Models\VariantAttribute;
use App\Models\VariantOption;
use Illuminate\Database\Seeder;

class VariantAttributeSeeder extends Seeder
{
    public function run(): void
    {
        // Buat attribute "Size"
        $sizeAttribute = VariantAttribute::firstOrCreate(
            ['name' => 'Size'],
            ['code' => 'SIZE']
        );

        // Tambah opsi untuk Size
        $sizeOptions = [
            ['name' => '250ml', 'code' => '250ML', 'sort_order' => 1],
            ['name' => '500ml', 'code' => '500ML', 'sort_order' => 2],
            ['name' => '1000ml', 'code' => '1000ML', 'sort_order' => 3],
            ['name' => 'Cup', 'code' => 'CUP', 'sort_order' => 4],
        ];

        foreach ($sizeOptions as $option) {
            VariantOption::firstOrCreate(
                ['attribute_id' => $sizeAttribute->id, 'name' => $option['name']],
                ['code' => $option['code'], 'sort_order' => $option['sort_order']]
            );
        }

        // Buat attribute "Type" (opsional untuk kategori lain)
        $typeAttribute = VariantAttribute::firstOrCreate(
            ['name' => 'Type'],
            ['code' => 'TYPE']
        );

        $typeOptions = [
            ['name' => 'Hot', 'code' => 'HOT'],
            ['name' => 'Cold', 'code' => 'COLD'],
            ['name' => 'Iced', 'code' => 'ICED'],
        ];

        foreach ($typeOptions as $option) {
            VariantOption::firstOrCreate(
                ['attribute_id' => $typeAttribute->id, 'name' => $option['name']],
                ['code' => $option['code']]
            );
        }
    }
}
