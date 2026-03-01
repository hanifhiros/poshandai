<div x-data="sidebarOffcanvas()" x-init="init()" class="flex bg-indigo-50">
  <nav x-bind:class="sidebarClass()" class="overflow-hidden flex flex-col border-none shadow-xl">
    <!-- LOGO -->
    <div class="bg-[#0C9044] text-white py-4 rounded-br-lg ">
      <div class="flex items-center justify-between">
        <!-- Kiri: Logo + Teks -->
        <div class="flex items-center ">
          <div class="ms-2 grid w-16 h-16 shrink-0 place-content-center">
            <img src="{{ asset('assets/svg/handai-logo.svg') }}" alt="Handai Logo" width="38" height="auto"
              class="object-contain" />
          </div>

          <div>
            <h1 x-show="open" x-transition class=" font-bold text-2xl tracking-tighter text-nowrap">
              Handai Coffee
            </h1>
          </div>
        </div>

        <!-- Kanan: Tombol "X" (Mobile) -->
        <div x-show="isMobile && open" x-transition class="cursor-pointer">
          <!-- <a @click="open = false" class="text-white hover:text-gray-100 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </a> -->
        </div>
      </div>
    </div>


    <!-- ISI SIDEBAR -->
    <div x-data="" class="flex-1 overflow-y-auto space-y-1 p-3">
      @php use App\Helpers\RoleHelper; @endphp
      @if (RoleHelper::hasRole('Superadmin'))
      <a href="{{ route('superadmin.dashboard') }}"
         @click="selected = 'Superadmin Dashboard'"
         :class="selected === 'Superadmin Dashboard' ? 'bg-green-600/10 text-green-800' : 'text-slate-500 hover:bg-slate-100'"
         class="relative flex h-15 w-full items-center rounded-md transition-colors cursor-pointer">
          <div class="grid h-full w-16 place-content-center">
              <i :class="selected === 'Superadmin Dashboard' ? 'ti ti-device-desktop-filled' : 'ti ti-device-desktop'"></i>
          </div>
          <span x-show="open" x-transition class="absolute ml-16 text font-medium ">
              Superadmin Dashboard
          </span>
      </a>
      @endif      
      <div class="h-3 mt-3 flex items-center">
          <p x-show="open" x-transition class="text-slate-600 ps-6 text-sm">Menu</p>
          <hr x-show="!open" x-transition class="border-t border-slate-300 w-full" />
      </div>

      <!-- Dashboard Button -->
      <a :href="'{{ route('manager.dashboard') }}'" class="relative flex h-15 w-full items-center rounded-md transition-colors ">
          <button type="button" @click="selected = 'All Products'" 
                  :class="selected === 'All Products' ? 'bg-green-600/10 text-green-800' : 'text-slate-500 hover:bg-slate-100'"
                  class="relative flex h-15 w-full items-center rounded-md transition-colors cursor-pointer">
              <div class="grid h-full w-16 place-content-center">
                  <i :class="selected === 'All Products' ? 'ti ti-category-filled' : 'ti ti-category'"></i>
              </div>
              <span x-show="open" x-transition class="absolute ml-16 text font-medium text-nowrap">
                  Dashboard
              </span>
          </button>
      </a>

      
    
  

<!-- Additional Menu Buttons -->


<!-- OPERATIONAL (menu normal) -->
<!-- OPERATIONAL GROUP -->

{{-- @if (RoleHelper::hasAnyRoleIncludingDescendants(['Manager-Operational']))
    <p>User punya Manager-Operational atau turunannya!</p>
@endif --}}
@if (RoleHelper::hasAnyRoleIncludingDescendants(['Reseller']) && isset($reseller)&&RoleHelper::hasAnyRoleIncludingDescendants(['Superadmin']))
    <a href="{{ url('/customer-order/login?reseller=' . $reseller->code) }}" 
       target="_blank" rel="noopener noreferrer"
       class="flex items-center gap-2 text-gray-600 text-sm px-4 py-2 mt-10 rounded-lg border border-gray-300 hover:border-green-500 hover:text-green-600 transition duration-150">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Form Reseller
    </a>
