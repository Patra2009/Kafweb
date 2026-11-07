@extends('layouts.dashboard', ['title' => 'Dashboard Admin', 'subtitle' => 'Kelola sistem kafe secara menyeluruh'])

@section('sidebar')
<a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl bg-[#6B4423] text-white font-medium">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
    </svg>
    <span>Dashboard</span>
</a>

<div class="pt-4 pb-2">
    <p class="px-4 text-xs font-semibold text-[#9BA17B] uppercase tracking-wider">Manajemen</p>
</div>

<a href="{{ route('admin.categories') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl hover:bg-[#4A2F1A] text-[#E8DCC4] hover:text-white transition-colors">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
    </svg>
    <span>Kategori</span>
</a>

<a href="{{ route('admin.menus') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl hover:bg-[#4A2F1A] text-[#E8DCC4] hover:text-white transition-colors">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
    </svg>
    <span>Menu</span>
</a>

<a href="{{ route('admin.staff') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl hover:bg-[#4A2F1A] text-[#E8DCC4] hover:text-white transition-colors">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
    </svg>
    <span>Staff</span>
</a>

<a href="{{ route('admin.customers') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl hover:bg-[#4A2F1A] text-[#E8DCC4] hover:text-white transition-colors">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
    </svg>
    <span>Pelanggan</span>
</a>

<div class="pt-4 pb-2">
    <p class="px-4 text-xs font-semibold text-[#9BA17B] uppercase tracking-wider">Laporan</p>
</div>

<a href="{{ route('admin.reports') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl hover:bg-[#4A2F1A] text-[#E8DCC4] hover:text-white transition-colors">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
    </svg>
    <span>Laporan Keuangan</span>
</a>

<div class="pt-4 pb-2">
    <p class="px-4 text-xs font-semibold text-[#9BA17B] uppercase tracking-wider">Akses Kasir</p>
</div>

<a href="{{ route('kasir.orders') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl hover:bg-[#4A2F1A] text-[#E8DCC4] hover:text-white transition-colors">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
    </svg>
    <span>Kelola Pesanan</span>
</a>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Welcome Banner -->
    <div class="bg-gradient-to-r from-[#6B4423] to-[#8B5A3C] rounded-2xl p-6 text-white shadow-lg">
        <h2 class="text-2xl font-bold mb-2">Selamat Datang, Admin! ☕</h2>
        <p class="text-[#E8DCC4]">Kelola seluruh sistem KafWeb dari sini</p>
    </div>

    <!-- Key Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Revenue Today -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-[#E8DCC4]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-[#9BA17B] font-medium">Pendapatan Hari Ini</p>
                    <p class="text-3xl font-bold text-[#3E2723] mt-2" id="todayRevenue">Rp 0</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Orders Today -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-[#E8DCC4]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-[#9BA17B] font-medium">Pesanan Hari Ini</p>
                    <p class="text-3xl font-bold text-[#3E2723] mt-2" id="todayOrders">0</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Menus -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-[#E8DCC4]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-[#9BA17B] font-medium">Total Menu</p>
                    <p class="text-3xl font-bold text-[#3E2723] mt-2" id="totalMenus">0</p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Customers -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-[#E8DCC4]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-[#9BA17B] font-medium">Total Pelanggan</p>
                    <p class="text-3xl font-bold text-[#3E2723] mt-2" id="totalCustomers">0</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <a href="{{ route('admin.menus') }}" class="bg-white rounded-2xl p-6 shadow-sm border border-[#E8DCC4] hover:shadow-lg transition-all group">
            <div class="flex items-center space-x-4">
                <div class="w-14 h-14 bg-[#FFF8F0] group-hover:bg-[#6B4423] rounded-2xl flex items-center justify-center transition-colors">
                    <svg class="w-7 h-7 text-[#6B4423] group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-[#3E2723] group-hover:text-[#6B4423] transition-colors">Tambah Menu</h3>
                    <p class="text-sm text-[#9BA17B]">Kelola menu makanan & minuman</p>
                </div>
            </div>
        </a>

        <a href="{{ route('admin.staff') }}" class="bg-white rounded-2xl p-6 shadow-sm border border-[#E8DCC4] hover:shadow-lg transition-all group">
            <div class="flex items-center space-x-4">
                <div class="w-14 h-14 bg-[#FFF8F0] group-hover:bg-[#6B4423] rounded-2xl flex items-center justify-center transition-colors">
                    <svg class="w-7 h-7 text-[#6B4423] group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-[#3E2723] group-hover:text-[#6B4423] transition-colors">Kelola Staff</h3>
                    <p class="text-sm text-[#9BA17B]">Manajemen akun kasir & admin</p>
                </div>
            </div>
        </a>

        <a href="{{ route('admin.reports') }}" class="bg-white rounded-2xl p-6 shadow-sm border border-[#E8DCC4] hover:shadow-lg transition-all group">
            <div class="flex items-center space-x-4">
                <div class="w-14 h-14 bg-[#FFF8F0] group-hover:bg-[#6B4423] rounded-2xl flex items-center justify-center transition-colors">
                    <svg class="w-7 h-7 text-[#6B4423] group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-[#3E2723] group-hover:text-[#6B4423] transition-colors">Lihat Laporan</h3>
                    <p class="text-sm text-[#9BA17B]">Analisis penjualan & keuangan</p>
                </div>
            </div>
        </a>
    </div>

    <!-- Recent Activity -->
    <div class="bg-white rounded-2xl shadow-sm border border-[#E8DCC4] overflow-hidden">
        <div class="px-6 py-4 border-b border-[#E8DCC4]">
            <h3 class="text-lg font-bold text-[#3E2723]">Aktivitas Terbaru</h3>
        </div>
        <div class="p-6">
            <div id="recentActivity" class="space-y-3">
                <div class="text-center py-8 text-[#9BA17B]">
                    <p>Memuat aktivitas...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', async function() {
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

    try {
        // Load summary report
        const summaryResponse = await apiCall(`${apiBase}/admin/reports/summary`);
        
        if (summaryResponse && summaryResponse.ok) {
            const summary = await summaryResponse.json();
            document.getElementById('todayRevenue').textContent = 
                'Rp ' + new Intl.NumberFormat('id-ID').format(summary.data.total_revenue || 0);
            document.getElementById('todayOrders').textContent = summary.data.total_orders || 0;
        }

        // Load menus count
        const menusResponse = await fetch(`${apiBase}/menus`, {
            headers: { 'Accept': 'application/json' }
        });
        if (menusResponse.ok) {
            const menus = await menusResponse.json();
            document.getElementById('totalMenus').textContent = menus.data.length;
        }

        // Load customers count (admin endpoint)
        const customersResponse = await apiCall(`${apiBase}/admin/customers`);
        if (customersResponse && customersResponse.ok) {
            const customers = await customersResponse.json();
            document.getElementById('totalCustomers').textContent = customers.data.length;
        }

        // Load recent orders as activity
        const ordersResponse = await apiCall(`${apiBase}/kasir/orders`);
        
        if (ordersResponse && ordersResponse.ok) {
            const orders = await ordersResponse.json();
            displayRecentActivity(orders.data.slice(0, 5));
        }
        
    } catch (error) {
        console.error('Error loading dashboard data:', error);
    }
});

function displayRecentActivity(orders) {
    const container = document.getElementById('recentActivity');
    
    if (orders.length === 0) {
        container.innerHTML = '<p class="text-center py-4 text-[#9BA17B]">Belum ada aktivitas hari ini</p>';
        return;
    }
    
    container.innerHTML = orders.map(order => `
        <div class="flex items-center justify-between p-3 bg-[#FFF8F0] rounded-xl">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center border border-[#E8DCC4]">
                    <span class="text-xs font-bold text-[#6B4423]">#${order.id}</span>
                </div>
                <div>
                    <p class="text-sm font-semibold text-[#3E2723]">Pesanan ${order.table_number || 'Walk-in'}</p>
                    <p class="text-xs text-[#9BA17B]">${formatTime(order.created_at)}</p>
                </div>
            </div>
            <div class="text-right">
                <p class="text-sm font-bold text-[#6B4423]">Rp ${new Intl.NumberFormat('id-ID').format(order.total_price)}</p>
                <span class="inline-block px-2 py-1 rounded-lg text-xs font-semibold ${getStatusStyle(order.order_status)}">
                    ${getStatusLabel(order.order_status)}
                </span>
            </div>
        </div>
    `).join('');
}

function formatTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString('id-ID', { 
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
        'pending': 'Pending',
        'processing': 'Diproses',
        'ready': 'Siap',
        'completed': 'Selesai',
        'cancelled': 'Batal'
    };
    return labels[status] || status;
}
</script>
@endsection
