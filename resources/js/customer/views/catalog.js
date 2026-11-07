import { fetchMenus } from '../api.js';
import { addToCart, cartCount, cartItems, cartTotal, onCartChange, tableNumber } from '../state.js';
import { fmtIDR, foodPhoto, h, toast } from '../ui.js';

/** Foto kafe untuk selamat datang di atas katalog (unsplash, terverifikasi hidup). */
const HERO_URL = 'https://images.unsplash.com/photo-1554118811-1e0d58224f24?auto=format&fit=crop&w=1200&h=520&q=80';

/** @type { import('../api.js').KafWebMenu[] } */
let menus = [];

const filter = { category: 'all', search: '' };

/** @type { Map<number, { actions: HTMLElement, refreshLeaf: () => void }> } */
const leafRegistry = new Map();

function refreshLeaves() {
    leafRegistry.forEach((leaf) => leaf.refreshLeaf());
}

onCartChange(refreshLeaves);

/**
 * Halaman katalog pelanggan.
 *
 * @returns { { node: HTMLElement, destroy: () => void } }
 */
export function renderCatalog() {
    /** @type { HTMLElement | null } */
    let grid = null;
    /** @type { HTMLElement | null } */
    let cartBar = null;

    const root = h('div', { class: 'fade-up' });

    // --- Selamat datang band: foto kafe + sapaan hangat ---------------------
    const greeting = h(
        'section',
        { class: 'relative overflow-hidden rounded-3xl shadow-card' },
        h('img', {
            src: HERO_URL,
            alt: 'Suasana kafe KafWeb yang hangat dan nyaman',
            class: 'absolute inset-0 h-full w-full object-cover',
            loading: 'eager',
            decoding: 'async',
        }),
        h('div', { class: 'absolute inset-0 bg-gradient-to-r from-ink-950/85 via-ink-950/55 to-ink-950/10', 'aria-hidden': 'true' }),
        h(
            'div',
            { class: 'relative flex flex-col justify-center gap-1.5 p-5 py-8 md:py-10' },
            h('h1', { class: 'font-display text-2xl font-bold leading-snug text-paper md:text-3xl' }, 'Halo! Selamat datang'),
            h('p', { class: 'max-w-md text-sm leading-relaxed text-paper/85 md:text-base' }, 'Pesan favoritmu langsung di sini, tinggal tunggu diantar ke meja.'),
            tableNumber ? h('p', { class: 'mt-2 inline-flex w-fit items-center gap-1.5 rounded-full border border-white/30 bg-white/15 px-3 py-1 text-xs font-bold text-white backdrop-blur-sm' }, h('i', { class: 'ph ph-map-pin text-sm', 'aria-hidden': 'true' }), 'Meja ', tableNumber) : null,
        ),
    );
    root.append(greeting);

    // --- Pencarian + keripik kategori ---------------------------------------
    const searchWrap = h(
        'div',
        { class: 'relative mt-4' },
        h('i', { class: 'ph ph-magnifying-glass pointer-events-none absolute inset-y-0 left-4 grid place-items-center text-lg text-bark-600', 'aria-hidden': 'true' }),
        h('input', {
            class: 'input pl-11',
            type: 'search',
            placeholder: 'Cari menu, contoh: kopi susu...',
            'aria-label': 'Cari menu',
            oninput: (event) => {
                filter.search = event.target.value.trim().toLowerCase();
                applyFilter();
            },
        }),
    );

    const chipRow = h('div', { class: 'mt-3 flex gap-2 overflow-x-auto pb-1 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden' });
    const chipsWrap = h('div', { class: 'sticky top-16 z-30 -mx-4 mt-4 bg-cream-50/95 px-4 pb-2 pt-1 backdrop-blur' }, searchWrap, chipRow);
    root.append(chipsWrap);

    // --- Grid menu -----------------------------------------------------------
    grid = h('div', { class: 'mt-3 grid grid-cols-1 gap-4 sm:grid-cols-2', 'aria-live': 'polite' });
    root.append(grid);
    load();

    root.append(cartBar = cartBarNode());

    return { node: root, destroy };

    function destroy() {
        leafRegistry.clear();
    }

    async function load() {
        skeletonGrid();
        try {
            const { data } = await fetchMenus();
            menus = data;
            renderChips();
            applyFilter();
        } catch (error) {
            renderLoadError(/** @type { Error } */ (error).message);
        }
    }

    function skeletonGrid() {
        grid.replaceChildren(
            ...Array.from({ length: 6 }, () =>
                h(
                    'div',
                    { class: 'card overflow-hidden', 'aria-hidden': 'true' },
                    h('div', { class: 'skeleton aspect-[16/10] rounded-none' }),
                    h('div', { class: 'space-y-2 p-4' }, h('div', { class: 'skeleton h-4 w-3/5' }), h('div', { class: 'skeleton h-3 w-4/5' }), h('div', { class: 'flex justify-between pt-2' }, h('div', { class: 'skeleton h-4 w-20' }), h('div', { class: 'skeleton h-8 w-24 rounded-xl' }))),
                ),
            ),
        );
    }

    function renderLoadError(message) {
        leafRegistry.clear();
        grid.replaceChildren(
            errorCard(message, () => {
                skeletonGrid();
                load();
            }),
        );
    }

    function renderChips() {
        /** @type { Array<{ id: number, name: string }> } */
        const categories = [];
        for (const menu of menus) {
            if (menu.category && !categories.some((entry) => entry.id === menu.category.id)) {
                categories.push({ id: menu.category.id, name: menu.category.name });
            }
        }

        const chips = [
            h('button', { class: 'chip', 'aria-pressed': String(filter.category === 'all'), onclick: () => setCategory('all') }, 'Semua'),
            ...categories.map((category) =>
                h('button', { class: 'chip', 'aria-pressed': String(filter.category === category.id), onclick: () => setCategory(category.id) }, category.name),
            ),
        ];
        chipRow.replaceChildren(...chips);
    }

    function setCategory(id) {
        filter.category = id;
        renderChips();
        applyFilter();
    }

    function applyFilter() {
        const visible = menus.filter((menu) => {
            if (filter.category !== 'all' && menu.category?.id !== filter.category) {
                return false;
            }
            if (filter.search && !`${menu.name} ${menu.description ?? ''}`.toLowerCase().includes(filter.search)) {
                return false;
            }
            return true;
        });

        leafRegistry.clear();

        if (visible.length === 0) {
            grid.replaceChildren(
                h(
                    'div',
                    { class: 'card mt-2 grid place-items-center gap-2 p-10 text-center sm:col-span-2' },
                    h('i', { class: 'ph ph-coffee text-4xl text-clay-500/60', 'aria-hidden': 'true' }),
                    h('p', { class: 'font-semibold text-bark-800' }, 'Menu tidak ditemukan'),
                    h('p', { class: 'max-w-xs text-sm text-bark-600' }, 'Coba kata kunci lain atau lihat kategori yang tersedia.'),
                    h('button', {
                        class: 'btn btn-ghost mt-1',
                        onclick: () => {
                            filter.search = '';
                            filter.category = 'all';
                            const input = searchWrap.querySelector('input');
                            if (input) {
                                input.value = '';
                            }
                            renderChips();
                            applyFilter();
                        },
                    }, 'Tampilkan semua'),
                ),
            );
            return;
        }

        grid.replaceChildren(...visible.map((menu) => menuCard(menu)));
    }

    /**
     * Kartu menu: foto besar menggugah selera, deskripsi ringkas,
     * harga, stok, dan aksi tambah/stepper.
     */
    function menuCard(menu) {
        const soldOut = menu.stock <= 0;

        const photoWrap = h(
            'div',
            { class: 'relative aspect-[16/10] overflow-hidden' },
            foodPhoto(menu.image_url, menu.name, 'h-full w-full object-cover transition-transform duration-500 hover:scale-[1.04]'),
            soldOut ? h('div', { class: 'absolute inset-0 grid place-items-center bg-ink-950/55 backdrop-blur-[2px]' }, h('span', { class: 'rounded-full bg-paper px-4 py-1.5 text-sm font-bold text-ink-950' }, 'Menu habis')) : null,
        );

        const info = h(
            'div',
            { class: 'flex flex-1 flex-col gap-1 p-4 pt-3' },
            h('div', { class: 'flex items-start justify-between gap-3' }, h('h3', { class: 'font-display text-base font-bold leading-tight text-ink-950' }, menu.name),
                menu.stock > 0 && menu.stock <= 10 ? h('span', { class: 'badge badge-clay shrink-0' }, 'Tersisa ', menu.stock) : null),
            menu.description ? h('p', { class: 'line-clamp-2 text-[13px] leading-relaxed text-bark-600' }, menu.description) : null,
            h('div', { class: 'mt-auto flex items-center justify-between pt-2.5' },
                h('strong', { class: 'text-[15px] font-extrabold text-clay-700' }, 'Rp ', Math.round(Number.parseFloat(menu.price)).toLocaleString('id-ID')),
                h('div', { 'data-actions': '' }),
            ),
        );

        const card = h('article', { class: 'card flex flex-col overflow-hidden' }, photoWrap, info);

        const leaf = { actions: info.querySelector('[data-actions]'), refreshLeaf: () => renderActions() };

        function renderActions() {
            leafRegistry.set(menu.id, leaf);
            if (soldOut) {
                leaf.actions.replaceChildren(h('button', { class: 'btn btn-ghost btn-sm cursor-not-allowed', disabled: true, 'aria-label': `${menu.name} sedang habis` }, 'Habis'));
                return;
            }

            const inCart = cartCount() ? countInCart(menu) : 0;
            if (inCart > 0) {
                leaf.actions.replaceChildren(
                    h('div', { class: 'stepper' },
                        h('button', { class: 'stepper-btn', 'aria-label': `Kurangi ${menu.name}`, onclick: () => addToCart(menu, -1) }, h('i', { class: 'ph ph-minus', 'aria-hidden': 'true' })),
                        h('span', { class: 'min-w-5 text-center text-sm font-extrabold tabular-nums text-ink-950' }, String(inCart)),
                        h('button', {
                            class: 'stepper-btn',
                            'aria-label': `Tambah ${menu.name}`,
                            title: inCart >= menu.stock ? 'Stok menu ini terbatas' : null,
                            onclick: () => {
                                if (inCart >= menu.stock) {
                                    toast(`Stok ${menu.name} hanya tersisa ${menu.stock}.`, 'warn');
                                    return;
                                }
                                addToCart(menu, 1);
                            },
                        }, h('i', { class: 'ph ph-plus', 'aria-hidden': 'true' })),
                    ),
                );
                return;
            }

            leaf.actions.replaceChildren(
                h('button', { class: 'btn btn-primary btn-sm', 'aria-label': `Tambahkan ${menu.name} ke keranjang`, onclick: () => addToCart(menu) }, h('i', { class: 'ph ph-plus', 'aria-hidden': 'true' }), 'Tambah'),
            );
        }

        renderActions();
        return card;
    }

    function countInCart(menu) {
        return cartItems().find((item) => item.menu_id === menu.id)?.qty ?? 0;
    }
}

