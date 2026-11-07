# Dokumentasi API — KafWeb (Sistem Pemesanan Kafe)

REST API backend-only untuk pemesanan kafe: seluruh respons berupa JSON
(`application/json`), tidak ada halaman web. Kirim header `Accept: application/json`
pada setiap request agar error dikembalikan sebagai JSON.

- **Base URL**: `http://localhost:8000/api/v1` (sesuaikan host saat deploy)
- **Versi**: `v1` di-prefix pada semua route (`routes/api.php`)
- **Auth**: Sanctum Personal Access Token, dikirim sebagai header `Authorization: Bearer <token>`
- **Waktu**: semua timestamp ISO-8601 (`2026-11-02T15:30:00+00:00`)
- **Uang**: field uang dari database (`price`, `total_price`, `amount`) berupa
  **string 2 desimal** (contoh `"25000.00"`, hasil cast `decimal:2` Laravel);
  field hasil hitung (`line_total`, `change`) berupa angka. Tanpa `Rp`.

---

## 1. Ringkasan Endpoint per Peran

### Pelanggan (publik, tanpa login)

| Method | Endpoint | Fungsi |
|---|---|---|
| GET | `/menus` | Daftar menu yang tersedia |
| GET | `/menus/{id}` | Detail satu menu |
| POST | `/orders` | Checkout / buat pesanan |
| GET | `/orders/{tracking_code}/status` | Lacak status pesanan |

### Staf (otentikasi token)

| Method | Endpoint | Peran | Fungsi |
|---|---|---|---|
| POST | `/auth/login` | — | Login, dapat token |
| GET | `/auth/me` | admin, kasir | Profil yang sedang login |
| POST | `/auth/logout` | admin, kasir | Cabut token saat ini |

### Kasir (login; kasir dan admin boleh)

| Method | Endpoint | Fungsi |
|---|---|---|
| GET | `/kasir/orders` | Daftar pesanan masuk untuk dashboard |
| GET | `/kasir/orders/{id}` | Detail satu pesanan |
| POST | `/kasir/orders` | Buat pesanan walk-in (manual di kasir) |
| PATCH | `/kasir/orders/{id}/status` | Update status pesanan |
| POST | `/kasir/orders/{id}/payment` | Catat pembayaran |
| GET | `/kasir/customers` | Cari/daftar pelanggan |
| POST | `/kasir/customers` | Daftarkan pelanggan (atau ambil yang sudah ada) |

### Admin (login; hanya admin)

| Method | Endpoint | Fungsi |
|---|---|---|
| GET / POST | `/admin/categories` | Lihat / tambah kategori |
| PATCH / DELETE | `/admin/categories/{id}` | Ubah / hapus kategori |
| GET / POST | `/admin/menus` | Lihat / tambah menu |
| GET / PATCH / DELETE | `/admin/menus/{id}` | Detail / ubah / hapus menu |
| GET / POST | `/admin/staff` | Lihat / tambah staf |
| GET / PATCH / DELETE | `/admin/staff/{id}` | Detail / ubah / hapus staf |
| GET / POST | `/admin/customers` | Lihat / tambah pelanggan |
| GET / PATCH / DELETE | `/admin/customers/{id}` | Detail / ubah / hapus pelanggan |
| GET | `/admin/reports/summary` | Ringkasan pendapatan per periode |
| GET | `/admin/reports/daily` | Rekap harian pesanan lunas |
| GET | `/admin/reports/top-items` | Menu terlaris |

Semua endpoint di bawah `/kasir/*` dan `/admin/*` memerlukan header
`Authorization: Bearer <token>`; tanpa token → `401`.

---

## 2. Autentikasi Staf

### `POST /auth/login` — publik (rate limited: throttle `auth`)

```json
{
  "username": "kasir",
  "password": "kasir123"
}
```

**200 OK** (token Sanctum: `plainTextToken`)

```json
{
  "token": "1|Yt4kQ9a8cCdLpX",
  "user": {
    "id": 2,
    "name": "Kasir Pagi",
    "username": "kasir",
    "role": "kasir",
    "role_label": "Kasir",
    "created_at": "2026-11-02T15:30:00+00:00"
  }
}
```

