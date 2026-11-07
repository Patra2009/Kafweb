@extends('layouts.dashboard', ['title' => 'Kelola Pelanggan', 'subtitle' => 'Cari dan daftarkan pelanggan'])

@section('sidebar')
<a href="{{ route('kasir.dashboard') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl hover:bg-[#4A2F1A] text-[#E8DCC4] hover:text-white transition-colors">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
    </svg>
    <span>Dashboard</span>
</a>

<a href="{{ route('kasir.orders') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl hover:bg-[#4A2F1A] text-[#E8DCC4] hover:text-white transition-colors">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
    </svg>
    <span>Pesanan</span>
</a>

<a href="{{ route('kasir.customers') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl bg-[#6B4423] text-white font-medium">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
    </svg>
    <span>Pelanggan</span>
</a>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Search & Add Customer -->
    <div class="bg-white rounded-2xl p-4 shadow-sm border border-[#E8DCC4]">
        <div class="flex flex-col md:flex-row gap-3">
            <div class="flex-1 relative">
                <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-[#9BA17B]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input 
                    type="text" 
                    id="searchInput" 
                    placeholder="Cari berdasarkan nama atau nomor telepon..." 
                    class="w-full pl-12 pr-4 py-3 bg-[#FFF8F0] border-2 border-[#E8DCC4] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#6B4423] text-[#3E2723]"
                    oninput="searchCustomers(this.value)"
                >
            </div>
            <button onclick="showAddCustomerModal()" class="px-6 py-3 bg-[#6B4423] hover:bg-[#8B5A3C] text-white font-semibold rounded-xl transition-colors flex items-center justify-center space-x-2 whitespace-nowrap">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Tambah Pelanggan</span>
            </button>
        </div>
    </div>

    <!-- Customers List -->
    <div id="customersList" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="col-span-full text-center py-12 text-[#9BA17B]">
            <div class="animate-spin w-12 h-12 border-4 border-[#E8DCC4] border-t-[#6B4423] rounded-full mx-auto mb-4"></div>
            <p>Memuat data pelanggan...</p>
        </div>
    </div>
</div>

<!-- Add Customer Modal -->
<div id="addCustomerModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full">
        <div class="border-b border-[#E8DCC4] px-6 py-4 flex items-center justify-between rounded-t-3xl">
            <h3 class="text-xl font-bold text-[#3E2723]">Tambah Pelanggan Baru</h3>
            <button onclick="closeAddCustomerModal()" class="p-2 hover:bg-[#FFF8F0] rounded-xl transition-colors">
                <svg class="w-6 h-6 text-[#6B4423]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <form id="addCustomerForm" class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-semibold text-[#6B4423] mb-2">Nama Pelanggan *</label>
                <input 
                    type="text" 
                    id="customerName" 
                    required
                    class="w-full px-4 py-3 bg-[#FFF8F0] border-2 border-[#E8DCC4] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#6B4423]"
                    placeholder="Contoh: Budi Santoso"
                >
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-[#6B4423] mb-2">Nomor Telepon *</label>
                <input 
                    type="tel" 
                    id="customerPhone" 
                    required
                    class="w-full px-4 py-3 bg-[#FFF8F0] border-2 border-[#E8DCC4] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#6B4423]"
                    placeholder="08123456789"
                    pattern="[0-9]{10,13}"
                >
                <p class="text-xs text-[#9BA17B] mt-1">Format: 08xxxxxxxxxx (10-13 digit)</p>
            </div>
            
            <button 
                type="submit" 
                class="w-full py-3 bg-[#6B4423] hover:bg-[#8B5A3C] text-white font-semibold rounded-xl transition-colors"
            >
                Simpan Pelanggan
            </button>
        </form>
    </div>
</div>

<script>
let allCustomers = [];
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
    loadCustomers();
    
    document.getElementById('addCustomerForm').addEventListener('submit', function(e) {
        e.preventDefault();
        addCustomer();
    });
});

async function loadCustomers() {
    try {
        const response = await apiCall(`${apiBase}/kasir/customers`);
        
        if (response && response.ok) {
            const data = await response.json();
            allCustomers = data.data;
            displayCustomers(allCustomers);
        }
    } catch (error) {
        console.error('Error loading customers:', error);
        showError('Gagal memuat data pelanggan');
    }
}

