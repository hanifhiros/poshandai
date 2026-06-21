<div x-data="sidebarOffcanvas()" x-init="init()" class="flex bg-indigo-50">
  <nav x-bind:class="sidebarClass()" class="overflow-hidden flex flex-col border-none shadow-xl">
    <!-- LOGO -->
    <div class="bg-[#0C9044] text-white py-4 rounded-br-lg ">
      <div class="flex items-center justify-between">
        <!-- Kiri: Logo + Teks -->
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
    <div x-data="{ selected: '{{ $categoryName }}' }" class="flex-1 overflow-y-auto space-y-1 p-3">
      <div class="h-3 mt-3 flex items-center">
          <p x-show="open" x-transition class="text-slate-600 ps-6 text-sm">Menu</p>
          <hr x-show="!open" x-transition class="border-t border-slate-300 w-full" />
      </div>

      <!-- All Products Button -->
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

      <!-- Categories Loop -->
      @foreach ($categories as $category)
          <a :href="'/pos/products?category=' + '{{ $category->category_name }}'" class="relative flex h-15 w-full items-center rounded-md transition-colors">
              <button type="button" 
                      @click="selected = '{{ $category->category_name }}'"
                      :class="{
                          'bg-green-600/10 text-green-800': selected === '{{ $category->category_name }}',
                          'text-slate-500 hover:bg-slate-100': selected !== '{{ $category->category_name }}'
                      }"
                      class="relative flex h-15 w-full items-center rounded-md transition-colors cursor-pointer">
                  <div class="grid h-full w-16 place-content-center">
                      <i :class="selected === '{{ $category->category_name }}' ? '{{ $category->category_icon }}-filled' : '{{ $category->category_icon }}'"></i>
                  </div>
                  <span x-show="open" x-transition class="absolute ml-16 text font-medium text-nowrap">
                      {{ $category->category_name }}
                  </span>
              </button>
          </a>
      @endforeach
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
      <a  href="{{ route('logout') }}" class="w-full flex items-center m-2 transition-colors">
          <div class="flex items-center ms-3 p-2">
            <div class="grid place-content-center ">
            <i class="ti ti-logout"></i> 
            </div>
            <span x-show="open" x-transition class=" absolute ml-10  text-nowrap">
              Logout
            </span>
          </div>
        </a>
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