- Username atau password salah → **401** `{"message": "Username atau password salah."}`
- Terlalu banyak percobaan → **429**
- `role` bersifat read-only; klien memakai nilainya untuk menampilkan panel kasir/admin.

### `GET /auth/me` — profil token aktif

```json
{ "data": { "id": 2, "name": "Kasir Pagi", "username": "kasir", "role": "kasir", "role_label": "Kasir", "...": "..." } }
```

### `POST /auth/logout` — cabut token yang dipakai pada request ini

```json
{ "message": "Logout berhasil." }
```

---

## 3. Endpoint Pelanggan (Publik)

### 3.1 `GET /menus` — katalog

Query parameter (opsional):
- `category_id` — filter per kategori
- `search` — pencarian substring pada nama menu

Hanya menu dengan `is_available = true` yang dikembalikan.

**200 OK**

```json
{
  "data": [
    {
      "id": 1,
      "name": "Kopi Susu Gula Aren",
      "description": "Espresso, susu, gula aren",
      "price": "18000.00",
      "stock": 40,
      "image_url": null,
      "is_available": true,
      "category": { "id": 1, "name": "Kopi" },
      "created_at": "2026-11-02T15:30:00+00:00"
    }
  ]
}
```

### 3.2 `GET /menus/{id}` — detail menu

- 404 jika menu tidak ada **atau** sedang tidak tersedia.

### 3.3 `POST /orders` — checkout (tanpa login)

Body:

```json
{
  "phone": "081234567890",
  "name": "Andi",
  "table_number": "A4",
  "items": [
    { "menu_id": 1, "quantity": 2, "notes": "less sugar" },
    { "menu_id": 5, "quantity": 1 }
  ]
}
```

Aturan field:
- `phone` + `name` opsional; jika `phone` diisi dan belum terdaftar, pelanggan
  otomatis dibuat (`firstOrCreate` per nomor telepon).
- `customer_id` opsional (dipakai aplikasi kasir ketika memilih pelanggan terdaftar).
- `table_number` opsional (kosong = takeaway).
- `items` wajib: `menu_id` harus ada di tabel `menus`, `quantity` 1–99.
- `price` **tidak boleh** dikirim klien — server membaca dari tabel `menus`.

**201 Created**

```json
{
  "data": {
    "id": 12,
    "table_number": "A4",
    "tracking_code": "K7PD2XQA",
    "total_price": "43000.00",
    "payment_status": "pending",
    "order_status": "pending",
    "order_status_label": "Menunggu Konfirmasi",
    "paid_at": null,
    "created_at": "2026-11-02T16:05:00+00:00",
    "customer": { "id": 3, "name": "Andi", "phone": "081234567890" },
    "items": [
      { "id": 21, "menu_id": 1, "menu_name": "Kopi Susu Gula Aren", "quantity": 2, "price": "18000.00", "line_total": 36000, "notes": "less sugar" },
      { "id": 22, "menu_id": 5, "menu_name": "Croissant", "quantity": 1, "price": "7000.00", "line_total": 7000, "notes": null }
    ],
    "payments": []
  }
}
```

Error:
- **422** — validasi gagal, stok kurang, menu tidak tersedia/tidak ada
  (`errors.items = ["Stok menu Croissant tidak cukup (sisa 0)."]`)

Simpan `tracking_code` untuk polling status (lihat 3.4). ID order bertambah
sekuensial sehingga **sengaja tidak dikembalikan ke pelanggan**.

### 3.4 `GET /orders/{tracking_code}/status` — lacak pesanan

**200 OK**

```json
{
  "data": {
    "order_status": "ready",
    "order_status_label": "Siap Diambil",
    "payment_status": "paid",
    "table_number": "A4",
    "total_price": "43000.00",
    "created_at": "2026-11-02T16:05:00+00:00"
  }
}
```

- 404 jika kode salah.

---

## 4. Endpoint Kasir

> Header wajib: `Authorization: Bearer <token>` dari login kasir/admin.

### 4.1 `GET /kasir/orders` — antrean pesanan

Query parameter (opsional):

