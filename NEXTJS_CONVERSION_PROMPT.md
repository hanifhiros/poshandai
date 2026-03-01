# Mega Prompt: Laravel → Next.js Full Conversion

> **Instruksi**: Copy-paste prompt di bawah ini ke AI assistant (Claude/GPT) untuk memulai konversi. Prompt ini sudah mencakup SELURUH spesifikasi dari codebase Laravel Handai POS.

---

# ========== MULAI PROMPT ==========

Kamu adalah expert full-stack developer. Tugasmu adalah mengkonversi **seluruh** aplikasi POS (Point of Sale) bernama "Handai POS" dari **Laravel 11 (PHP)** ke **Next.js 15 (App Router) + TypeScript**.

## TECH STACK TARGET

```
Framework:      Next.js 15 (App Router, Server Components, Server Actions)
Language:       TypeScript (strict mode)
ORM:            Prisma 6 + PostgreSQL (atau SQLite untuk dev)
Auth:           NextAuth.js v5 (Auth.js) — CredentialsProvider
State:          Zustand 5 (client-side state: cart, sidebar)
Styling:        Tailwind CSS 4 + DaisyUI 5
Charts:         Chart.js 4 + react-chartjs-2
Payment:        Midtrans (Snap.js popup + server-side token generation)
PDF:            @react-pdf/renderer
Icons:          @tabler/icons-react
Animation:      @lottiefiles/dotlottie-react
Validation:     Zod (shared schemas for client + server)
Date:           date-fns
Utilities:      clsx + tailwind-merge
Toast:          sonner
Slider:         swiper
```

## PRISMA SCHEMA — 37 MODELS

Konversi SEMUA model berikut ke `prisma/schema.prisma`. Perhatikan nama tabel (beberapa tidak standard/plural).

