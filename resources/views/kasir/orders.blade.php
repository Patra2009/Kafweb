@extends('layouts.dashboard', ['title' => 'Kelola Pesanan', 'subtitle' => 'Update status dan proses pembayaran'])

@section('sidebar')
<a href="{{ route('kasir.dashboard') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl hover:bg-[#4A2F1A] text-[#E8DCC4] hover:text-white transition-colors">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
    </svg>
    <span>Dashboard</span>
</a>

<a href="{{ route('kasir.orders') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl bg-[#6B4423] text-white font-medium">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
    </svg>
    <span>Pesanan</span>
</a>

<a href="{{ route('kasir.customers') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl hover:bg-[#4A2F1A] text-[#E8DCC4] hover:text-white transition-colors">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
    </svg>
    <span>Pelanggan</span>
</a>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Filters -->
    <div class="bg-white rounded-2xl p-4 shadow-sm border border-[#E8DCC4]">
        <div class="flex flex-wrap gap-3">
            <button data-filter="all" onclick="filterOrders('all')" class="filter-btn px-4 py-2 rounded-xl font-medium transition-all bg-[#6B4423] text-white">
                Semua
            </button>
            <button data-filter="pending" onclick="filterOrders('pending')" class="filter-btn px-4 py-2 rounded-xl font-medium transition-all bg-[#FFF8F0] text-[#6B4423] hover:bg-[#E8DCC4]">
                Pending
            </button>
            <button data-filter="processing" onclick="filterOrders('processing')" class="filter-btn px-4 py-2 rounded-xl font-medium transition-all bg-[#FFF8F0] text-[#6B4423] hover:bg-[#E8DCC4]">
                Diproses
            </button>
            <button data-filter="ready" onclick="filterOrders('ready')" class="filter-btn px-4 py-2 rounded-xl font-medium transition-all bg-[#FFF8F0] text-[#6B4423] hover:bg-[#E8DCC4]">
                Siap
            </button>
            <button data-filter="unpaid" onclick="filterOrders('unpaid')" class="filter-btn px-4 py-2 rounded-xl font-medium transition-all bg-[#FFF8F0] text-[#6B4423] hover:bg-[#E8DCC4]">
                Belum Dibayar
            </button>
        </div>
    </div>

    <!-- Orders List -->
    <div id="ordersList" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="col-span-full text-center py-12 text-[#9BA17B]">
            <div class="animate-spin w-12 h-12 border-4 border-[#E8DCC4] border-t-[#6B4423] rounded-full mx-auto mb-4"></div>
            <p>Memuat data pesanan...</p>
        </div>
    </div>
</div>

<!-- Order Detail Modal -->
<div id="orderModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div id="modalContent"></div>
    </div>
</div>

<script>
let currentFilter = 'all';
let allOrders = [];
const apiBase = '/api/v1';
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

// Helper function for API calls
async function apiCall(url, options = {}) {
    const response = await fetch(url, {
        ...options,
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            ...options.headers
        },
        credentials: 'same-origin'
    });
    
    if (!response.ok && response.status === 401) {
        window.location.href = '/login';
        return null;
    }
    
    return response;
}

document.addEventListener('DOMContentLoaded', function() {
    loadOrders();
    // Auto-refresh every 30 seconds
    setInterval(loadOrders, 30000);
});

async function loadOrders() {
    try {
        const response = await apiCall(`${apiBase}/kasir/orders`);
        
        if (response && response.ok) {
            const data = await response.json();
            allOrders = data.data;
            filterOrders(currentFilter);
        }
    } catch (error) {
        console.error('Error loading orders:', error);
        showError('Gagal memuat data pesanan');
    }
}

function filterOrders(filter) {
    currentFilter = filter;

    // Update active filter button memakai data-filter,
    // sehingga aman dipanggil dari loadOrders()/polling tanpa objek event.
    document.querySelectorAll('.filter-btn').forEach(btn => {
        const active = btn.dataset.filter === filter;
        btn.classList.toggle('bg-[#6B4423]', active);
        btn.classList.toggle('text-white', active);
        btn.classList.toggle('bg-[#FFF8F0]', !active);
        btn.classList.toggle('text-[#6B4423]', !active);
    });
    
    let filtered = allOrders;
    
    if (filter === 'unpaid') {
        filtered = allOrders.filter(o => o.payment_status === 'pending');
    } else if (filter !== 'all') {
        filtered = allOrders.filter(o => o.order_status === filter);
    }
    
    displayOrders(filtered);
}