| Param | Nilai | Default perilaku |
|---|---|---|
| `order_status` | `pending\|processing\|ready\|completed\|cancelled` | tanpa filter → **pesanan aktif saja** (bukan completed/cancelled) |
| `payment_status` | `pending\|paid` | — |
| `date` | `YYYY-MM-DD` | — |

Diurutkan terbaru dulu. Setiap item memuat relasi `items.menu`, `customer`, `payments`.

**200 OK** — bentuk sama dengan respons checkout (3.3) dalam `data` array.

### 4.2 `GET /kasir/orders/{id}` — detail satu pesanan

`payments` memuat nama kasir yang menerima uang (`cashier_name`).

### 4.3 `POST /kasir/orders` — pesanan walk-in

Body persis seperti `POST /orders` (3.3); logika bisnis identik
(`OrderService::create`). Dipakai kasir untuk pesanan langsung di kasir tanpa aplikasi pelanggan.

### 4.4 `PATCH /kasir/orders/{id}/status` — ubah status pesanan ⭐

Body:

```json
{ "order_status": "processing" }
```

Nilai yang diterima: `pending`, `processing`, `ready`, `completed`, `cancelled`.

**200 OK** — resource pesanan setelah perubahan (model `data` sama dengan 3.3).

```json
{
  "data": {
    "id": 12,
    "order_status": "processing",
    "order_status_label": "Sedang Diproses",
    "payment_status": "pending",
    "...": "..."
  }
}
```

Error:
- **409** transisi terlarang (workflow di section 8) atau aturan khusus:
  - pagar lunas (`paid`) tidak boleh dibatalkan lewat aplikasi,
  - `completed` menuntut status pembayaran `paid`,
  - status `completed` / `cancelled` adalah terminal (tidak bisa diubah lagi).

### 4.5 `POST /kasir/orders/{id}/payment` — catat pembayaran

Body:

```json
{ "method": "cash", "amount": 50000 }
```

- `method`: `cash` | `qris` | `debit_card` | `e_wallet`
- `amount`: `>= total tagihan`, max `99999999`
- Pembayaran dibuat **atomik**: `payments` dibuat, `payment_status` menjadi
  `paid`, dan `paid_at` terisi dalam satu transaksi (`lockForUpdate`).
- Uang kembalian dikembalikan pada `change`.

**200 OK**

```json
{
  "data": {
    "id": 7,
    "amount": "50000.00",
    "method": "cash",
    "method_label": "Tunai",
    "cashier_name": "Kasir Pagi",
    "paid_at": "2026-11-02T16:10:00+00:00"
  },
  "change": 7000,
  "message": "Pembayaran tercatat."
}
```

Error:
- **422** — `amount < total`: `errors.amount = ["Nominal bayar (Rp43,000.00) kurang dari total tagihan (Rp43,000.00)."]`
- **409** — pesanan sudah dibayar sebelumnya (double-pay terproteksi), atau pesanan yang sudah dibatalkan dicoba dibayar.

### 4.6 `GET /kasir/customers` — cari pelanggan (paginate)

Query: `search` (nama/telepon), `per_page` (default 10, max 50).

**200 OK** — Resource Collections dengan pagination Laravel (section 6).

```json
{
  "data": [
    { "id": 3, "name": "Andi", "phone": "081234567890", "created_at": "..." }
  ],
  "links": { "first": "...", "last": "...", "prev": null, "next": "..." },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 1,
    "path": "http://localhost:8000/api/v1/kasir/customers",
    "per_page": 10,
    "to": 1,
    "total": 1
  }
}
```

### 4.7 `POST /kasir/customers` — daftar / pilih pelanggan

Body: `{ "name": "Andi", "phone": "081234567890" }`

- Telepon duplikat → **200** dengan data pelanggan lama + pesan
  `"Nomor telepon sudah terdaftar, menggunakan data pelanggan yang ada."`
- Telepon baru → **201** + `{ "data": {...}, "message": "Pelanggan baru tersimpan." }`

---

## 5. Endpoint Admin

> Header wajib: `Authorization: Bearer <token>` role `admin`.

### 5.1 Kategori — `/admin/categories`