```prisma
// prisma/schema.prisma
generator client {
  provider = "prisma-client-js"
}

datasource db {
  provider = "postgresql" // atau "sqlite" untuk dev
  url      = env("DATABASE_URL")
}

// ══════════════════════════════════════
// CORE: Users, Roles, Stores
// ══════════════════════════════════════

model User {
  id              Int       @id @default(autoincrement())
  name            String
  email           String    @unique
  password        String
  contactNumber   String?   @map("contact_number")
  emailVerifiedAt DateTime? @map("email_verified_at")
  rememberToken   String?   @map("remember_token")
  createdBy       Int?      @map("created_by")
  createdAt       DateTime  @default(now()) @map("created_at")
  updatedAt       DateTime  @updatedAt @map("updated_at")

  ownedStores     Store[]        @relation("StoreOwner")
  roleUserStores  RoleUserStore[]
  reseller        Reseller?
  createdJournals Journal[]      @relation("JournalCreator")
  
  @@map("users")
}

model Role {
  id        Int      @id @default(autoincrement())
  name      String
  parentId  Int?     @map("parent_id")
  createdAt DateTime @default(now()) @map("created_at")
  updatedAt DateTime @updatedAt @map("updated_at")

  parent         Role?          @relation("RoleHierarchy", fields: [parentId], references: [id])
  children       Role[]         @relation("RoleHierarchy")
  roleUserStores RoleUserStore[]

  @@map("roles")
}

model RoleUserStore {
  id           Int      @id @default(autoincrement())
  userId       Int      @map("user_id")
  roleId       Int      @map("role_id")
  storeId      Int?     @map("store_id")
  isMultistore Boolean  @default(false) @map("is_multistore")
  createdBy    Int?     @map("created_by")
  createdAt    DateTime @default(now()) @map("created_at")
  updatedAt    DateTime @updatedAt @map("updated_at")

  user  User  @relation(fields: [userId], references: [id])
  role  Role  @relation(fields: [roleId], references: [id])
  store Store? @relation(fields: [storeId], references: [id])

  @@map("role_user_store")
}

model Store {
  id           Int     @id @default(autoincrement())
  storeName    String  @map("store_name")
  storeAddress String? @map("store_address")
  ownerId      Int     @map("owner_id")
  longitude    Float?
  latitude     Float?
  createdAt    DateTime @default(now()) @map("created_at")
  updatedAt    DateTime @updatedAt @map("updated_at")

  owner            User               @relation("StoreOwner", fields: [ownerId], references: [id])
  roleUserStores   RoleUserStore[]
  products         Product[]
  stocks           Stock[]
  orders           Order[]
  employees        Employee[]
  resellers        ResellerStore[]
  customerStats    CustomerStore[]
  stockBatches     StockBatch[]
  productionHistories ProductionHistory[]
  rndHistories     RNDHistory[]
  journals         Journal[]
  chartOfAccounts  ChartOfAccount[]
  financialPeriods FinancialPeriod[]
  stockMovements   StockMovement[]
  stockAdjustments StockAdjustment[]
  wasteLogs        WasteLog[]
  suppliers        Supplier[]

  @@map("store")
}

model Employee {
  id            Int     @id @default(autoincrement())
  name          String
  email         String?
  password      String
  contactNumber String? @map("contact_number")
  position      String?
  salary        Decimal @default(0) @db.Decimal(10, 0)
  storeId       Int     @map("store_id")
  createdAt     DateTime @default(now()) @map("created_at")
  updatedAt     DateTime @updatedAt @map("updated_at")

  store               Store              @relation(fields: [storeId], references: [id])
  productionHistories ProductionHistory[] @relation("ProductionPIC")

  @@map("employee")
}

// ══════════════════════════════════════
// PRODUCTS & VARIANTS
// ══════════════════════════════════════

model ProductCategory {
  id           Int    @id @default(autoincrement())
  categoryName String @map("category_name")
  categoryIcon String? @map("category_icon")

  products Product[]

  @@map("product_category")
}

model Product {
  id              Int      @id @default(autoincrement())
  name            String
  categoryId      Int      @map("category_id")
  productImage    String?  @map("product_image")
  isPromo         Boolean  @default(false) @map("is_promo")
  priceDiscount   Decimal? @default(0) @map("price_discount") @db.Decimal(10, 0)
  storeId         Int      @map("store_id")
  expiredDuration Int?     @map("expired_duration") // days
  imageUrl        String?  @map("image_url")
  hpp             Decimal? @map("hpp") @db.Decimal(10, 0)
  createdAt       DateTime @default(now()) @map("created_at")
  updatedAt       DateTime @updatedAt @map("updated_at")

  category  ProductCategory  @relation(fields: [categoryId], references: [id])
  store     Store            @relation(fields: [storeId], references: [id])
  variants  ProductVariant[]
  boms      Bom[]
  invoices  Invoice[]

  @@map("product")
}

model VariantAttribute {
  id   Int    @id @default(autoincrement())
  name String
  code String?

  options VariantOption[]

  @@map("variant_attributes")
}

model VariantOption {
  id          Int    @id @default(autoincrement())
  attributeId Int    @map("attribute_id")
  name        String
  code        String?
  storeId     Int?   @map("store_id")
  sortOrder   Int    @default(0) @map("sort_order")

  attribute       VariantAttribute         @relation(fields: [attributeId], references: [id])
  productVariants ProductVariantOption[]

  @@map("variant_options")
}

model ProductVariant {
  id                   Int      @id @default(autoincrement())
  productId            Int      @map("product_id")
  size                 String?
  price                Decimal  @default(0) @db.Decimal(10, 0)
  quantity             Int      @default(0)
  priceDiscount        Decimal? @map("price_discount") @db.Decimal(10, 0)
  isPromo              Boolean  @default(false) @map("is_promo")
  storeId              Int      @map("store_id")
  hpp                  Decimal? @db.Decimal(10, 0)
  productName          String?  @map("product_name")
  variantOptionSummary String?  @map("variant_option_summary")
  createdAt            DateTime @default(now()) @map("created_at")
  updatedAt            DateTime @updatedAt @map("updated_at")

  product             Product                @relation(fields: [productId], references: [id])
  sku                 Sku?
  variantOptions      ProductVariantOption[]
  productionHistories ProductionHistory[]
  boms                Bom[]
  invoices            Invoice[]
  stockMovements      StockMovement[]       @relation("VariantMovements")
  stockAdjustments    StockAdjustment[]
  wasteLogs           WasteLog[]

  @@map("product_variants")
}

model ProductVariantOption {
  productVariantId Int @map("product_variant_id")
  variantOptionId  Int @map("variant_option_id")

  productVariant ProductVariant @relation(fields: [productVariantId], references: [id])
  variantOption  VariantOption  @relation(fields: [variantOptionId], references: [id])

  @@id([productVariantId, variantOptionId])
  @@map("product_variant_option")
}

model Sku {
  id               Int    @id @default(autoincrement())
  productVariantId Int    @unique @map("product_variant_id")
  skuCode          String @map("sku_code")
  barcode          String?

  productVariant ProductVariant @relation(fields: [productVariantId], references: [id])

  @@map("sku")
}

model Bom {
  id                Int     @id @default(autoincrement())
  productId         Int?    @map("product_id")
  productVariantsId Int     @map("product_variants_id")
  stockId           Int     @map("stock_id")
  quantityRequired  Decimal @map("quantity_required") @db.Decimal(10, 3)
  storeId           Int     @map("store_id")
  unitId            Int     @map("unit_id")

  product        Product?       @relation(fields: [productId], references: [id])
  productVariant ProductVariant @relation(fields: [productVariantsId], references: [id])
  stock          Stock          @relation(fields: [stockId], references: [id])
  unit           Unit           @relation(fields: [unitId], references: [id])

  @@map("bom")
}

// ══════════════════════════════════════
// STOCK & INVENTORY
// ══════════════════════════════════════

model StockCategory {
  id                Int    @id @default(autoincrement())
  stockCategoryName String @map("stock_category_name")

  stocks Stock[]

  @@map("stock_category")
  // Constants: RAW_MATERIAL = 1, WIP = 3
}

model Unit {
  id       Int    @id @default(autoincrement())
  symbol   String
  name     String
  unitType String? @map("unit_type")

  conversionsFrom      UnitConversion[]       @relation("ConversionFrom")
  conversionsTo        UnitConversion[]       @relation("ConversionTo")
  stocks               Stock[]
  stockBatches         StockBatch[]
  boms                 Bom[]
  productionStockUsage ProductionStockUsage[]
  rndStockUsage        RNDStockUsage[]
  stockMovements       StockMovement[]
  stockAdjustments     StockAdjustment[]
  wasteLogs            WasteLog[]

  @@map("units")
}

model UnitConversion {
  id             Int     @id @default(autoincrement())
  fromUnitId     Int     @map("from_unit_id")
  toUnitId       Int     @map("to_unit_id")
  conversionRate Decimal @map("conversion_rate") @db.Decimal(15, 6)

  fromUnit Unit @relation("ConversionFrom", fields: [fromUnitId], references: [id])
  toUnit   Unit @relation("ConversionTo", fields: [toUnitId], references: [id])

  @@map("unit_conversions")
}

model Stock {
  id              Int     @id @default(autoincrement())
  name            String
  pricePerUnit    Decimal @default(0) @map("price_per_unit") @db.Decimal(10, 2)
  unitQty         Decimal @default(0) @map("unit_qty") @db.Decimal(10, 3)
  unitId          Int     @map("unit_id")
  expiredDuration Int?    @map("expired_duration")
  stockCategoryId Int     @map("stock_category_id")
  storeId         Int     @map("store_id")
  createdAt       DateTime @default(now()) @map("created_at")
  updatedAt       DateTime @updatedAt @map("updated_at")

  unit          Unit          @relation(fields: [unitId], references: [id])
  stockCategory StockCategory @relation(fields: [stockCategoryId], references: [id])
  store         Store         @relation(fields: [storeId], references: [id])
  batches       StockBatch[]
  boms          Bom[]
  productionStockUsage ProductionStockUsage[]
  rndStockUsage RNDStockUsage[]
  stockMovements StockMovement[]
  stockAdjustments StockAdjustment[]
  wasteLogs     WasteLog[]

  @@map("stock")
}

model StockBatch {
  id              Int       @id @default(autoincrement())
  stockName       String?   @map("stock_name")
  stockId         Int?      @map("stock_id")
  unitQty         Decimal   @default(0) @map("unit_qty") @db.Decimal(10, 3)
  unitId          Int       @map("unit_id")
  cost            Decimal   @default(0) @db.Decimal(10, 2)
  buyDate         DateTime  @map("buy_date")
  storeId         Int       @map("store_id")
  notaUrl         String?   @map("nota_url")
  purchaseGroup   String?   @map("purchase_group")
  supplierName    String?   @map("supplier_name")
  supplierId      Int?      @map("supplier_id")
  invoiceRef      String?   @map("invoice_ref")
  paymentMethod   String?   @map("payment_method")
  dueDate         DateTime? @map("due_date")
  discount        Decimal?  @default(0) @db.Decimal(10, 2)
  tax             Decimal?  @default(0) @db.Decimal(10, 2)
  purchaseNotes   String?   @map("purchase_notes")
  expiredDuration Int?      @map("expired_duration")
  isStored        String    @default("ya") @map("isStored") // ENUM: ya/tidak
  paidAt          DateTime? @map("paid_at")
  createdAt       DateTime  @default(now()) @map("created_at")
  updatedAt       DateTime  @updatedAt @map("updated_at")

  stock    Stock?    @relation(fields: [stockId], references: [id])
  unit     Unit      @relation(fields: [unitId], references: [id])
  supplier Supplier? @relation(fields: [supplierId], references: [id])
  store    Store     @relation(fields: [storeId], references: [id])

  @@map("stock_batches")
}

model Supplier {
  id            Int      @id @default(autoincrement())
  storeId       Int      @map("store_id")
  name          String
  contactPerson String?  @map("contact_person")
  phone         String?
  email         String?
  address       String?
  city          String?
  paymentTerms  String?  @map("payment_terms")
  bankName      String?  @map("bank_name")
  bankAccount   String?  @map("bank_account")
  notes         String?
  isActive      Boolean  @default(true) @map("is_active")
  createdAt     DateTime @default(now()) @map("created_at")
  updatedAt     DateTime @updatedAt @map("updated_at")

  store        Store        @relation(fields: [storeId], references: [id])
  stockBatches StockBatch[]

  @@map("suppliers")
}

model StockMovement {
  id               Int      @id @default(autoincrement())
  storeId          Int      @map("store_id")
  stockId          Int?     @map("stock_id")
  productVariantId Int?     @map("product_variant_id")
  movementType     String   @map("movement_type")
  // Types: PURCHASE_IN, PRODUCTION_OUT, PRODUCTION_IN, SALE_OUT, SALE_RETURN, ADJUSTMENT, EXPIRED_OUT, WASTE_OUT, RND_OUT
  quantity         Decimal  @db.Decimal(10, 3)
  unitId           Int?     @map("unit_id")
  costPerUnit      Decimal  @default(0) @map("cost_per_unit") @db.Decimal(10, 2)
  totalCost        Decimal  @default(0) @map("total_cost") @db.Decimal(10, 2)
  referenceType    String?  @map("reference_type")
  referenceId      Int?     @map("reference_id")
  notes            String?
  createdBy        Int?     @map("created_by")
  createdAt        DateTime @default(now()) @map("created_at")
  updatedAt        DateTime @updatedAt @map("updated_at")

  store          Store           @relation(fields: [storeId], references: [id])
  stock          Stock?          @relation(fields: [stockId], references: [id])
  productVariant ProductVariant? @relation("VariantMovements", fields: [productVariantId], references: [id])
  unit           Unit?           @relation(fields: [unitId], references: [id])

  @@map("stock_movements")
}

model StockAdjustment {
  id               Int      @id @default(autoincrement())
  storeId          Int      @map("store_id")
  adjustmentDate   DateTime @map("adjustment_date")
  adjustmentNumber String   @map("adjustment_number")
  stockId          Int?     @map("stock_id")
  productVariantId Int?     @map("product_variant_id")
  itemType         String   @map("item_type")
  itemName         String   @map("item_name")
  systemQty        Decimal  @map("system_qty") @db.Decimal(10, 3)
  actualQty        Decimal  @map("actual_qty") @db.Decimal(10, 3)
  difference       Decimal  @db.Decimal(10, 3)
  unitId           Int?     @map("unit_id")
  costPerUnit      Decimal  @default(0) @map("cost_per_unit") @db.Decimal(10, 2)
  totalCostImpact  Decimal  @default(0) @map("total_cost_impact") @db.Decimal(10, 2)
  reason           String?
  notes            String?
  picId            Int?     @map("pic_id")
  createdBy        Int?     @map("created_by")
  status           String   @default("draft") // draft, approved, completed
  createdAt        DateTime @default(now()) @map("created_at")
  updatedAt        DateTime @updatedAt @map("updated_at")

  store          Store           @relation(fields: [storeId], references: [id])
  stock          Stock?          @relation(fields: [stockId], references: [id])
  productVariant ProductVariant? @relation(fields: [productVariantId], references: [id])
  unit           Unit?           @relation(fields: [unitId], references: [id])

  @@map("stock_adjustments")
}

// ══════════════════════════════════════
// PRODUCTION & R&D
// ══════════════════════════════════════

model ProductionHistory {
  id                   Int      @id @default(autoincrement())
  quantityProduced     Int      @map("quantity_produced")
  productionDate       DateTime @map("production_date")
  picId                Int?     @map("pic_id")
  productVariantsId    Int?     @map("product_variants_id")
  storeId              Int      @map("store_id")
  productName          String?  @map("product_name")
  variantOptionSummary String?  @map("variant_option_summary")
  isStored             String   @default("ya") @map("isStored") // ya/tidak
  createdAt            DateTime @default(now()) @map("created_at")
  updatedAt            DateTime @updatedAt @map("updated_at")

  productVariant ProductVariant?        @relation(fields: [productVariantsId], references: [id])
  pic            Employee?              @relation("ProductionPIC", fields: [picId], references: [id])
  store          Store                  @relation(fields: [storeId], references: [id])
  usages         ProductionStockUsage[]

  @@map("production_history")
}

model ProductionStockUsage {
  id                  Int     @id @default(autoincrement())
  productionHistoryId Int     @map("production_history_id")
  stockId             Int?    @map("stock_id")
  unitId              Int?    @map("unit_id")
  stockName           String? @map("stock_name")
  quantity            Decimal @db.Decimal(10, 3)

  productionHistory ProductionHistory @relation(fields: [productionHistoryId], references: [id])
  stock             Stock?            @relation(fields: [stockId], references: [id])
  unit              Unit?             @relation(fields: [unitId], references: [id])

  @@map("production_stock_usage")
}

model RNDHistory {
  id        Int      @id @default(autoincrement())
  storeId   Int      @map("store_id")
  rndName   String   @map("rnd_name")
  picId     Int?     @map("pic_id")
  rndDate   DateTime @map("rnd_date")
  deskripsi String?
  createdAt DateTime @default(now()) @map("created_at")
  updatedAt DateTime @updatedAt @map("updated_at")

  store       Store           @relation(fields: [storeId], references: [id])
  stockUsages RNDStockUsage[]

  @@map("rnd_history")
}

model RNDStockUsage {
  id           Int     @id @default(autoincrement())
  rndId        Int     @map("rnd_id")
  stockName    String? @map("stock_name")
  stockId      Int?    @map("stock_id")
  unitId       Int?    @map("unit_id")
  quantityUsed Decimal @map("quantity_used") @db.Decimal(10, 3)
  status       String?
  manualName   String? @map("manual_name")
  cost         Decimal? @db.Decimal(10, 2)

  rndHistory RNDHistory @relation(fields: [rndId], references: [id])
  stock      Stock?     @relation(fields: [stockId], references: [id])
  unit       Unit?      @relation(fields: [unitId], references: [id])

  @@map("rnd_stock_usage")
}

model WasteLog {
  id               Int      @id @default(autoincrement())
  storeId          Int      @map("store_id")
  wasteDate        DateTime @map("waste_date")
  itemType         String   @map("item_type") // stock / product
  stockId          Int?     @map("stock_id")
  productVariantId Int?     @map("product_variant_id")
  itemName         String   @map("item_name")
  quantity         Decimal  @db.Decimal(10, 3)
  unitId           Int?     @map("unit_id")
  costPerUnit      Decimal  @default(0) @map("cost_per_unit") @db.Decimal(10, 2)
  totalCost        Decimal  @default(0) @map("total_cost") @db.Decimal(10, 2)
  reason           String   // expired, spillage, quality_reject, damaged, other
  notes            String?
  picId            Int?     @map("pic_id")
  createdBy        Int?     @map("created_by")
  createdAt        DateTime @default(now()) @map("created_at")
  updatedAt        DateTime @updatedAt @map("updated_at")

  store          Store           @relation(fields: [storeId], references: [id])
  stock          Stock?          @relation(fields: [stockId], references: [id])
  productVariant ProductVariant? @relation(fields: [productVariantId], references: [id])
  unit           Unit?           @relation(fields: [unitId], references: [id])

  @@map("waste_logs")
}

// ══════════════════════════════════════
// SALES & ORDERS
// ══════════════════════════════════════

model Customer {
  id             Int      @id @default(autoincrement())
  name           String
  nickname       String?
  contactNumber  String?  @map("contact_number")
  email          String?
  address        String?
  gender         String?
  qtyOrdered     Int      @default(0) @map("qty_ordered")
  qtyOrderedAvg  Int      @default(0) @map("qty_ordered_avg")
  hasOrdered     Boolean  @default(false) @map("has_ordered")
  createdBy      Int?     @map("created_by")
  storeId        Int?     @map("store_id")
  password       String?
  createdAt      DateTime @default(now()) @map("created_at")
  updatedAt      DateTime @updatedAt @map("updated_at")

  orders        Order[]
  customerStores CustomerStore[]

  @@map("customer")
}

model CustomerStore {
  id               Int       @id @default(autoincrement())
  customerId       Int       @map("customer_id")
  storeId          Int       @map("store_id")
  totalOrderedQty  Int       @default(0) @map("total_ordered_qty")
  averageOrderedQty Int      @default(0) @map("average_ordered_qty")
  totalOrders      Int       @default(0) @map("total_orders")
  firstOrderedAt   DateTime? @map("first_ordered_at")
  lastOrderedAt    DateTime? @map("last_ordered_at")
  createdAt        DateTime  @default(now()) @map("created_at")
  updatedAt        DateTime  @updatedAt @map("updated_at")

  customer Customer @relation(fields: [customerId], references: [id])
  store    Store    @relation(fields: [storeId], references: [id])

  @@map("customer_store")
}

model Promo {
  id               Int      @id @default(autoincrement())
  promoCode        String   @map("Promo_Code")
  discountRate     Decimal  @map("discount_rate") @db.Decimal(5, 2)
  maxDiscountPrice Decimal  @default(0) @map("max_discount_price") @db.Decimal(10, 0)
  isActive         Boolean  @default(true) @map("is_active")
  orderId          Int?     @map("order_id")
  createdAt        DateTime @default(now()) @map("created_at")
  updatedAt        DateTime @updatedAt @map("updated_at")

  orders Order[]

  @@map("promo")
}

model Order {
  id              Int      @id @default(autoincrement())
  orderId         String?  @map("order_id")
  snapToken       String?  @map("snap_token")
  grossAmount     Decimal  @default(0) @map("gross_amount") @db.Decimal(10, 0)
  totalItemPrice  Decimal  @default(0) @map("total_item_price") @db.Decimal(10, 0)
  orderOrigin     String?  @map("order_origin")
  deliveryFee     Decimal  @default(0) @map("delivery_fee") @db.Decimal(10, 0)
  promoId         Int?     @map("PROMO_ID")
  note            String?
  orderStatus     String   @default("pending") @map("order_status")
  description     String?
  customerId      Int?     @map("customer_id")
  paymentId       String?  @map("payment_id")
  sellerId        Int?     @map("seller_id")
  storeId         Int      @map("store_id")
  midtransStatus  String?  @map("midtrans_status")
  paymentType     String?  @map("payment_type")
  vaNumber        String?  @map("va_number")
  pdfUrl          String?  @map("pdf_url")
  midtransResponse String? @map("midtrans_response")
  deliveryDate    DateTime? @map("delivery_date")
  deliveryTime    String?  @map("delivery_time")
  totalHppOrders  Decimal? @map("total_hpp_orders") @db.Decimal(10, 0)
  deliveryAddress String?  @map("delivery_address")
  resellerId      Int?     @map("reseller_id")
  pajak           Decimal  @default(0) @db.Decimal(10, 0)
  ongkosKirim     Decimal  @default(0) @map("ongkos_kirim") @db.Decimal(10, 0)
  kemasan         Decimal  @default(0) @db.Decimal(10, 0)
  createdAt       DateTime @default(now()) @map("created_at")
  updatedAt       DateTime @updatedAt @map("updated_at")

  customer Customer? @relation(fields: [customerId], references: [id])
  promo    Promo?    @relation(fields: [promoId], references: [id])
  store    Store     @relation(fields: [storeId], references: [id])
  reseller Reseller? @relation(fields: [resellerId], references: [id])
  invoices Invoice[]

  @@map("orders")
}

model Invoice {
  id             Int     @id @default(autoincrement())
  orderId        Int     @map("order_id")
  productId      Int?    @map("product_id")
  variantId      Int?    @map("variant_id")
  quantityBought Int     @map("quantity_bought")
  price          Decimal @db.Decimal(10, 0)
  totalPrice     Decimal @map("total_price") @db.Decimal(10, 0)
  productName    String? @map("product_name")
  variantName    String? @map("variant_name")
  createdAt      DateTime @default(now()) @map("created_at")
  updatedAt      DateTime @updatedAt @map("updated_at")

  order   Order           @relation(fields: [orderId], references: [id])
  product Product?        @relation(fields: [productId], references: [id])
  variant ProductVariant? @relation(fields: [variantId], references: [id])

  @@map("invoice")
}

// ══════════════════════════════════════
// RESELLER
// ══════════════════════════════════════

model Reseller {
  id     Int     @id @default(autoincrement())
  userId Int     @unique @map("user_id")
  name   String
  code   String  @unique
  phone  String?
  status String  @default("active")
  createdAt DateTime @default(now()) @map("created_at")
  updatedAt DateTime @updatedAt @map("updated_at")

  user           User            @relation(fields: [userId], references: [id])
  resellerStores ResellerStore[]
  orders         Order[]

  @@map("resellers")
}

model ResellerStore {
  id           Int     @id @default(autoincrement())
  resellerId   Int     @map("reseller_id")
  storeId      Int     @map("store_id")
  paymentRate  Decimal? @map("payment_rate") @db.Decimal(5, 2)
  qtySold      Int     @default(0) @map("qty_sold")
  totalSold    Decimal @default(0) @map("total_sold") @db.Decimal(10, 0)
  totalCommission Decimal @default(0) @map("total_commission") @db.Decimal(10, 0)
  createdAt    DateTime @default(now()) @map("created_at")
  updatedAt    DateTime @updatedAt @map("updated_at")

  reseller Reseller @relation(fields: [resellerId], references: [id])
  store    Store    @relation(fields: [storeId], references: [id])

  @@map("reseller_store")
}

// ══════════════════════════════════════
// ACCOUNTING (Double-Entry Bookkeeping)
// ══════════════════════════════════════

model ChartOfAccount {
  id          Int      @id @default(autoincrement())
  storeId     Int      @map("store_id")
  code        String
  name        String
  type        String   // asset, liability, equity, revenue, cogs, expense
  subType     String?  @map("sub_type")
  // sub_types: kas, bank, piutang, inventory_raw, inventory_fg, hutang, modal, retained_earnings, penjualan, hpp, gaji, operasional, adjustment
  parentId    Int?     @map("parent_id")
  isSystem    Boolean  @default(false) @map("is_system")
  isActive    Boolean  @default(true) @map("is_active")
  description String?
  createdAt   DateTime @default(now()) @map("created_at")
  updatedAt   DateTime @updatedAt @map("updated_at")

  store          Store            @relation(fields: [storeId], references: [id])
  parent         ChartOfAccount?  @relation("COAHierarchy", fields: [parentId], references: [id])
  children       ChartOfAccount[] @relation("COAHierarchy")
  journalEntries JournalEntry[]

  @@map("chart_of_accounts")
}

model Journal {
  id             Int      @id @default(autoincrement())
  storeId        Int      @map("store_id")
  journalNumber  String   @map("journal_number")
  journalDate    DateTime @map("journal_date")
  description    String
  source         String   // POS, KASIR, PURCHASE, PRODUCTION, ADJUSTMENT, EXPIRED, WASTE, CANCEL, MANUAL
  referenceType  String?  @map("reference_type")
  referenceId    Int?     @map("reference_id")
  totalDebit     Decimal  @default(0) @map("total_debit") @db.Decimal(12, 2)
  totalCredit    Decimal  @default(0) @map("total_credit") @db.Decimal(12, 2)
  createdBy      Int?     @map("created_by")
  createdAt      DateTime @default(now()) @map("created_at")
  updatedAt      DateTime @updatedAt @map("updated_at")

  store   Store         @relation(fields: [storeId], references: [id])
  creator User?         @relation("JournalCreator", fields: [createdBy], references: [id])
  entries JournalEntry[]

  @@map("journals")
}

model JournalEntry {
  id        Int     @id @default(autoincrement())
  journalId Int     @map("journal_id")
  accountId Int     @map("account_id")
  debit     Decimal @default(0) @db.Decimal(12, 2)
  credit    Decimal @default(0) @db.Decimal(12, 2)
  memo      String?

  journal Journal        @relation(fields: [journalId], references: [id])
  account ChartOfAccount @relation(fields: [accountId], references: [id])

  @@map("journal_entries")
}

model FinancialPeriod {
  id        Int       @id @default(autoincrement())
  storeId   Int       @map("store_id")
  name      String
  startDate DateTime  @map("start_date")
  endDate   DateTime  @map("end_date")
  status    String    @default("open")
  closedBy  Int?      @map("closed_by")
  closedAt  DateTime? @map("closed_at")

  store Store @relation(fields: [storeId], references: [id])

  @@map("financial_periods")
}
```

