<?php

namespace App\Imports;

use App\Models\Supplier;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Illuminate\Support\Facades\DB;

class SupplierImport implements ToModel, WithHeadingRow, WithValidation, WithChunkReading, SkipsEmptyRows, SkipsOnError
{
    use Importable, SkipsErrors;

    protected int $storeId;
    protected array $summary = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => []];

    protected array $supplierCache = [];
    protected bool $cacheLoaded = false;

    public function __construct(int $storeId)
    {
        $this->storeId = $storeId;
    }

    public function chunkSize(): int
    {
        return (int) config('importexport.import_chunk_size', 1000);
    }

    protected function ensureCacheLoaded(): void
    {
        if ($this->cacheLoaded) return;

        Supplier::where('store_id', $this->storeId)->get()->each(function ($s) {
            $this->supplierCache[strtolower($s->name)] = $s;
        });

        $this->cacheLoaded = true;
    }

    public function model(array $row)
    {
        $this->ensureCacheLoaded();

        $name = trim($row['nama_supplier'] ?? '');
        if (empty($name)) return null;

        $nameLower = strtolower($name);

        // Check existing from cache
        if (isset($this->supplierCache[$nameLower])) {
            $exists = $this->supplierCache[$nameLower];
            $fields = [];
            $map = [
                'contact_person'             => 'contact_person',
                'telepon'                    => 'phone',
                'email'                      => 'email',
                'alamat'                     => 'address',
                'kota'                       => 'city',
                'payment_terms_codnet30net60' => 'payment_terms',
                'nama_bank'                  => 'bank_name',
                'no_rekening'                => 'bank_account',
                'catatan'                    => 'notes',
            ];
            foreach ($map as $col => $attr) {
                if (isset($row[$col])) {
                    $value = trim($row[$col]);
                    if ($attr === 'payment_terms') {
                        $value = strtoupper($value ?: 'COD');
                    }
                    if ($exists->{$attr} != $value) {
                        $fields[$attr] = $value;
                    }
                }
            }
            if (!empty($fields)) {
                DB::transaction(fn() => $exists->update($fields));
                $this->summary['updated']++;
            } else {
                $this->summary['skipped']++;
            }
            return null;
        }

        $this->summary['created']++;

        $supplier = new Supplier([
            'store_id'       => $this->storeId,
            'name'           => $name,
            'contact_person' => trim($row['contact_person'] ?? ''),
            'phone'          => trim($row['telepon'] ?? ''),
            'email'          => trim($row['email'] ?? ''),
            'address'        => trim($row['alamat'] ?? ''),
            'city'           => trim($row['kota'] ?? ''),
            'payment_terms'  => strtoupper(trim($row['payment_terms_codnet30net60'] ?? 'COD')),
            'bank_name'      => trim($row['nama_bank'] ?? ''),
            'bank_account'   => trim($row['no_rekening'] ?? ''),
            'notes'          => trim($row['catatan'] ?? ''),
            'is_active'      => true,
        ]);

        $this->supplierCache[$nameLower] = $supplier;
        return $supplier;
    }

    public function rules(): array
    {
        return [
            'nama_supplier' => 'required|string|max:255',
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'nama_supplier.required' => 'Kolom nama_supplier wajib diisi.',
        ];
    }

    public function getSummary(): array
    {
        return $this->summary;
    }
}
