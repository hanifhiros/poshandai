# 📋 Handai POS — Migration Plan: Laravel → Next.js

## 1. Executive Summary

| Metric | Nilai |
|--------|-------|
| **Total PHP Controllers** | 45 files (~8,761 lines) |
| **Total Blade Views** | 98 files (~18,754 lines) |
| **Total Models** | 37 files (~1,649 lines) |
| **Total Services** | 2 files (~1,057 lines) |
| **Database Migrations** | 53 files |
| **Total Estimasi Konversi** | ~30,221+ lines kode yang perlu di-rewrite |
| **Estimasi Waktu** | 8–12 minggu (1 developer full-time) |

---

## 2. Recommended Tech Stack

| Layer | Laravel (Current) | Next.js (Target) | Alasan |
|-------|-------------------|-------------------|--------|
| **Framework** | Laravel 11 | **Next.js 15 (App Router)** | Server Components + Server Actions = pattern mirip Laravel |
| **Language** | PHP | **TypeScript** | Type safety, DX lebih baik |
| **ORM** | Eloquent | **Prisma** | Relasi, migration, type-safe queries |
| **Database** | SQLite | **PostgreSQL** (recommended) atau tetap SQLite | Prisma support penuh, lebih production-ready |
| **Auth** | Session + Passport | **NextAuth.js v5 (Auth.js)** | Multi-role, session-based, credential provider |
| **Styling** | Tailwind + DaisyUI + Alpine.js | **Tailwind + DaisyUI + React state** | Alpine.js diganti React hooks |
| **Charts** | Chart.js (CDN) | **Chart.js via react-chartjs-2** | Wrapper React untuk Chart.js |
| **Payment** | Midtrans Snap (CDN) | **Midtrans Snap JS + API Routes** | Sama, hanya beda integration pattern |
| **PDF** | DomPDF | **@react-pdf/renderer** atau **Puppeteer** | PDF generation server-side |
| **Icons** | Tabler Icons + FontAwesome | **@tabler/icons-react + react-icons** | React native icons |
| **Animation** | Lottie (CDN) | **@lottiefiles/dotlottie-react** | Sudah ada di package.json |
| **API Mobile** | Laravel API Routes + Sanctum | **Next.js API Routes + JWT** | REST endpoints untuk Flutter app |
| **File Storage** | Laravel Storage (local) | **Local/S3 via Next.js API** | Upload handling via formidable/multer |

---

## 3. Database Schema (Prisma)

### 3.1 Semua Model yang Perlu Dikonversi (37 model)

#### Core Business
| Model Laravel | Tabel | Prisma Model | Relasi Utama |
|---------------|-------|--------------|--------------|
| `User` | users | `User` | roles (M2M via RoleUserStore), ownedStores |
| `Role` | roles | `Role` | users (M2M), self-referencing parent/children |
| `RoleUserStore` | role_user_store | `RoleUserStore` | Pivot: user_id, role_id, store_id |
| `Store` | store | `Store` | owner (User), resellers (M2M), customers (M2M) |
| `Employee` | employee | `Employee` | store, productionHistories |

#### Products & Variants
| Model | Tabel | Relasi Utama |
|-------|-------|--------------|
| `Product` | product | category, variants, sizePrices, productionHistories (through) |
| `ProductCategory` | product_category | products |
| `ProductVariants` | product_variants | product, variantOptions (M2M), sku, productionHistories |
| `VariantAttribute` | variant_attributes | options |
| `VariantOption` | variant_options | attribute, productVariants (M2M) |
| `Sku` | sku | productVariant |
| `Bom` | bom | product, stock, unit, productVariants |

#### Stock & Inventory
| Model | Tabel | Relasi Utama |
|-------|-------|--------------|
| `Stock` | stock | batches, unit, stockCategory, availableUnits (M2M) |
| `StockBatch` | stock_batches | stock, unit, supplier, store |
| `StockCategory` | stock_category | batches |
| `Unit` | units | conversionsFrom, conversionsTo |
| `UnitConversion` | unit_conversions | fromUnit, toUnit |
| `StockMovement` | stock_movements | store, stock, productVariant, unit, creator |
| `StockAdjustment` | stock_adjustments | store, stock, productVariant, unit, pic, creator |
| `Supplier` | suppliers | store, stockBatches |

#### Production & R&D
| Model | Tabel | Relasi Utama |
|-------|-------|--------------|
| `ProductionHistory` | production_history | product (through), usages, variant, pic |
| `ProductionStockUsage` | production_stock_usage | productionHistory, stock, unit |
| `RNDHistory` | rnd_history | pic, stockUsages |
| `RNDStockUsage` | rnd_stock_usage | stock, unit, rndHistory |
| `WasteLog` | waste_logs | store, stock, productVariant, unit, pic, creator |

