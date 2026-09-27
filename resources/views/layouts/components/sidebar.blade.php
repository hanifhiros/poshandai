<div x-data="sidebarOffcanvas()" x-init="init()" class="flex bg-indigo-50">
  <nav x-bind:class="sidebarClass()" class="overflow-hidden flex flex-col border-none shadow-xl">
    <div class="bg-[#0C9044] text-white py-4 rounded-br-lg ">
      <div class="flex items-center justify-between">
        <div class="flex items-center ">
          <div class="ms-2 grid w-16 h-16 shrink-0 place-content-center">
            <img src="{{ asset('assets/favicon.ico') }}" alt="Handai Logo" width="38" height="auto"
              class="object-contain" />
          </div>

          <div>
            <p x-show="open && !open" x-transition class=" font-bold text-3xl  tracking-tighter text-nowrap">
              Handai Coffee
            </p>
            <h1 x-show="open  && open" x-transition class=" font-bold text-2xl tracking-tighter text-nowrap">
              Handai Coffee
            </h1>
          </div>
        </div>

        <div x-show="isMobile && open" x-transition class="cursor-pointer">
        </div>
      </div>
    </div>

    @php use App\Helpers\RoleHelper; @endphp
    
    @if(session('is_simulating') || (auth()->check() && auth()->user()->role === 'Superadmin'))
      <a href="{{ route('superadmin.dashboard') }}"
         @click="selected = 'Superadmin Dashboard'"
         :class="selected === 'Superadmin Dashboard' ? 'bg-green-600/10 text-green-800' : 'text-slate-500 hover:bg-slate-100'"
         class="relative flex h-15 w-full items-center rounded-md transition-colors cursor-pointer space-y-1 p-3 mt-3 ">
          <div class="grid h-full w-16 place-content-center">
              <i :class="selected === 'Superadmin Dashboard' ? 'ti ti-device-desktop-filled' : 'ti ti-device-desktop'"></i>
          </div>
          <span x-show="open" x-transition class="absolute ml-16 text font-medium ">
              Kembali ke Superadmin
          </span>
      </a>
    @endif
    
    <div x-data="{ selected: '{{ $categoryName ?? '' }}' }" class="flex-1 overflow-y-auto space-y-1 p-3 hide-scrollbar">
      
      <div class="h-3 mt-3 flex items-center">
          <p x-show="open" x-transition class="text-slate-600 ps-6 text-sm font-semibold">POS / Kasir</p>
          <hr x-show="!open" x-transition class="border-t border-slate-300 w-full" />
      </div>

      <a :href="'/pos/products?category=All Products'" class="relative flex h-15 w-full items-center rounded-md transition-colors ">
          <button type="button" @click="selected = 'All Products'" 
                  :class="selected === 'All Products' ? 'bg-green-600/10 text-green-800' : 'text-slate-500 hover:bg-slate-100'"
                  class="relative flex h-15 w-full items-center rounded-md transition-colors cursor-pointer">
              <div class="grid h-full w-16 place-content-center">
                  <i :class="selected === 'All Products' ? 'ti ti-category-filled' : 'ti ti-category'"></i>
              </div>
              <span x-show="open" x-transition class="absolute ml-16 text font-medium text-nowrap">
                  All Products
              </span>
          </button>
      </a>

      @foreach (($categories ?? []) as $category)
          <a :href="'/pos/products?category=' + '{{ $category->category_name ?? '' }}'" class="relative flex h-15 w-full items-center rounded-md transition-colors">
              <button type="button" 
                @click="selected = '{{ $category->category_name ?? '' }}'"
                      :class="{
                  'bg-green-600/10 text-green-800': selected === '{{ $category->category_name ?? '' }}',
                  'text-slate-500 hover:bg-slate-100': selected !== '{{ $category->category_name ?? '' }}'
                      }"
                      class="relative flex h-15 w-full items-center rounded-md transition-colors cursor-pointer">
                  <div class="grid h-full w-16 place-content-center">
                <i :class="selected === '{{ $category->category_name ?? '' }}' ? '{{ $category->category_icon ?? '' }}-filled' : '{{ $category->category_icon ?? '' }}'"></i>
                  </div>
                  <span x-show="open" x-transition class="absolute ml-16 text font-medium text-nowrap">
                {{ $category->category_name ?? '' }}
                  </span>
              </button>
          </a>
      @endforeach

      <div class="h-3 mt-6 mb-2 flex items-center">
          <p x-show="open" x-transition class="text-slate-600 ps-6 text-sm font-semibold">Operasional</p>
          <hr x-show="!open" x-transition class="border-t border-slate-300 w-full" />
      </div>

      <a href="{{ route('manager.dashboard') }}" class="relative flex h-15 w-full items-center rounded-md transition-colors">
          <button type="button" @click="selected = 'Manager Dashboard'" 
                  :class="selected === 'Manager Dashboard' ? 'bg-green-600/10 text-green-800' : 'text-slate-500 hover:bg-slate-100'"
                  class="relative flex h-15 w-full items-center rounded-md transition-colors cursor-pointer">
              <div class="grid h-full w-16 place-content-center">
                  <i :class="selected === 'Manager Dashboard' ? 'ti ti-layout-dashboard-filled' : 'ti ti-layout-dashboard'"></i>
              </div>
              <span x-show="open" x-transition class="absolute ml-16 text font-medium text-nowrap">
                  Dashboard Utama
              </span>
          </button>
      </a>

      <a href="{{ route('manager.operational.orders.index') }}" class="relative flex h-15 w-full items-center rounded-md transition-colors">
          <button type="button" @click="selected = 'Pesanan'" 
                  :class="selected === 'Pesanan' ? 'bg-green-600/10 text-green-800' : 'text-slate-500 hover:bg-slate-100'"
                  class="relative flex h-15 w-full items-center rounded-md transition-colors cursor-pointer">
              <div class="grid h-full w-16 place-content-center">
                  <i :class="selected === 'Pesanan' ? 'ti ti-receipt-filled' : 'ti ti-receipt'"></i>
              </div>
              <span x-show="open" x-transition class="absolute ml-16 text font-medium text-nowrap">
                  Pesanan
              </span>
          </button>
      </a>

      <a href="{{ route('manager.inventory.products') }}" class="relative flex h-15 w-full items-center rounded-md transition-colors">
          <button type="button" @click="selected = 'Produk'" 
                  :class="selected === 'Produk' ? 'bg-green-600/10 text-green-800' : 'text-slate-500 hover:bg-slate-100'"
                  class="relative flex h-15 w-full items-center rounded-md transition-colors cursor-pointer">
              <div class="grid h-full w-16 place-content-center">
                  <i :class="selected === 'Produk' ? 'ti ti-box-filled' : 'ti ti-box'"></i>
              </div>
              <span x-show="open" x-transition class="absolute ml-16 text font-medium text-nowrap">
                  Produk / Etalase
              </span>
          </button>
      </a>

      <a href="{{ route('manager.inventory.stock') }}" class="relative flex h-15 w-full items-center rounded-md transition-colors">
          <button type="button" @click="selected = 'Stok'" 
                  :class="selected === 'Stok' ? 'bg-green-600/10 text-green-800' : 'text-slate-500 hover:bg-slate-100'"
                  class="relative flex h-15 w-full items-center rounded-md transition-colors cursor-pointer">
              <div class="grid h-full w-16 place-content-center">
                  <i :class="selected === 'Stok' ? 'ti ti-packages' : 'ti ti-package'"></i>
              </div>
              <span x-show="open" x-transition class="absolute ml-16 text font-medium text-nowrap">
                  Stok Gudang
              </span>
          </button>
      </a>

      <a href="{{ route('manager.operational.stock-movements.index') }}" class="relative flex h-15 w-full items-center rounded-md transition-colors">
          <button type="button" @click="selected = 'Mutasi Stok'" 
                  :class="selected === 'Mutasi Stok' ? 'bg-green-600/10 text-green-800' : 'text-slate-500 hover:bg-slate-100'"
                  class="relative flex h-15 w-full items-center rounded-md transition-colors cursor-pointer">
              <div class="grid h-full w-16 place-content-center">
                  <i :class="selected === 'Mutasi Stok' ? 'ti ti-arrows-exchange-2' : 'ti ti-arrows-exchange'"></i>
              </div>
              <span x-show="open" x-transition class="absolute ml-16 text font-medium text-nowrap">
                  Mutasi Stok
              </span>
          </button>
      </a>

      <a href="{{ route('manager.operational.stock-opname.index') }}" class="relative flex h-15 w-full items-center rounded-md transition-colors">
          <button type="button" @click="selected = 'Stock Opname'" 
                  :class="selected === 'Stock Opname' ? 'bg-green-600/10 text-green-800' : 'text-slate-500 hover:bg-slate-100'"
                  class="relative flex h-15 w-full items-center rounded-md transition-colors cursor-pointer">
              <div class="grid h-full w-16 place-content-center">
                  <i :class="selected === 'Stock Opname' ? 'ti ti-clipboard-check' : 'ti ti-clipboard-list'"></i>
              </div>
              <span x-show="open" x-transition class="absolute ml-16 text font-medium text-nowrap">
                  Stock Opname
              </span>
          </button>
      </a>

      <a href="{{ route('manager.operational.produksi') }}" class="relative flex h-15 w-full items-center rounded-md transition-colors">
          <button type="button" @click="selected = 'Produksi'" 
                  :class="selected === 'Produksi' ? 'bg-green-600/10 text-green-800' : 'text-slate-500 hover:bg-slate-100'"
                  class="relative flex h-15 w-full items-center rounded-md transition-colors cursor-pointer">
              <div class="grid h-full w-16 place-content-center">
                  <i class="ti ti-tools-kitchen-2"></i>
              </div>
              <span x-show="open" x-transition class="absolute ml-16 text font-medium text-nowrap">
                  Produksi
              </span>
          </button>
      </a>

      <a href="{{ route('manager.inventory.recipes.index') }}" class="relative flex h-15 w-full items-center rounded-md transition-colors">
          <button type="button" @click="selected = 'Resep'" 
                  :class="selected === 'Resep' ? 'bg-green-600/10 text-green-800' : 'text-slate-500 hover:bg-slate-100'"
                  class="relative flex h-15 w-full items-center rounded-md transition-colors cursor-pointer">
              <div class="grid h-full w-16 place-content-center">
                  <i :class="selected === 'Resep' ? 'ti ti-book-filled' : 'ti ti-book'"></i>
              </div>
              <span x-show="open" x-transition class="absolute ml-16 text font-medium text-nowrap">
                  Resep (BOM)
              </span>
          </button>
      </a>

      <a href="{{ route('manager.inventory.stock-batches.index') }}" class="relative flex h-15 w-full items-center rounded-md transition-colors">
          <button type="button" @click="selected = 'Batch Stok'" 
                  :class="selected === 'Batch Stok' ? 'bg-green-600/10 text-green-800' : 'text-slate-500 hover:bg-slate-100'"
                  class="relative flex h-15 w-full items-center rounded-md transition-colors cursor-pointer">
              <div class="grid h-full w-16 place-content-center">
                  <i class="ti ti-stack"></i>
              </div>
              <span x-show="open" x-transition class="absolute ml-16 text font-medium text-nowrap">
                  Batch Stok
              </span>
          </button>
      </a>

      <a href="{{ route('manager.operational.suppliers.index') }}" class="relative flex h-15 w-full items-center rounded-md transition-colors">
          <button type="button" @click="selected = 'Supplier'" 
                  :class="selected === 'Supplier' ? 'bg-green-600/10 text-green-800' : 'text-slate-500 hover:bg-slate-100'"
                  class="relative flex h-15 w-full items-center rounded-md transition-colors cursor-pointer">
              <div class="grid h-full w-16 place-content-center">
                  <i :class="selected === 'Supplier' ? 'ti ti-truck-delivery' : 'ti ti-truck'"></i>
              </div>
              <span x-show="open" x-transition class="absolute ml-16 text font-medium text-nowrap">
                  Supplier Pemasok
              </span>
          </button>
      </a>

      <a href="{{ route('manager.operational.po.index') }}" class="relative flex h-15 w-full items-center rounded-md transition-colors">
          <button type="button" @click="selected = 'Purchase Order'" 
                  :class="selected === 'Purchase Order' ? 'bg-green-600/10 text-green-800' : 'text-slate-500 hover:bg-slate-100'"
                  class="relative flex h-15 w-full items-center rounded-md transition-colors cursor-pointer">
              <div class="grid h-full w-16 place-content-center">
                  <i :class="selected === 'Purchase Order' ? 'ti ti-file-invoice' : 'ti ti-file-invoice'"></i>
              </div>
              <span x-show="open" x-transition class="absolute ml-16 text font-medium text-nowrap">
                  Purchase Order
              </span>
          </button>
      </a>

      {{-- Section Marketing --}}
      <div class="h-3 mt-6 mb-2 flex items-center">
          <p x-show="open" x-transition class="text-slate-600 ps-6 text-sm font-semibold">Marketing</p>
          <hr x-show="!open" x-transition class="border-t border-slate-300 w-full" />
      </div>

      <a href="{{ route('manager.marketing.dashboard') }}" class="relative flex h-15 w-full items-center rounded-md transition-colors">
          <button type="button" @click="selected = 'Dashboard Marketing'" 
                  :class="selected === 'Dashboard Marketing' ? 'bg-green-600/10 text-green-800' : 'text-slate-500 hover:bg-slate-100'"
                  class="relative flex h-15 w-full items-center rounded-md transition-colors cursor-pointer">
              <div class="grid h-full w-16 place-content-center">
                  <i :class="selected === 'Dashboard Marketing' ? 'ti ti-chart-bar-filled' : 'ti ti-chart-bar'"></i>
              </div>
              <span x-show="open" x-transition class="absolute ml-16 text font-medium text-nowrap">
                  Dashboard Marketing
              </span>
          </button>
      </a>

      <a href="{{ route('manager.marketing.customer-analytics') }}" class="relative flex h-15 w-full items-center rounded-md transition-colors">
          <button type="button" @click="selected = 'Customer Analytics'" 
                  :class="selected === 'Customer Analytics' ? 'bg-green-600/10 text-green-800' : 'text-slate-500 hover:bg-slate-100'"
                  class="relative flex h-15 w-full items-center rounded-md transition-colors cursor-pointer">
              <div class="grid h-full w-16 place-content-center">
                  <i :class="selected === 'Customer Analytics' ? 'ti ti-users-group' : 'ti ti-users'"></i>
              </div>
              <span x-show="open" x-transition class="absolute ml-16 text font-medium text-nowrap">
                  Analisis Pelanggan
              </span>
          </button>
      </a>

      <a href="{{ route('manager.marketing.retention') }}" class="relative flex h-15 w-full items-center rounded-md transition-colors">
          <button type="button" @click="selected = 'Retensi Pelanggan'" 
                  :class="selected === 'Retensi Pelanggan' ? 'bg-green-600/10 text-green-800' : 'text-slate-500 hover:bg-slate-100'"
                  class="relative flex h-15 w-full items-center rounded-md transition-colors cursor-pointer">
              <div class="grid h-full w-16 place-content-center">
                  <i :class="selected === 'Retensi Pelanggan' ? 'ti ti-user-check' : 'ti ti-user-check'"></i>
              </div>
              <span x-show="open" x-transition class="absolute ml-16 text font-medium text-nowrap">
                  Retensi Pelanggan
              </span>
          </button>
      </a>

      <a href="{{ route('manager.marketing.product-performance') }}" class="relative flex h-15 w-full items-center rounded-md transition-colors">
          <button type="button" @click="selected = 'Performa Produk'" 
                  :class="selected === 'Performa Produk' ? 'bg-green-600/10 text-green-800' : 'text-slate-500 hover:bg-slate-100'"
                  class="relative flex h-15 w-full items-center rounded-md transition-colors cursor-pointer">
              <div class="grid h-full w-16 place-content-center">
                  <i :class="selected === 'Performa Produk' ? 'ti ti-trending-up' : 'ti ti-trending-up'"></i>
              </div>
              <span x-show="open" x-transition class="absolute ml-16 text font-medium text-nowrap">
                  Performa Produk
              </span>
          </button>
      </a>

      <a href="{{ route('manager.marketing.revenue-analytics') }}" class="relative flex h-15 w-full items-center rounded-md transition-colors">
          <button type="button" @click="selected = 'Analisis Omset'" 
                  :class="selected === 'Analisis Omset' ? 'bg-green-600/10 text-green-800' : 'text-slate-500 hover:bg-slate-100'"
                  class="relative flex h-15 w-full items-center rounded-md transition-colors cursor-pointer">
              <div class="grid h-full w-16 place-content-center">
                  <i :class="selected === 'Analisis Omset' ? 'ti ti-chart-pie' : 'ti ti-chart-pie'"></i>
              </div>
              <span x-show="open" x-transition class="absolute ml-16 text font-medium text-nowrap">
                  Analisis Omset Marketing
              </span>
          </button>
      </a>

      <a href="{{ route('manager.marketing.campaign-analysis') }}" class="relative flex h-15 w-full items-center rounded-md transition-colors">
          <button type="button" @click="selected = 'Campaign Analysis'" 
                  :class="selected === 'Campaign Analysis' ? 'bg-green-600/10 text-green-800' : 'text-slate-500 hover:bg-slate-100'"
                  class="relative flex h-15 w-full items-center rounded-md transition-colors cursor-pointer">
              <div class="grid h-full w-16 place-content-center">
                  <i :class="selected === 'Campaign Analysis' ? 'ti ti-speakerphone' : 'ti ti-speakerphone'"></i>
              </div>
              <span x-show="open" x-transition class="absolute ml-16 text font-medium text-nowrap">
                  Kampanye Promosi
              </span>
          </button>
      </a>

      <a href="{{ route('manager.marketing.customers.index') }}" class="relative flex h-15 w-full items-center rounded-md transition-colors">
          <button type="button" @click="selected = 'Pelanggan CRM'" 
                  :class="selected === 'Pelanggan CRM' ? 'bg-green-600/10 text-green-800' : 'text-slate-500 hover:bg-slate-100'"
                  class="relative flex h-15 w-full items-center rounded-md transition-colors cursor-pointer">
              <div class="grid h-full w-16 place-content-center">
                  <i :class="selected === 'Pelanggan CRM' ? 'ti ti-address-book' : 'ti ti-address-book'"></i>
              </div>
              <span x-show="open" x-transition class="absolute ml-16 text font-medium text-nowrap">
                  Data Pelanggan (CRM)
              </span>
          </button>
      </a>

      {{-- Section Keuangan --}}
      <div class="h-3 mt-6 mb-2 flex items-center">
          <p x-show="open" x-transition class="text-slate-600 ps-6 text-sm font-semibold">Keuangan (Finance)</p>
          <hr x-show="!open" x-transition class="border-t border-slate-300 w-full" />
      </div>

      <a href="{{ route('manager.finance.dashboard.index') }}" class="relative flex h-15 w-full items-center rounded-md transition-colors">
          <button type="button" @click="selected = 'Dashboard Keuangan'" 
                  :class="selected === 'Dashboard Keuangan' ? 'bg-green-600/10 text-green-800' : 'text-slate-500 hover:bg-slate-100'"
                  class="relative flex h-15 w-full items-center rounded-md transition-colors cursor-pointer">
              <div class="grid h-full w-16 place-content-center">
                  <i :class="selected === 'Dashboard Keuangan' ? 'ti ti-wallet' : 'ti ti-wallet'"></i>
              </div>
              <span x-show="open" x-transition class="absolute ml-16 text font-medium text-nowrap">
                  Dashboard Keuangan
              </span>
          </button>
      </a>

      <a href="{{ route('manager.finance.revenue.index') }}" class="relative flex h-15 w-full items-center rounded-md transition-colors">
          <button type="button" @click="selected = 'Pendapatan'" 
                  :class="selected === 'Pendapatan' ? 'bg-[#0C9044]/10 text-[#0C9044]' : 'text-slate-500 hover:bg-slate-100'"
                  class="relative flex h-15 w-full items-center rounded-md transition-colors cursor-pointer">
              <div class="grid h-full w-16 place-content-center">
                  <i :class="selected === 'Pendapatan' ? 'ti ti-cash' : 'ti ti-cash'"></i>
              </div>
              <span x-show="open" x-transition class="absolute ml-16 text font-medium text-nowrap">
                  Pemasukan / Revenue
              </span>
          </button>
      </a>

      <a href="{{ route('manager.finance.expenses.index') }}" class="relative flex h-15 w-full items-center rounded-md transition-colors">
          <button type="button" @click="selected = 'Pengeluaran'" 
                  :class="selected === 'Pengeluaran' ? 'bg-green-600/10 text-green-800' : 'text-slate-500 hover:bg-slate-100'"
                  class="relative flex h-15 w-full items-center rounded-md transition-colors cursor-pointer">
              <div class="grid h-full w-16 place-content-center">
                  <i :class="selected === 'Pengeluaran' ? 'ti ti-receipt-refund' : 'ti ti-receipt-refund'"></i>
              </div>
              <span x-show="open" x-transition class="absolute ml-16 text font-medium text-nowrap">
                  Pengeluaran (Expenses)
              </span>
          </button>
      </a>

      <a href="{{ route('manager.finance.profit-loss.index') }}" class="relative flex h-15 w-full items-center rounded-md transition-colors">
          <button type="button" @click="selected = 'Laba Rugi'" 
                  :class="selected === 'Laba Rugi' ? 'bg-green-600/10 text-green-800' : 'text-slate-500 hover:bg-slate-100'"
                  class="relative flex h-15 w-full items-center rounded-md transition-colors cursor-pointer">
              <div class="grid h-full w-16 place-content-center">
                  <i :class="selected === 'Laba Rugi' ? 'ti ti-report-analytics' : 'ti ti-report-analytics'"></i>
              </div>
              <span x-show="open" x-transition class="absolute ml-16 text font-medium text-nowrap">
                  Laporan Laba Rugi
              </span>
          </button>
      </a>

      <a href="{{ route('manager.finance.cashflow.index') }}" class="relative flex h-15 w-full items-center rounded-md transition-colors">
          <button type="button" @click="selected = 'Arus Kas'" 
                  :class="selected === 'Arus Kas' ? 'bg-green-600/10 text-green-800' : 'text-slate-500 hover:bg-slate-100'"
                  class="relative flex h-15 w-full items-center rounded-md transition-colors cursor-pointer">
              <div class="grid h-full w-16 place-content-center">
                  <i :class="selected === 'Arus Kas' ? 'ti ti-arrows-left-right' : 'ti ti-arrows-left-right'"></i>
              </div>
              <span x-show="open" x-transition class="absolute ml-16 text font-medium text-nowrap">
                  Arus Kas (Cashflow)
              </span>
          </button>
      </a>

      <a href="{{ route('manager.finance.ap.index') }}" class="relative flex h-15 w-full items-center rounded-md transition-colors">
          <button type="button" @click="selected = 'Hutang AP'" 
                  :class="selected === 'Hutang AP' ? 'bg-green-600/10 text-green-800' : 'text-slate-500 hover:bg-slate-100'"
                  class="relative flex h-15 w-full items-center rounded-md transition-colors cursor-pointer">
              <div class="grid h-full w-16 place-content-center">
                  <i :class="selected === 'Hutang AP' ? 'ti ti-scale-outline' : 'ti ti-scale'"></i>
              </div>
              <span x-show="open" x-transition class="absolute ml-16 text font-medium text-nowrap">
                  Hutang Usaha (AP)
              </span>
          </button>
      </a>

      <a href="{{ route('manager.finance.ar.index') }}" class="relative flex h-15 w-full items-center rounded-md transition-colors">
          <button type="button" @click="selected = 'Piutang AR'" 
                  :class="selected === 'Piutang AR' ? 'bg-green-600/10 text-green-800' : 'text-slate-500 hover:bg-slate-100'"
                  class="relative flex h-15 w-full items-center rounded-md transition-colors cursor-pointer">
              <div class="grid h-full w-16 place-content-center">
                  <i :class="selected === 'Piutang AR' ? 'ti ti-coin' : 'ti ti-coin'"></i>
              </div>
              <span x-show="open" x-transition class="absolute ml-16 text font-medium text-nowrap">
                  Piutang Usaha (AR)
              </span>
          </button>
      </a>

      <a href="{{ route('manager.finance.accounting.dashboard') }}" class="relative flex h-15 w-full items-center rounded-md transition-colors">
          <button type="button" @click="selected = 'Akuntansi'" 
                  :class="selected === 'Akuntansi' ? 'bg-green-600/10 text-green-800' : 'text-slate-500 hover:bg-slate-100'"
                  class="relative flex h-15 w-full items-center rounded-md transition-colors cursor-pointer">
              <div class="grid h-full w-16 place-content-center">
                  <i :class="selected === 'Akuntansi' ? 'ti ti-calculator' : 'ti ti-calculator'"></i>
              </div>
              <span x-show="open" x-transition class="absolute ml-16 text font-medium text-nowrap">
                  Akuntansi & Jurnal
              </span>
          </button>
      </a>

      <a href="{{ route('manager.finance.invoices.index') }}" class="relative flex h-15 w-full items-center rounded-md transition-colors">
          <button type="button" @click="selected = 'Invoices'" 
                  :class="selected === 'Invoices' ? 'bg-green-600/10 text-green-800' : 'text-slate-500 hover:bg-slate-100'"
                  class="relative flex h-15 w-full items-center rounded-md transition-colors cursor-pointer">
              <div class="grid h-full w-16 place-content-center">
                  <i :class="selected === 'Invoices' ? 'ti ti-file-text' : 'ti ti-file-text'"></i>
              </div>
              <span x-show="open" x-transition class="absolute ml-16 text font-medium text-nowrap">
                  Invoice & Penagihan
              </span>
          </button>
      </a>

      <a href="{{ route('finance.rnd-request.index') }}" class="relative flex h-15 w-full items-center rounded-md transition-colors">
          <button type="button" @click="selected = 'RND Finance'" 
                  :class="selected === 'RND Finance' ? 'bg-green-600/10 text-green-800' : 'text-slate-500 hover:bg-slate-100'"
                  class="relative flex h-15 w-full items-center rounded-md transition-colors cursor-pointer">
              <div class="grid h-full w-16 place-content-center">
                  <i :class="selected === 'RND Finance' ? 'ti ti-flask' : 'ti ti-flask'"></i>
              </div>
              <span x-show="open" x-transition class="absolute ml-16 text font-medium text-nowrap">
                  Pengajuan R&D
              </span>
          </button>
      </a>

  </div>

    <div class="relative flex border-t border-slate-300">
      <div class="relative flex w-full  cursor-pointer items-center justify-between  transition-colors hover:bg-slate-100">
        <a type="button" @click="toggleSidebar()" class="w-full flex items-center m-2 transition-colors">
          <div class="flex items-center ms-3 p-2">
            <div class="grid place-content-center ">
              <i class="ti ti-square-chevrons-right  transition-transform duration-300"
                :class="{'transform rotate-180': open}"></i>
            </div>
            <span x-show="open" x-transition class=" absolute ml-10  text-nowrap">
              Hide
            </span>
          </div>
        </a>
      </div>
    </div>
    
     <div class="relative ">
      <div class="relative flex w-full cursor-pointer text-red-600 items-center justify-between transition-colors hover:bg-red-100">
        <form id="logout-form" action="{{ route('universal.logout') }}" method="POST" class="w-full">
          @csrf
          <button type="submit"
            class="w-full flex items-center text-red-600 hover:bg-red-100 transition-colors m-2 p-2">
            <div class="grid place-content-center me-2">
              <i class="ti ti-logout"></i>
            </div>
            <span x-show="open" x-transition>Logout</span>
          </button>
        </form>
      </div>
    </div>

  </nav>

  <button x-show="isMobile && !open" class="fixed top-1/2 right-0 transform -translate-y-1/2
         bg-teal-500 text-white px-3 py-2 rounded-l-full shadow-lg
         flex items-center z-50" @click="open = true">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
      stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M15 19l-7-7 7-7" />
    </svg>
  </button>

