import { fetchTrack } from '../api.js';
import { recentOrders, rememberOrder } from '../state.js';
import { fmtIDR, h, timeHM, toast } from '../ui.js';

/** Label & copy naratif alur status (aturan bisnis di section 8 docs/API_ENDPOINTS.md). */
const FLOW = [
    { key: 'pending', label: 'Diterima', copy: 'Pesanan masuk dan sedang menunggu konfirmasi kasir.' },
    { key: 'processing', label: 'Diproses', copy: 'Dapur sedang menyiapkan pesananmu dengan hati-hati.' },
    { key: 'ready', label: 'Siap Diambil', copy: 'Pesananmu sudah siap, tunggu dimatangkan di meja.' },
    { key: 'completed', label: 'Selesai', copy: 'Pesanan selesai, terima kasih sudah mampir!' },
];

const CANCELLED_COPY = 'Pesanan dibatalkan. Konfirmasi lebih lanjut ke staf kasir, ya.';

/**
 * Halaman lacak status pesanan via kode pelacakan, dengan polling tiap 5 detik.
 *
 * @param { URLSearchParams } params query pada hash, misal kode=K7PD2XQA
 * @returns { { node: HTMLElement, destroy: (): void } }
 */
export function renderTrack(params) {
    let poller = null;

    const root = h('div', { class: 'fade-up space-y-4' });

    const backBtn = h(
        'button',
        { class: 'stepper-btn h-10 w-10', 'aria-label': 'Kembali ke menu', onclick: () => (window.location.hash = '#/') },
        h('i', { class: 'ph ph-arrow-left', 'aria-hidden': 'true' }),
    );
    root.append(h('div', { class: 'flex items-center gap-3' }, backBtn, h('h1', { class: 'font-display text-xl font-bold text-ink-950' }, 'Lacak Pesanan')));

    // --- Kartu input kode -----------------------------------------------------
    const codeInput = h('input', {
        class: 'input font-mono uppercase tracking-[0.2em] placeholder:tracking-normal',
        type: 'text',
        maxlength: '16',
        autocapitalize: 'characters',
        placeholder: 'K7PD2XQA',
        'aria-label': 'Kode pelacakan',
        oninput: (event) => {
            event.target.value = String(event.target.value).toUpperCase().replace(/[^A-Z0-9]/g, '');
        },
        onkeydown: (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                trackFromInput();
            }
        },
    });

    const statusArea = h('div', { id: 'track-status', 'aria-live': 'polite' });

    root.append(
        h(
            'div',
            { class: 'card p-4' },
            h('label', { class: 'label' }, 'Kode pelacakan'),
            h('div', { class: 'flex gap-2' },
                codeInput,
                h('button', { class: 'btn btn-primary shrink-0 px-5', 'aria-label': 'Lacak pesanan', onclick: trackFromInput }, h('i', { class: 'ph ph-magnifying-glass', 'aria-hidden': 'true' }), 'Lacak'),
            ),
            h('p', { class: 'mt-1.5 text-xs text-bark-600' }, 'Tulis kode dari struk kasirmu, langsung lacak statusnya.'),
        ),
    );

    const recents = recentOrders();
    if (recents.length > 0) {
        root.append(
            h(
                'div',
                { class: 'flex flex-wrap items-center gap-2' },
                h('span', { class: 'text-xs font-semibold text-bark-600' }, 'Pesanan terbaru:'),
                ...recents.map((entry) =>
                    h('a', { class: 'chip px-3 text-xs', href: `#/lacak?kode=${entry.code}&baru=1` }, h('i', { class: 'ph ph-receipt text-sm', 'aria-hidden': 'true' }), entry.code),
                ),
            ),
        );
    }

    root.append(statusArea);

    const initialCode = params.get('kode');
    if (initialCode) {
        codeInput.value = initialCode.toUpperCase();
        load(initialCode.toUpperCase(), params.get('baru') === '1');
    } else {
        statusArea.replaceChildren(emptyState());
    }

    return { node: root, destroy };

    /** Bersihkan polling saat pindah halaman. */
    function destroy() {
        if (poller) {
            window.clearInterval(poller);
            poller = null;
        }
    }

    function trackFromInput() {
        const value = codeInput.value.trim().toUpperCase();
        if (!value) {
            codeInput.focus();
            toast('Tulis kode pelacakan dulu dari struk kasir.', 'warn');
            return;
        }
        rememberOrder(value);
        load(value);
    }

    async function load(code, isNew = false) {
        rememberOrder(code);
        skeleton();
        try {
            const { data } = await fetchTrack(code);
            render(data, code, isNew);
            attachPolling(code, isNew);
        } catch (error) {
            const status = /** @type { import('../api.js').ApiError } */ (error).status;
            if (status === 404) {
                statusArea.replaceChildren(notFound(code));
            } else {
                statusArea.replaceChildren(retryCard(/** @type { string } */ (error.message ?? 'Gagal terhubung.'), () => load(code)));
            }
        }
    }

    function attachPolling(code, isNew) {
        destroy();
        poller = window.setInterval(() => {
            fetchTrack(code)
                .then(({ data }) => {
                    render(data, code, false);
                })
                .catch(() => {
                    // Cek keluar sinyal: tunggu interval berikutnya.
                });
        }, 5000);
    }

    function skeleton() {
        statusArea.replaceChildren(
            h(
                'div',
                { class: 'card space-y-4 p-5' },
                h('div', { class: 'skeleton h-6 w-28' }),
                h('div', { class: 'skeleton h-8 w-44' }),
                h('div', { class: 'skeleton h-2 w-64 rounded-full' }),
            ),
        );
    }

    /**
     * Kartu alur status pesanan.
     */
    function render(status, code, isNew) {
        if (status.order_status === 'cancelled') {
            destroy();
            statusArea.replaceChildren(cancelledCard(code, status));
            return;
        }

        const activeIndex = FLOW.findIndex((step) => step.key === status.order_status);
        const paid = status.payment_status === 'paid';

        const stageBar = h('span', { class: `badge ${activeIndex >= 2 ? 'badge-sage' : 'badge-clay'}` }, status.order_status_label);

        const metaRow = h(
            'div',
            { class: 'mt-4 divide-y divide-cream-200 border-t border-cream-200 pt-4 text-sm' },
            h('div', { class: 'flex justify-between py-1' }, h('span', { class: 'text-bark-600' }, 'Nomor meja'), h('strong', { class: 'text-bark-800' }, status.table_number ? `Meja ${status.table_number}` : 'Take Away')),
            h('div', { class: 'flex justify-between py-1' }, h('span', { class: 'text-bark-600' }, 'Total'), h('strong', { class: 'tabular-nums text-bark-800' }, fmtIDR(status.total_price))),
            h('div', { class: 'flex items-center justify-between gap-3 py-1' },
                h('span', { class: 'text-bark-600' }, 'Pembayaran'),
                paid
                    ? h('span', { class: 'badge badge-sage' }, h('i', { class: 'ph ph-check', 'aria-hidden': 'true' }), 'Sudah dibayar')
                    : h('span', { class: 'badge badge-clay' }, 'Belum dibayar di kasir'),
            ),
        );

        statusArea.replaceChildren(
            h(
                'section',
                { class: `card p-5 ${isNew ? 'ring-2 ring-clay-500/40' : ''}` },
                isNew ? h('div', { class: 'mb-3 flex items-center gap-2 rounded-2xl bg-sage-50 px-3 py-2 text-xs font-semibold text-sage-700' }, h('i', { class: 'ph ph-check', 'aria-hidden': 'true' }), 'Pesanan berhasil dikirim ke kasir. Simpan kode ini untuk memantau!') : null,
                h('div', { class: 'flex items-center justify-between gap-3' },
                    h('span', { class: 'inline-flex rounded-xl bg-cream-100 px-2.5 py-1.5 font-mono text-base font-semibold tracking-[0.22em] text-ink-950' }, code),
                    stageBar,
                ),
                h('ol', { class: 'mt-5 space-y-0' }, FLOW.map((step, index) => stepRow(step, index, activeIndex))),
                metaRow,
                h('p', { class: 'mt-3 text-[11px] text-bark-600' }, timeHM(new Date()), ' - diperbarui otomatis tiap 5 detik.'),
            ),
        );
    }

    function stepRow(step, index, activeIndex) {
        const done = index < activeIndex;
        const active = index === activeIndex;
        const circle = done
            ? h('span', { class: 'grid h-7 w-7 shrink-0 place-items-center rounded-full bg-sage-600 text-white' }, h('i', { class: 'ph ph-check', 'aria-hidden': 'true' }))
            : active
                ? h('span', { class: 'grid h-7 w-7 shrink-0 place-items-center rounded-full border-2 border-clay-600 bg-clay-100 text-clay-700' }, h('i', { class: 'ph ph-gear', 'aria-hidden': 'true' }))
                : h('span', { class: 'grid h-7 w-7 shrink-0 place-items-center rounded-full border-2 border-cream-200 bg-paper text-bark-600' }, String(index + 1));

        return h(
            'li',
            { class: 'relative flex gap-3 pb-5 last:pb-0' },
            index < FLOW.length - 1 ? h('span', { class: `absolute left-[13px] top-7 h-full w-0.5 ${done ? 'bg-sage-600/50' : 'bg-cream-200'}`, 'aria-hidden': 'true' }) : null,
            circle,
            h(
                'div',
                {},
                h('p', { class: `text-sm font-bold ${active ? 'text-clay-700' : 'text-bark-800'}` }, step.label),
                active ? h('p', { class: 'text-xs leading-relaxed text-bark-800' }, step.copy) : null,
            ),
        );
    }

    function cancelledCard(code, status) {
        return h(
            'section',
            { class: 'card p-5 ring-2 ring-berry-200' },
            h('div', { class: 'flex items-center justify-between gap-3' },
                h('span', { class: 'inline-flex rounded-xl bg-cream-100 px-2.5 py-1.5 font-mono text-base font-semibold tracking-[0.22em] text-ink-950' }, code),
                h('span', { class: 'badge badge-berry' }, status.order_status_label),
            ),
            h('div', { class: 'mt-5 flex gap-3 rounded-2xl bg-berry-50 p-4' },
                h('i', { class: 'ph ph-x-circle text-2xl text-berry-600', 'aria-hidden': 'true' }),
                h('p', { class: 'text-sm font-medium leading-relaxed text-berry-600' }, CANCELLED_COPY),
            ),
            h('p', { class: 'mt-3 text-[11px] text-bark-600' }, timeHM(new Date()), ' - terakhir diperbarui.'),
        );
    }

    function notFound(code) {
        return h(
            'div',
            { class: 'card grid place-items-center gap-2 p-8 text-center' },
            h('i', { class: 'ph ph-receipt text-4xl text-berry-600/60', 'aria-hidden': 'true' }),
            h('p', { class: 'font-semibold text-bark-800' }, 'Kode tidak ditemukan'),
            h('p', { class: 'max-w-xs text-sm text-bark-600' }, `Kode "${code}" tidak cocok. Periksa lagi strukmu atau tanya kasir.`),
        );
    }

    function retryCard(message, onRetry) {
        return h(
            'div',
            { class: 'card grid place-items-center gap-2 p-8 text-center' },
            h('i', { class: 'ph ph-wifi-slash text-4xl text-berry-600/70', 'aria-hidden': 'true' }),
            h('p', { class: 'font-semibold text-bark-800' }, 'Lacak gagal dimuat'),
            h('p', { class: 'max-w-xs text-sm text-bark-600' }, message),
            h('button', { class: 'btn btn-ghost mt-1', onclick: onRetry }, 'Coba lagi'),
        );
    }

    function emptyState() {
        return h(
            'div',
            { class: 'card grid place-items-center gap-2 p-8 text-center' },
            h('i', { class: 'ph ph-receipt text-5xl text-clay-500/40', 'aria-hidden': 'true' }),
            h('p', { class: 'font-semibold text-bark-800' }, 'Belum ada pesanan yang dilacak'),
            h('p', { class: 'max-w-xs text-sm text-bark-600' }, 'Masukkan kode di atas, status pesananmu akan muncul di sini dan ter-update sendiri.'),
        );
    }
}