---

## AUTHENTICATION & AUTHORIZATION

### System Roles (5 roles):
1. **Superadmin** — manage all users & stores, simulate any role
2. **Manager** — full access to assigned store(s): inventory, operational, finance, marketing
3. **POS** — point of sale: product grid, cart, checkout, transaction history
4. **Kasir** — cashier: similar to POS but different checkout flow (order first, pay later)
5. **Reseller** — external sales: dashboard showing commissions & sales

### Auth Rules:
- Users can have MULTIPLE roles across DIFFERENT stores (via `role_user_store` pivot)
- Session stores `user_role` and `selected_store` — all queries scoped to selected store
- Superadmin bypasses all role/store checks
- Customers have SEPARATE auth (different model, password-based, session-based)
- API mobile uses JWT (via Laravel Passport, convert to NextAuth JWT)

### Implementation:
```typescript
// lib/auth.ts — NextAuth v5 config
// CredentialsProvider for User login (email + password)
// JWT callback: inject { role, storeId, userId } into token
// Session callback: expose role + storeId to client

// middleware.ts — Edge middleware
// /manager/* → require role=Manager, storeId required
// /pos/* → require role=POS, storeId required
// /kasir/* → require role=Kasir, storeId required
// /superadmin/* → require role=Superadmin
// /reseller/* → require role=Reseller
// /api/* (protected) → validate JWT
// /order/* → public (customer session, not NextAuth)

// Store selection: store as cookie, read via cookies() in Server Components
```