#### Sales & Orders
| Model | Tabel | Relasi Utama |
|-------|-------|--------------|
| `Order` | orders | customer, promo, invoices, store, reseller |
| `Invoice` | invoice | order, product, variant |
| `Customer` | customer | store, stores (M2M via CustomerStore) |
| `CustomerStore` | customer_store | customer, store |
| `Promo` | promo | — |

#### Reseller
| Model | Tabel | Relasi Utama |
|-------|-------|--------------|
| `Reseller` | resellers | user, stores (M2M via ResellerStore) |
| `ResellerStore` | reseller_store | Pivot: reseller_id, store_id |

#### Accounting
| Model | Tabel | Relasi Utama |
|-------|-------|--------------|
| `ChartOfAccount` | chart_of_accounts | store, parent/children (self), journalEntries |
| `Journal` | journals | store, entries, creator |
| `JournalEntry` | journal_entries | journal, account |
| `FinancialPeriod` | financial_periods | store |

### 3.2 Prioritas Konversi Schema

```
Phase 1: User, Role, RoleUserStore, Store, Employee
Phase 2: Product, ProductCategory, ProductVariants, VariantAttribute, VariantOption, Sku, Bom
Phase 3: Stock, StockBatch, StockCategory, Unit, UnitConversion, Supplier
Phase 4: Order, Invoice, Customer, CustomerStore, Promo
Phase 5: ProductionHistory, ProductionStockUsage, RNDHistory, RNDStockUsage
Phase 6: StockMovement, StockAdjustment, WasteLog
Phase 7: ChartOfAccount, Journal, JournalEntry, FinancialPeriod
```

---

## 4. Authentication & Authorization

### Current (Laravel)
- **Session-based** auth via Laravel's built-in auth
- **Passport** for API (OAuth2 / Personal Access Tokens)
- **2 Middleware**: `CekRole` (session-based, checks `user_role` & `selected_store`) + `CheckRole` (model-based via `hasRole()`)
- **5 Roles**: Superadmin, Manager, POS, Kasir, Reseller
- **Store scoping**: Users access data scoped to their `selected_store` via session
- **Customer auth**: Separate `Customer` model (not `User`), session-based

### Target (Next.js)
```
NextAuth.js v5 (Auth.js)
├── CredentialsProvider (email/password login)
├── Session strategy: JWT (untuk API mobile) + Database sessions (untuk web)
├── Callbacks:
│   ├── jwt() — inject role, storeId ke token
│   └── session() — expose role, storeId ke client
├── Middleware (middleware.ts):
│   ├── /manager/* → require role=Manager + storeId
│   ├── /pos/* → require role=POS + storeId
│   ├── /kasir/* → require role=Kasir + storeId
│   ├── /superadmin/* → require role=Superadmin
│   └── /api/* → validate JWT/session
└── Customer Auth:
    └── Separate CredentialsProvider atau custom session handling
```

---

## 5. Page & Route Mapping

### 5.1 Public Pages (No Auth)

| Laravel Route | Next.js Route | Blade View | React Page |
|---------------|---------------|------------|------------|
| `GET /` | `/` | `index.blade.php` | `app/page.tsx` |
| `GET /login` | `/login` | `login.blade.php` | `app/login/page.tsx` |
| `GET /register` | `/register` | `register.blade.php` | `app/register/page.tsx` |
| `GET /reseller/register` | `/reseller/register` | `public/reseller-register.blade.php` | `app/reseller/register/page.tsx` |

### 5.2 Customer Order Pages (Public/Session)

| Laravel Route | Next.js Route | React Page |
|---------------|---------------|------------|
| `GET /customer-order/login` | `/order/login` | `app/order/login/page.tsx` |
| `GET /customer-order/register` | `/order/register` | `app/order/register/page.tsx` |
| `GET /customer-order/select-store` | `/order/select-store` | `app/order/select-store/page.tsx` |
| `GET /customer-order` | `/order` | `app/order/page.tsx` |
| `GET /customer-order/checkout` | `/order/checkout` | `app/order/checkout/page.tsx` |

### 5.3 Superadmin Pages

| Laravel Route | Next.js Route | React Page |
|---------------|---------------|------------|
| `GET /superadmin/dashboard` | `/superadmin` | `app/(dashboard)/superadmin/page.tsx` |
| `GET /superadmin/accounts` | `/superadmin/accounts` | `app/(dashboard)/superadmin/accounts/page.tsx` |
| `GET /superadmin/accounts/create` | `/superadmin/accounts/create` | `app/(dashboard)/superadmin/accounts/create/page.tsx` |
| `GET /superadmin/accounts/{id}/edit` | `/superadmin/accounts/[id]/edit` | `app/(dashboard)/superadmin/accounts/[id]/edit/page.tsx` |
| `GET /superadmin/stores` | `/superadmin/stores` | `app/(dashboard)/superadmin/stores/page.tsx` |
| `GET /superadmin/stores/create` | `/superadmin/stores/create` | `app/(dashboard)/superadmin/stores/create/page.tsx` |
| `GET /superadmin/simulate` | `/superadmin/simulate` | `app/(dashboard)/superadmin/simulate/page.tsx` |

