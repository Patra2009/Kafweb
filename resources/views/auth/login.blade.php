@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-[#FFF8F0] via-[#E8DCC4] to-[#D4C5A9] px-4">
    <div class="w-full max-w-md">
        <!-- Logo & Welcome -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-[#6B4423] rounded-3xl shadow-lg mb-4">
                <svg class="w-10 h-10 text-[#FFF8F0]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-[#3E2723] mb-2">KafWeb</h1>
            <p class="text-[#6B4423] text-sm">Masuk ke Dashboard Staff</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white rounded-3xl shadow-xl p-8 border border-[#E8DCC4]">
            @if($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-2xl">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"/>
                        </svg>
                        <span class="text-red-700 text-sm font-medium">{{ $errors->first() }}</span>
                    </div>
                </div>
            @endif

            @if(session('success'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-2xl">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                        </svg>
                        <span class="text-green-700 text-sm font-medium">{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf

                <!-- Username -->
                <div>
                    <label for="username" class="block text-sm font-semibold text-[#6B4423] mb-2">
                        Username
                    </label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        value="{{ old('username') }}"
                        required 
                        autofocus
                        class="w-full px-4 py-3 bg-[#FFF8F0] border-2 border-[#E8DCC4] rounded-2xl focus:outline-none focus:ring-2 focus:ring-[#6B4423] focus:border-transparent transition-all text-[#3E2723] placeholder-[#9BA17B]"
                        placeholder="Masukkan username"
                    >
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-semibold text-[#6B4423] mb-2">
                        Password
                    </label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required
                        class="w-full px-4 py-3 bg-[#FFF8F0] border-2 border-[#E8DCC4] rounded-2xl focus:outline-none focus:ring-2 focus:ring-[#6B4423] focus:border-transparent transition-all text-[#3E2723] placeholder-[#9BA17B]"
                        placeholder="Masukkan password"
                    >
                </div>

                <!-- Login Button -->
                <button 
                    type="submit" 
                    class="w-full py-3.5 bg-gradient-to-r from-[#6B4423] to-[#8B5A3C] text-white font-semibold rounded-2xl shadow-lg hover:shadow-xl hover:from-[#8B5A3C] hover:to-[#6B4423] transition-all duration-300 transform hover:-translate-y-0.5"
                >
                    Masuk Dashboard
                </button>
            </form>

            <!-- Demo Credentials -->
            <div class="mt-6 pt-6 border-t border-[#E8DCC4]">
                <p class="text-xs text-[#9BA17B] text-center mb-3 font-medium">Akun Demo:</p>
                <div class="grid grid-cols-2 gap-3">
                    <div class="p-3 bg-[#FFF8F0] rounded-xl border border-[#E8DCC4]">
                        <p class="text-xs font-bold text-[#6B4423]">Admin</p>
                        <p class="text-xs text-[#9BA17B] mt-1">admin / admin123</p>
                    </div>
                    <div class="p-3 bg-[#FFF8F0] rounded-xl border border-[#E8DCC4]">
                        <p class="text-xs font-bold text-[#6B4423]">Kasir</p>
                        <p class="text-xs text-[#9BA17B] mt-1">kasir / kasir123</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Back to Customer Portal -->
        <div class="text-center mt-6">
            <a href="{{ route('home') }}" class="text-sm text-[#6B4423] hover:text-[#3E2723] font-medium inline-flex items-center transition-colors">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali ke Portal Pelanggan
            </a>
        </div>
    </div>
</div>
@endsection