@endif
{{-- <p>{{session('selected_store'), session('selected_store_')}}adddada</p> --}}


@if (RoleHelper::hasAnyRoleIncludingDescendants(['Manager-Operational']))
<!-- Operational Dropdown -->
<div class="space-y-1">
  <button type="button"
  @click="toggleDropdown('operational')"
  class="flex items-center justify-between w-full px-4 py-2 rounded-md transition-colors text-sm font-medium
  {{ (request()->is('manager/operational/*') || request()->is('manager/inventory/*')) ? 'bg-green-600/10 text-green-800' : 'text-slate-600 hover:bg-slate-100' }}">
  
  <div class="flex items-center gap-2">
    <i class="ti ti-briefcase"></i>
    <span x-show="open">Operational</span>
  </div>
  <i class="ti ti-chevron-down transition-transform" :class="dropdowns.operational ? 'rotate-180' : ''"></i>
</button>


  <div x-show="dropdowns.operational" x-transition class="pl-10 mt-1 space-y-0.5 text-sm text-slate-600">

    {{-- ═══ SUPPLY CHAIN ═══ --}}
    <p class="flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400 pt-2 pb-1 px-4" x-show="open">
      <i class="ti ti-truck text-xs"></i> Supply Chain
    </p>

    @if (RoleHelper::hasAnyRoleIncludingDescendants(['Manager-Operational-InventoryController']))
      <a href="{{ route('manager.operational.suppliers.index') }}"
        class="flex items-center gap-2 px-4 py-1.5 rounded hover:bg-green-50/50 transition {{ request()->is('manager/operational/suppliers*') ? 'bg-green-50/50 text-green-700 font-semibold' : '' }}">
        <i class="ti ti-building-store text-base"></i> <span>Supplier</span>
      </a>
    @endif

    @if (RoleHelper::hasAnyRoleIncludingDescendants(['Manager-Operational-InventoryController']))
      <a href="{{ route('manager.inventory.stock-batches.index') }}"
        class="flex items-center gap-2 px-4 py-1.5 rounded hover:bg-green-50/50 transition {{ request()->is('manager/inventory/stock-batches*') ? 'bg-green-50/50 text-green-700 font-semibold' : '' }}">
        <i class="ti ti-package-import text-base"></i> <span>Pembelian Bahan</span>
      </a>
    @endif

    {{-- ═══ GUDANG & STOK ═══ --}}
    <p class="flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400 pt-3 pb-1 px-4" x-show="open">
      <i class="ti ti-building-warehouse text-xs"></i> Gudang & Stok
    </p>

    @if (RoleHelper::hasAnyRoleIncludingDescendants(['Manager-Operational-InventoryController']))
      <a href="{{ route('manager.inventory.stock') }}"
        class="flex items-center gap-2 px-4 py-1.5 rounded hover:bg-green-50/50 transition {{ request()->is('manager/inventory/stock') ? 'bg-green-50/50 text-green-700 font-semibold' : '' }}">
        <i class="ti ti-packages text-base"></i> <span>Stok Gudang</span>
      </a>
    @endif

    @if (RoleHelper::hasAnyRoleIncludingDescendants(['Manager-Operational-InventoryController']))
      <a href="{{ route('manager.operational.stock-movements.index') }}"
        class="flex items-center gap-2 px-4 py-1.5 rounded hover:bg-green-50/50 transition {{ request()->is('manager/operational/stock-movements*') ? 'bg-green-50/50 text-green-700 font-semibold' : '' }}">
        <i class="ti ti-transfer text-base"></i> <span>Mutasi Stok</span>
      </a>
    @endif

    @if (RoleHelper::hasAnyRoleIncludingDescendants(['Manager-Operational-InventoryController']))
      <a href="{{ route('manager.operational.stock-opname.index') }}"
        class="flex items-center gap-2 px-4 py-1.5 rounded hover:bg-green-50/50 transition {{ request()->is('manager/operational/stock-opname*') ? 'bg-green-50/50 text-green-700 font-semibold' : '' }}">
        <i class="ti ti-clipboard-check text-base"></i> <span>Stock Opname</span>
      </a>
    @endif

    {{-- ═══ PRODUK & RESEP ═══ --}}
    <p class="flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400 pt-3 pb-1 px-4" x-show="open">
      <i class="ti ti-recipe text-xs"></i> Produk & Resep
    </p>

    @if (RoleHelper::hasAnyRoleIncludingDescendants(['Manager-Operational']))
      <a href="{{ route('manager.inventory.products') }}"
        class="flex items-center gap-2 px-4 py-1.5 rounded hover:bg-green-50/50 transition {{ request()->is('manager/inventory/products*') ? 'bg-green-50/50 text-green-700 font-semibold' : '' }}">
        <i class="ti ti-box text-base"></i> <span>Daftar Produk</span>
      </a>
    @endif

    @if (RoleHelper::hasRole('Manager-Operational-RnD'))
      <a href="{{ route('manager.inventory.recipes.index') }}"
        class="flex items-center gap-2 px-4 py-1.5 rounded hover:bg-green-50/50 transition {{ request()->is('manager/inventory/recipes*') ? 'bg-green-50/50 text-green-700 font-semibold' : '' }}">
        <i class="ti ti-chef-hat text-base"></i> <span>Resep / BOM</span>
      </a>
    @endif

    {{-- ═══ PRODUKSI ═══ --}}
    <p class="flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400 pt-3 pb-1 px-4" x-show="open">
      <i class="ti ti-assembly text-xs"></i> Produksi
    </p>

    @if (RoleHelper::hasRole('Manager-Operational-ProductionController'))
      <a href="{{ route('manager.operational.produksi') }}"
        class="flex items-center gap-2 px-4 py-1.5 rounded hover:bg-green-50/50 transition {{ request()->is('manager/operational/produksi*') ? 'bg-green-50/50 text-green-700 font-semibold' : '' }}">
        <i class="ti ti-tools-kitchen-2 text-base"></i> <span>Riwayat Produksi</span>
      </a>
    @endif

    @if (RoleHelper::hasRole('Manager-Operational-RnD'))
      <a href="{{ route('manager.operational.rnd') }}"
        class="flex items-center gap-2 px-4 py-1.5 rounded hover:bg-green-50/50 transition {{ request()->is('manager/operational/rnd*') ? 'bg-green-50/50 text-green-700 font-semibold' : '' }}">
        <i class="ti ti-flask text-base"></i> <span>Riset & Pengembangan</span>
      </a>
    @endif

    {{-- ═══ WASTE ═══ --}}
    <p class="flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400 pt-3 pb-1 px-4" x-show="open">
      <i class="ti ti-trash text-xs"></i> Waste
    </p>

    @if (RoleHelper::hasAnyRoleIncludingDescendants(['Manager-Operational']))
      <a href="{{ route('manager.operational.waste.index') }}"
        class="flex items-center gap-2 px-4 py-1.5 rounded hover:bg-green-50/50 transition {{ request()->is('manager/operational/waste*') ? 'bg-green-50/50 text-green-700 font-semibold' : '' }}">
        <i class="ti ti-recycle text-base"></i> <span>Waste / Basi</span>
      </a>
    @endif

    {{-- ═══ PENJUALAN ═══ --}}
    <p class="flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400 pt-3 pb-1 px-4" x-show="open">
      <i class="ti ti-shopping-cart text-xs"></i> Penjualan
    </p>

    @if (RoleHelper::hasRole('Manager-Operational-OrderController'))
      <a href="{{ route('manager.operational.orders.index') }}"
        class="flex items-center gap-2 px-4 py-1.5 rounded hover:bg-green-50/50 transition {{ request()->is('manager/operational/orders*') ? 'bg-green-50/50 text-green-700 font-semibold' : '' }}">
        <i class="ti ti-receipt text-base"></i> <span>Riwayat Pesanan</span>
      </a>
    @endif

  </div>