### 5.4 Manager Pages (28 pages — module terbesar)

| Module | Laravel Route | Next.js Route |
|--------|---------------|---------------|
| **Store Selection** | `GET /manager/select-store` | `/manager/select-store` |
| **Dashboard** | `GET /manager/dashboard` | `/manager` |
| **Inventory > Products** | `GET /manager/inventory/products` | `/manager/inventory/products` |
| | `GET /manager/inventory/products/create` | `/manager/inventory/products/create` |
| | `GET /manager/inventory/products/{id}/edit` | `/manager/inventory/products/[id]/edit` |
| **Inventory > Stock** | `GET /manager/inventory/stock` | `/manager/inventory/stock` |
| | `GET /manager/inventory/stock/create` | `/manager/inventory/stock/create` |
| | `GET /manager/inventory/stock/{stock}/edit` | `/manager/inventory/stock/[id]/edit` |
| **Inventory > Stock Batches** | `GET /manager/inventory/stock-batches` | `/manager/inventory/stock-batches` |
| | `GET /manager/inventory/stock-batches/create` | `/manager/inventory/stock-batches/create` |
| **Inventory > Recipes (BOM)** | `GET /manager/inventory/recipes` | `/manager/inventory/recipes` |
| | `GET /manager/inventory/recipes/create` | `/manager/inventory/recipes/create` |
| | `GET /manager/inventory/recipes/{variant}/edit` | `/manager/inventory/recipes/[id]/edit` |
| **Operational > Produksi** | `GET /manager/operational/produksi` | `/manager/operational/production` |
| | `GET /manager/operational/produksi/create` | `/manager/operational/production/create` |
| **Operational > R&D** | `GET /manager/operational/rnd` | `/manager/operational/rnd` |
| | `GET /manager/operational/rnd/create` | `/manager/operational/rnd/create` |
| **Operational > Orders** | `GET /manager/operational/orders` | `/manager/operational/orders` |
| **Operational > Suppliers** | `GET /manager/operational/suppliers` | `/manager/operational/suppliers` |
| | `GET /manager/operational/suppliers/create` | `/manager/operational/suppliers/create` |
| | `GET /manager/operational/suppliers/{id}/edit` | `/manager/operational/suppliers/[id]/edit` |
| **Operational > Waste** | `GET /manager/operational/waste` | `/manager/operational/waste` |
| | `GET /manager/operational/waste/create` | `/manager/operational/waste/create` |
| **Operational > Stock Movements** | `GET /manager/operational/stock-movements` | `/manager/operational/stock-movements` |
| **Operational > Stock Opname** | `GET /manager/operational/stock-opname` | `/manager/operational/stock-opname` |
| | `GET /manager/operational/stock-opname/create` | `/manager/operational/stock-opname/create` |
| **Finance > Invoices** | `GET /manager/finance/invoices` | `/manager/finance/invoices` |
| | `GET /manager/finance/invoices/{id}` | `/manager/finance/invoices/[id]` |
| **Finance > Employees** | `GET /manager/finance/employees` | `/manager/finance/employees` |
| | `GET /manager/finance/employees/create` | `/manager/finance/employees/create` |
| **Finance > RND Request** | `GET /manager/finance/rnd-request` | `/manager/finance/rnd-request` |
| **Finance > RND Log** | `GET /manager/finance/rnd/log` | `/manager/finance/rnd-log` |
| **Finance > Stock Batch Finance** | `GET /manager/finance/stock-batches-finance` | `/manager/finance/stock-batches` |
| **Finance > Accounting Dashboard** | `GET /manager/finance/accounting/dashboard` | `/manager/finance/accounting` |
| **Finance > Chart of Accounts** | `GET /manager/finance/accounting/chart-of-accounts` | `/manager/finance/accounting/coa` |
| **Finance > Journal Entries** | `GET /manager/finance/accounting/journal-entries` | `/manager/finance/accounting/journals` |
| **Finance > Income Statement** | `GET /manager/finance/accounting/income-statement` | `/manager/finance/accounting/income` |
| **Finance > Balance Sheet** | `GET /manager/finance/accounting/balance-sheet` | `/manager/finance/accounting/balance` |
| **Finance > Cash Flow** | `GET /manager/finance/accounting/cash-flow` | `/manager/finance/accounting/cashflow` |
| **Marketing > Customers** | `GET /manager/marketing/customers` | `/manager/marketing/customers` |
| | `GET /manager/marketing/customers/create` | `/manager/marketing/customers/create` |
| | `GET /manager/marketing/customers/{id}/edit` | `/manager/marketing/customers/[id]/edit` |
| **Marketing > Resellers** | `GET /manager/marketing/resellers` | `/manager/marketing/resellers` |
| | `GET /manager/marketing/resellers/create` | `/manager/marketing/resellers/create` |

### 5.5 POS Pages

