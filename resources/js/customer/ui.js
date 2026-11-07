/**
 * Helper DOM tanpa dependensi: membangun pohon elemen secara imperatif
 * (bukan innerHTML) agar tidak ada celah injeksi markup.
 *
 * @param { keyof HTMLElementTagNameMap } tag
 * @param { Record<string, unknown> | null } props
 * @param { Array<Node | string | null | undefined | Array<Node | string | null | undefined>> } children
 * @returns { HTMLElement | HTMLInputElement | HTMLButtonElement }
 */
export function h(tag, props = null, ...children) {
    const el = document.createElement(tag);

    if (props) {
        for (const [key, value] of Object.entries(props)) {
            if (value === null || value === undefined || value === false) {
                continue;
            }

            if (key.startsWith('on') && typeof value === 'function') {
                el.addEventListener(key.slice(2).toLowerCase(), value);
            } else if (key === 'class') {
                el.className = value;
            } else if (value === true) {
                el.setAttribute(key, '');
            } else if (typeof value === 'object') {
                el.setAttribute(key, JSON.stringify(value));
            } else {
                el.setAttribute(key, String(value));
            }
        }
    }

    for (const child of children.flat()) {
        if (child === null || child === undefined || child === false) {
            continue;
        }
        el.append(child instanceof Node ? child : document.createTextNode(String(child)));
    }

    return el;
}

/**
 * Format angka uang string basis data ("18000.00") menjadi rupiah.
 *
 * @param { string | number } value
 * @returns { string } contoh "Rp 18.000"
 */
export function fmtIDR(value) {
    const amount = Math.round(Number.parseFloat(value) || 0);
    return 'Rp ' + amount.toLocaleString('id-ID');
}

/**
 * Waktu jam-menit lokal untuk stempel "terakhir diperbarui".
 *
 * @param { Date | string } date
 */
export function timeHM(date) {
    return new Date(date).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
}

/**
 * Elemen foto menu dengan fallback hangat ketika sumber gagal dimuat,
 * supaya kartu tetap rapi saat pelanggan offline atau URL mati.
 *
 * @param { string | null } src
 * @param { string } alt
 * @param { string } className kelas tambahan untuk elemen img
 * @returns { HTMLElement } img atau div pengganti
 */
export function foodPhoto(src, alt, className) {
    if (!src) {
        return photoFallback(alt);
    }

    const img = h('img', {
        src,
        alt,
        class: className,
        loading: 'lazy',
        decoding: 'async',
    });
    img.addEventListener('error', () => img.replaceWith(photoFallback(alt)));

    return img;
}

function photoFallback(alt) {
    return h(
        'div',
        { class: 'grid h-full w-full place-items-center bg-cream-200/60 text-clay-500', role: 'img', 'aria-label': alt },
        h('i', { class: 'ph ph-coffee text-3xl', 'aria-hidden': 'true' }),
    );
}

/**
 * Toast ringan berada di atas bar navigasi bawah.
 *
 * @param { string } message
 * @param { 'ok' | 'warn' } [tone]
 */
export function toast(message, tone = 'ok') {
    const container = document.getElementById('kafweb-toasts') ?? createToastHost();
    const colors = tone === 'ok'
        ? 'bg-ink-950 text-paper'
        : 'bg-berry-600 text-white';
    const node = h('div', {
        class: `pointer-events-none fixed inset-x-6 bottom-24 z-50 mx-auto flex max-w-sm justify-center sm:bottom-20 ${colors} rounded-2xl px-4 py-3 text-center text-sm font-semibold shadow-raise`,
        role: 'status',
        'aria-live': 'polite',
        style: { animation: 'fade-up .3s var(--ease-warm) both' },
    }, message);

    container.append(node);
    window.setTimeout(() => node.remove(), 2800);
}

function createToastHost() {
    const host = h('div', { id: 'kafweb-toasts', class: 'contents' });
    document.body.append(host);
    return host;
}
