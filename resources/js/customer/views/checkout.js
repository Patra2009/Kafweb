import { ApiError, postOrder } from '../api.js';
import { cartItems, cartCount, cartTotal, clearCart, guestProfile, onCartChange, saveGuest, setNotes, setQty, setTableNumber, tableNumber } from '../state.js';
import { fmtIDR, foodPhoto, h, toast } from '../ui.js';

/** @type { Record<string, HTMLElement | null> } wadah galat per field form checkout. */
const fieldErrors = {};

/** Penanda pengiriman berjalan, dipakai lintas render/instans form checkout. */
let submitting = false;

/**
 * Halaman keranjang + form checkout pelanggan. Daftar item dan ringkasan
 * ikut membangun ulang ketika isi keranjang berubah dari dalam form.
 *
 * @returns { { node: HTMLElement, destroy: () => void } }
 */
export function renderCheckout() {
    const root = h('div', { class: 'fade-up' });

    const backBtn = h(
        'button',
        { class: 'stepper-btn h-10 w-10', 'aria-label': 'Kembali ke menu', onclick: () => (window.location.hash = '#/') },
        h('i', { class: 'ph ph-arrow-left', 'aria-hidden': 'true' }),
    );
    root.append(h('div', { class: 'flex items-center gap-3' }, backBtn, h('h1', { class: 'font-display text-xl font-bold text-ink-950' }, 'Keranjang')));

    const body = h('div');
    root.append(body);

    const paint = () => {
        if (cartCount() === 0) {
            body.replaceChildren(emptyCard());
            return;
        }
        body.replaceChildren(itemsCard(), formCard(), summaryCard());
    };

    paint();

    const stopSync = onCartChange(() => {
        if (submitting) {
            return;
        }
        paint();
    });

    return { node: root, destroy: stopSync };
}

function emptyCard() {
    return h(
        'div',
        { class: 'card mt-4 grid place-items-center gap-3 p-10 text-center' },
        h('i', { class: 'ph ph-coffee text-5xl text-clay-500/40', 'aria-hidden': 'true' }),
        h('p', { class: 'font-semibold text-bark-800' }, 'Keranjangmu masih kosong'),
        h('p', { class: 'max-w-xs text-sm text-bark-600' }, 'Pilih menu favoritmu dulu di halaman Menu, lalu kembali untuk mengirim pesanan.'),
        h('a', { href: '#/', class: 'btn btn-primary mt-1' }, 'Lihat Menu'),
    );
}

function itemsCard() {
    const list = h('ul', { class: 'divide-y divide-cream-200', 'data-items': '' });
    list.replaceChildren(...cartItems().map((item) => itemRow(item)));

    return h('div', { class: 'card mt-4' }, list);
}

function itemRow(item) {
    const photo = h('div', { class: 'h-16 w-16 shrink-0 overflow-hidden rounded-2xl bg-cream-100' }, foodPhoto(item.image, item.name, 'h-full w-full object-cover'));

    const quantity = h('span', { class: 'min-w-5 text-center text-sm font-extrabold tabular-nums text-ink-950' }, String(item.qty));

    const notesToggle = h('button', {
        class: 'stepper-btn',
        'aria-label': `Edit catatan ${item.name}`,
        'aria-expanded': item.notes ? 'true' : 'false',
        title: 'Catatan dapur',
        onclick: () => {
            const nowOpen = extraRow.classList.contains('hidden');
            extraRow.classList.toggle('hidden', !nowOpen);
            notesToggle.setAttribute('aria-expanded', String(nowOpen));
            if (nowOpen) {
                notesInput.focus();
            }
        },
    }, h('i', { class: 'ph ph-note-pencil', 'aria-hidden': 'true' }));

    const notesInput = h('input', {
        class: 'input h-10 w-full text-sm',
        type: 'text',
        maxlength: '100',
        placeholder: 'Catatan dapur, contoh: less sugar, tanpa es',
        value: item.notes,
        'aria-label': `Catatan ${item.name}`,
        oninput: (event) => setNotes(item.menu_id, /** @type { HTMLInputElement } */ (event.target).value),
    });

    const extraRow = h('div', { class: `mt-2 ${item.notes ? '' : 'hidden'}` }, notesInput);

    return h(
        'li',
        { class: 'p-4' },
        h(
            'div',
            { class: 'flex gap-3' },
            photo,
            h(
                'div',
                { class: 'min-w-0 flex-1' },
                h('div', { class: 'flex items-start justify-between gap-2' },
                    h('h3', { class: 'truncate text-sm font-bold text-ink-950' }, item.name),
                    h('div', { class: 'stepper shrink-0' },
                        h('button', { class: 'stepper-btn', 'aria-label': `Kurangi ${item.name}`, onclick: () => change(-1) }, h('i', { class: 'ph ph-minus', 'aria-hidden': 'true' })),
                        quantity,
                        h('button', {
                            class: 'stepper-btn',
                            'aria-label': `Tambah ${item.name}`,
                            title: item.qty >= item.stock ? 'Stok menu ini terbatas' : null,
                            onclick: () => change(1),
                        }, h('i', { class: 'ph ph-plus', 'aria-hidden': 'true' })),
                    ),
                ),
                h('div', { class: 'mt-1 flex items-center justify-between gap-2' },
                    h('p', { class: 'text-xs text-bark-600' }, fmtIDR(String(item.price)), ' x ', item.qty),
                    h('p', { class: 'text-sm font-bold tabular-nums text-bark-800' }, fmtIDR(String(item.price * item.qty))),
                ),
                h('div', { class: 'mt-2 flex justify-end' }, notesToggle),
                extraRow,
            ),
        ),
    );

    function change(delta) {
        const next = item.qty + delta;
        if (delta > 0 && next > item.stock) {
            toast(`Stok ${item.name} hanya tersisa ${item.stock}.`, 'warn');
            return;
        }
        setQty(item.menu_id, next);
    }
}