| Laravel Route | Next.js Route | React Page |
|---------------|---------------|------------|
| `GET /pos/select-store` | `/pos/select-store` | `app/(dashboard)/pos/select-store/page.tsx` |
| `GET /pos/dashboard` | `/pos` | `app/(dashboard)/pos/page.tsx` |
| `GET /pos/start-order` | `/pos/start-order` | `app/(dashboard)/pos/start-order/page.tsx` |
| `GET /pos/cart` | `/pos/cart` | `app/(dashboard)/pos/cart/page.tsx` |
| `GET /pos/history` | `/pos/history` | `app/(dashboard)/pos/history/page.tsx` |
| `GET /pos/invoice/print/{order}` | `/pos/invoice/[id]` | `app/(dashboard)/pos/invoice/[id]/page.tsx` |

### 5.6 Kasir Pages

| Laravel Route | Next.js Route | React Page |
|---------------|---------------|------------|
| `GET /kasir/select-store` | `/kasir/select-store` | `app/(dashboard)/kasir/select-store/page.tsx` |
| `GET /kasir/dashboard` | `/kasir` | `app/(dashboard)/kasir/page.tsx` |
| `GET /kasir/cart` | `/kasir/cart` | `app/(dashboard)/kasir/cart/page.tsx` |
| `GET /kasir/invoice/print/{order}` | `/kasir/invoice/[id]` | `app/(dashboard)/kasir/invoice/[id]/page.tsx` |

### 5.7 Reseller Pages

| Laravel Route | Next.js Route | React Page |
|---------------|---------------|------------|
| `GET /reseller/dashboard` | `/reseller` | `app/(dashboard)/reseller/page.tsx` |

---

## 6. API Routes (untuk Mobile/Flutter App)

### Current: 46 API endpoints under `/api/*`

Semua perlu dikonversi ke Next.js API Routes (`app/api/`):

```
app/api/
├── auth/
│   ├── login/route.ts          (POST — mobile login, return JWT)
│   └── register/route.ts       (POST — mobile register)
├── user/
│   └── profile/route.ts        (GET — user profile)
├── stores/
│   ├── route.ts                (GET — list stores)
│   ├── nearby/route.ts         (GET — nearby stores)
│   └── [id]/
│       ├── route.ts            (GET — store detail)
│       └── products/route.ts   (GET — store products)
├── products/
│   ├── route.ts                (GET — products by store)
│   └── store/route.ts          (POST — create product)
├── product-variants/
│   ├── route.ts                (GET — variants by product)
│   └── v1/route.ts             (GET — variant API index)
├── variant-attributes/
│   └── route.ts                (GET — list attributes)
├── stocks/
│   ├── route.ts                (GET, POST — list/create stocks)
│   ├── add/route.ts            (POST — quick add stock)
│   └── [categoryId]/route.ts   (GET — stocks by category)
├── stock-batches/
│   ├── route.ts                (GET — list batches)
│   └── [stockId]/route.ts      (POST — create batch)
├── stock-categories/
│   └── route.ts                (GET — list categories)
├── units/
│   └── route.ts                (GET — list units)
├── dashboard/
│   ├── customers/route.ts      (GET)
│   ├── finance/route.ts        (GET)
│   ├── sales-today/route.ts    (GET)
│   ├── count-by-size/route.ts  (GET)
│   ├── production-standard/route.ts (GET)
│   ├── today-production/route.ts    (GET)
│   └── manager/route.ts        (GET — summary)
├── productions/
│   ├── route.ts                (GET — list productions)
│   ├── form/route.ts           (GET — production form data)
│   ├── store/route.ts          (POST — create production)
│   └── filters/route.ts        (GET — production filters)
├── checkout/
│   ├── route.ts                (POST — create order)
│   └── cart/route.ts           (POST — session cart)
├── orders/
│   ├── route.ts                (GET — list orders)
│   └── [id]/
│       └── update-status/route.ts (POST — update order status)
└── customers/
    ├── route.ts                (GET, POST — list/create)
    ├── mobile/route.ts         (GET — mobile customer list)
    └── [id]/route.ts           (PUT, DELETE — update/delete)
```

---

## 7. Business Logic Services

### 7.1 InventoryService (12 methods → `lib/services/inventory.ts`)

| Method | Next.js Equivalent |
|--------|-------------------|
| `recordPurchaseIn()` | Server Action atau API Route |
| `recordProductionConsumption()` | Server Action |
| `recordProductionOutput()` | Server Action |
| `validateCartStock()` | Server-side validation |
| `processSaleDeduction()` | Server Action (dalam checkout flow) |
| `validateAndDeductOnShip()` | Server Action (order management) |
| `restoreStockOnCancel()` | Server Action (order cancel) |
| `recordExpiredReduction()` | Server Action |
| `recordRndConsumption()` | Server Action |
| `recordAdjustment()` | Server Action |
| `recordWasteStock()` | Server Action |
| `recordWasteProduct()` | Server Action |

### 7.2 AccountingService (12 methods → `lib/services/accounting.ts`)

