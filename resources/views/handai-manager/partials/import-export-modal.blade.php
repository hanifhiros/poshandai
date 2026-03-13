{{-- Import/Export Modal Partial --}}
{{-- Usage: @include('handai-manager.partials.import-export-modal', ['type' => 'stock', 'label' => 'Bahan Baku']) --}}

<div x-data="{
        showImportExport: {{ session('modal_open') === ($type ?? '') ? 'true' : 'false' }},
        activeTab: '{{ session('modal_tab', 'export') }}',
        exporting: false,
        importing: false,
        historyId: {{ session('history_id') ?? 'null' }},
        pollStatus: null,
        pollProgress: 0,
        pollLabel: '',
        pollDone: false,
        pollFailed: false,
        async startExport(eventOrUrl) {
            this.exporting = true;
            let url = null;
            // accept either a string URL or an event from @click
            if (typeof eventOrUrl === 'string') {
                url = eventOrUrl;
            } else if (eventOrUrl && eventOrUrl.currentTarget) {
                // called from @click with $event on an <a>
                eventOrUrl.preventDefault();
                url = eventOrUrl.currentTarget.href;
            } else if (eventOrUrl && eventOrUrl.target) {
                // fallback: try to find enclosing anchor
                const a = eventOrUrl.target.closest && eventOrUrl.target.closest('a');
                if (a) {
                    eventOrUrl.preventDefault && eventOrUrl.preventDefault();
                    url = a.href;
                }
            }

            if (!url) {
                console.error('startExport: no url provided');
                this.exporting = false;
                return;
            }

            try {
                const res = await fetch(url, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
                const data = await res.json();
                if (res.ok && data && data.download_url) {
                    // create invisible iframe to trigger browser download without opening new tab
                    let iframe = document.getElementById('io-download-iframe');
                    if (!iframe) {
                        iframe = document.createElement('iframe');
                        iframe.id = 'io-download-iframe';
                        iframe.style.display = 'none';
                        document.body.appendChild(iframe);
                    }
                    const onLoad = () => { try{ iframe.removeEventListener('load', onLoad); }catch(e){} };
                    iframe.addEventListener('load', onLoad);
                    // set src to download URL — should trigger download
                    iframe.src = data.download_url;
                    // immediately clear UI: close modal and stop exporting
                    this.exporting = false;
                    this.showImportExport = false;
                    try{ window.dispatchEvent(new Event('loading-end')); }catch(e){}
                    // fallback safety: ensure cleared after 20s in case load never fires
                    setTimeout(() => { try{ iframe.removeEventListener('load', onLoad); }catch(e){}; this.exporting = false; this.showImportExport = false; }, 20000);
                    return;
                }
                if (data && data.error) {
                    alert(data.error);
                    try{ window.dispatchEvent(new Event('loading-end')); }catch(e){}
                } else {
                    console.error('Export endpoint returned non-OK status', res.status, data);
                    try{ window.dispatchEvent(new Event('loading-end')); }catch(e){}
                }
            } catch (e) {
                console.error('Failed to start export', e);
                try{ window.dispatchEvent(new Event('loading-end')); }catch(e){}
            } finally {
                if (!document.getElementById('io-download-iframe')) {
                    this.exporting = false;
                }
                try{ window.dispatchEvent(new Event('loading-end')); }catch(e){}
            }
        }
    }"
    x-init="
        if(historyId) { startPolling(historyId) }
    "
    x-effect="if(!showImportExport && pollStatus) { clearInterval(pollStatus); pollStatus=null; }"
    class="inline-block">
    {{-- Trigger Button --}}
    <button @click="showImportExport = true" type="button"
            class="h-9 inline-flex items-center gap-1.5 px-3.5 text-[13px] font-medium bg-white border border-gray-200 text-gray-600 hover:border-gray-300 hover:text-gray-700 rounded-lg transition cursor-pointer">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
        </svg>
        Import / Export
    </button>

    {{-- Modal --}}
    <template x-teleport="body">
        <div x-show="showImportExport" x-cloak
             class="fixed inset-0 z-[10000] flex items-center justify-center p-4"
             @keydown.escape.window="showImportExport = false">
            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-white/90 z-[10000]" @click="showImportExport = false"></div>

            {{-- Panel --}}
            <div x-show="showImportExport"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden z-[10001]">

                {{-- Header --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <div>
                        <h3 class="text-[16px] font-semibold text-gray-800">Import / Export</h3>
                        <p class="text-[12px] text-gray-400 mt-0.5">{{ $label ?? 'Data' }}</p>
                    </div>
                    <button @click="showImportExport = false"
                            class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-gray-100 transition text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Tabs --}}
                <div class="flex border-b border-gray-100 px-6">
                    <button @click="activeTab = 'export'"
                            class="px-4 py-3 text-[13px] font-medium border-b-2 transition"
                            :class="activeTab === 'export' ? 'border-emerald-500 text-emerald-600' : 'border-transparent text-gray-400 hover:text-gray-600'">
                        <svg class="w-4 h-4 inline -mt-0.5 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                        Export
                    </button>
                    <button @click="activeTab = 'import'"
                            class="px-4 py-3 text-[13px] font-medium border-b-2 transition"
                            :class="activeTab === 'import' ? 'border-emerald-500 text-emerald-600' : 'border-transparent text-gray-400 hover:text-gray-600'">
                        <svg class="w-4 h-4 inline -mt-0.5 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                        Import
                    </button>
                </div>

                {{-- flash messages (shown only when modal is open) --}}
                @if(session('modal_open') === ($type ?? ''))
                    @if(session('success'))
                        <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-[13px] flex items-center gap-2">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            {{ session('success') }}
                        </div>
                    @elseif(session('error'))
                        <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-[13px] flex items-center gap-2">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            {{ session('error') }}
                        </div>
                    @endif
                @endif

                {{-- Export Tab --}}
                <div x-show="activeTab === 'export'" class="p-6">
                    <p class="text-[13px] text-gray-500 mb-4">Download data {{ $label ?? '' }} lengkap ke file spreadsheet.</p>

                    {{-- Progress Indicator (shows when queued job active) --}}
                    <template x-if="historyId && !pollDone && !pollFailed">
                        <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-xl">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="w-4 h-4 border-2 border-blue-400 border-t-transparent rounded-full animate-spin"></span>
                                <span class="text-[13px] font-medium text-blue-700" x-text="pollLabel || 'Memproses...'"></span>
                            </div>
                            <div class="h-2 bg-blue-100 rounded-full overflow-hidden">
                                <div class="h-full bg-blue-500 rounded-full transition-all duration-300" :style="'width:'+pollProgress+'%'"></div>
                            </div>
                            <span class="text-[11px] text-blue-500 mt-1 block" x-text="pollProgress+'% selesai'"></span>
                        </div>
                    </template>

                    <div class="flex flex-col gap-2">
                        <div class="flex gap-3">
                                <a href="{{ route('manager.io.export', ['type' => $type, 'format' => 'xlsx']) }}"
                                    @click.prevent="startExport($event)"
                                    :class="exporting ? 'opacity-50 pointer-events-none' : ''"
                                    class="flex-1 h-11 inline-flex items-center justify-center gap-2 text-[13px] font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                                Download XLSX
                            </a>
                                     <a href="{{ route('manager.io.export', ['type' => $type, 'format' => 'csv']) }}"
                                         @click.prevent="startExport($event)"
                                         :class="exporting ? 'opacity-50 pointer-events-none' : ''"
                                         class="flex-1 h-11 inline-flex items-center justify-center gap-2 text-[13px] font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                                Download CSV
                            </a>
                        </div>
                        {{-- history link under export buttons --}}
                        <div class="text-right">
                            <a href="{{ route('manager.io.history') }}" class="text-[12px] text-gray-500 hover:text-emerald-600 transition">
                                History Import/Export
                            </a>
                        </div>
                    </div>

                    @if(in_array($type ?? '', ['stock', 'product', 'supplier', 'customer', 'reseller']))
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <p class="text-[12px] text-gray-400 mb-2">Atau ambil template kosong untuk import:</p>
                        <a href="{{ route('manager.io.template', $type) }}"
                           class="inline-flex items-center gap-1.5 text-[12px] font-medium text-emerald-600 hover:text-emerald-700 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                            Download Template Import
                        </a>
                    </div>
                    @endif
                </div>

                {{-- Import Tab --}}
                <div x-show="activeTab === 'import'" class="p-6">
                    @if(in_array($type ?? '', ['stock', 'product', 'supplier', 'customer', 'reseller']))
                    <form action="{{ route('manager.io.import', $type) }}" method="POST" enctype="multipart/form-data"
                          x-data="{ fileName: '', dragover: false }"
                          @submit.stop>
                        @csrf

                        <p class="text-[13px] text-gray-500 mb-3">Upload file XLSX/CSV untuk menambah data {{ $label ?? '' }} secara massal.</p>

                        {{-- Drag & Drop Area --}}
                        <div class="relative border-2 border-dashed rounded-xl p-6 text-center transition"
                             :class="dragover ? 'border-emerald-400 bg-emerald-50' : 'border-gray-200 hover:border-gray-300'"
                             @dragover.prevent="dragover = true"
                             @dragleave.prevent="dragover = false"
                             @drop.prevent="dragover = false; $refs.fileInput.files = $event.dataTransfer.files; fileName = $refs.fileInput.files[0]?.name || ''">

                            <input type="file" name="file" x-ref="fileInput" accept=".xlsx,.xls,.csv" required
                                   @change="fileName = $refs.fileInput.files[0]?.name || ''"
                                   class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">

                            <div x-show="!fileName">
                                <svg class="w-8 h-8 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                                </svg>
                                <p class="text-[13px] text-gray-500">Drag & drop file di sini, atau <span class="text-emerald-600 font-medium">klik untuk browse</span></p>
                                <p class="text-[11px] text-gray-400 mt-1">Format: .xlsx, .xls, .csv (maks 10MB)</p>
                            </div>

                            <div x-show="fileName" class="flex items-center justify-center gap-2">
                                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="text-[13px] font-medium text-gray-700" x-text="fileName"></span>
                            </div>
                        </div>

                        {{-- Queue Option --}}
                        <div class="flex items-center gap-2 mt-3">
                            <input type="checkbox" name="queue" id="queueImport_{{ $type }}" class="h-4 w-4 text-emerald-600 focus:ring-emerald-500 border-gray-300 rounded">
                            <label for="queueImport_{{ $type }}" class="text-[12px] text-gray-600">Proses di latar belakang (antrian)</label>
                        </div>

                        {{-- Duplicate Strategy --}}
                        <div class="mt-3">
                            <label class="text-[12px] font-medium text-gray-500 block mb-1">Jika data duplikat:</label>
                            <select name="duplicate_strategy" class="w-full h-9 text-[13px] border border-gray-200 rounded-lg px-3 bg-white outline-none focus:border-emerald-400 focus:ring-1 focus:ring-emerald-100">
                                <option value="skip">Lewati (skip)</option>
                                <option value="update">Update yang ada</option>
                                <option value="error">Tandai error</option>
                            </select>
                        </div>

                        {{-- Template & History Links (side-by-side) --}}
                        <div class="mt-4 grid grid-cols-2 gap-4 items-center">
                            <a href="{{ route('manager.io.template', $type) }}"
                               class="flex items-center gap-2 text-[12px] text-emerald-600 hover:text-emerald-700 font-medium transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                                </svg>
                                Template Import
                            </a>

                            <a href="{{ route('manager.io.history') }}"
                               class="flex items-center gap-2 text-[12px] text-gray-500 hover:text-emerald-600 font-medium transition justify-end">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                History
                            </a>
                        </div>

                        {{-- Submit button --}}
                        <div class="mt-4">
                            <button type="submit"
                                    class="h-10 px-5 inline-flex items-center gap-2 text-[13px] font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition shadow-sm disabled:opacity-50"
                                    :disabled="!fileName || importing"
                                    @click.stop="importing=true">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                                Upload & Import
                            </button>
                        </div>
                    </form>
                    @else
                    <div class="text-center py-6">
                        <svg class="w-10 h-10 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                        </svg>
                        <p class="text-[13px] text-gray-500">Import tidak tersedia untuk tipe data ini.</p>
                        <p class="text-[12px] text-gray-400 mt-1">Gunakan fitur export untuk mengunduh data.</p>
                    </div>
                    @endif
                </div>

            </div>
        </div>
    </template>
</div>

@once
@push('scripts')
<script>
function startPolling(historyId) {
    if (!historyId) return;
    const url = `{{ url('manager/import-export/history') }}/${historyId}/status`;
    const el = document.querySelector('[x-data]'); // Alpine picks up from context
    const interval = setInterval(async () => {
        try {
            const res = await fetch(url);
            const data = await res.json();
            // The x-data component will read these via Alpine reactivity
            window.dispatchEvent(new CustomEvent('io-poll-update', { detail: data }));
            if (['completed', 'failed'].includes(data.status)) {
                clearInterval(interval);
            }
        } catch (e) {
            clearInterval(interval);
        }
    }, 3000);
}
</script>
@endpush
@endonce