/** Bar mini-keranjang mengapung (muncul ketika keranjang berisi). */
function cartBarNode() {
    const bar = h('div', { class: 'pointer-events-none fixed inset-x-4 bottom-20 z-30 mx-auto max-w-xl sm:bottom-6' });
    let currentShown = false;

    const refresh = () => {
        const count = cartCount();
        const shouldShow = count > 0;
        if (shouldShow === currentShown) {
            return;
        }
        currentShown = shouldShow;
        if (!shouldShow) {
            bar.replaceChildren();
            return;
        }
        bar.replaceChildren(
            h(
                'div',
                { class: 'pointer-events-auto flex items-center justify-between gap-3 rounded-2xl bg-ink-950 px-4 py-3 shadow-raise' },
                h('div', { class: 'flex items-center gap-2 text-sm font-semibold text-paper' },
                    h('i', { class: 'ph ph-tote text-lg text-clay-500', 'aria-hidden': 'true' }),
                    count, ' item, ', h('span', { class: 'tabular-nums' }, fmtIDR(String(cartTotal()))),
                ),
                h('a', { href: '#/checkout', class: 'btn btn-primary btn-sm' }, 'Keranjang ', h('i', { class: 'ph ph-caret-right', 'aria-hidden': 'true' })),
            ),
        );
    };

    onCartChange(refresh);
    refresh();
    return bar;
}

/** Kartu galat pemuatan dengan aksi coba lagi. */
export function errorCard(message, onRetry) {
    return h(
        'div',
        { class: 'card grid place-items-center gap-2 p-10 text-center' },
        h('i', { class: 'ph ph-wifi-slash text-4xl text-berry-600/70', 'aria-hidden': 'true' }),
        h('p', { class: 'font-semibold text-bark-800' }, 'Menu gagal dimuat'),
        h('p', { class: 'max-w-xs text-sm text-bark-600' }, message),
        h('button', { class: 'btn btn-ghost mt-1', onclick: onRetry }, 'Coba lagi ', h('i', { class: 'ph ph-arrows-clockwise', 'aria-hidden': 'true' })),
    );
}