Double-entry accounting — semua journal methods tetap sebagai server-side functions. Pattern akan sama: Prisma transactions menggantikan DB::transaction().

### 7.3 ConversionHelper → `lib/helpers/conversion.ts`

Unit conversion with caching — langsung convert ke TypeScript, gunakan in-memory Map cache.

---

## 8. Frontend Component Strategy

### 8.1 Layout Hierarchy

```
app/
├── layout.tsx                      (Root — HTML, metadata, providers)
├── (auth)/
│   ├── layout.tsx                  (Blank layout — login/register)
│   ├── login/page.tsx
│   └── register/page.tsx
├── (dashboard)/
│   ├── layout.tsx                  (Sidebar + Navbar + Store context)
│   ├── manager/
│   │   ├── layout.tsx              (Manager sidebar items)
│   │   ├── page.tsx                (Dashboard)
│   │   ├── inventory/...
│   │   ├── operational/...
│   │   ├── finance/...
│   │   └── marketing/...
│   ├── pos/
│   │   ├── layout.tsx              (POS-specific layout)
│   │   └── ...
│   ├── kasir/
│   │   ├── layout.tsx              
│   │   └── ...
│   ├── superadmin/
│   │   ├── layout.tsx              
│   │   └── ...
│   └── reseller/
│       └── ...
└── order/                          (Customer order — public layout)
    ├── layout.tsx
    └── ...
```

### 8.2 Shared Components (menggantikan Alpine.js)

| Alpine.js Pattern | React Equivalent |
|-------------------|-----------------|
| `x-data="{ open: false }"` | `const [open, setOpen] = useState(false)` |
| `x-show="open"` | `{open && <div>...</div>}` |
| `@click="open = !open"` | `onClick={() => setOpen(!open)}` |
| `x-model="search"` | `value={search} onChange={e => setSearch(e.target.value)}` |
| `x-for="item in items"` | `{items.map(item => <div key={item.id}>...</div>)}` |
| `Alpine.store('sidebar')` | React Context atau Zustand store |
| `x-init="fetch(...)"` | `useEffect` atau React Server Components |

### 8.3 Key Reusable Components to Build

```
components/
├── ui/                          (Base UI — bisa pakai shadcn/ui)
│   ├── Button.tsx
│   ├── Input.tsx
│   ├── Select.tsx
│   ├── Modal.tsx
│   ├── Table.tsx
│   ├── Card.tsx
│   ├── Badge.tsx
│   └── Toast.tsx
├── layout/
│   ├── Sidebar.tsx
│   ├── Navbar.tsx
│   ├── StoreSelector.tsx        (Dropdown pilih toko)
│   └── Breadcrumb.tsx
├── data/
│   ├── DataTable.tsx            (Table + sort + filter + pagination)
│   ├── SearchInput.tsx
│   └── ExportButton.tsx
├── charts/
│   ├── SalesChart.tsx
│   ├── StockChart.tsx
│   └── ProductionChart.tsx
├── forms/
│   ├── ProductForm.tsx
│   ├── StockForm.tsx
│   ├── ProductionForm.tsx
│   └── RecipeForm.tsx
├── cart/
│   ├── CartPanel.tsx
│   ├── CartItem.tsx
│   └── CheckoutSummary.tsx
└── invoice/
    ├── InvoicePrint.tsx
    └── InvoicePDF.tsx
```

---

## 9. Migration Phases (Detailed)

### Phase 1: Foundation (Minggu 1-2)
**Goal**: Project setup + Auth + Database

```
Tasks:
├── [ ] npx create-next-app@latest handai-pos-next --typescript --tailwind --app
├── [ ] Install dependencies: prisma, next-auth, daisyui, react-chartjs-2, etc.
├── [ ] Setup Prisma schema (ALL 37 models + relations)
├── [ ] Migration: npx prisma migrate dev
├── [ ] Seed data (convert Laravel seeders → Prisma seed)
├── [ ] NextAuth.js setup:
│   ├── [ ] CredentialsProvider (User login)
│   ├── [ ] Customer auth (separate flow)
│   ├── [ ] JWT callbacks (role, storeId in token)
│   └── [ ] Middleware.ts (role-based route protection)
├── [ ] Store selection mechanism (cookie/session based)
├── [ ] Base layout components (Sidebar, Navbar, StoreSelector)
├── [ ] Shared UI components (Button, Input, Table, Modal, Toast)
└── [ ] ConversionHelper → lib/helpers/conversion.ts
```

**Deliverable**: Login, register, store selection, dashboard skeleton

---

### Phase 2: Superadmin Module (Minggu 2-3)
**Goal**: Admin panel untuk manage users & stores

```
Pages: 7
Controllers to convert: SuperadminController, AccountController, SimulationController, StoreController
Lines of code: ~400

Tasks:
├── [ ] /superadmin/dashboard
├── [ ] /superadmin/accounts (CRUD)
├── [ ] /superadmin/stores (CRUD)
├── [ ] /superadmin/simulate
└── [ ] Server Actions for all mutations
```

