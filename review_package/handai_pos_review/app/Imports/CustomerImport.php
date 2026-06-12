<?php

namespace App\Imports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Illuminate\Support\Facades\DB;

class CustomerImport implements ToModel, WithHeadingRow, WithValidation, WithChunkReading, SkipsEmptyRows, SkipsOnError
{
    use Importable, SkipsErrors;

    protected int $storeId;
    protected int $userId;
    protected array $summary = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => []];

    protected array $customerCache = [];
    protected bool $cacheLoaded = false;

    public function __construct(int $storeId, int $userId)
    {
        $this->storeId = $storeId;
        $this->userId  = $userId;
    }

    public function chunkSize(): int
    {
        return (int) config('importexport.import_chunk_size', 1000);
    }

    protected function ensureCacheLoaded(): void
    {
        if ($this->cacheLoaded) return;

        Customer::where('store_id', $this->storeId)->get()->each(function ($c) {
            $this->customerCache[strtolower($c->name)] = $c;
        });

        $this->cacheLoaded = true;
    }

    protected function normalizeGender(string $raw): ?string
    {
        $upper = strtoupper(trim($raw));
        return match (true) {
            in_array($upper, ['L', 'LAKI-LAKI', 'MALE', 'M']) => 'Laki-laki',
            in_array($upper, ['P', 'PEREMPUAN', 'FEMALE', 'F', 'W']) => 'Perempuan',
            default => null,
        };
    }

    public function model(array $row)
    {
        $this->ensureCacheLoaded();

        $name = trim($row['nama'] ?? '');
        if (empty($name)) return null;

        $nameLower = strtolower($name);

        if (isset($this->customerCache[$nameLower])) {
            $exists = $this->customerCache[$nameLower];
            $fields = [];
            if (isset($row['nickname']) && $exists->nickname != trim($row['nickname'])) {
                $fields['nickname'] = trim($row['nickname']);
            }
            if (isset($row['no_telepon']) && $exists->contact_number != trim($row['no_telepon'])) {
                $fields['contact_number'] = trim($row['no_telepon']);
            }
            if (isset($row['email']) && $exists->email != trim($row['email'])) {
                $fields['email'] = trim($row['email']);
            }
            if (isset($row['alamat']) && $exists->address != trim($row['alamat'])) {
                $fields['address'] = trim($row['alamat']);
            }
            $genderRaw = $row['gender_lp'] ?? $row['gender'] ?? '';
            if ($genderRaw) {
                $gender = $this->normalizeGender($genderRaw);
                if ($gender && $exists->gender !== $gender) {
                    $fields['gender'] = $gender;
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

        $genderRaw = $row['gender_lp'] ?? $row['gender'] ?? '';
        $gender = $genderRaw ? $this->normalizeGender($genderRaw) : null;

        $this->summary['created']++;

        $customer = new Customer([
            'name'            => $name,
            'nickname'        => trim($row['nickname'] ?? ''),
            'contact_number'  => trim($row['no_telepon'] ?? ''),
            'email'           => trim($row['email'] ?? ''),
            'address'         => trim($row['alamat'] ?? ''),
            'gender'          => $gender,
            'store_id'        => $this->storeId,
            'created_by'      => $this->userId,
            'has_ordered'     => false,
            'qty_ordered'     => 0,
            'qty_ordered_avg' => 0,
        ]);

        $this->customerCache[$nameLower] = $customer;
        return $customer;
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
