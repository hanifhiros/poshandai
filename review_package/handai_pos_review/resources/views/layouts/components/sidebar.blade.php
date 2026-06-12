<div x-data="sidebarOffcanvas()" x-init="init()" class="flex bg-indigo-50">
  <nav x-bind:class="sidebarClass()" class="overflow-hidden flex flex-col border-none shadow-xl">
    <!-- LOGO -->
    <div class="bg-[#0C9044] text-white py-4 rounded-br-lg ">
      <div class="flex items-center justify-between">
        <!-- Kiri: Logo + Teks -->
        <div class="flex items-center ">
          <div class="ms-2 grid w-16 h-10 shrink-0 place-content-center">
            <img src="{{ asset('assets/svg/handai-logo.svg') }}" alt="Handai Logo" width="32" height="auto"
              class="object-contain" />
          </div>

          <div>
            <h1 x-show="open" x-transition class="font-poppins  text-2xl tracking-tighter text-nowrap">
              Handai Coffee
            </h1>
          </div>
        </div>

        <!-- Kanan: Tombol "X" (Mobile) -->
        <div x-show="isMobile && open" x-transition>
          <button @click="open = false" class="text-white hover:text-gray-100 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>
    </div>


    <!-- ISI SIDEBAR -->
    <div class="flex-1 overflow-y-auto space-y-1 p-3">
      <button type="button" @click="selected = 'Dashboard'" :class="selected === 'Dashboard' ? 'bg-green-100 text-green-800 border-l-4 border-green-600' : 'text-slate-500 hover:bg-slate-100'"
        class="relative flex h-10 w-full items-center rounded-md transition-colors">
        <div class="grid h-full w-16 place-content-center ">
          <!-- Ganti icon berdasarkan state 'selected' -->
          <i :class="selected === 'Dashboard' ? 'ti ti-home-filled ps-5' : 'ti ti-home ps-0'"></i>
        </div>
        <span x-show="open" x-transition class="absolute ml-16 font-medium text-nowrap">
          Dashboard
        </span>
      </button>


      <button type="button" @click="selected = 'Sales'" :class="selected === 'Sales' ? 'bg-green-600/10 text-green-800' : 'text-slate-500 hover:bg-slate-100'"
        class="relative flex h-10 w-full items-center rounded-md transition-colors">
        <div class="grid h-full w-16 place-content-center ">
          <i :class="selected === 'Sales' ? 'ti ti-rosette-discount-filled ' : 'ti ti-rosette-discount '"></i>
        </div>
        <span x-show="open" x-transition class="absolute ml-16 text font-medium  text-nowrap">Sales</span>

      </button>

      <button type="button" @click="selected = 'View Site'" :class="selected === 'View Site' ? 'bg-green-600/10 text-green-800' : 'text-slate-500 hover:bg-slate-100'"
        class="relative flex h-10 w-full items-center rounded-md transition-colors">
        <div class="grid h-full w-16 place-content-center ">
          <i :class="selected === 'View Site' ? 'ti ti-salad-filled ' : 'ti ti-salad '"></i>
        </div>
        <span x-show="open" x-transition class="absolute ml-16 text font-medium  text-nowrap">View Site</span>
      </button>

      <button type="button" @click="selected = 'Products'" :class="selected === 'Products' ? 'bg-green-600/10 text-green-800' : 'text-slate-500 hover:bg-slate-100'"
        class="relative flex h-10 w-full items-center rounded-md transition-colors">
        <div class="grid h-full w-16 place-content-center ">
          <i :class="selected === 'Products' ? 'ti ti-bottle-filled ' : 'ti ti-bottle '"></i>
        </div>
        <span x-show="open" x-transition class="absolute ml-16 text font-medium  text-nowrap">Products</span>
      </button>




    </div>

    <!-- BUTTON COLLAPSE -->
    <div class="relative flex border-t border-slate-300 ">
      <div
        class="relative flex w-full  cursor-pointer items-center justify-between  transition-colors hover:bg-slate-100">
        <button type="button" @click="toggleSidebar()" class="w-full flex items-center m-2 transition-colors">
          <div class="flex items-center ms-3 p-2">
            <div class="grid place-content-center ">
              <i class="ti ti-square-chevrons-right  transition-transform duration-300"
                :class="{'transform rotate-180': open}"></i>
            </div>
            <span x-show="open" x-transition class=" absolute ml-10  text-nowrap">
              Hide
            </span>
          </div>
        </button>
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
            ? "fixed inset-y-0 left-0 w-[225px] bg-white border-r border-slate-300 z-50 transform transition-transform duration-300 ease-in-out flex flex-col"
            : "fixed inset-y-0 left-0 w-[225px] bg-white border-r border-slate-300 z-50 transform -translate-x-full flex flex-col ";
        }
      }
    }
  }
</script>