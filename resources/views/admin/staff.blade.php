@extends('layouts.dashboard', ['title' => 'Kelola Staff', 'subtitle' => 'Manajemen akun kasir dan admin'])

@section('sidebar')
@include('admin.partials.sidebar', ['active' => 'staff'])
@endsection

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h3 class="text-lg font-bold text-[#3E2723]">Daftar Staff</h3>
            <p class="text-sm text-[#9BA17B]">Kelola akun admin dan kasir</p>
        </div>
        <button onclick="showAddModal()" class="px-6 py-3 bg-[#6B4423] hover:bg-[#8B5A3C] text-white font-semibold rounded-xl transition-colors flex items-center space-x-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span>Tambah Staff</span>
        </button>
    </div>

    <div id="staffList" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="col-span-full text-center py-12 text-[#9BA17B]">
            <div class="animate-spin w-12 h-12 border-4 border-[#E8DCC4] border-t-[#6B4423] rounded-full mx-auto mb-4"></div>
            <p>Memuat data staff...</p>
        </div>
    </div>
</div>

<!-- Modal -->
<div id="staffModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full">
        <div class="border-b border-[#E8DCC4] px-6 py-4 flex items-center justify-between">
            <h3 class="text-xl font-bold text-[#3E2723]" id="modalTitle">Tambah Staff</h3>
            <button onclick="closeModal()" class="p-2 hover:bg-[#FFF8F0] rounded-xl transition-colors">
                <svg class="w-6 h-6 text-[#6B4423]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <form id="staffForm" class="p-6 space-y-4">
            <input type="hidden" id="staffId">
            <div>
                <label class="block text-sm font-semibold text-[#6B4423] mb-2">Nama Lengkap *</label>
                <input type="text" id="staffName" required class="w-full px-4 py-3 bg-[#FFF8F0] border-2 border-[#E8DCC4] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#6B4423]">
            </div>
            <div>
                <label class="block text-sm font-semibold text-[#6B4423] mb-2">Username *</label>
                <input type="text" id="staffUsername" required class="w-full px-4 py-3 bg-[#FFF8F0] border-2 border-[#E8DCC4] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#6B4423]">
            </div>
            <div>
                <label class="block text-sm font-semibold text-[#6B4423] mb-2">Password <span id="passwordLabel">*</span></label>
                <input type="password" id="staffPassword" class="w-full px-4 py-3 bg-[#FFF8F0] border-2 border-[#E8DCC4] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#6B4423]">
                <p class="text-xs text-[#9BA17B] mt-1">Minimal 6 karakter. <span id="editPasswordHint" class="hidden">Kosongkan jika tidak ingin mengubah password.</span></p>
            </div>
            <div>
                <label class="block text-sm font-semibold text-[#6B4423] mb-2">Role *</label>
                <select id="staffRole" required class="w-full px-4 py-3 bg-[#FFF8F0] border-2 border-[#E8DCC4] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#6B4423]">
                    <option value="kasir">Kasir</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <button type="submit" class="w-full py-3 bg-[#6B4423] hover:bg-[#8B5A3C] text-white font-semibold rounded-xl transition-colors">
                Simpan Staff
            </button>
        </form>
    </div>
</div>

<script>
let staff = [];
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
    loadStaff();
    document.getElementById('staffForm').addEventListener('submit', function(e) {
        e.preventDefault();
        saveStaff();
    });
});