function searchCustomers(query) {
    if (!query.trim()) {
        displayCustomers(allCustomers);
        return;
    }
    
    const filtered = allCustomers.filter(customer => 
        customer.name.toLowerCase().includes(query.toLowerCase()) ||
        customer.phone.includes(query)
    );
    
    displayCustomers(filtered);
}

function displayCustomers(customers) {
    const container = document.getElementById('customersList');
    
    if (customers.length === 0) {
        container.innerHTML = `
            <div class="col-span-full text-center py-12 text-[#9BA17B]">
                <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <p class="text-lg font-medium">Tidak ada pelanggan ditemukan</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = customers.map(customer => `
        <div class="bg-white rounded-2xl shadow-sm border border-[#E8DCC4] overflow-hidden hover:shadow-lg transition-shadow">
            <div class="p-5">
                <!-- Customer Avatar -->
                <div class="flex items-center space-x-4 mb-4">
                    <div class="w-14 h-14 bg-gradient-to-br from-[#6B4423] to-[#8B5A3C] rounded-2xl flex items-center justify-center flex-shrink-0">
                        <span class="text-xl font-bold text-white">${customer.name.charAt(0).toUpperCase()}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="font-bold text-[#3E2723] truncate">${customer.name}</h3>
                        <p class="text-sm text-[#9BA17B] truncate">${customer.phone}</p>
                    </div>
                </div>
                
                <!-- Customer Info -->
                <div class="space-y-2 p-3 bg-[#FFF8F0] rounded-xl">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-[#9BA17B]">Customer ID:</span>
                        <span class="font-semibold text-[#3E2723]">#${customer.id}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-[#9BA17B]">Terdaftar:</span>
                        <span class="font-semibold text-[#3E2723]">${formatDate(customer.created_at)}</span>
                    </div>
                </div>
                
                <!-- Action Button -->
                <button 
                    onclick="selectCustomer(${customer.id})" 
                    class="w-full mt-4 py-2.5 bg-[#FFF8F0] hover:bg-[#6B4423] border-2 border-[#6B4423] text-[#6B4423] hover:text-white font-semibold rounded-xl transition-all"
                >
                    Pilih Pelanggan
                </button>
            </div>
        </div>
    `).join('');
}

function showAddCustomerModal() {
    document.getElementById('addCustomerModal').classList.remove('hidden');
    document.getElementById('customerName').focus();
}

function closeAddCustomerModal() {
    document.getElementById('addCustomerModal').classList.add('hidden');
    document.getElementById('addCustomerForm').reset();
}

async function addCustomer() {
    const name = document.getElementById('customerName').value.trim();
    const phone = document.getElementById('customerPhone').value.trim();
    
    if (!name || !phone) {
        showError('Nama dan nomor telepon harus diisi');
        return;
    }
    
    // Validate phone format
    if (!/^[0-9]{10,13}$/.test(phone)) {
        showError('Format nomor telepon tidak valid (10-13 digit angka)');
        return;
    }
    
    try {
        const response = await apiCall(`${apiBase}/kasir/customers`, {
            method: 'POST',
            body: JSON.stringify({ name, phone })
        });
        
        if (response && response.ok) {
            showSuccess('Pelanggan berhasil ditambahkan');
            closeAddCustomerModal();
            loadCustomers();
        } else {
            const error = response ? await response.json() : {};
            showError(error.message || 'Gagal menambahkan pelanggan');
        }
    } catch (error) {
        console.error('Error adding customer:', error);
        showError('Gagal menambahkan pelanggan');
    }
}

function selectCustomer(customerId) {
    const customer = allCustomers.find(c => c.id === customerId);
    if (customer) {
        // Copy customer info to clipboard for easy use
        const info = `ID: ${customer.id}\nNama: ${customer.name}\nTelepon: ${customer.phone}`;
        
        // Try to copy to clipboard
        if (navigator.clipboard) {
            navigator.clipboard.writeText(customer.id.toString());
            showSuccess(`Customer ID ${customer.id} disalin! Gunakan saat membuat pesanan.`);
        } else {
            showSuccess(`Customer ID: ${customer.id} - ${customer.name}`);
        }
    }
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', { 
        day: '2-digit', 
        month: 'short', 
        year: 'numeric'
    });
}

function showSuccess(message) {
    alert('✓ ' + message);
}

function showError(message) {
    alert('✗ ' + message);
}
</script>
@endsection