</div>
@endif


<!-- Marketing GROUP -->
@if (RoleHelper::hasAnyRoleIncludingDescendants(['Manager-Marketing']))
<div class="space-y-1">
  <!-- Toggle Button -->
  <button type="button"
    @click="toggleDropdown('marketing')"
    class="flex items-center justify-between w-full px-4 py-2 rounded-md transition-colors text-sm font-medium
    {{ request()->is('manager/marketing/*') ? 'bg-green-600/10 text-green-800' : 'text-slate-600 hover:bg-slate-100' }}">
  <div class="flex items-center gap-2">
    <i class="ti ti-package"></i>
    <span x-show="open">Marketing</span>
  </div>
  <i class="ti ti-chevron-down transition-transform"
     :class="dropdowns.marketing ? 'rotate-180' : ''"></i>
</button>


  <!-- Submenu -->
  <div x-show="dropdowns.marketing" x-transition class="pl-12 mt-1 space-y-1 text-sm text-slate-600">
      <a href="{{route('manager.marketing.customers.index')}}"
        class="block text-sm px-4 py-1 rounded hover:text-green-600 
        {{ request()->is('manager/marketing/customers') ? 'text-green-600 font-semibold' : '' }}">
        Customer Database
      </a>

      <a href="{{route('manager.marketing.resellers.index')}}"
      class="block text-sm px-4 py-1 rounded hover:text-green-600 
      {{ request()->is('manager/marketing/resellers') ? 'text-green-600 font-semibold' : '' }}">
      Resellers
    </a>

  </div>
  
