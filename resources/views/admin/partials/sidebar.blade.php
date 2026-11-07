{{-- Admin Sidebar Navigation --}}
<a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl {{ ($active ?? '') === 'dashboard' ? 'bg-[#6B4423] text-white font-medium' : 'hover:bg-[#4A2F1A] text-[#E8DCC4] hover:text-white' }} transition-colors">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
    </svg>
    <span>Dashboard</span>
</a>

<div class="pt-4 pb-2">
    <p class="px-4 text-xs font-semibold text-[#9BA17B] uppercase tracking-wider">Manajemen</p>
</div>

<a href="{{ route('admin.categories') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl {{ ($active ?? '') === 'categories' ? 'bg-[#6B4423] text-white font-medium' : 'hover:bg-[#4A2F1A] text-[#E8DCC4] hover:text-white' }} transition-colors">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
    </svg>
    <span>Kategori</span>
</a>

<a href="{{ route('admin.menus') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl {{ ($active ?? '') === 'menus' ? 'bg-[#6B4423] text-white font-medium' : 'hover:bg-[#4A2F1A] text-[#E8DCC4] hover:text-white' }} transition-colors">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
    </svg>
    <span>Menu</span>
</a>

<a href="{{ route('admin.staff') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl {{ ($active ?? '') === 'staff' ? 'bg-[#6B4423] text-white font-medium' : 'hover:bg-[#4A2F1A] text-[#E8DCC4] hover:text-white' }} transition-colors">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
    </svg>
    <span>Staff</span>
</a>

<a href="{{ route('admin.customers') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl {{ ($active ?? '') === 'customers' ? 'bg-[#6B4423] text-white font-medium' : 'hover:bg-[#4A2F1A] text-[#E8DCC4] hover:text-white' }} transition-colors">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
    </svg>
    <span>Pelanggan</span>
</a>

<div class="pt-4 pb-2">
    <p class="px-4 text-xs font-semibold text-[#9BA17B] uppercase tracking-wider">Laporan</p>
</div>

<a href="{{ route('admin.reports') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl {{ ($active ?? '') === 'reports' ? 'bg-[#6B4423] text-white font-medium' : 'hover:bg-[#4A2F1A] text-[#E8DCC4] hover:text-white' }} transition-colors">
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