- `GET` — semua kategori + `menus_count`; urut nama.
- `POST` — `{ "name": "Kopi" }` → **201**.
- `PATCH /admin/categories/{id}` — `{ "name": "Minuman" }` → **200**.
- `DELETE /admin/categories/{id}` — **409** jika masih ada menu di dalamnya.

### 5.2 Menu — `/admin/menus`

- `GET` — semua menu (termasuk yang `is_available=false`), filter
  `category_id` + `search`.
- `POST` body:

```json
{
  "category_id": 1,
  "name": "Americano",
  "description": "Double shot espresso, tanpa susu",
  "price": 15000,
  "stock": 50,
  "image_url": null,
  "is_available": true
}
```

- `PATCH /admin/menus/{id}` — field dikirim parsial; cukup yang ingin diubah
  (contoh restock: `{ "stock": 100 }`).
- `DELETE /admin/menus/{id}` — **409** jika menu sudah pernah dipesan (ada
  baris di `order_items`); gunakan `{"is_available": false}` untuk menyembunyikannya.

### 5.3 Staf — `/admin/staff`

- `GET` — filter `role=admin|kasir`; paginate (`per_page` default 15, max 50).
- `POST` — `{ "name": "Kasir Malam", "username": "kasir3", "password": "rahasia123", "role": "kasir" }` → **201**
  (password otomatis di-hash via model cast).
- `PATCH /admin/staff/{id}` — update parsial; `password` hanya berubah jika dikirim.
- `DELETE /admin/staff/{id}` — guardrail: admin tidak boleh menghapus akun
  dirinya sendiri, dan admin terakhir tidak boleh dihapus → **409**.

### 5.4 Pelanggan — `/admin/customers`

- `GET` — paginate, filter `search` (nama/telepon), menyertakan `orders_count`.
- `POST` — `{ "name": "Budi", "phone": "085678901234" }` → **201**.
- `PATCH /admin/customers/{id}` — ubah nama/telepon.
- `DELETE /admin/customers/{id}` — **409** jika masih punya riwayat pesanan.

### 5.5 Laporan keuangan — `/admin/reports/*`

Query periode (opsional, bisa salah satu atau keduanya): `from=YYYY-MM-DD`, `to=YYYY-MM-DD`;
default = awal bulan s.d. hari ini.

#### `GET /admin/reports/summary`

```json
{
  "data": {
    "period": { "from": "2026-11-01", "to": "2026-11-02" },
    "revenue": 128000,
    "paid_orders": 4,
    "average_ticket": 32000,
    "cancelled_orders": 0,
    "active_orders": 1,
    "unpaid_orders": 1
  }
}
```

#### `GET /admin/reports/daily` — rekap harian (pesanan lunas)

```json
{
  "data": [
    { "day": "2026-11-02", "orders_count": 3, "revenue": "96000", "average_ticket": "32000" }
  ]
}
```

#### `GET /admin/reports/top-items?limit=10` — menu terlaris (max 50)

```json
{
  "data": [
    {
      "menu_id": 1,
      "menu_name": "Kopi Susu Gula Aren",
      "sold_quantity": 4,
      "revenue": "72000"
    }
  ]
}
```

---

## 6. Bentuk Response

**Tanpa pagination** (menu, kasir orders):

```json
{ "data": [ ... ] }
```

**Dengan pagination** (kasir/admin customers, admin staff):
Laravel paginator default → `data` + `links` + `meta` (contoh lihat 4.6).

**Single resource**: `{ "data": {...} }` (dan kadang field tambahan `message`/`change`).

## 7. Kode Status HTTP & Format Error

