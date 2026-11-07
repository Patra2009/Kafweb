@extends('layouts.dashboard', ['title' => 'Dashboard Kasir', 'subtitle' => 'Kelola pesanan dan pembayaran'])

@section('sidebar')
<a href="{{ route('kasir.dashboard') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl bg-[#6B4423] text-white font-medium">
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

<a href="{{ route('kasir.customers') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl hover:bg-[#4A2F1A] text-[#E8DCC4] hover:text-white transition-colors">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
    </svg>
    <span>Pelanggan</span>
</a>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Pending Orders -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-[#E8DCC4]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-[#9BA17B] font-medium">Pesanan Pending</p>
                    <p class="text-3xl font-bold text-[#3E2723] mt-2" id="pendingCount">-</p>
                </div>
                <div class="w-12 h-12 bg-[#FFF8F0] rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-[#C2674A]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Processing Orders -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-[#E8DCC4]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-[#9BA17B] font-medium">Sedang Diproses</p>
                    <p class="text-3xl font-bold text-[#3E2723] mt-2" id="processingCount">-</p>
                </div>
                <div class="w-12 h-12 bg-[#FFF8F0] rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-[#6B4423]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Ready Orders -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-[#E8DCC4]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-[#9BA17B] font-medium">Siap Diambil</p>
                    <p class="text-3xl font-bold text-[#3E2723] mt-2" id="readyCount">-</p>
                </div>
                <div class="w-12 h-12 bg-[#FFF8F0] rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-[#9BA17B]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Today's Revenue -->
        <div class="bg-gradient-to-br from-[#6B4423] to-[#8B5A3C] rounded-2xl p-6 shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-[#E8DCC4] font-medium">Pendapatan Hari Ini</p>
                    <p class="text-3xl font-bold text-white mt-2" id="todayRevenue">Rp 0</p>
                </div>
                <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="bg-white rounded-2xl shadow-sm border border-[#E8DCC4] overflow-hidden">
        <div class="px-6 py-4 border-b border-[#E8DCC4] flex items-center justify-between">
            <h3 class="text-lg font-bold text-[#3E2723]">Pesanan Terbaru</h3>
            <a href="{{ route('kasir.orders') }}" class="text-sm text-[#6B4423] hover:text-[#3E2723] font-medium">
                Lihat Semua →
            </a>
        </div>
        <div class="p-6">
            <div id="recentOrders" class="space-y-4">
                <div class="text-center py-8 text-[#9BA17B]">
                    <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <p>Memuat data pesanan...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', async function() {
    const apiBase = '/api/v1';
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    // Helper function for API calls with CSRF
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
        // Fetch order statistics
        const statsResponse = await apiCall(`${apiBase}/kasir/orders`);
        
        if (statsResponse && statsResponse.ok) {
            const statsData = await statsResponse.json();
            
            // Count orders by status
            const pending = statsData.data.filter(o => o.order_status === 'pending').length;
            const processing = statsData.data.filter(o => o.order_status === 'processing').length;
            const ready = statsData.data.filter(o => o.order_status === 'ready').length;
            
            document.getElementById('pendingCount').textContent = pending;
            document.getElementById('processingCount').textContent = processing;
            document.getElementById('readyCount').textContent = ready;
            
            // Calculate today's revenue from paid orders
            const todayRevenue = statsData.data
                .filter(o => o.payment_status === 'paid')
                .reduce((sum, o) => sum + parseFloat(o.total_price), 0);
            
            document.getElementById('todayRevenue').textContent = 
                'Rp ' + new Intl.NumberFormat('id-ID').format(todayRevenue);
            
            // Display recent orders (limit 5)
            displayRecentOrders(statsData.data.slice(0, 5));
        }
    } catch (error) {
        console.error('Error loading dashboard data:', error);
    }
});

function displayRecentOrders(orders) {
    const container = document.getElementById('recentOrders');
    
    if (orders.length === 0) {
        container.innerHTML = `
            <div class="text-center py-8 text-[#9BA17B]">
                <p>Belum ada pesanan hari ini</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = orders.map(order => `
        <div class="flex items-center justify-between p-4 bg-[#FFF8F0] rounded-xl border border-[#E8DCC4] hover:shadow-md transition-shadow">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center border border-[#E8DCC4]">
                    <span class="text-sm font-bold text-[#6B4423]">#${order.id}</span>
                </div>
                <div>
                    <p class="font-semibold text-[#3E2723]">${order.table_number || 'Walk-in'}</p>
                    <p class="text-sm text-[#9BA17B]">${order.items?.length || 0} item(s)</p>
                </div>
            </div>
            <div class="text-right">
                <p class="font-bold text-[#3E2723]">Rp ${new Intl.NumberFormat('id-ID').format(order.total_price)}</p>
                <span class="inline-block mt-1 px-3 py-1 rounded-lg text-xs font-semibold ${getStatusStyle(order.order_status)}">
                    ${getStatusLabel(order.order_status)}
                </span>
            </div>
        </div>
    `).join('');
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
</script>
@endsection
