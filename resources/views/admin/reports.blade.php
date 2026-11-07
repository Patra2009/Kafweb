@extends('layouts.dashboard', ['title' => 'Laporan Keuangan', 'subtitle' => 'Analisis penjualan dan pendapatan'])

@section('sidebar')
@include('admin.partials.sidebar', ['active' => 'reports'])
@endsection

@section('content')
<div class="space-y-6">
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-[#E8DCC4]">
            <p class="text-sm text-[#9BA17B] font-medium mb-2">Total Pendapatan</p>
            <p class="text-3xl font-bold text-[#3E2723]" id="totalRevenue">Rp 0</p>
        </div>
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-[#E8DCC4]">
            <p class="text-sm text-[#9BA17B] font-medium mb-2">Total Pesanan</p>
            <p class="text-3xl font-bold text-[#3E2723]" id="totalOrders">0</p>
        </div>
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-[#E8DCC4]">
            <p class="text-sm text-[#9BA17B] font-medium mb-2">Rata-rata Transaksi</p>
            <p class="text-3xl font-bold text-[#3E2723]" id="avgTransaction">Rp 0</p>
        </div>
    </div>

    <!-- Date Filter -->
    <div class="bg-white rounded-2xl p-4 shadow-sm border border-[#E8DCC4]">
        <div class="flex flex-wrap gap-3">
            <input type="date" id="filterDate" class="px-4 py-2 bg-[#FFF8F0] border-2 border-[#E8DCC4] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#6B4423]">
            <button onclick="loadDailyReport()" class="px-6 py-2 bg-[#6B4423] hover:bg-[#8B5A3C] text-white font-semibold rounded-xl transition-colors">
                Lihat Laporan Harian
            </button>
        </div>
    </div>

    <!-- Top Selling Items -->
    <div class="bg-white rounded-2xl shadow-sm border border-[#E8DCC4] overflow-hidden">
        <div class="px-6 py-4 border-b border-[#E8DCC4]">
            <h3 class="text-lg font-bold text-[#3E2723]">Menu Terlaris</h3>
        </div>
        <div class="p-6">
            <div id="topItems" class="space-y-3">
                <p class="text-center text-[#9BA17B]">Memuat data...</p>
            </div>
        </div>
    </div>

    <!-- Daily Report -->
    <div id="dailyReportSection" class="bg-white rounded-2xl shadow-sm border border-[#E8DCC4] overflow-hidden hidden">
        <div class="px-6 py-4 border-b border-[#E8DCC4]">
            <h3 class="text-lg font-bold text-[#3E2723]">Laporan Harian</h3>
        </div>
        <div class="p-6">
            <div id="dailyReport"></div>
        </div>
    </div>
</div>

<script>
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

document.addEventListener('DOMContentLoaded', async function() {
    // Set today's date
    document.getElementById('filterDate').valueAsDate = new Date();
    
    // Load reports
    await loadSummary();
    await loadTopItems();
});

async function loadSummary() {
    try {
        const response = await apiCall(`${apiBase}/admin/reports/summary`);
        if (response && response.ok) {
            const data = await response.json();
            document.getElementById('totalRevenue').textContent = 
                'Rp ' + new Intl.NumberFormat('id-ID').format(data.data.total_revenue || 0);
            document.getElementById('totalOrders').textContent = data.data.total_orders || 0;
            
            const avg = data.data.total_orders > 0 
                ? data.data.total_revenue / data.data.total_orders 
                : 0;
            document.getElementById('avgTransaction').textContent = 
                'Rp ' + new Intl.NumberFormat('id-ID').format(avg);
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

async function loadTopItems() {
    try {
        const response = await apiCall(`${apiBase}/admin/reports/top-items`);
        if (response && response.ok) {
            const data = await response.json();
            const container = document.getElementById('topItems');
            
            if (data.data.length === 0) {
                container.innerHTML = '<p class="text-center text-[#9BA17B]">Belum ada data penjualan</p>';
                return;
            }
            
            container.innerHTML = data.data.map((item, index) => `
                <div class="flex items-center justify-between p-4 bg-[#FFF8F0] rounded-xl">
                    <div class="flex items-center space-x-4">
                        <div class="w-10 h-10 bg-gradient-to-br from-[#6B4423] to-[#8B5A3C] rounded-xl flex items-center justify-center">
                            <span class="font-bold text-white">${index + 1}</span>
                        </div>
                        <div>
                            <p class="font-semibold text-[#3E2723]">${item.menu_name}</p>
                            <p class="text-sm text-[#9BA17B]">${item.total_sold} terjual</p>
                        </div>
                    </div>
                    <p class="font-bold text-[#6B4423]">Rp ${new Intl.NumberFormat('id-ID').format(item.total_revenue)}</p>
                </div>
            `).join('');
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

async function loadDailyReport() {
    const date = document.getElementById('filterDate').value;
    if (!date) {
        alert('Pilih tanggal terlebih dahulu');
        return;
    }
    
    try {
        const response = await apiCall(`${apiBase}/admin/reports/daily?date=${date}`);
        if (response && response.ok) {
            const data = await response.json();
            const section = document.getElementById('dailyReportSection');
            const container = document.getElementById('dailyReport');
            
            section.classList.remove('hidden');
            
            container.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="p-4 bg-[#FFF8F0] rounded-xl">
                        <p class="text-sm text-[#9BA17B] mb-1">Pendapatan</p>
                        <p class="text-2xl font-bold text-[#6B4423]">Rp ${new Intl.NumberFormat('id-ID').format(data.data.total_revenue || 0)}</p>
                    </div>
                    <div class="p-4 bg-[#FFF8F0] rounded-xl">
                        <p class="text-sm text-[#9BA17B] mb-1">Pesanan</p>
                        <p class="text-2xl font-bold text-[#3E2723]">${data.data.total_orders || 0}</p>
                    </div>
                    <div class="p-4 bg-[#FFF8F0] rounded-xl">
                        <p class="text-sm text-[#9BA17B] mb-1">Pesanan Selesai</p>
                        <p class="text-2xl font-bold text-green-600">${data.data.completed_orders || 0}</p>
                    </div>
                </div>
                <p class="text-sm text-[#9BA17B]">Laporan untuk tanggal: ${new Date(date).toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}</p>
            `;
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Gagal memuat laporan harian');
    }
}
</script>
@endsection