---

## ALL PAGES & ROUTES (75 pages total)

### Public (no auth):
```
/                           → Landing page
/login                      → User login form
/register                   → User registration
/reseller/register          → Public reseller registration
/order/login                → Customer login
/order/register             → Customer registration
/order/select-store         → Customer picks a store
/order                      → Customer browsing + cart (form.blade.php)
/order/checkout             → Customer checkout with Midtrans
```

### Superadmin (role=Superadmin):
```
/superadmin                 → Dashboard (stats)
/superadmin/accounts        → User list (CRUD)
/superadmin/accounts/create → Create user + assign role+store
/superadmin/accounts/[id]/edit → Edit user
/superadmin/stores          → Store list (CRUD)
/superadmin/stores/create   → Create store
/superadmin/simulate        → Simulate login as any role in any store
```

### Manager (role=Manager, store scoped):
```
/manager/select-store       → Pick store (if multi-store)
/manager                    → Dashboard (charts: sales, production, stock levels)

# Inventory
/manager/inventory/products                → Product list + variant management
/manager/inventory/products/create         → Create product + variants + images
/manager/inventory/products/[id]/edit      → Edit product
/manager/inventory/stock                   → Stock (raw material) list
/manager/inventory/stock/create            → Create stock + initial batch
/manager/inventory/stock/[id]/edit         → Edit stock
/manager/inventory/stock-batches           → Stock batch list
/manager/inventory/stock-batches/create    → Create batch (with purchase invoice)
/manager/inventory/recipes                 → BOM/Recipe list
/manager/inventory/recipes/create          → Create recipe (ingredients per variant)
/manager/inventory/recipes/[id]/edit       → Edit recipe

# Operational
/manager/operational/production            → Production history list
/manager/operational/production/create     → Create production (uses BOM, deducts stock)
/manager/operational/rnd                   → R&D experiment list
/manager/operational/rnd/create            → Create R&D (uses stock)
/manager/operational/orders                → Order management (mark shipped/cancel)
/manager/operational/suppliers             → Supplier CRUD
/manager/operational/suppliers/create
/manager/operational/suppliers/[id]/edit
/manager/operational/waste                 → Waste log list
/manager/operational/waste/create          → Log waste (stock or product)
/manager/operational/stock-movements       → Stock movement history (read-only log)
/manager/operational/stock-opname          → Stock opname list
/manager/operational/stock-opname/create   → Create stock opname (system vs actual qty)

# Finance
/manager/finance/invoices                  → Invoice list
/manager/finance/invoices/[id]             → Invoice detail + print + PDF
/manager/finance/employees                 → Employee list
/manager/finance/employees/create          → Create employee
/manager/finance/rnd-request               → RND request approval (approve/reject all)
/manager/finance/rnd-log                   → RND activity log
/manager/finance/stock-batches             → Stock batch finance log
/manager/finance/accounting                → Accounting dashboard (pie charts, totals)
/manager/finance/accounting/coa            → Chart of Accounts
/manager/finance/accounting/journals       → Journal entries list
/manager/finance/accounting/income         → Income Statement report
/manager/finance/accounting/balance        → Balance Sheet report
/manager/finance/accounting/cashflow       → Cash Flow report

# Marketing
/manager/marketing/customers               → Customer CRUD
/manager/marketing/customers/create
/manager/marketing/customers/[id]/edit
/manager/marketing/resellers               → Reseller CRUD
/manager/marketing/resellers/create
```

