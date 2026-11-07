@extends('layouts.dashboard', ['title' => 'Kelola Kategori', 'subtitle' => 'Manajemen kategori menu'])

@section('sidebar')
<a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl hover:bg-[#4A2F1A] text-[#E8DCC4] hover:text-white transition-colors">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
    </svg>
    <span>Dashboard</span>
</a>

<div class="pt-4 pb-2">
    <p class="px-4 text-xs font-semibold text-[#9BA17B] uppercase tracking-wider">Manajemen</p>
</div>

<a href="{{ route('admin.categories') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl bg-[#6B4423] text-white font-medium">
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
    <!-- Add Category Button -->
    <div class="flex justify-between items-center">
        <div>
            <h3 class="text-lg font-bold text-[#3E2723]">Daftar Kategori</h3>
            <p class="text-sm text-[#9BA17B]">Kelola kategori untuk mengelompokkan menu</p>
        </div>
        <button onclick="showAddModal()" class="px-6 py-3 bg-[#6B4423] hover:bg-[#8B5A3C] text-white font-semibold rounded-xl transition-colors flex items-center space-x-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span>Tambah Kategori</span>
        </button>
    </div>

    <!-- Categories Grid -->
    <div id="categoriesList" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="col-span-full text-center py-12 text-[#9BA17B]">
            <div class="animate-spin w-12 h-12 border-4 border-[#E8DCC4] border-t-[#6B4423] rounded-full mx-auto mb-4"></div>
            <p>Memuat data kategori...</p>
        </div>
    </div>
</div>

<!-- Add/Edit Modal -->
<div id="categoryModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full">
        <div class="border-b border-[#E8DCC4] px-6 py-4 flex items-center justify-between">
            <h3 class="text-xl font-bold text-[#3E2723]" id="modalTitle">Tambah Kategori</h3>
            <button onclick="closeModal()" class="p-2 hover:bg-[#FFF8F0] rounded-xl transition-colors">
                <svg class="w-6 h-6 text-[#6B4423]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <form id="categoryForm" class="p-6 space-y-4">
            <input type="hidden" id="categoryId">
            <div>
                <label class="block text-sm font-semibold text-[#6B4423] mb-2">Nama Kategori *</label>
                <input 
                    type="text" 
                    id="categoryName" 
                    required
                    class="w-full px-4 py-3 bg-[#FFF8F0] border-2 border-[#E8DCC4] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#6B4423]"
                    placeholder="Contoh: Minuman Panas"
                >
            </div>
            
            <button type="submit" class="w-full py-3 bg-[#6B4423] hover:bg-[#8B5A3C] text-white font-semibold rounded-xl transition-colors">
                Simpan Kategori
            </button>
        </form>
    </div>
</div>

<script>
let categories = [];
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
    loadCategories();
    
    document.getElementById('categoryForm').addEventListener('submit', function(e) {
        e.preventDefault();
        saveCategory();
    });
});

async function loadCategories() {
    try {
        const response = await apiCall(`${apiBase}/admin/categories`);
        
        if (response && response.ok) {
            const data = await response.json();
            categories = data.data;
            displayCategories();
        }
    } catch (error) {
        console.error('Error loading categories:', error);
        alert('Gagal memuat data kategori');
    }
}

function displayCategories() {
    const container = document.getElementById('categoriesList');
    
    if (categories.length === 0) {
        container.innerHTML = `
            <div class="col-span-full text-center py-12 text-[#9BA17B]">
                <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
                <p class="text-lg font-medium">Belum ada kategori</p>
                <button onclick="showAddModal()" class="mt-4 text-[#6B4423] font-semibold hover:underline">
                    + Tambah kategori pertama
                </button>
            </div>
        `;
        return;
    }
    
    container.innerHTML = categories.map(category => `
        <div class="bg-white rounded-2xl shadow-sm border border-[#E8DCC4] overflow-hidden hover:shadow-lg transition-shadow">
            <div class="p-6">
                <div class="w-16 h-16 bg-gradient-to-br from-[#6B4423] to-[#8B5A3C] rounded-2xl flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                </div>
                
                <h3 class="text-lg font-bold text-[#3E2723] mb-2">${category.name}</h3>
                <p class="text-sm text-[#9BA17B] mb-4">ID: #${category.id}</p>
                
                <div class="flex space-x-2">
                    <button onclick="editCategory(${category.id})" class="flex-1 py-2 bg-[#FFF8F0] hover:bg-[#E8DCC4] text-[#6B4423] font-semibold rounded-xl transition-colors">
                        Edit
                    </button>
                    <button onclick="deleteCategory(${category.id}, '${category.name}')" class="flex-1 py-2 bg-red-50 hover:bg-red-100 text-red-600 font-semibold rounded-xl transition-colors">
                        Hapus
                    </button>
                </div>
            </div>
        </div>
    `).join('');
}

function showAddModal() {
    document.getElementById('modalTitle').textContent = 'Tambah Kategori';
    document.getElementById('categoryId').value = '';
    document.getElementById('categoryName').value = '';
    document.getElementById('categoryModal').classList.remove('hidden');
    document.getElementById('categoryName').focus();
}

function editCategory(id) {
    const category = categories.find(c => c.id === id);
    if (!category) return;
    
    document.getElementById('modalTitle').textContent = 'Edit Kategori';
    document.getElementById('categoryId').value = category.id;
    document.getElementById('categoryName').value = category.name;
    document.getElementById('categoryModal').classList.remove('hidden');
    document.getElementById('categoryName').focus();
}

function closeModal() {
    document.getElementById('categoryModal').classList.add('hidden');
    document.getElementById('categoryForm').reset();
}

async function saveCategory() {
    const id = document.getElementById('categoryId').value;
    const name = document.getElementById('categoryName').value.trim();
    
    if (!name) {
        alert('Nama kategori harus diisi');
        return;
    }
    
    const url = id ? `${apiBase}/admin/categories/${id}` : `${apiBase}/admin/categories`;
    const method = id ? 'PUT' : 'POST';
    
    try {
        const response = await apiCall(url, {
            method: method,
            body: JSON.stringify({ name })
        });
        
        if (response && response.ok) {
            alert(`✓ Kategori berhasil ${id ? 'diupdate' : 'ditambahkan'}`);
            closeModal();
            loadCategories();
        } else {
            const error = response ? await response.json() : {};
            alert('✗ ' + (error.message || 'Gagal menyimpan kategori'));
        }
    } catch (error) {
        console.error('Error saving category:', error);
        alert('✗ Gagal menyimpan kategori');
    }
}

async function deleteCategory(id, name) {
    if (!confirm(`Yakin ingin menghapus kategori "${name}"?\n\nMenu dengan kategori ini akan kehilangan kategorinya.`)) {
        return;
    }
    
    try {
        const response = await apiCall(`${apiBase}/admin/categories/${id}`, {
            method: 'DELETE'
        });
        
        if (response && response.ok) {
            alert('✓ Kategori berhasil dihapus');
            loadCategories();
        } else {
            const error = response ? await response.json() : {};
            alert('✗ ' + (error.message || 'Gagal menghapus kategori'));
        }
    } catch (error) {
        console.error('Error deleting category:', error);
        alert('✗ Gagal menghapus kategori');
    }
}
</script>
@endsection
