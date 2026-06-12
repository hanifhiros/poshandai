<?php

return [

    /*
    |--------------------------------------------------------------------------
    | File Validation
    |--------------------------------------------------------------------------
    */
    'max_file_size_kb' => env('IE_MAX_FILE_SIZE_KB', 10240), // 10 MB

    'allowed_mimes' => ['xlsx', 'xls', 'csv'],

    /*
    |--------------------------------------------------------------------------
    | Chunk & Performance
    |--------------------------------------------------------------------------
    */
    'export_chunk_size' => env('IE_EXPORT_CHUNK_SIZE', 2000),
    'import_chunk_size' => env('IE_IMPORT_CHUNK_SIZE', 1000),

    // Row threshold: above this, auto‑queue
    'queue_threshold' => env('IE_QUEUE_THRESHOLD', 5000),

    /*
    |--------------------------------------------------------------------------
    | Storage
    |--------------------------------------------------------------------------
    */
    'disk' => env('IE_STORAGE_DISK', 'local'),

    'paths' => [
        'exports'      => 'exports',
        'imports'       => 'imports',
        'error_logs'    => 'import-errors',
        'templates'     => 'import-templates',
    ],

    // Auto-delete files older than X days
    'retention_days' => env('IE_RETENTION_DAYS', 30),

    /*
    |--------------------------------------------------------------------------
    | Queue
    |--------------------------------------------------------------------------
    */
    'queue_name'    => env('IE_QUEUE_NAME', 'default'),
    'queue_timeout' => env('IE_QUEUE_TIMEOUT', 600),  // 10 minutes
    'queue_tries'   => env('IE_QUEUE_TRIES', 3),

    /*
    |--------------------------------------------------------------------------
    | Concurrency Lock
    |--------------------------------------------------------------------------
    */
    'lock_timeout' => env('IE_LOCK_TIMEOUT', 300), // seconds

    /*
    |--------------------------------------------------------------------------
    | Valid types & their configs
    |--------------------------------------------------------------------------
    */
    'types' => [
        'stock' => [
            'label'      => 'Bahan Baku',
            'importable' => true,
        ],
        'product' => [
            'label'      => 'Produk Jadi',
            'importable' => true,
        ],
        'supplier' => [
            'label'      => 'Supplier',
            'importable' => true,
        ],
        'customer' => [
            'label'      => 'Customer',
            'importable' => true,
        ],
        'reseller' => [
            'label'      => 'Reseller',
            'importable' => true,
        ],
        'recipe' => [
            'label'      => 'Resep (BOM)',
            'importable' => false,
        ],
        'production' => [
            'label'      => 'Riwayat Produksi',
            'importable' => false,
        ],
        'waste' => [
            'label'      => 'Waste / Basi',
            'importable' => false,
        ],
        'stock-movement' => [
            'label'      => 'Stock Movement',
            'importable' => false,
        ],
        'purchase' => [
            'label'      => 'Pembelian',
            'importable' => false,
        ],
    ],

];