### POS (role=POS, store scoped):
```
/pos/select-store           → Pick store
/pos                        → Dashboard: product grid + cart panel (side-by-side)
/pos/start-order            → Start new order
/pos/cart                   → Cart + checkout (Midtrans Snap popup)
/pos/history                → Transaction history
/pos/invoice/[id]           → Print invoice
```

### Kasir (role=Kasir, store scoped):
```
/kasir/select-store         → Pick store
/kasir                      → Dashboard
/kasir/cart                 → Cart + checkout
/kasir/invoice/[id]         → Print invoice + PDF download
```

### Reseller (role=Reseller):
```
/reseller                   → Dashboard (sales, commissions per store)
```

---

## BUSINESS LOGIC SERVICES (CRITICAL — must be exact)

### 1. InventoryService (`lib/services/inventory.ts`)
All methods are server-side only. Use Prisma transactions.

```typescript
// recordPurchaseIn(storeId, stock, batch, convertedQty): StockMovement
//   → Creates PURCHASE_IN movement after batch created

// recordProductionConsumption(storeId, stock, usedQty, bomUnitId, productionHistoryId): StockMovement
//   → Creates PRODUCTION_OUT movement, deducts raw material stock

// recordProductionOutput(storeId, variant, quantityProduced, hppPerUnit, productionHistoryId): StockMovement
//   → Creates PRODUCTION_IN movement, increases finished goods

// validateCartStock(cart): string[]
//   → Validates all cart variants have sufficient quantity, returns error messages

// processSaleDeduction(cart, orderId, storeId): number (totalHpp)
//   → Deducts product variant quantities, creates SALE_OUT movements, returns total HPP

// validateAndDeductOnShip(order): void
//   → For Kasir flow: validates + deducts stock when marking order as shipped. Throws on insufficient stock.

// restoreStockOnCancel(order, previousStatus): void
//   → Restores stock quantities + creates SALE_RETURN movements when cancelling a shipped order

// recordExpiredReduction(storeId, stock, expiredQty): StockMovement
//   → Creates EXPIRED_OUT movement

// recordRndConsumption(storeId, stock, quantity, rndHistoryId): StockMovement
//   → Creates RND_OUT movement

// recordAdjustment(storeId, stockId?, variantId?, quantityChange, unitId?, reason, costPerUnit?): StockMovement
//   → Creates ADJUSTMENT movement (positive or negative)

// recordWasteStock(storeId, stock, quantity, wasteLogId, reason?): StockMovement
//   → Creates WASTE_OUT for raw material

// recordWasteProduct(storeId, variant, quantity, wasteLogId, reason?): StockMovement
//   → Creates WASTE_OUT for finished product
```

