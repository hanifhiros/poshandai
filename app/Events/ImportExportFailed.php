<?php

namespace App\Events;

use App\Models\ImportExportHistory;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ImportExportFailed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public ImportExportHistory $history,
        public string $reason = '',
    ) {}
}