async function loadStaff() {
    try {
        const response = await apiCall(`${apiBase}/admin/staff`);
        if (response && response.ok) {
            const data = await response.json();
            staff = data.data;
            displayStaff();
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

function displayStaff() {
    const container = document.getElementById('staffList');
    if (staff.length === 0) {
        container.innerHTML = '<div class="col-span-full text-center py-12 text-[#9BA17B]"><p>Belum ada staff</p></div>';
        return;
    }
    
    container.innerHTML = staff.map(s => `
        <div class="bg-white rounded-2xl shadow-sm border border-[#E8DCC4] p-5">
            <div class="flex items-center space-x-4 mb-4">
                <div class="w-14 h-14 bg-gradient-to-br from-[#6B4423] to-[#8B5A3C] rounded-2xl flex items-center justify-center">
                    <span class="text-xl font-bold text-white">${s.name.charAt(0).toUpperCase()}</span>
                </div>
                <div class="flex-1">
                    <h3 class="font-bold text-[#3E2723]">${s.name}</h3>
                    <p class="text-sm text-[#9BA17B]">@${s.username}</p>
                </div>
            </div>
            <div class="mb-4 p-3 bg-[#FFF8F0] rounded-xl">
                <span class="inline-block px-3 py-1 rounded-lg text-xs font-semibold ${s.role === 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700'}">
                    ${s.role === 'admin' ? '👑 Admin' : '💼 Kasir'}
                </span>
            </div>
            <div class="flex space-x-2">
                <button onclick="editStaff(${s.id})" class="flex-1 py-2 bg-[#FFF8F0] hover:bg-[#E8DCC4] text-[#6B4423] font-semibold rounded-xl transition-colors">Edit</button>
                <button onclick="deleteStaff(${s.id}, '${s.name}')" class="flex-1 py-2 bg-red-50 hover:bg-red-100 text-red-600 font-semibold rounded-xl transition-colors">Hapus</button>
            </div>
        </div>
    `).join('');
}

function showAddModal() {
    document.getElementById('modalTitle').textContent = 'Tambah Staff';
    document.getElementById('staffForm').reset();
    document.getElementById('staffId').value = '';
    document.getElementById('staffPassword').required = true;
    document.getElementById('passwordLabel').textContent = '*';
    document.getElementById('editPasswordHint').classList.add('hidden');
    document.getElementById('staffModal').classList.remove('hidden');
}

function editStaff(id) {
    const s = staff.find(x => x.id === id);
    if (!s) return;
    
    document.getElementById('modalTitle').textContent = 'Edit Staff';
    document.getElementById('staffId').value = s.id;
    document.getElementById('staffName').value = s.name;
    document.getElementById('staffUsername').value = s.username;
    document.getElementById('staffPassword').value = '';
    document.getElementById('staffPassword').required = false;
    document.getElementById('passwordLabel').textContent = '';
    document.getElementById('editPasswordHint').classList.remove('hidden');
    document.getElementById('staffRole').value = s.role;
    document.getElementById('staffModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('staffModal').classList.add('hidden');
}

async function saveStaff() {
    const id = document.getElementById('staffId').value;
    const data = {
        name: document.getElementById('staffName').value.trim(),
        username: document.getElementById('staffUsername').value.trim(),
        role: document.getElementById('staffRole').value
    };
    
    const password = document.getElementById('staffPassword').value;
    if (password) {
        if (password.length < 6) {
            alert('Password minimal 6 karakter');
            return;
        }
        data.password = password;
    } else if (!id) {
        alert('Password harus diisi untuk staff baru');
        return;
    }
    
    const url = id ? `${apiBase}/admin/staff/${id}` : `${apiBase}/admin/staff`;
    const method = id ? 'PUT' : 'POST';
    
    try {
        const response = await apiCall(url, {
            method: method,
            body: JSON.stringify(data)
        });
        
        if (response && response.ok) {
            alert(`✓ Staff berhasil ${id ? 'diupdate' : 'ditambahkan'}`);
            closeModal();
            loadStaff();
        } else {
            const error = response ? await response.json() : {};
            alert('✗ ' + (error.message || 'Gagal menyimpan staff'));
        }
    } catch (error) {
        alert('✗ Gagal menyimpan staff');
    }
}

async function deleteStaff(id, name) {
    if (!confirm(`Yakin ingin menghapus staff "${name}"?`)) return;
    
    try {
        const response = await apiCall(`${apiBase}/admin/staff/${id}`, {
            method: 'DELETE'
        });
        
        if (response && response.ok) {
            alert('✓ Staff berhasil dihapus');
            loadStaff();
        } else {
            alert('✗ Gagal menghapus staff');
        }
    } catch (error) {
        alert('✗ Gagal menghapus staff');
    }
}
</script>
@endsection
