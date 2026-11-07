-- ============================================================================
-- KafWeb — Café Ordering System (backend-only)
-- Skema MySQL 8.x untuk 3 peran: Pelanggan, Kasir, Admin
--
-- Fungsi file :
--   1) Blueprint database untuk review DBA / provisioning manual.
--   2) Pembanding dengan Laravel Migrations (sumber kebenaran aplikasi).
-- Semua query aplikasi menggunakan prepared statements; tidak ada string
-- SQL yang digabung langsung dari input pengguna.
-- ============================================================================

-- ----------------------------------------------------------------------------
-- users — akun staf (admin & kasir). Pelanggan TIDAK login via tabel ini;
--         mereka dikenali via tabel customers melalui nomor telepon.
-- ----------------------------------------------------------------------------
CREATE TABLE users (
    id          BIGINT UNSIGNED    NOT NULL AUTO_INCREMENT,
    name        VARCHAR(255)       NOT NULL,
    username    VARCHAR(50)        NOT NULL,
    password    VARCHAR(255)       NOT NULL COMMENT 'Bcrypt hash, tidak pernah menyimpan plaintext',
    role        ENUM('admin','kasir') NOT NULL DEFAULT 'kasir',
    created_at  TIMESTAMP          NULL DEFAULT NULL,
    updated_at  TIMESTAMP          NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY users_username_unique (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- customers — identita pesanan pelanggan (identifikasi hanya dengan nomor HP).
-- ----------------------------------------------------------------------------
CREATE TABLE customers (
    id          BIGINT UNSIGNED    NOT NULL AUTO_INCREMENT,
    name        VARCHAR(255)       NOT NULL,
    phone       VARCHAR(20)        NOT NULL COMMENT 'unci identifikasi pelanggan',
    created_at  TIMESTAMP          NULL DEFAULT NULL,
    updated_at  TIMESTAMP          NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY customers_phone_unique (phone)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- categories — pengelompokan menu (favorit: kopi, non-kopi, makanan, …)
-- ----------------------------------------------------------------------------
CREATE TABLE categories (
    id          BIGINT UNSIGNED    NOT NULL AUTO_INCREMENT,
    name        VARCHAR(255)       NOT NULL,
    created_at  TIMESTAMP          NULL DEFAULT NULL,
    updated_at  TIMESTAMP          NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY categories_name_unique (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- menus — katalog menu yang dijual.
--   price        : DECIMAL(10,2) — uang bukan float
--   stock        : dikurangi atomik ketika pesanan dilakukan (FOR UPDATE)
--   is_available : switch persistensi oleh admin; hide tanpa hapus riwayat
-- ----------------------------------------------------------------------------
CREATE TABLE menus (
    id            BIGINT UNSIGNED    NOT NULL AUTO_INCREMENT,
    category_id   BIGINT UNSIGNED    NOT NULL,
    name          VARCHAR(255)       NOT NULL,
    description   TEXT               NULL,
    price         DECIMAL(10,2)      NOT NULL,
    stock         INT UNSIGNED       NOT NULL DEFAULT 0,
    image_url     VARCHAR(255)       NULL,
    is_available  TINYINT(1)         NOT NULL DEFAULT 1,
    created_at    TIMESTAMP          NULL DEFAULT NULL,
    updated_at    TIMESTAMP          NULL DEFAULT NULL,
    PRIMARY KEY (id),
    KEY menus_is_available_index (is_available),
    CONSTRAINT menus_category_id_foreign
        FOREIGN KEY (category_id) REFERENCES categories (id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- orders — header ringkas transaksi.
--   customer_id NULLABLE : memungkinkan walk-in (tanpa data pelanggan)
--   tracking_code         : random 8 karakter — pelanggan melacak status
--                           tanpa harus tahu ID pesanan yang berurutan
--   payment_status        : 'pending' | 'paid'  (kasir menerima pembayaran)
--   order_status          : 'pending' -> 'processing' -> 'ready' -> 'completed'
--                           (atau 'cancelled' saat belum dibayar)
--   paid_at               : waktu pembayaran dilakukan (untuk laporan)
-- ----------------------------------------------------------------------------
CREATE TABLE orders (
    id              BIGINT UNSIGNED        NOT NULL AUTO_INCREMENT,
    customer_id     BIGINT UNSIGNED        NULL,
    table_number    VARCHAR(10)            NULL,
    total_price     DECIMAL(12,2)          NOT NULL DEFAULT 0 COMMENT 'dihitung server; harga input tidak dipercaya',
    tracking_code   VARCHAR(12)            NULL,
    payment_status  ENUM('pending','paid') NOT NULL DEFAULT 'pending',
    order_status    ENUM('pending','processing','ready','completed','cancelled') NOT NULL DEFAULT 'pending',
    paid_at         TIMESTAMP              NULL DEFAULT NULL,
    created_at      TIMESTAMP              NULL DEFAULT NULL,
    updated_at      TIMESTAMP              NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY orders_tracking_code_unique (tracking_code),
    KEY orders_payment_status_index (payment_status),
    KEY orders_order_status_index (order_status),
    KEY orders_created_at_index (created_at),
    CONSTRAINT orders_customer_id_foreign
        FOREIGN KEY (customer_id) REFERENCES customers (id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- order_items — detail item per pesanan.
--   price : SNAPSHOT harga menu saat pesanan dibuat (menu boleh menaikkan harga
--           kemudian; riwayat penjualan tidak boleh berubah).
-- ----------------------------------------------------------------------------
CREATE TABLE order_items (
    id         BIGINT UNSIGNED    NOT NULL AUTO_INCREMENT,
    order_id   BIGINT UNSIGNED    NOT NULL,
    menu_id    BIGINT UNSIGNED    NOT NULL,
    quantity   INT UNSIGNED       NOT NULL,
    price      DECIMAL(10,2)      NOT NULL COMMENT 'snapshot harga menu (per item)',
    notes      VARCHAR(255)       NULL COMMENT 'contoh; less sugar, no ice',
    created_at TIMESTAMP          NULL DEFAULT NULL,
    updated_at TIMESTAMP          NULL DEFAULT NULL,
    PRIMARY KEY (id),
    KEY order_items_order_id_index (order_id),
    KEY order_items_menu_id_index (menu_id),
    CONSTRAINT order_items_order_id_foreign
        FOREIGN KEY (order_id) REFERENCES orders (id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT order_items_menu_id_foreign
        FOREIGN KEY (menu_id) REFERENCES menus (id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- payments — catatan pembayaran oleh kasir.
--   amount >= orders.total_price; selisihnya = kembalian untuk kas.
--   cashier_id → users: jejak staf pendana yang menerima dana.
-- ----------------------------------------------------------------------------
CREATE TABLE payments (
    id         BIGINT UNSIGNED    NOT NULL AUTO_INCREMENT,
    order_id   BIGINT UNSIGNED    NOT NULL,
    cashier_id BIGINT UNSIGNED    NULL,
    amount     DECIMAL(12,2)      NOT NULL COMMENT 'uang yang diterima kasir',
    method     ENUM('cash','qris','debit_card','e_wallet') NOT NULL DEFAULT 'cash',
    paid_at    TIMESTAMP          NULL DEFAULT NULL,
    created_at TIMESTAMP          NULL DEFAULT NULL,
    updated_at TIMESTAMP          NULL DEFAULT NULL,
    PRIMARY KEY (id),
    KEY payments_order_id_index (order_id),
    CONSTRAINT payments_order_id_foreign
        FOREIGN KEY (order_id) REFERENCES orders (id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT payments_cashier_id_foreign
        FOREIGN KEY (cashier_id) REFERENCES users (id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Bonus: melihat penggunaan FK saat instalasi:
--   SELECT TABLE_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME
--   FROM information_schema.REFERENTIAL_CONSTRAINTS
--   WHERE CONSTRAINT_SCHEMA = 'kafweb';
-- ---------------------------------------------------------------------------