### 2. AccountingService (`lib/services/accounting.ts`)
Double-entry bookkeeping. ALL journal entries must balance (debit == credit).

```typescript
// createJournal(storeId, description, source, entries[], refType?, refId?, date?): Journal
//   → Core method: creates journal + entries in transaction. Validates debit == credit.
//   → entries = [{ accountId, debit, credit, memo? }]

// journalSale(storeId, grossAmount, totalHpp, orderId, source='POS'): Journal
//   → Dr Kas (grossAmount) + Dr HPP (totalHpp) / Cr Penjualan (grossAmount) + Cr Inventory FG (totalHpp)

// journalPurchaseCash(storeId, cost, batchId, stockName): Journal
//   → Dr Inventory Raw Material / Cr Kas

// journalPurchaseCredit(storeId, cost, batchId, stockName): Journal
//   → Dr Inventory Raw Material / Cr Hutang Usaha

// journalPayDebt(storeId, amount, refId?, description?): Journal
//   → Dr Hutang Usaha / Cr Kas

// journalProduction(storeId, totalCost, productionId, productName): Journal | null
//   → Dr Inventory FG / Cr Inventory Raw. Returns null if cost is 0.

// journalSaleReturn(storeId, grossAmount, totalHpp, orderId): Journal
//   → Reverse of journalSale

// journalExpired(storeId, expiredValue, stockId, stockName): Journal | null
//   → Dr Biaya Penyesuaian / Cr Inventory Raw

// journalWaste(storeId, wasteValue, itemName, itemType='stock', wasteLogId?): Journal | null
//   → Dr Biaya Adjustment / Cr Inventory (raw or FG depending on itemType)

// journalAdjustment(storeId, value, isPositive, reason, stockId?): Journal | null
//   → Manual stock adjustment journal

// Chart of Accounts auto-seeds per store (ensureCOA/seedCOAForStore)
// Sub-types: kas, bank, piutang, inventory_raw, inventory_fg, hutang, modal, retained_earnings, penjualan, hpp, gaji, operasional, adjustment

// sumByType(storeId, types, startDate?, endDate?): number
//   → Aggregated balance for given account types in period

// breakdownByType(storeId, type, startDate?, endDate?): {accountId, name, balance}[]
//   → Per-account breakdown
```

### 3. ConversionHelper (`lib/helpers/conversion.ts`)
```typescript
// getConversionRate(fromUnitId, toUnitId): number | null
//   → Looks up unit_conversions table. If forward not found, checks reverse (1/rate).
//   → Uses in-memory Map cache to avoid repeated DB calls.

// preloadAll(): void — bulk load all conversions into cache
// clearCache(): void
```

### 4. Stock Recalculation Logic
```typescript
// Stock.recalculateStockSummary():
//   → Recalculates stock.unit_qty and stock.price_per_unit from valid batches (isStored='ya')
//   → Uses unit conversion to normalize quantities to stock's base unit
//   → Weighted average price calculation
```

---

## SPECIAL PATTERNS TO IMPLEMENT