</div>

<style>
  /* Menghilangkan scrollbar tapi tetap bisa scroll */
  .hide-scrollbar::-webkit-scrollbar {
      display: none;
  }
  .hide-scrollbar {
      -ms-overflow-style: none;
      scrollbar-width: none;
  }
</style>

<script>
  function sidebarOffcanvas() {
    return {
      open: window.innerWidth >= 768,
      selected: 'Dashboard',
      isMobile: window.innerWidth < 768,

      init() {
        window.addEventListener('resize', () => this.checkWidth());
        this.checkWidth();
      },

      checkWidth() {
        if (window.innerWidth < 768) {
          this.isMobile = true;
          this.open = false;
        } else {
          this.isMobile = false;
          const savedState = localStorage.getItem('sidebarOpen');
          this.open = savedState !== null ? (savedState === "true") : true;
        }
      },

      toggleSidebar() {
        this.open = !this.open;
        if (!this.isMobile) {
          localStorage.setItem('sidebarOpen', this.open);
        }
      },

      sidebarClass() {
        if (!this.isMobile) {
          return this.open
            ? "relative sticky top-0 h-screen shrink-0 border-r border-slate-300 bg-white transition-[width] duration-300 ease-in-out overflow-hidden flex flex-col w-[250px]"
            : "relative sticky top-0 h-screen shrink-0 border-r border-slate-300 bg-white transition-[width] duration-300 ease-in-out overflow-hidden flex flex-col w-20";
        } else {
          return this.open
            ? "fixed inset-y-0 left-0 w-[300px] bg-white border-r border-slate-300 z-50 transform transition-transform duration-300 ease-in-out flex flex-col"
            : "fixed inset-y-0 left-0 w-[300px] bg-white border-r border-slate-300 z-50 transform -translate-x-full flex flex-col ";
        }
      }
    }
  }
</script>