**Deliverable**: Full superadmin panel

---

### Phase 3: Manager — Inventory Module (Minggu 3-5)
**Goal**: Product, Stock, Recipe (BOM) management — module terbesar

```
Pages: ~15
Controllers: InventoryController (1,035 lines!), RecipeController, StockBatchController
Lines of code: ~1,400

Tasks:
├── [ ] InventoryService → lib/services/inventory.ts
├── [ ] /manager/inventory/products (list, create, edit, delete)
│   ├── [ ] Variant management (create/delete variants)
│   ├── [ ] Expired production handling (discard/ignore)
│   └── [ ] Image upload
├── [ ] /manager/inventory/stock (list, create, edit, delete)
│   ├── [ ] Stock batch creation
│   ├── [ ] Quick-create stock
│   └── [ ] Batch from RND conversion
├── [ ] /manager/inventory/stock-batches (list, create, delete)
├── [ ] /manager/inventory/recipes (list, create, edit, delete)
│   └── [ ] BOM management (ingredients per variant)
└── [ ] Unit conversion system
```

**Deliverable**: Full inventory management

---

### Phase 4: Manager — Operational Module (Minggu 5-6)
**Goal**: Production, R&D, Orders, Suppliers, Waste, Stock Opname

```
Pages: ~15
Controllers: OperationalController, rndController, OrderController, SupplierController, WasteController, StockOpnameController, StockMovementController
Lines of code: ~900

Tasks:
├── [ ] /manager/operational/production (list, create + stock deduction)
├── [ ] /manager/operational/rnd (list, create + stock usage)
├── [ ] /manager/operational/orders (list, mark-shipped, cancel)
├── [ ] /manager/operational/suppliers (CRUD)
├── [ ] /manager/operational/waste (list, create)
├── [ ] /manager/operational/stock-movements (log view)
└── [ ] /manager/operational/stock-opname (list, create)
```

**Deliverable**: Full operational management

---

### Phase 5: Manager — Finance Module (Minggu 6-8)
**Goal**: Invoices, Employees, Accounting (double-entry), Reports

```
Pages: ~14
Controllers: FinanceController, InvoiceController, EmployeeController, AccountingController, RNDLogController, rndRequestController, StockBatchesController
Lines of code: ~700
Special: AccountingService (full double-entry bookkeeping)

Tasks:
├── [ ] AccountingService → lib/services/accounting.ts
│   ├── [ ] Prisma transactions for journal creation
│   └── [ ] Auto-seed Chart of Accounts per store
├── [ ] /manager/finance/invoices (list, detail, print, PDF)
├── [ ] /manager/finance/employees (list, create)
├── [ ] /manager/finance/rnd-request (list, approve/reject)
├── [ ] /manager/finance/rnd-log
├── [ ] /manager/finance/stock-batches
├── [ ] /manager/finance/accounting/dashboard
├── [ ] /manager/finance/accounting/coa (Chart of Accounts)
├── [ ] /manager/finance/accounting/journals
├── [ ] /manager/finance/accounting/income (Income Statement)
├── [ ] /manager/finance/accounting/balance (Balance Sheet)
└── [ ] /manager/finance/accounting/cashflow (Cash Flow)
```

**Deliverable**: Full finance & accounting

---

### Phase 6: Manager — Marketing + POS + Kasir (Minggu 8-10)
**Goal**: Customer/Reseller management, POS dashboard, Cart, Checkout

```
Pages: ~15
Controllers: CustomerController, ResellerController, DashboardPOS, CartController (POS & Kasir), CheckoutController, HistoryController, InvoiceController
Lines of code: ~2,000
Special: Midtrans payment integration, real-time cart management

Tasks:
├── [ ] /manager/marketing/customers (CRUD)
├── [ ] /manager/marketing/resellers (CRUD + attach to store)
├── [ ] POS Module:
│   ├── [ ] /pos/dashboard (product grid + cart panel)
│   ├── [ ] Cart system (add, update qty, remove, promo, notes)
│   ├── [ ] /pos/cart (checkout page + Midtrans Snap)
│   ├── [ ] /pos/history (transaction history)
│   └── [ ] /pos/invoice/[id] (print invoice)
├── [ ] Kasir Module:
│   ├── [ ] /kasir/dashboard
│   ├── [ ] Cart system (similar to POS)
│   ├── [ ] /kasir/cart (checkout)
│   └── [ ] /kasir/invoice/[id] (print + PDF)
└── [ ] Midtrans integration:
    ├── [ ] Snap token generation (API route)
    ├── [ ] Payment callback handling
    └── [ ] Client-side Snap popup
```

**Deliverable**: Full POS, Kasir, and Marketing

---

### Phase 7: Customer Order + Reseller + Mobile API (Minggu 10-12)
**Goal**: Public customer ordering, Reseller dashboard, all mobile API endpoints

