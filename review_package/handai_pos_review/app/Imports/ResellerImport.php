<?php

namespace App\Imports;

use App\Models\Reseller;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Illuminate\Support\Facades\DB;

class ResellerImport implements ToModel, WithHeadingRow, WithValidation, WithChunkReading, SkipsEmptyRows, SkipsOnError
{
    use Importable, SkipsErrors;

    protected int $storeId;
    protected array $summary = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => []];

    protected array $resellerCache = [];
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

        Reseller::with(['stores' => fn($q) => $q->where('store_id', $this->storeId)])->get()->each(function ($r) {
            $this->resellerCache[strtolower($r->name)] = $r;
        });

        $this->cacheLoaded = true;
    }

    public function model(array $row)
    {
        $this->ensureCacheLoaded();

        $name = trim($row['nama'] ?? '');
        if (empty($name)) return null;

        $nameLower = strtolower($name);

        if (isset($this->resellerCache[$nameLower])) {
            $exists = $this->resellerCache[$nameLower];

            DB::transaction(function () use ($exists, $row) {
                $fields = [];
                if (isset($row['kode']) && $exists->code != trim($row['kode'])) {
                    $fields['code'] = trim($row['kode']);
                }
                if (isset($row['telepon']) && $exists->phone != trim($row['telepon'])) {
                    $fields['phone'] = trim($row['telepon']);
                }
                if (!empty($fields)) {
                    $exists->update($fields);
                    $this->summary['updated']++;
                }

                // Handle pivot
                $paymentRate = floatval($row['payment_rate'] ?? 0);
                $pivot = $exists->stores()->where('store_id', $this->storeId)->first();
                if ($pivot) {
                    $current = $pivot->pivot->payment_rate ?? 0;
                    if ($current != $paymentRate) {
                        $exists->stores()->updateExistingPivot($this->storeId, ['payment_rate' => $paymentRate]);
                        $this->summary['updated']++;
                    }
                } else {
                    $exists->stores()->attach($this->storeId, ['payment_rate' => $paymentRate, 'qty_sold' => 0]);
                    $this->summary['updated']++;
                }
            });

            return null;
        }

        $this->summary['created']++;

        DB::transaction(function () use ($name, $row, $nameLower) {
            $reseller = Reseller::create([
                'name'   => $name,
                'code'   => trim($row['kode'] ?? ''),
                'phone'  => trim($row['telepon'] ?? ''),
                'status' => 'active',
            ]);

            $reseller->stores()->attach($this->storeId, [
                'payment_rate' => floatval($row['payment_rate'] ?? 0),
                'qty_sold'     => 0,
            ]);

            $this->resellerCache[$nameLower] = $reseller;
        });

        return null;
    }

    public function rules(): array
    {
        return [
            'nama' => 'required|string|max:255',
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'nama.required' => 'Kolom nama wajib diisi.',
        ];
    }

    public function getSummary(): array
    {
        return $this->summary;
    }
}