function formCard() {
    const nameInput = /** @type { HTMLInputElement } */ (h('input', { id: 'f-name', class: 'input', type: 'text', name: 'name', placeholder: 'Nama kamu, opsional', 'aria-describedby': 'err-name', value: guestProfile().name }));
    const phoneInput = /** @type { HTMLInputElement } */ (h('input', { id: 'f-phone', class: 'input', type: 'tel', name: 'phone', placeholder: '08xx, opsional', 'aria-describedby': 'err-phone', value: guestProfile().phone }));
    const tableInput = /** @type { HTMLInputElement } */ (h('input', { id: 'f-table', class: 'input', type: 'text', name: 'table_number', placeholder: 'Kosong kalau take away', value: tableNumber }));

    const banner = h('div', { class: 'hidden rounded-2xl border border-berry-200 bg-berry-50 p-3 text-sm font-medium text-berry-600', role: 'alert' });

    const submitBtn = h('button', { type: 'submit', class: 'btn btn-primary h-12 w-full text-base' }, h('i', { class: 'ph ph-paper-plane-tilt', 'aria-hidden': 'true' }), 'Kirim Pesanan');

    const form = h(
        'form',
        { class: 'card mt-4 space-y-4 p-4', onsubmit: (event) => handleSubmit(event) },
        fieldWrap('Nama', nameInput, 'name', 'Nanti dipanggil saat pesananmu siap.'),
        fieldWrap('Nomor HP', phoneInput, 'phone', 'Opsional, dipakai bila kami perlu mengonfirmasi.'),
        fieldWrap('Nomor meja', tableInput, 'table_number', 'Kosongkan untuk pesanan takeaway.'),
        banner,
        submitBtn,
    );

    async function handleSubmit(event) {
        event.preventDefault();
        if (submitting) {
            return;
        }
        submitting = true;
        clearErrors();

        const name = nameInput.value.trim();
        const phone = phoneInput.value.trim();
        const table = tableInput.value.trim();
        setTableNumber(table);
        saveGuest({ name, phone });

        submitBtn.disabled = true;
        submitBtn.replaceChildren('Mengirim pesanan...');

        try {
            const { data } = await postOrder({
                name: name || undefined,
                phone: phone || undefined,
                table_number: table || undefined,
                items: cartItems().map((item) => ({ menu_id: item.menu_id, quantity: item.qty, notes: item.notes || undefined })),
            });

            clearCart();
            submitting = false;
            toast('Pesanan dibuat! Simpan kode lacaknya ya.');
            window.location.hash = `#/lacak?kode=${data.tracking_code}&baru=1`;
        } catch (error) {
            submitting = false;
            submitBtn.disabled = false;
            submitBtn.replaceChildren(h('i', { class: 'ph ph-paper-plane-tilt', 'aria-hidden': 'true' }), 'Kirim Pesanan');

            if (error instanceof ApiError) {
                const errors = error.errors ?? {};
                /** @type { Record<string, string> } */
                const labels = { name: 'Nama', phone: 'Nomor HP', table_number: 'Nomor meja' };
                for (const [key, label] of Object.entries(labels)) {
                    const messages = errors[key];
                    if (messages) {
                        showFieldError(key, `${label}: ${Array.isArray(messages) ? messages[0] : messages}`);
                    }
                }

                const itemErrors = errors.items;
                if (itemErrors) {
                    banner.replaceChildren(Array.isArray(itemErrors) ? itemErrors.join(' ') : String(itemErrors));
                    banner.classList.remove('hidden');
                }

                if (!Object.keys(errors).length && error.status !== 422) {
                    banner.replaceChildren(error.message);
                    banner.classList.remove('hidden');
                }
            } else {
                banner.replaceChildren('Tidak bisa mengirim pesanan, coba lagi.');
                banner.classList.remove('hidden');
            }
        }
    }

    function clearErrors() {
        banner.classList.add('hidden');
        banner.replaceChildren();
        Object.values(fieldErrors).forEach((slot) => slot?.replaceChildren());
    }

    return form;
}

function summaryCard() {
    const summary = h('div', { 'data-summary': '' });
    const rows = cartItems().map((item) =>
        h(
            'div',
            { class: 'flex justify-between gap-4 py-1 text-sm' },
            h('span', { class: 'text-bark-700' }, item.name, ' x', item.qty),
            h('span', { class: 'font-semibold tabular-nums text-bark-800' }, fmtIDR(String(item.price * item.qty))),
        ),
    );

    summary.replaceChildren(
        h('div', { class: 'space-y-1' }, rows),
        h('div', { class: 'mt-3 flex justify-between border-t border-cream-200 pt-3' },
            h('span', { class: 'font-semibold text-bark-800' }, 'Total'),
            h('strong', { class: 'font-display text-xl font-bold tabular-nums text-clay-700' }, fmtIDR(String(cartTotal()))),
        ),
    );

    return h('div', { class: 'card mt-4 p-4' }, summary);
}

function fieldWrap(labelText, input, key, helper) {
    const errorSlot = h('div', { id: `err-${key}` });
    fieldErrors[key] = errorSlot;
    return h('div', {}, h('label', { class: 'label', for: `f-${key}` }, labelText), input, errorSlot, helper !== null && helper !== undefined ? h('p', { class: 'mt-1 text-xs text-bark-600' }, helper) : null);
}