```
Pages: ~7
Controllers: CustomerAuthController, CustomerOrderController, CustomerStoreSelectorController, DashboardController (Reseller), + 15 API controllers
Lines of code: ~2,500+

Tasks:
├── [ ] Customer Order Flow:
│   ├── [ ] /order/login + /order/register
│   ├── [ ] /order/select-store
│   ├── [ ] /order (product browsing + cart)
│   └── [ ] /order/checkout (Midtrans)
├── [ ] Reseller Dashboard:
│   └── [ ] /reseller (view sales, commissions)
├── [ ] Mobile API Routes (all 46 endpoints):
│   ├── [ ] Auth endpoints (login, register, profile)
│   ├── [ ] Dashboard endpoints (sales, finance, stock, production)
│   ├── [ ] Stock management endpoints
│   ├── [ ] Production endpoints
│   ├── [ ] Order endpoints
│   └── [ ] Customer endpoints
└── [ ] Testing & QA
```

**Deliverable**: Complete application

---

## 10. Key Technical Challenges

### 10.1 Session-based Store Scoping
**Laravel**: `session('selected_store')` dipakai di hampir semua query
**Next.js**: Gunakan cookie + middleware. Store ID di-set via cookie, dibaca di Server Components via `cookies()`.

### 10.2 Cart System
**Laravel**: Session-based cart (`session('cart')`)
**Next.js**: Options:
- **Cookie-based** (untuk small carts)
- **Database-backed** (untuk persistence)
- **Zustand store** (client-side, synced to API) ← **Recommended**

### 10.3 Midtrans Payment
**Laravel**: `Midtrans\Snap::createTransaction()` di backend + Snap.js popup frontend
**Next.js**: Midtrans REST API di API Route + Snap.js popup tetap sama

### 10.4 PDF Generation
**Laravel**: DomPDF renders Blade views to PDF
**Next.js**: Options:
- **@react-pdf/renderer** (React components → PDF)
- **Puppeteer** (render page → PDF, heavier)
- **jsPDF** (client-side, simpler)

### 10.5 File Upload (Product Images, Nota)
**Laravel**: `Storage::disk('public')->put()`
**Next.js**: API Route + `formidable` or `multer` → save to `public/uploads/` or S3

### 10.6 Eloquent → Prisma Patterns

| Eloquent | Prisma |
|----------|--------|
| `Model::where('x', 1)->get()` | `prisma.model.findMany({ where: { x: 1 } })` |
| `Model::with('relation')->find($id)` | `prisma.model.findUnique({ where: { id }, include: { relation: true } })` |
| `Model::create([...])` | `prisma.model.create({ data: {...} })` |
| `DB::transaction(fn => ...)` | `prisma.$transaction(async (tx) => ...)` |
| `$model->relation()->attach(...)` | `prisma.model.update({ data: { relation: { connect: {...} } } })` |
| `Model::whereHas('rel', fn)` | `prisma.model.findMany({ where: { rel: { some: {...} } } })` |
| `hasManyThrough` | Nested include atau raw query |
| `$model->save()` | `prisma.model.update({ where: { id }, data: {...} })` |

---

## 11. Folder Structure (Next.js Project)

```
handai-pos-next/
├── prisma/
│   ├── schema.prisma           (All 37 models)
│   ├── migrations/
│   └── seed.ts
├── src/
│   ├── app/
│   │   ├── layout.tsx
│   │   ├── page.tsx            (Landing page)
│   │   ├── (auth)/
│   │   │   ├── layout.tsx
│   │   │   ├── login/page.tsx
│   │   │   └── register/page.tsx
│   │   ├── (dashboard)/
│   │   │   ├── layout.tsx      (Sidebar+Navbar+StoreProvider)
│   │   │   ├── manager/
│   │   │   │   ├── layout.tsx
│   │   │   │   ├── page.tsx    (Dashboard)
│   │   │   │   ├── inventory/
│   │   │   │   ├── operational/
│   │   │   │   ├── finance/
│   │   │   │   └── marketing/
│   │   │   ├── pos/
│   │   │   ├── kasir/
│   │   │   ├── superadmin/
│   │   │   └── reseller/
│   │   ├── order/              (Customer order — public)
│   │   └── api/                (Mobile API routes)
│   │       ├── auth/
│   │       ├── stores/
│   │       ├── stocks/
│   │       ├── products/
│   │       ├── productions/
│   │       ├── orders/
│   │       ├── customers/
│   │       ├── dashboard/
│   │       └── checkout/
│   ├── components/
│   │   ├── ui/                 (Base components)
│   │   ├── layout/             (Sidebar, Navbar, etc.)
│   │   ├── charts/             (Chart components)
│   │   ├── forms/              (Form components)
│   │   ├── cart/               (Cart components)
│   │   └── invoice/            (Invoice/PDF components)
│   ├── lib/
│   │   ├── prisma.ts           (Prisma client singleton)
│   │   ├── auth.ts             (NextAuth config)
│   │   ├── midtrans.ts         (Midtrans client)
│   │   ├── services/
│   │   │   ├── inventory.ts    (InventoryService)
│   │   │   └── accounting.ts   (AccountingService)
│   │   ├── helpers/
│   │   │   ├── conversion.ts   (Unit conversion)
│   │   │   ├── formatter.ts    (Number formatting)
│   │   │   └── role.ts         (Role helpers)
│   │   └── validations/
│   │       ├── product.ts      (Zod schemas)
│   │       ├── stock.ts
│   │       └── order.ts
│   ├── hooks/
│   │   ├── useCart.ts
│   │   ├── useStore.ts
│   │   └── useAuth.ts
│   ├── stores/                 (Zustand stores)
│   │   ├── cart-store.ts
│   │   └── sidebar-store.ts
│   ├── types/
│   │   └── index.ts            (Shared TypeScript types)
│   └── middleware.ts           (Auth + role middleware)
├── public/
│   ├── assets/
│   ├── animations/
│   └── uploads/
├── .env
├── next.config.ts
├── tailwind.config.ts
├── tsconfig.json
└── package.json
```