| HTTP | Situasi | Contoh body |
|---|---|---|
| 400 | request JSON malformed / bukan JSON | `{"message": "..."}` |
| 401 | tanpa/ salah token (route terlindungi) | `{"message": "Unauthenticated."}` |
| 403 | token ada tetapi role tak sesuai grup route (kasir mencoba endpoint `/admin/*`) | `{"message": "Akses ditolak: peran Anda tidak berhak mengakses endpoint ini."}` |
| 404 | model / kode tracking / route tidak ada | `{"message": "Kode pelacakan pesanan tidak ditemukan."}` |
| 409 | konflik alur bisnis (transisi status terlarang, double-pay, atau menghapus entitas yang masih dipakai data lain) | `{"message": "Pesanan harus dibayar terlebih dahulu sebelum ditandai selesai."}` |
| 422 | validasi gagal (FormRequest) | `{"message": "...", "errors": {"amount": ["Nominal bayar (Rp5,000.00) kurang dari total tagihan (Rp43,000.00)."]}}` |
| 429 | rate limit (login `throttle:auth`) | `{"message": "Too Many Attempts."}` |
| 500 | tak terduga | — |

Catatan 403: middleware custom `EnsureRole` (`role:kasir,admin`) menyaring peran
**sebelum** masuk ke controller, sehingga seorang kasir tidak punya kemungkinan
menyentuh endpoint `/admin/*` sekalipun mengetahui URL-nya.

> Catatan `APP_DEBUG`: selama `APP_DEBUG=true` (bawaan di lokal), respons bermasalah
> menyertakan detail `exception`/`trace`. Saat go-live, set `APP_DEBUG=false` di
> `.env` agar error dikembalikan hanya sebagai `{"message": "Server Error"}`.

## 8. Aturan Bisnis Utama

1. **Pipeline status pesanan** (aturan terpusat di `App\Enums\OrderStatus`):

   ```
   pending ──> processing ──> ready ──> completed
      │             │           │
      └─────────────┴───────────┴──> cancelled
   ```

   - `pending → processing | ready | cancelled`
   - `processing → ready | cancelled`
   - `ready → completed | cancelled`
   - `completed` / `cancelled` = **terminal**, tidak bisa diubah lagi.
   - `cancelled` hanya diperbolehkan bila `payment_status = pending`. Jika sudah
     `paid`, endpoint menolak (**409**) — refund ditangani administratif manual.
   - `completed` hanya bila `payment_status = paid` (bayar dulu, baru selesai).

2. **Pembayaran tercatat atomik**: `POST /payment` mengunci baris order
   (`lockForUpdate`) untuk mencegah dua transaksi pembayaran terjadi bersamaan.
   `payments` dibuat, `payment_status` menjadi `paid`, dan `paid_at` terisi
   dalam satu transaksi. `change` dikembalikan untuk kasir menghitung
   kembalian tunai.

3. **Stok dikurangi di server, harga tetap server-side**: item dengan `menu_id` sama
   digabung dulu kuantitasnya, lalu stok dicek dan baris menu dikunci dalam satu
   transaksi dengan `SELECT ... FOR UPDATE`, sehingga dua kasir yang checkout
   bersamaan tidak bisa menjual melebihi stok. Harga `order_items.price` dibaca
   dari tabel `menus`, bukan dari body request.

4. **Tracking code** (8 karakter acak, e.g. `K7PD2XQA`) adalah satu-satunya
   identifier yang diberi ke pelanggan untuk polling status; ID order sekuensial
   tidak dikirim ke pelanggan.

5. **Hapus data historis terproteksi**: kategori yang berisi menu, menu yang pernah
   dipesan, dan pelanggan yang punya riwayat pesanan menolak dihapus (409) agar
   laporan keuangan tidak kehilangan data.

## 9. Nilai Enum

| Kumpulan | Anggota | Label Indonesia |
|---|---|---|
| `order_status` | `pending` `processing` `ready` `completed` `cancelled` | Menunggu Konfirmasi, Sedang Diproses, Siap Diambil, Selesai, Dibatalkan |
| `payment_status` | `pending` `paid` | — |
| `payment_method` | `cash` `qris` `debit_card` `e_wallet` | Tunai, QRIS, Kartu Debit, E-Wallet |
| `role` | `admin` `kasir` | Admin, Kasir |

---

*Referensi implementasi: `routes/api.php`, `app/Http/Controllers/Api/V1/*`,
`app/Http/Requests/*`, `app/Http/Resources/*`, `app/Services/OrderService.php`,
`app/Services/PaymentService.php`, `app/Enums/*`. Skema tabel: `database/schema.sql`.*

*Terakhir diperbarui: 2026-11-02*