### 1. Cart System (POS, Kasir, Customer Order)
```
Current: Laravel session-based cart
Target: Zustand store (client) + optional API sync

Cart item = { variantId, productName, variantSummary, price, quantity, note, hpp }
Cart features: add, update qty, remove, clear, apply promo, remove promo, item notes
Promo: discount_rate (percentage) with max_discount_price cap
Additional charges: pajak, ongkos_kirim, kemasan (per-order, not per-item)
```

### 2. Midtrans Payment Integration
```
Flow:
1. Client submits checkout → Server Action creates Order + Invoices
2. Server calls Midtrans Snap API to get snap_token
3. Client opens Snap popup with snap_token
4. On success: update order status to 'berhasil'
5. Webhook endpoint (/api/midtrans/callback) for async notification

Env vars: MIDTRANS_SERVER_KEY, MIDTRANS_CLIENT_KEY, MIDTRANS_IS_PRODUCTION
Currently using SANDBOX mode.
```

### 3. Store-Scoped Data
```
Almost ALL queries filter by store_id from session/cookie.
Pattern: const storeId = cookies().get('selected_store')?.value
Every list page, create/update action must scope to storeId.
```

### 4. PDF Generation (Invoices)
```
Invoice print: HTML page for window.print()
Invoice PDF: server-side PDF generation (DomPDF → @react-pdf/renderer)
Content: Store info, customer info, line items, totals, payment info
```

### 5. Image Upload
```
Product images: uploaded to public/storage/products/
Stock batch notas: uploaded to public/storage/notas/
Pattern: formData → API route → save to public/uploads/ or S3
```

---

## FOLDER STRUCTURE