</div>
@endif

<!-- FINANCE GROUP -->
@if (RoleHelper::hasAnyRoleIncludingDescendants(['Manager-Finance']))
<div class="space-y-1">
  <!-- Toggle Button -->
  <button type="button"
    @click="toggleDropdown('finance')"
    class="flex items-center justify-between w-full px-4 py-2 rounded-md transition-colors text-sm font-medium
    {{ request()->is('manager/finance/*') ? 'bg-green-600/10 text-green-800' : 'text-slate-600 hover:bg-slate-100' }}">
  <div class="flex items-center gap-2">
    <i class="ti ti-package"></i>
    <span x-show="open">Finance</span>
  </div>
  <i class="ti ti-chevron-down transition-transform"
     :class="dropdowns.finance ? 'rotate-180' : ''"></i>
</button>


  <!-- Submenu -->
  <div x-show="dropdowns.finance" x-transition class="pl-12 mt-1 space-y-1 text-sm text-slate-600">

    {{-- ── Akuntansi ── --}}
    <p class="text-[10px] uppercase tracking-widest text-gray-400 font-bold px-4 pt-2 pb-0.5">Akuntansi</p>
    <a href="{{ route('manager.finance.accounting.dashboard') }}"
       class="block text-sm px-4 py-1 rounded hover:text-green-600 {{ request()->is('manager/finance/accounting/dashboard') ? 'text-green-600 font-semibold' : '' }}">
       Dashboard Keuangan
    </a>
    <a href="{{ route('manager.finance.accounting.coa') }}"
       class="block text-sm px-4 py-1 rounded hover:text-green-600 {{ request()->is('manager/finance/accounting/chart-of-accounts') ? 'text-green-600 font-semibold' : '' }}">
       Chart of Accounts
    </a>
    <a href="{{ route('manager.finance.accounting.journals') }}"
       class="block text-sm px-4 py-1 rounded hover:text-green-600 {{ request()->is('manager/finance/accounting/journal-entries') ? 'text-green-600 font-semibold' : '' }}">
       Jurnal
    </a>
    <a href="{{ route('manager.finance.accounting.income') }}"
       class="block text-sm px-4 py-1 rounded hover:text-green-600 {{ request()->is('manager/finance/accounting/income-statement') ? 'text-green-600 font-semibold' : '' }}">
       Laba Rugi
    </a>
    <a href="{{ route('manager.finance.accounting.balance') }}"
       class="block text-sm px-4 py-1 rounded hover:text-green-600 {{ request()->is('manager/finance/accounting/balance-sheet') ? 'text-green-600 font-semibold' : '' }}">
       Neraca
    </a>
    <a href="{{ route('manager.finance.accounting.cashflow') }}"
       class="block text-sm px-4 py-1 rounded hover:text-green-600 {{ request()->is('manager/finance/accounting/cash-flow') ? 'text-green-600 font-semibold' : '' }}">
       Arus Kas
    </a>

    {{-- ── Lainnya ── --}}
    <p class="text-[10px] uppercase tracking-widest text-gray-400 font-bold px-4 pt-3 pb-0.5">Lainnya</p>
    <a href="{{route('manager.finance.employees.index')}}"
   class="block text-sm px-4 py-1 rounded hover:text-green-600 
   {{ request()->is('manager/finance/employees') ? 'text-green-600 font-semibold' : '' }}">
   Employees
