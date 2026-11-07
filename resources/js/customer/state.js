/**
 * State keranjang + identitas tamu + kode lacak terakhir.
 * Disimpan di localStorage agar tak hilang saat pelanggan pindah halaman.
 */

const CART_KEY = 'kafweb.cart.v1';
const GUEST_KEY = 'kafweb.guest.v1';
const RECENT_KEY = 'kafweb.recent.v1';

/** @typedef { {
 *  menu_id: number,
 *  name: string,
 *  price: number,
 *  image: string | null,
 *  stock: number,
 *  qty: number,
 *  notes: string,
 * } } CartItem
 */

/** @type { Set<() => void> } */
const listeners = new Set();

/**
 * @template T
 * @param { Storage } storage
 * @param { string } key
 * @returns { T | null }
 */
function read(storage, key) {
    try {
        const raw = storage.getItem(key);
        return raw ? JSON.parse(raw) : null;
    } catch {
        return null;
    }
}

/**
 * @param { Storage } storage
 * @param { string } key
 * @param { unknown } value
 */
function write(storage, key, value) {
    try {
        storage.setItem(key, JSON.stringify(value));
    } catch {
        // localStorage penuh/diblokir: abaikan, sesi ini tetap ingat di memori.
    }
}

/** @type { CartItem[] } */
let items = read(localStorage, CART_KEY) ?? [];

/** Nomor meja dari QR (?meja=A4) atau kode pelacakan punya pelanggan. */
export let tableNumber = new URLSearchParams(window.location.search).get('meja') ?? '';

/**
 * Info tamu (nama, no HP) supaya checkout berikutnya tinggal pakai.
 * @type { { name: string, phone: string } }
 */
let guest = read(localStorage, GUEST_KEY) ?? { name: '', phone: '' };

/** @type { Array<{ code: string, at: string }> } */
let recent = read(localStorage, RECENT_KEY) ?? [];

function persist() {
    write(localStorage, CART_KEY, items);
}

function emit() {
    listeners.forEach((fn) => fn());
}

/**
 * Daftarkan callback; balikan harus dipanggil lewat destroy() untuk
 * melepas listener agar tidak bocor antar-pergantian halaman.
 *
 * @param { () => void } fn callback dipanggil tiap perubahan keranjang
 * @returns { () => void }
 */
export function onCartChange(fn) {
    listeners.add(fn);
    return () => listeners.delete(fn);
}

export function cartItems() {
    return items;
}

export function cartCount() {
    return items.reduce((sum, item) => sum + item.qty, 0);
}

export function cartTotal() {
    return items.reduce((sum, item) => sum + item.qty * item.price, 0);
}

/**
 * Tambah menu ke keranjang, dibatasi stok.
 *
 * @param { import('./api.js').KafWebMenu } menu
 * @param { number } [delta=1]
 */
export function addToCart(menu, delta = 1) {
    const existing = items.find((item) => item.menu_id === menu.id);
    if (existing) {
        setQty(menu.id, existing.qty + delta, menu.stock);
        return;
    }
    if (menu.stock <= 0 || delta <= 0) {
        return;
    }
    items = [
        ...items,
        {
            menu_id: menu.id,
            name: menu.name,
            price: Math.round(Number.parseFloat(menu.price)),
            image: menu.image_url,
            stock: menu.stock,
            qty: Math.min(delta, menu.stock),
            notes: '',
        },
    ];
    persist();
    emit();
}

/**
 * Ubah jumlah item (0 berupa hapus).
 *
 * @param { number } menuId
 * @param { number } qty
 * @param { number } [stock]
 */
export function setQty(menuId, qty, stock) {
    const item = items.find((entry) => entry.menu_id === menuId);
    if (!item) {
        return;
    }
    const cap = stock ?? item.stock;
    const next = Math.min(Math.max(qty, 0), cap);
    items = next === 0 ? items.filter((entry) => entry !== item) : items.map((entry) => (entry === item ? { ...item, qty: next, stock: cap } : entry));
    persist();
    emit();
}

/**
 * Simpan catatan khusus satu item. Sengaja tidak memicu emit supaya
 * input catatan yang sedang diketik tidak ke-rebuild saat fokus.
 *
 * @param { number } menuId
 * @param { string } notes
 */
export function setNotes(menuId, notes) {
    items = items.map((item) => (item.menu_id === menuId ? { ...item, notes } : item));
    persist();
}

export function clearCart() {
    items = [];
    persist();
    emit();
}

/**
 * @param { string } value nomor meja baru ("" berarti takeaway)
 */
export function setTableNumber(value) {
    tableNumber = value;
}

export function guestProfile() {
    return guest;
}

/**
 * @param { { name?: string | null, phone?: string | null } } profile
 */
export function saveGuest(profile) {
    guest = {
        name: profile.name ?? guest.name,
        phone: profile.phone ?? guest.phone,
    };
    write(localStorage, GUEST_KEY, guest);
}

export function recentOrders() {
    return recent;
}

export function rememberOrder(code) {
    recent = [
        { code, at: new Date().toISOString().slice(0, 10) },
        ...recent.filter((entry) => entry.code !== code),
    ].slice(0, 4);
    write(localStorage, RECENT_KEY, recent);
}