```
handai-pos-next/
├── prisma/
│   ├── schema.prisma
│   └── seed.ts
├── src/
│   ├── app/
│   │   ├── layout.tsx            (Providers: SessionProvider, Toaster)
│   │   ├── page.tsx              (Landing page)
│   │   ├── (auth)/
│   │   │   ├── layout.tsx        (Blank, centered)
│   │   │   ├── login/page.tsx
│   │   │   └── register/page.tsx
│   │   ├── (dashboard)/
│   │   │   ├── layout.tsx        (Sidebar + Navbar + StoreProvider)
│   │   │   ├── manager/
│   │   │   │   ├── layout.tsx
│   │   │   │   ├── page.tsx
│   │   │   │   ├── select-store/page.tsx
│   │   │   │   ├── inventory/
│   │   │   │   │   ├── products/
│   │   │   │   │   │   ├── page.tsx
│   │   │   │   │   │   ├── create/page.tsx
│   │   │   │   │   │   └── [id]/edit/page.tsx
│   │   │   │   │   ├── stock/
│   │   │   │   │   │   ├── page.tsx
│   │   │   │   │   │   ├── create/page.tsx
│   │   │   │   │   │   └── [id]/edit/page.tsx
│   │   │   │   │   ├── stock-batches/
│   │   │   │   │   ├── recipes/
│   │   │   │   ├── operational/
│   │   │   │   │   ├── production/
│   │   │   │   │   ├── rnd/
│   │   │   │   │   ├── orders/
│   │   │   │   │   ├── suppliers/
│   │   │   │   │   ├── waste/
│   │   │   │   │   ├── stock-movements/
│   │   │   │   │   └── stock-opname/
│   │   │   │   ├── finance/
│   │   │   │   │   ├── invoices/
│   │   │   │   │   ├── employees/
│   │   │   │   │   ├── rnd-request/
│   │   │   │   │   ├── rnd-log/
│   │   │   │   │   ├── stock-batches/
│   │   │   │   │   └── accounting/
│   │   │   │   │       ├── page.tsx (dashboard)
│   │   │   │   │       ├── coa/
│   │   │   │   │       ├── journals/
│   │   │   │   │       ├── income/
│   │   │   │   │       ├── balance/
│   │   │   │   │       └── cashflow/
│   │   │   │   └── marketing/
│   │   │   │       ├── customers/
│   │   │   │       └── resellers/
│   │   │   ├── pos/
│   │   │   │   ├── page.tsx      (Product grid + cart)
│   │   │   │   ├── select-store/page.tsx
│   │   │   │   ├── cart/page.tsx
│   │   │   │   ├── history/page.tsx
│   │   │   │   └── invoice/[id]/page.tsx
│   │   │   ├── kasir/
│   │   │   │   ├── page.tsx
│   │   │   │   ├── select-store/page.tsx
│   │   │   │   ├── cart/page.tsx
│   │   │   │   └── invoice/[id]/page.tsx
│   │   │   ├── superadmin/
│   │   │   │   ├── page.tsx
│   │   │   │   ├── accounts/
│   │   │   │   ├── stores/
│   │   │   │   └── simulate/
│   │   │   └── reseller/
│   │   │       └── page.tsx
│   │   ├── order/                (Customer order — NO NextAuth, custom session)
│   │   │   ├── layout.tsx
│   │   │   ├── login/page.tsx
│   │   │   ├── register/page.tsx
│   │   │   ├── select-store/page.tsx
│   │   │   ├── page.tsx          (Browse + cart)
│   │   │   └── checkout/page.tsx
│   │   └── api/
│   │       ├── auth/[...nextauth]/route.ts
│   │       ├── midtrans/callback/route.ts
│   │       ├── upload/route.ts
│   │       └── mobile/           (ALL mobile API endpoints)
│   │           ├── auth/login/route.ts
│   │           ├── auth/register/route.ts
│   │           ├── user/profile/route.ts
│   │           ├── stores/route.ts
│   │           ├── stores/nearby/route.ts
│   │           ├── stores/[id]/route.ts
│   │           ├── stores/[id]/products/route.ts
│   │           ├── products/route.ts
│   │           ├── product-variants/route.ts
│   │           ├── variant-attributes/route.ts
│   │           ├── stocks/route.ts
│   │           ├── stocks/add/route.ts
│   │           ├── stocks/[categoryId]/route.ts
│   │           ├── stock-batches/route.ts
│   │           ├── stock-categories/route.ts
│   │           ├── units/route.ts
│   │           ├── dashboard/
│   │           │   ├── customers/route.ts
│   │           │   ├── finance/route.ts
│   │           │   ├── sales-today/route.ts
│   │           │   ├── count-by-size/route.ts
│   │           │   ├── production-standard/route.ts
│   │           │   ├── today-production/route.ts
│   │           │   └── manager/route.ts
│   │           ├── productions/route.ts
│   │           ├── productions/form/route.ts
│   │           ├── productions/store/route.ts
│   │           ├── productions/filters/route.ts
│   │           ├── checkout/route.ts
│   │           ├── cart/route.ts
│   │           ├── orders/route.ts
│   │           ├── orders/[id]/update-status/route.ts
│   │           └── customers/route.ts
│   ├── components/
│   │   ├── ui/                   (DaisyUI-based)
│   │   │   ├── Button.tsx
│   │   │   ├── Input.tsx
│   │   │   ├── Select.tsx
│   │   │   ├── Modal.tsx
│   │   │   ├── DataTable.tsx     (Sortable, filterable, paginated)
│   │   │   ├── Card.tsx
│   │   │   ├── Badge.tsx
│   │   │   ├── Tabs.tsx
│   │   │   └── ConfirmDialog.tsx
│   │   ├── layout/
│   │   │   ├── Sidebar.tsx       (Collapsible, role-based menu items)
│   │   │   ├── Navbar.tsx        (User info, store name, logout)
│   │   │   ├── StoreSelector.tsx (Dropdown)
│   │   │   └── Breadcrumb.tsx
│   │   ├── charts/
│   │   │   ├── SalesChart.tsx
│   │   │   ├── StockChart.tsx
│   │   │   ├── ProductionChart.tsx
│   │   │   └── FinanceChart.tsx
│   │   ├── forms/
│   │   │   ├── ProductForm.tsx   (With variant combinator)
│   │   │   ├── StockForm.tsx     (With batch creation)
│   │   │   ├── ProductionForm.tsx (BOM auto-fill or manual ingredients)
│   │   │   └── RecipeForm.tsx    (BOM editor)
│   │   ├── cart/
│   │   │   ├── CartPanel.tsx
│   │   │   ├── CartItem.tsx
│   │   │   └── CheckoutSummary.tsx
│   │   └── invoice/
│   │       ├── InvoicePrint.tsx
│   │       └── InvoicePDF.tsx
│   ├── lib/
│   │   ├── prisma.ts             (Singleton client)
│   │   ├── auth.ts               (NextAuth config)
│   │   ├── auth-guard.ts         (Server-side auth helpers)
│   │   ├── midtrans.ts           (Snap API client)
│   │   ├── services/
│   │   │   ├── inventory.ts
│   │   │   └── accounting.ts
│   │   ├── helpers/
│   │   │   ├── conversion.ts     (Unit conversion + cache)
│   │   │   ├── formatter.ts      (Currency/number formatting: rb, jt, M)
│   │   │   └── role.ts           (Role hierarchy check)
│   │   └── validations/          (Zod schemas)
│   │       ├── auth.ts
│   │       ├── product.ts
│   │       ├── stock.ts
│   │       ├── production.ts
│   │       ├── order.ts
│   │       └── employee.ts
│   ├── actions/                   (Server Actions)
│   │   ├── auth.ts
│   │   ├── product.ts
│   │   ├── stock.ts
│   │   ├── production.ts
│   │   ├── order.ts
│   │   ├── rnd.ts
│   │   ├── waste.ts
│   │   ├── supplier.ts
│   │   ├── employee.ts
│   │   ├── customer.ts
│   │   ├── reseller.ts
│   │   └── accounting.ts
│   ├── hooks/
│   │   ├── useCart.ts
│   │   ├── useStore.ts
│   │   └── useDebounce.ts
│   ├── stores/                    (Zustand)
│   │   ├── cart-store.ts
│   │   └── sidebar-store.ts
│   ├── types/
│   │   └── index.ts
│   └── middleware.ts
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

## CODE QUALITY REQUIREMENTS

1. **TypeScript strict mode** — no `any`, all functions typed
2. **Server Components by default** — only add `'use client'` when needed (interactivity)
3. **Server Actions** for all mutations (create, update, delete) — NOT API routes for web
4. **API Routes** only for mobile/external API and webhooks
5. **Zod validation** on both client (form) and server (action) side
6. **Error handling**: try/catch in Server Actions, return `{ success: boolean, error?: string }`
7. **Loading states**: `loading.tsx` files for each route segment
8. **Error boundaries**: `error.tsx` files for graceful error handling
9. **Optimistic updates** where appropriate (cart operations)
10. **Prisma transactions** (`prisma.$transaction`) for all multi-table operations
11. **No `console.log`** in production code — use proper error handling
12. **Consistent naming**: camelCase for variables/functions, PascalCase for components
13. **Import order**: React → Next.js → third-party → local (enforce with ESLint)
14. **Accessibility**: proper ARIA labels, keyboard navigation
15. **Responsive design**: mobile-first, works on all screen sizes
16. **Indonesian language** for all user-facing text (labels, messages, errors)
17. **DaisyUI theme**: use DaisyUI component classes (btn, card, table, modal, etc.)

---

## EXECUTION INSTRUCTIONS

Implement the conversion in this exact order:

### Step 1: Project Setup
- `npx create-next-app@latest` with TypeScript + Tailwind + App Router
- Install ALL dependencies from the package.json spec above
- Setup `prisma/schema.prisma` with ALL 37 models above
- Run `npx prisma migrate dev`
- Create `lib/prisma.ts` singleton
- Create Prisma seed script with sample data

### Step 2: Auth System
- Setup NextAuth v5 with CredentialsProvider
- Create login/register pages
- Implement middleware.ts for role-based route protection
- Implement store selection (cookie-based)
- Test: login → select store → redirect to role-appropriate dashboard

### Step 3: Layout & Shared Components
- Dashboard layout (Sidebar + Navbar)
- Sidebar: role-based menu items
- Store selector in navbar
- DataTable component (reusable for all list pages)
- Form components (Input, Select, etc.)
- Modal, ConfirmDialog, Toast setup

### Step 4-7: Feature Modules (one at a time)
For each module, implement:
1. Server Actions (data fetching + mutations)
2. Zod validation schemas
3. Page components (list, create, edit, detail)
4. Test the full CRUD flow

Order: Superadmin → Inventory → Operational → Finance → POS/Kasir → Customer Order → Reseller → Mobile API

### Step 8: Testing & Polish
- Test all flows end-to-end
- Add loading.tsx and error.tsx
- Responsive testing
- Performance optimization

---

## CRITICAL NOTES

1. **Store scoping is MANDATORY** — every data query must filter by the user's selected store. Missing this = data leak between stores.

2. **Double-entry accounting must ALWAYS balance** — every Journal's total_debit must equal total_credit. Validate in createJournal().

3. **Stock mutations must be atomic** — use Prisma transactions for any operation that modifies stock quantities AND creates movements.

4. **Production flow uses unit conversion** — when BOM says "use 500g flour" but stock is stored in "kg", ConversionHelper must convert correctly.

5. **Cart promo logic**: `discounted = totalItemPrice * (discount_rate/100)`, capped at `max_discount_price`. Gross = totalItemPrice - discounted + pajak + ongkosKirim + kemasan.

6. **HPP (Harga Pokok Penjualan)** calculation during production: `newHpp = ((oldQty * oldHpp) + totalMaterialCost) / (oldQty + newQty)` — weighted average.

7. **Expired stock**: Products have `expired_duration` (days). Production histories are checked: if `production_date + expired_duration < today`, mark as expired. Allow "discard" (reduce quantity) or "ignore" (mark as not stored).

8. **Customer auth is SEPARATE from User auth** — Customers have their own model, own login flow, own session. Don't mix with NextAuth User sessions.

# ========== AKHIR PROMPT ==========
