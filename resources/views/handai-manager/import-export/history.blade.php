@extends('handai-manager.layouts.master')
@section('title', 'History Import / Export')

@section('page-style')
<style>
    [x-cloak] { display: none !important; }
    :root {
        --io-bg: #f1f5f9; --io-card: #ffffff; --io-border: #e2e8f0;
        --io-muted: #94a3b8; --io-text: #0f172a; --io-secondary: #475569;
        --io-accent: #0C9044; --io-accent-light: #ecfdf5; --io-accent-hover: #0a7a3a;
        --io-success: #10b981; --io-warn: #f59e0b; --io-danger: #ef4444;
        --io-info: #3b82f6; --io-radius: 16px;
        --io-shadow: 0 1px 3px 0 rgba(0,0,0,.04), 0 1px 2px -1px rgba(0,0,0,.04);
    }
    .io-card { background: var(--io-card); border: 1px solid var(--io-border); border-radius: var(--io-radius); box-shadow: var(--io-shadow); }
    .io-btn { display: inline-flex; align-items: center; gap: 6px; height: 38px; padding: 0 18px; font-size: 13px; font-weight: 600; border-radius: 10px; border: none; cursor: pointer; transition: all .15s ease; white-space: nowrap; }
    .io-btn-sm { height: 32px; padding: 0 12px; font-size: 12px; gap: 4px; }
    .io-btn-outline { background: transparent; color: var(--io-secondary); border: 1.5px solid var(--io-border); }
    .io-btn-outline:hover { background: #f8fafc; border-color: #cbd5e1; }
    .io-btn-primary { background: var(--io-accent); color: #fff; }
    .io-btn-primary:hover { background: var(--io-accent-hover); }
    .io-btn-danger { background: var(--io-danger); color: #fff; }
    .io-btn-danger:hover { background: #dc2626; }
    .io-input { height: 36px; padding: 0 12px; font-size: 13px; border: 1px solid var(--io-border); border-radius: 10px; background: #fff; outline: none; transition: border .15s, box-shadow .15s; }
    .io-input:focus { border-color: var(--io-accent); box-shadow: 0 0 0 3px rgba(12,144,68,.1); }

    .io-badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; line-height: 1; }
    .io-badge-pending   { background: #e0e7ff; color: #4338ca; }
    .io-badge-validating{ background: #fef3c7; color: #92400e; }
    .io-badge-processing{ background: #dbeafe; color: #1e40af; }
    .io-badge-completed { background: #d1fae5; color: #065f46; }
    .io-badge-failed    { background: #fee2e2; color: #991b1b; }

    .io-progress { height: 6px; background: #e2e8f0; border-radius: 999px; overflow: hidden; }
    .io-progress-bar { height: 100%; background: var(--io-accent); border-radius: 999px; transition: width .4s ease; }
</style>
@endsection

@section('content')
<div class="p-4 md:p-8 max-w-screen-xl mx-auto" x-data="historyPage()" x-init="init()">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-800">History Import / Export</h1>
            <p class="text-[13px] text-gray-400 mt-1">Pantau semua proses import/export yang berjalan maupun selesai.</p>
        </div>
        <a href="{{ url()->previous() }}" class="io-btn io-btn-outline io-btn-sm">
            <i class="ti ti-arrow-left text-[15px]"></i> Kembali
        </a>
    </div>

    {{-- Filters --}}
    <div class="io-card p-4 mb-6 flex flex-wrap gap-3 items-end">
        <div>
            <label class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide block mb-1">Operasi</label>
            <select x-model="filter.operation" @change="applyFilter()" class="io-input w-36">
                <option value="">Semua</option>
                <option value="export">Export</option>
                <option value="import">Import</option>
            </select>
        </div>
        <div>
            <label class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide block mb-1">Tipe</label>
            <select x-model="filter.type" @change="applyFilter()" class="io-input w-40">
                <option value="">Semua</option>
                @foreach($types as $key => $cfg)
                    <option value="{{ $key }}">{{ $cfg['label'] }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide block mb-1">Status</label>
            <select x-model="filter.status" @change="applyFilter()" class="io-input w-36">
                <option value="">Semua</option>
                <option value="pending">Pending</option>
                <option value="validating">Validating</option>
                <option value="processing">Processing</option>
                <option value="completed">Completed</option>
                <option value="failed">Failed</option>
            </select>
        </div>
    </div>

    {{-- Table --}}
    <div class="io-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50/60">
                        <th class="px-4 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">#</th>
                        <th class="px-4 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Operasi</th>
                        <th class="px-4 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Tipe</th>
                        <th class="px-4 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">File</th>
                        <th class="px-4 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Progress</th>
                        <th class="px-4 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Rows</th>
                        <th class="px-4 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Durasi</th>
                        <th class="px-4 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Waktu</th>
                        <th class="px-4 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($histories as $h)
                    <tr class="hover:bg-gray-50/40 transition" x-data="{ row: {{ json_encode([
                            'id'               => $h->id,
                            'status'           => $h->status,
                            'progress_percent' => $h->progress_percent,
                            'total_rows'       => $h->total_rows,
                            'processed_rows'   => $h->processed_rows,
                            'success_rows'     => $h->success_rows,
                            'failed_rows'      => $h->failed_rows,
                            'is_downloadable'  => $h->is_downloadable,
                            'has_error_log'    => $h->has_error_log,
                        ]) }} }" x-init="
                            if(['pending','validating','processing'].includes(row.status)) {
                                registerPolling(row.id, (data) => { Object.assign(row, data); })
                            }
                        ">
                        <td class="px-4 py-3 text-[13px] text-gray-500">{{ $h->id }}</td>
                        <td class="px-4 py-3">
                            <span class="io-badge {{ $h->operation === 'export' ? 'io-badge-processing' : 'io-badge-pending' }}">
                                <i class="ti {{ $h->operation === 'export' ? 'ti-download' : 'ti-upload' }} text-[12px]"></i>
                                {{ ucfirst($h->operation) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-[13px] font-medium text-gray-700 capitalize">{{ $types[$h->type]['label'] ?? $h->type }}</td>
                        <td class="px-4 py-3 text-[12px] text-gray-500 max-w-[150px] truncate" title="{{ $h->file_name }}">{{ $h->file_name ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <span class="io-badge" :class="{
                                'io-badge-pending': row.status==='pending',
                                'io-badge-validating': row.status==='validating',
                                'io-badge-processing': row.status==='processing',
                                'io-badge-completed': row.status==='completed',
                                'io-badge-failed': row.status==='failed',
                            }">
                                <template x-if="['pending','validating','processing'].includes(row.status)">
                                    <span class="w-3 h-3 border-2 border-current border-t-transparent rounded-full animate-spin inline-block mr-1"></span>
                                </template>
                                <span x-text="row.status" class="capitalize"></span>
                            </span>
                        </td>
                        <td class="px-4 py-3 w-32">
                            <div class="io-progress">
                                <div class="io-progress-bar" :style="'width:'+row.progress_percent+'%'"></div>
                            </div>
                            <span class="text-[11px] text-gray-400" x-text="row.progress_percent+'%'"></span>
                        </td>
                        <td class="px-4 py-3 text-[12px] text-gray-600">
                            <span x-text="(row.success_rows||0)+'&#10003;'"></span>
                            <template x-if="row.failed_rows > 0">
                                <span class="text-red-500 ml-1" x-text="(row.failed_rows||0)+'&#10007;'"></span>
                            </template>
                            <span class="text-gray-400" x-text="'/ '+(row.total_rows||'?')"></span>
                        </td>
                        <td class="px-4 py-3 text-[12px] text-gray-500">{{ $h->duration_human ?? '-' }}</td>
                        <td class="px-4 py-3 text-[12px] text-gray-400">{{ $h->created_at?->format('d M Y H:i') }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-1">
                                {{-- Download --}}
                                <template x-if="row.is_downloadable">
                                    <a href="{{ route('manager.io.history.download', $h->id) }}"
                                       class="w-7 h-7 flex items-center justify-center rounded-lg hover:bg-emerald-50 text-emerald-600 transition"
                                       title="Download">
                                        <i class="ti ti-download text-[15px]"></i>
                                    </a>
                                </template>

                                {{-- Error Log --}}
                                <template x-if="row.has_error_log">
                                    <a href="{{ route('manager.io.history.errorlog', $h->id) }}"
                                       class="w-7 h-7 flex items-center justify-center rounded-lg hover:bg-red-50 text-red-500 transition"
                                       title="Download error log">
                                        <i class="ti ti-bug text-[15px]"></i>
                                    </a>
                                </template>

                                {{-- Retry --}}
                                @if($h->status === 'failed')
                                <form action="{{ route('manager.io.history.retry', $h->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit"
                                            class="w-7 h-7 flex items-center justify-center rounded-lg hover:bg-amber-50 text-amber-500 transition"
                                            title="Retry">
                                        <i class="ti ti-refresh text-[15px]"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="px-4 py-12 text-center">
                            <div class="flex flex-col items-center gap-2">
                                <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m6 4.125l2.25 2.25m0 0l2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>
                                </svg>
                                <p class="text-[13px] text-gray-400">Belum ada riwayat import/export.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($histories->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $histories->links() }}
        </div>
        @endif
    </div>

</div>
@endsection

@section('page-script')
<script>
function historyPage() {
    return {
        filter: { operation: '', type: '', status: '' },
        pollers: {},

        init() {
            // auto-poll active jobs every 3 seconds
        },

        applyFilter() {
            const params = new URLSearchParams();
            if (this.filter.operation) params.set('operation', this.filter.operation);
            if (this.filter.type) params.set('type', this.filter.type);
            if (this.filter.status) params.set('status', this.filter.status);
            window.location.search = params.toString();
        },

        registerPolling(id, callback) {
            if (this.pollers[id]) return;
            const url = `{{ url('manager/import-export/history') }}/${id}/status`;
            const interval = setInterval(async () => {
                try {
                    const res = await fetch(url);
                    const data = await res.json();
                    callback(data);
                    if (['completed', 'failed'].includes(data.status)) {
                        clearInterval(interval);
                        delete this.pollers[id];
                    }
                } catch (e) {
                    clearInterval(interval);
                }
            }, 3000);
            this.pollers[id] = interval;
        },
    };
}
</script>
@endsection