---

## 12. Dependencies (package.json)

```json
{
  "dependencies": {
    "next": "^15.x",
    "react": "^19.x",
    "react-dom": "^19.x",
    "@prisma/client": "^6.x",
    "next-auth": "^5.x",
    "zod": "^3.x",
    "zustand": "^5.x",
    "chart.js": "^4.x",
    "react-chartjs-2": "^5.x",
    "@tabler/icons-react": "^3.x",
    "react-icons": "^5.x",
    "@lottiefiles/dotlottie-react": "^0.x",
    "midtrans-client": "^1.x",
    "@react-pdf/renderer": "^4.x",
    "date-fns": "^4.x",
    "clsx": "^2.x",
    "tailwind-merge": "^2.x",
    "sonner": "^2.x",
    "swiper": "^11.x"
  },
  "devDependencies": {
    "typescript": "^5.x",
    "prisma": "^6.x",
    "@types/node": "^22.x",
    "@types/react": "^19.x",
    "tailwindcss": "^4.x",
    "daisyui": "^5.x",
    "postcss": "^8.x",
    "autoprefixer": "^10.x",
    "eslint": "^9.x",
    "eslint-config-next": "^15.x"
  }
}
```

---

## 13. Risk Assessment

| Risk | Impact | Mitigation |
|------|--------|------------|
| Prisma tidak support semua Eloquent patterns (scopes, accessors, throughRelations) | Medium | Custom TypeScript helper functions, raw queries jika perlu |
| Session-based cart lebih kompleks di Next.js | Medium | Gunakan Zustand + API sync |
| Midtrans callback handling berbeda | Low | Webhook API route, pattern sudah standar |
| PDF generation quality berbeda dari DomPDF | Medium | Test thoroughly, gunakan Puppeteer jika perlu |
| Multi-auth (User + Customer) di NextAuth | Medium | Separate session strategies atau custom logic |
| Alpine.js → React rewrite (481 directives) | High | Biggest effort — setiap page perlu di-rewrite secara manual |
| Double-entry accounting logic complexity | High | Hati-hati konversi, tambah unit tests |
| 37 model Prisma schema complexity | Medium | Generate dari migration, review relations carefully |

---

## 14. Estimasi Effort per Phase

| Phase | Scope | Est. Effort | Priority |
|-------|-------|-------------|----------|
| 1. Foundation | Setup, Auth, DB, Layout | 2 minggu | 🔴 Critical |
| 2. Superadmin | 7 pages, simple CRUD | 0.5 minggu | 🟡 High |
| 3. Inventory | 15 pages, complex logic | 2 minggu | 🔴 Critical |
| 4. Operational | 15 pages, production flow | 1.5 minggu | 🔴 Critical |
| 5. Finance | 14 pages, accounting | 2 minggu | 🟡 High |
| 6. POS + Kasir + Marketing | 15 pages, cart + payment | 2 minggu | 🔴 Critical |
| 7. Customer + Reseller + API | 7 pages + 46 API endpoints | 2 minggu | 🟡 High |
| **TOTAL** | **~75 pages + 46 API endpoints** | **~12 minggu** | |

---

## 15. Testing Strategy

```
Testing:
├── Unit Tests (Vitest):
│   ├── lib/services/inventory.test.ts
│   ├── lib/services/accounting.test.ts
│   ├── lib/helpers/conversion.test.ts
│   └── lib/validations/*.test.ts
├── Integration Tests (Vitest + Prisma):
│   ├── API route tests
│   └── Server Action tests
└── E2E Tests (Playwright):
    ├── Auth flow
    ├── POS checkout flow
    ├── Production flow
    └── Accounting journal flow
```

---

*Dokumen ini dibuat otomatis berdasarkan analisis lengkap codebase Handai POS Laravel.*
*Terakhir diperbarui: 1 Maret 2026*