function displayOrders(orders) {
    const container = document.getElementById('ordersList');
    
    if (orders.length === 0) {
        container.innerHTML = `
            <div class="col-span-full text-center py-12 text-[#9BA17B]">
                <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p class="text-lg font-medium">Tidak ada pesanan</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = orders.map(order => `
        <div class="bg-white rounded-2xl shadow-sm border border-[#E8DCC4] overflow-hidden hover:shadow-lg transition-shadow">
            <div class="p-5">
                <!-- Order Header -->
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <div class="flex items-center space-x-2 mb-1">
                            <span class="text-lg font-bold text-[#3E2723]">#${order.id}</span>
                            ${order.payment_status === 'paid' ? '<span class="px-2 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded-lg">LUNAS</span>' : ''}
                        </div>
                        <p class="text-sm text-[#9BA17B]">${formatTime(order.created_at)}</p>
                    </div>
                    <span class="px-3 py-1 rounded-lg text-xs font-semibold ${getStatusStyle(order.order_status)}">
                        ${getStatusLabel(order.order_status)}
                    </span>
                </div>
                
                <!-- Customer & Table -->
                <div class="mb-4 p-3 bg-[#FFF8F0] rounded-xl">
                    <div class="flex items-center space-x-3">
                        <svg class="w-5 h-5 text-[#6B4423]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-[#3E2723]">${order.customer?.name || 'Walk-in'}</p>
                            <p class="text-xs text-[#9BA17B]">Meja: ${order.table_number || '-'}</p>
                        </div>
                    </div>
                </div>
                
                <!-- Items Summary -->
                <div class="mb-4">
                    <p class="text-xs text-[#9BA17B] mb-2 font-medium">${order.items?.length || 0} Item:</p>
                    <div class="space-y-1">
                        ${(order.items || []).slice(0, 3).map(item => `
                            <div class="flex justify-between text-sm">
                                <span class="text-[#3E2723]">${item.quantity}x ${item.menu?.name || 'Menu'}</span>
                            </div>
                        `).join('')}
                        ${order.items?.length > 3 ? `<p class="text-xs text-[#9BA17B] italic">+${order.items.length - 3} item lainnya</p>` : ''}
                    </div>
                </div>
                
                <!-- Total -->
                <div class="mb-4 pt-3 border-t border-[#E8DCC4]">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-[#9BA17B]">Total:</span>
                        <span class="text-xl font-bold text-[#6B4423]">Rp ${new Intl.NumberFormat('id-ID').format(order.total_price)}</span>
                    </div>
                </div>
                
                <!-- Action Button -->
                <button onclick="showOrderDetail(${order.id})" class="w-full py-3 bg-[#6B4423] hover:bg-[#8B5A3C] text-white font-semibold rounded-xl transition-colors">
                    Kelola Pesanan
                </button>
            </div>
        </div>
    `).join('');
}

async function showOrderDetail(orderId) {
    const order = allOrders.find(o => o.id === orderId);
    if (!order) return;
    
    const modal = document.getElementById('orderModal');
    const content = document.getElementById('modalContent');
    
    content.innerHTML = `
        <!-- Modal Header -->
        <div class="sticky top-0 bg-white border-b border-[#E8DCC4] px-6 py-4 flex items-center justify-between rounded-t-3xl">
            <h3 class="text-xl font-bold text-[#3E2723]">Detail Pesanan #${order.id}</h3>
            <button onclick="closeModal()" class="p-2 hover:bg-[#FFF8F0] rounded-xl transition-colors">
                <svg class="w-6 h-6 text-[#6B4423]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <div class="p-6 space-y-6">
            <!-- Customer Info -->
            <div class="bg-[#FFF8F0] rounded-2xl p-4">
                <h4 class="text-sm font-bold text-[#6B4423] mb-3">Informasi Pelanggan</h4>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-[#9BA17B]">Nama:</span>
                        <span class="font-semibold text-[#3E2723]">${order.customer?.name || 'Walk-in'}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#9BA17B]">No. Meja:</span>
                        <span class="font-semibold text-[#3E2723]">${order.table_number || '-'}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#9BA17B]">Waktu Pesan:</span>
                        <span class="font-semibold text-[#3E2723]">${formatTime(order.created_at)}</span>
                    </div>
                </div>
            </div>
            
            <!-- Order Items -->
            <div>
                <h4 class="text-sm font-bold text-[#6B4423] mb-3">Item Pesanan</h4>
                <div class="space-y-2">
                    ${(order.items || []).map(item => `
                        <div class="flex justify-between items-start p-3 bg-white rounded-xl border border-[#E8DCC4]">
                            <div class="flex-1">
                                <p class="font-semibold text-[#3E2723]">${item.menu?.name || 'Menu'}</p>
                                <p class="text-sm text-[#9BA17B]">Rp ${new Intl.NumberFormat('id-ID').format(item.price)} × ${item.quantity}</p>
                                ${item.notes ? `<p class="text-xs text-[#9BA17B] mt-1 italic">Catatan: ${item.notes}</p>` : ''}
                            </div>
                            <span class="font-bold text-[#6B4423]">Rp ${new Intl.NumberFormat('id-ID').format(item.price * item.quantity)}</span>
                        </div>
                    `).join('')}
                </div>
                <div class="mt-4 pt-4 border-t-2 border-[#E8DCC4] flex justify-between items-center">
                    <span class="text-lg font-bold text-[#3E2723]">Total</span>
                    <span class="text-2xl font-bold text-[#6B4423]">Rp ${new Intl.NumberFormat('id-ID').format(order.total_price)}</span>
                </div>
            </div>
            
            <!-- Status Update -->
            ${order.order_status !== 'completed' && order.order_status !== 'cancelled' ? `
                <div>
                    <h4 class="text-sm font-bold text-[#6B4423] mb-3">Update Status</h4>
                    <div class="grid grid-cols-2 gap-3">
                        ${order.order_status === 'pending' ? `
                            <button onclick="updateOrderStatus(${order.id}, 'processing')" class="py-3 bg-blue-500 hover:bg-blue-600 text-white font-semibold rounded-xl transition-colors">
                                Mulai Proses
                            </button>
                            <button onclick="updateOrderStatus(${order.id}, 'cancelled')" class="py-3 bg-red-500 hover:bg-red-600 text-white font-semibold rounded-xl transition-colors">
                                Batalkan
                            </button>
                        ` : ''}
                        ${order.order_status === 'processing' ? `
                            <button onclick="updateOrderStatus(${order.id}, 'ready')" class="py-3 bg-green-500 hover:bg-green-600 text-white font-semibold rounded-xl transition-colors col-span-2">
                                Tandai Siap
                            </button>
                        ` : ''}
                        ${order.order_status === 'ready' && order.payment_status === 'paid' ? `
                            <button onclick="updateOrderStatus(${order.id}, 'completed')" class="py-3 bg-[#6B4423] hover:bg-[#8B5A3C] text-white font-semibold rounded-xl transition-colors col-span-2">
                                Selesaikan Pesanan
                            </button>
                        ` : ''}
                    </div>
                </div>
            ` : ''}
            
            <!-- Payment Section -->
            ${order.payment_status === 'pending' ? `
                <div>
                    <h4 class="text-sm font-bold text-[#6B4423] mb-3">Pembayaran</h4>
                    <div id="paymentForm" class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-[#6B4423] mb-2">Metode Pembayaran</label>
                            <select id="paymentMethod" class="w-full px-4 py-3 bg-[#FFF8F0] border-2 border-[#E8DCC4] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#6B4423]">
                                <option value="cash">Tunai</option>
                                <option value="qris">QRIS</option>
                                <option value="debit_card">Kartu Debit</option>
                                <option value="e_wallet">E-Wallet</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-[#6B4423] mb-2">Jumlah Bayar</label>
                            <input type="number" id="paymentAmount" value="${order.total_price}" min="${order.total_price}" step="1000" class="w-full px-4 py-3 bg-[#FFF8F0] border-2 border-[#E8DCC4] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#6B4423]">
                        </div>
                        <div id="changeDisplay" class="hidden p-4 bg-green-50 border-2 border-green-200 rounded-xl">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-green-700">Kembalian:</span>
                                <span class="text-xl font-bold text-green-700" id="changeAmount">Rp 0</span>
                            </div>
                        </div>
                        <button onclick="processPayment(${order.id})" class="w-full py-4 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-bold rounded-xl transition-all shadow-lg">
                            Proses Pembayaran
                        </button>
                    </div>
                </div>
            ` : `
                <div class="p-4 bg-green-50 border-2 border-green-200 rounded-xl text-center">
                    <svg class="w-12 h-12 text-green-500 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-green-700 font-bold">Pesanan Sudah Dibayar</p>
                    <p class="text-sm text-green-600 mt-1">Dibayar pada: ${formatTime(order.paid_at)}</p>
                </div>
            `}
        </div>
    `;
    
    modal.classList.remove('hidden');
    
    // Add change calculator for cash payment
    if (order.payment_status === 'pending') {
        const amountInput = document.getElementById('paymentAmount');
        const methodSelect = document.getElementById('paymentMethod');
        
        amountInput.addEventListener('input', calculateChange);
        methodSelect.addEventListener('change', function() {
            if (this.value === 'cash') {
                amountInput.disabled = false;
                calculateChange();
            } else {
                amountInput.value = order.total_price;
                amountInput.disabled = true;
                document.getElementById('changeDisplay').classList.add('hidden');
            }
        });
    }
}

function calculateChange() {
    const amount = parseFloat(document.getElementById('paymentAmount').value);
    const order = allOrders.find(o => document.getElementById('modalContent').innerHTML.includes(`#${o.id}`));
    
    if (order && amount >= parseFloat(order.total_price)) {
        const change = amount - parseFloat(order.total_price);
        document.getElementById('changeAmount').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(change);
        document.getElementById('changeDisplay').classList.remove('hidden');
    } else {
        document.getElementById('changeDisplay').classList.add('hidden');
    }
}

async function updateOrderStatus(orderId, newStatus) {
    if (!confirm(`Yakin ingin mengubah status pesanan menjadi "${getStatusLabel(newStatus)}"?`)) {
        return;
    }
    
    try {
        const response = await apiCall(`${apiBase}/kasir/orders/${orderId}/status`, {
            method: 'PATCH',
            body: JSON.stringify({ order_status: newStatus })
        });
        
        if (response && response.ok) {
            showSuccess('Status pesanan berhasil diupdate');
            closeModal();
            loadOrders();
        } else {
            const error = response ? await response.json() : {};
            showError(error.message || 'Gagal mengupdate status');
        }
    } catch (error) {
        console.error('Error updating status:', error);
        showError('Gagal mengupdate status pesanan');
    }
}

async function processPayment(orderId) {
    const method = document.getElementById('paymentMethod').value;
    const amount = parseFloat(document.getElementById('paymentAmount').value);
    const order = allOrders.find(o => o.id === orderId);
    
    if (amount < parseFloat(order.total_price)) {
        showError('Jumlah pembayaran kurang dari total tagihan');
        return;
    }
    
    if (!confirm(`Proses pembayaran Rp ${new Intl.NumberFormat('id-ID').format(amount)}?`)) {
        return;
    }
    
    try {
        const response = await apiCall(`${apiBase}/kasir/orders/${orderId}/payment`, {
            method: 'POST',
            body: JSON.stringify({ method, amount })
        });
        
        if (response && response.ok) {
            const result = await response.json();
            showSuccess(`Pembayaran berhasil! Kembalian: Rp ${new Intl.NumberFormat('id-ID').format(result.change)}`);
            closeModal();
            loadOrders();
        } else {
            const error = response ? await response.json() : {};
            showError(error.message || 'Gagal memproses pembayaran');
        }
    } catch (error) {
        console.error('Error processing payment:', error);
        showError('Gagal memproses pembayaran');
    }
}

function closeModal() {
    document.getElementById('orderModal').classList.add('hidden');
}

function formatTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString('id-ID', { 
        day: '2-digit', 
        month: 'short', 
        year: 'numeric',
        hour: '2-digit', 
        minute: '2-digit' 
    });
}

function getStatusStyle(status) {
    const styles = {
        'pending': 'bg-yellow-100 text-yellow-700',
        'processing': 'bg-blue-100 text-blue-700',
        'ready': 'bg-green-100 text-green-700',
        'completed': 'bg-gray-100 text-gray-700',
        'cancelled': 'bg-red-100 text-red-700'
    };
    return styles[status] || 'bg-gray-100 text-gray-700';
}

function getStatusLabel(status) {
    const labels = {
        'pending': 'Menunggu',
        'processing': 'Diproses',
        'ready': 'Siap',
        'completed': 'Selesai',
        'cancelled': 'Batal'
    };
    return labels[status] || status;
}

function showSuccess(message) {
    alert('✓ ' + message);
}

function showError(message) {
    alert('✗ ' + message);
}
</script>
@endsection
