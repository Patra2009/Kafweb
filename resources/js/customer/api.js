/**
 * Lapisan akses API KafWeb (REST /api/v1, JSON). Dokumentasi lengkap:
 * docs/API_ENDPOINTS.md
 */

const BASE = '/api/v1';

export class ApiError extends Error {
    /**
     * @param { number } status
     * @param { { message?: string, errors?: Record<string, string | string[]> } | null } payload
     */
    constructor(status, payload) {
        super(payload?.message ?? 'Terjadi kesalahan, coba lagi nanti.');
        this.name = 'ApiError';
        this.status = status;
        this.errors = payload?.errors ?? null;
    }
}

/**
 * @template T
 * @param { string } path
 * @param { { method?: string, body?: unknown } } [options]
 * @returns { Promise<T> }
 */
async function request(path, { method = 'GET', body } = {}) {
    /** @type { Response } */
    let response;
    try {
        response = await fetch(BASE + path, {
            method,
            headers: {
                Accept: 'application/json',
                ...(body !== undefined ? { 'Content-Type': 'application/json' } : {}),
            },
            body: body !== undefined ? JSON.stringify(body) : undefined,
        });
    } catch {
        throw new ApiError(0, { message: 'Tidak bisa menghubungi server. Periksa jaringan.' });
    }

    if (!response.ok) {
        let payload = null;
        try {
            payload = await response.json();
        } catch {
            // respons bukan JSON (misal HTML error), biarkan payload null.
        }
        throw new ApiError(response.status, payload);
    }

    if (response.status === 204) {
        return /** @type { T } */ (null);
    }

    return await response.json();
}

/**
 * Katalog menu publik. `data` berisi menu tersedia (is_available = true).
 *
 * @param { { category_id?: number, search?: string } } [params]
 * @returns { Promise<{ data: KafWebMenu[] }> }
 */
export function fetchMenus(params) {
    const query = new URLSearchParams();
    if (params?.category_id) {
        query.set('category_id', String(params.category_id));
    }
    if (params?.search) {
        query.set('search', params.search);
    }
    const qs = query.toString();
    return request(`/menus${qs ? `?${qs}` : ''}`);
}

/**
 * Checkout tanpa login.
 *
 * @param { {
 *   name?: string,
 *   phone?: string,
 *   table_number?: string,
 *   items: Array<{ menu_id: number, quantity: number, notes?: string | null }>,
 * } } payload
 * @returns { Promise<{ data: KafWebOrder }> }
 */
export function postOrder(payload) {
    return request('/orders', { method: 'POST', body: payload });
}

/**
 * Lacak status pesanan via kode pelacakan.
 *
 * @param { string } trackingCode
 * @returns { Promise<{ data: {
 *   order_status: string, order_status_label: string, payment_status: string,
 *   table_number: string | null, total_price: string, created_at: string,
 * }> }>
 */
export function fetchTrack(trackingCode) {
    return request(`/orders/${encodeURIComponent(trackingCode)}/status`);
}

/** @typedef { {
 *  id: number,
 *  name: string,
 *  description: string | null,
 *  price: string,
 *  stock: number,
 *  image_url: string | null,
 *  is_available: boolean,
 *  category: { id: number, name: string } | null,
 * } } KafWebMenu
 */

/** @typedef { {
 *  id: number,
 *  tracking_code: string,
 *  table_number: string | null,
 *  total_price: string,
 *  [key: string]: unknown,
 * } } KafWebOrder
 */
