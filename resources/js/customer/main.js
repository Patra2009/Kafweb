import { cartCount, onCartChange } from './state.js';
import { h } from './ui.js';
import { renderCatalog } from './views/catalog.js';
import { renderCheckout } from './views/checkout.js';
import { renderTrack } from './views/track.js';

const app = document.getElementById('app') ?? document.body;

/**
 * Router hash sederhana:
 *   #/            katalog
 *   #/checkout    keranjang + form
 *   #/lacak       pelacakan status (dengan query kode=...&baru=1)
 *
 * Query `?meja=A4` pada URL fisik (bukan hash) dipakai dari QR di meja.
 */

/** @type { (() => void) | null } */
let cleanup = null;

function parseHash() {
    const raw = (window.location.hash || '#/').replace(/^#/, '');
    const [path, query] = raw.split('?');
    return { path: path || '/', params: new URLSearchParams(query ?? '') };
}

function resolve(path) {
    switch (path) {
        case '/checkout':
            return { render: renderCheckout, nav: 'menu', title: 'Keranjang - KafWeb' };
        case '/lacak':
            return { render: renderTrack, nav: 'lacak', title: 'Lacak Pesanan - KafWeb' };
        default:
            return { render: renderCatalog, nav: 'menu', title: 'KafWeb - Pesan dari Meja' };
    }
}

/**
 * Bingkai halaman: header sticky (brand + tombol keranjang), konten, dan
 * bottom-nav mobile.
 *
 * @param { HTMLElement } content
 * @param { 'menu' | 'lacak' } navKey
 */
function shell(content, navKey) {
    const badge = h('span', {
        class: 'absolute -top-1.5 right-1 hidden min-w-5 rounded-full bg-clay-600 px-1.5 text-center text-[11px] font-extrabold leading-5 text-white',
        'data-cart-badge': '',
    });

    const syncBadge = () => {
        const count = cartCount();
        badge.replaceChildren(String(count));
        badge.classList.toggle('hidden', count === 0);
    };

    const header = h(
        'header',
        { class: 'sticky top-0 z-40 border-b border-cream-200/70 bg-cream-50/90 backdrop-blur' },
        h(
            'div',
            { class: 'mx-auto flex h-16 max-w-3xl items-center justify-between px-4' },
            h('a', { href: '#/', 'aria-label': 'Kembali ke menu', class: 'flex items-center gap-2' },
                h('i', { class: 'ph ph-coffee text-2xl text-clay-600', 'aria-hidden': 'true' }),
                h('span', { class: 'font-display text-lg font-bold tracking-tight text-ink-950' }, 'KafWeb'),
            ),
            h(
                'div',
                { class: 'hidden items-center gap-6 sm:flex', 'aria-label': 'Navigasi utama' },
                h('a', { href: '#/', class: `text-sm font-semibold transition-colors ${navKey === 'menu' ? 'text-clay-700' : 'text-bark-600 hover:text-bark-800'}` }, 'Menu'),
                h('a', { href: '#/lacak', class: `text-sm font-semibold transition-colors ${navKey === 'lacak' ? 'text-clay-700' : 'text-bark-600 hover:text-bark-800'}` }, 'Lacak'),
            ),
            h('a', {
                href: '#/checkout',
                class: 'relative inline-grid h-11 w-11 place-items-center rounded-2xl border border-cream-200 bg-paper text-bark-800 transition-colors hover:bg-cream-100',
                'aria-label': 'Buka keranjang',
            },
                h('i', { class: 'ph ph-tote text-xl', 'aria-hidden': 'true' }),
                badge,
            ),
        ),
    );

    syncBadge();
    onCartChange(syncBadge);

    const navItem = (target, key, icon, label) => {
        const active = navKey === key;
        return h('a', {
            href: target,
            class: `flex flex-col items-center justify-center gap-0.5 text-[11px] font-semibold ${active ? 'text-clay-700' : 'text-bark-600'}`,
            'aria-current': active ? 'page' : null,
        }, h('i', { class: `ph ${icon} text-xl`, 'aria-hidden': 'true' }), label);
    };

    const bottomNav = h(
        'nav',
        { class: 'fixed inset-x-0 bottom-0 z-40 grid h-16 grid-cols-2 border-t border-cream-200 bg-paper pb-[env(safe-area-inset-bottom)] sm:hidden', 'aria-label': 'Navigasi utama' },
        navItem('#/', 'menu', 'ph-fork-knife', 'Menu'),
        navItem('#/lacak', 'lacak', 'ph-receipt', 'Lacak'),
    );

    return h('div', {}, header, h('main', { class: 'mx-auto max-w-3xl px-4 pb-28 pt-4 sm:pb-10' }, content), bottomNav);
}

function render() {
    const { path, params } = parseHash();
    const matched = resolve(path);

    cleanup?.();
    cleanup = null;

    const view = matched.render(matched.nav === 'lacak' ? params : new URLSearchParams());
    app.replaceChildren(shell(view.node, matched.nav));
    if (typeof view.destroy === 'function') {
        cleanup = view.destroy;
    }

    document.title = matched.title;
    window.scrollTo({ top: 0 });
}

window.addEventListener('hashchange', render);
render();
app.removeAttribute('aria-busy');
