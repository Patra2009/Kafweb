@extends('layouts.dashboard', ['title' => 'Kelola Pelanggan', 'subtitle' => 'Manajemen data pelanggan'])

@section('sidebar')
@include('admin.partials.sidebar', ['active' => 'customers'])
@endsection

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-2xl p-4 shadow-sm border border-[#E8DCC4]">
        <input type="text" id="searchInput" placeholder="Cari pelanggan..." class="w-full px-4 py-3 bg-[#FFF8F0] border-2 border-[#E8DCC4] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#6B4423]" oninput="searchCustomers(this.value)">
    </div>

    <div id="customersList" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="col-span-full text-center py-12 text-[#9BA17B]">
            <div class="animate-spin w-12 h-12 border-4 border-[#E8DCC4] border-t-[#6B4423] rounded-full mx-auto mb-4"></div>
            <p>Memuat data pelanggan...</p>
        </div>
    </div>
</div>

<script>
let customers = [];
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

document.addEventListener('DOMContentLoaded', loadCustomers);

async function loadCustomers() {
    try {
        const response = await apiCall(`${apiBase}/admin/customers`);
        if (response && response.ok) {
            const data = await response.json();
            customers = data.data;
            displayCustomers(customers);
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

function searchCustomers(query) {
    const filtered = customers.filter(c => 
        c.name.toLowerCase().includes(query.toLowerCase()) || c.phone.includes(query)
    );
    displayCustomers(filtered);
}

function displayCustomers(list) {
    const container = document.getElementById('customersList');
    if (list.length === 0) {
        container.innerHTML = '<div class="col-span-full text-center py-12 text-[#9BA17B]"><p>Tidak ada pelanggan ditemukan</p></div>';
        return;
    }
    
    container.innerHTML = list.map(c => `
        <div class="bg-white rounded-2xl shadow-sm border border-[#E8DCC4] p-5">
            <div class="flex items-center space-x-4 mb-4">
                <div class="w-14 h-14 bg-gradient-to-br from-[#6B4423] to-[#8B5A3C] rounded-2xl flex items-center justify-center">
                    <span class="text-xl font-bold text-white">${c.name.charAt(0).toUpperCase()}</span>
                </div>
                <div class="flex-1">
                    <h3 class="font-bold text-[#3E2723]">${c.name}</h3>
                    <p class="text-sm text-[#9BA17B]">${c.phone}</p>
                </div>
            </div>
            <div class="p-3 bg-[#FFF8F0] rounded-xl text-sm">
                <div class="flex justify-between mb-1">
                    <span class="text-[#9BA17B]">Customer ID:</span>
                    <span class="font-semibold text-[#3E2723]">#${c.id}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-[#9BA17B]">Terdaftar:</span>
                    <span class="font-semibold text-[#3E2723]">${new Date(c.created_at).toLocaleDateString('id-ID')}</span>
                </div>
            </div>
        </div>
    `).join('');
}
</script>
@endsection