</a>


 <a href="{{ route('manager.finance.invoices.index') }}"

     class="block text-sm px-4 py-1 rounded hover:text-green-600 
     {{ request()->is('manager/finance/invoices') ? 'text-green-600 font-semibold' : '' }}">
     Invoice
  </a>
  <a href="{{ route('manager.finance.stock-batch-log.index') }}"

  class="block text-sm px-4 py-1 rounded hover:text-green-600 
  {{ request()->is('manager/finance/stock-batches-finance') ? 'text-green-600 font-semibold' : '' }}">
  Stock Batches Log
</a>


<a href="{{ route('finance.rnd-request.index') }}"
   class="block text-sm px-4 py-1 rounded hover:text-green-600 
  {{ request()->is('manager/finance/rnd-request') ? 'text-green-600 font-semibold' : '' }}">
   RnD Request
</a>
<a href="{{ route('manager.finance.rnd.log') }}"
   class="block text-sm px-4 py-1 rounded hover:text-green-600 
  {{ request()->is('manager/finance/rnd/log') ? 'text-green-600 font-semibold' : '' }}">
   RnD Log
</a>


    </div>
    <a href="{{ url('/customer-order/login?store_id=' . session('selected_store')) }}"
   class="flex items-center gap-2 text-gray-600 text-sm px-4 py-2 mt-10 rounded-lg border border-gray-300 hover:border-green-500 hover:text-green-600 transition duration-150">
   <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
   </svg>
   Form Beli Customer
</a>


  </div> 
@endif     
</div>

    <!-- BUTTON COLLAPSE -->
    <div class="relative flex border-t border-slate-300">
      <div
        class="relative flex w-full  cursor-pointer items-center justify-between  transition-colors hover:bg-slate-100">
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
     <!-- tambahin button logout -->
     <div class="relative ">
      <div class="relative flex w-full cursor-pointer text-red-600 items-center justify-between transition-colors hover:bg-red-100">
      <form action="{{ route('logout') }}" method="POST" class="w-full">
          @csrf
          <button type="submit" class="w-full flex items-center m-2 transition-colors cursor-pointer">
          <div class="flex items-center ms-3 p-2">
            <div class="grid place-content-center ">
            <i class="ti ti-logout"></i> 
            </div>
            <span x-show="open" x-transition class=" absolute ml-10  text-nowrap">
              Logout
            </span>
          </div>
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

<script>
  function sidebarOffcanvas() {
    return {
      open: window.innerWidth >= 768,
      selected: 'Dashboard',
      isMobile: window.innerWidth < 768,
      dropdowns: {
  
        operational: {!! (request()->is('manager/operational/*') || request()->is('manager/inventory/*')) ? 'true' : 'false' !!},
        marketing:{!! (request()->is('manager/marketing*')) ? 'true' : 'false' !!},
        finance:{!! (request()->is('manager/finance*')) ? 'true' : 'false' !!},

},

      toggleDropdown(menu) {
             this.dropdowns[menu] = !this.dropdowns[menu];
        },

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