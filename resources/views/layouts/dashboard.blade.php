<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="api-base" content="/api/v1">
    <title>{{ $title ?? 'Dashboard' }} - KafWeb</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        :root {
            --cream: #FFF8F0;
            --warm-beige: #E8DCC4;
            --coffee-brown: #6B4423;
            --dark-wood: #3E2723;
            --terracotta: #C2674A;
            --sage-green: #9BA17B;
            --soft-shadow: rgba(107, 68, 35, 0.08);
        }
        
        body {
            background-color: var(--cream);
            color: var(--dark-wood);
        }
    </style>
</head>
<body class="antialiased">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-64 bg-gradient-to-b from-[#3E2723] to-[#2C1810] text-white flex-shrink-0 hidden md:block">
            <div class="h-full flex flex-col">
                <!-- Logo -->
                <div class="p-6 border-b border-[#6B4423]">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-[#6B4423] rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-[#FFF8F0]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                            </svg>
                        </div>
                        <div>
                            <h1 class="font-bold text-lg">KafWeb</h1>
                            <p class="text-xs text-[#9BA17B]">{{ auth()->user()->role->label() }}</p>
                        </div>
                    </div>
                </div>

                <!-- Navigation -->
                <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
                    @yield('sidebar')
                </nav>

                <!-- User Profile & Logout -->
                <div class="p-4 border-t border-[#6B4423]">
                    <div class="flex items-center space-x-3 mb-3">
                        <div class="w-10 h-10 bg-[#6B4423] rounded-xl flex items-center justify-center">
                            <span class="text-sm font-bold">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold truncate">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-[#9BA17B] truncate">{{ auth()->user()->username }}</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full py-2 bg-[#C2674A] hover:bg-[#A5573D] rounded-xl text-sm font-medium transition-colors duration-200 flex items-center justify-center space-x-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            <span>Keluar</span>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Bar (Mobile) -->
            <header class="bg-white border-b border-[#E8DCC4] px-4 py-3 md:px-6 md:py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-[#3E2723]">{{ $title ?? 'Dashboard' }}</h2>
                        <p class="text-sm text-[#9BA17B]">{{ $subtitle ?? '' }}</p>
                    </div>
                    
                    <!-- Mobile Menu Button -->
                    <button id="mobileMenuBtn" class="md:hidden p-2 rounded-xl bg-[#FFF8F0] text-[#6B4423]">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                </div>
            </header>

            <!-- Page Content -->
            <div class="flex-1 overflow-y-auto p-4 md:p-6">
                @if(session('success'))
                    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-2xl flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                        </svg>
                        <span class="text-green-700 text-sm font-medium">{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-2xl flex items-center">
                        <svg class="w-5 h-5 text-red-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"/>
                        </svg>
                        <span class="text-red-700 text-sm font-medium">{{ session('error') }}</span>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    <!-- Mobile Sidebar Overlay -->
    <div id="mobileSidebar" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden md:hidden">
        <div class="w-64 h-full bg-gradient-to-b from-[#3E2723] to-[#2C1810] text-white">
            <div class="h-full flex flex-col">
                <div class="p-6 border-b border-[#6B4423] flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-[#6B4423] rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-[#FFF8F0]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                            </svg>
                        </div>
                        <div>
                            <h1 class="font-bold text-lg">KafWeb</h1>
                            <p class="text-xs text-[#9BA17B]">{{ auth()->user()->role->label() }}</p>
                        </div>
                    </div>
                    <button id="closeMobileMenu" class="p-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
                    @yield('sidebar')
                </nav>
                <div class="p-4 border-t border-[#6B4423]">
                    <div class="flex items-center space-x-3 mb-3">
                        <div class="w-10 h-10 bg-[#6B4423] rounded-xl flex items-center justify-center">
                            <span class="text-sm font-bold">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</span>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-semibold">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-[#9BA17B]">{{ auth()->user()->username }}</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full py-2 bg-[#C2674A] hover:bg-[#A5573D] rounded-xl text-sm font-medium transition-colors duration-200 flex items-center justify-center space-x-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            <span>Keluar</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Mobile menu toggle
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const mobileSidebar = document.getElementById('mobileSidebar');
        const closeMobileMenu = document.getElementById('closeMobileMenu');

        if (mobileMenuBtn) {
            mobileMenuBtn.addEventListener('click', () => {
                mobileSidebar.classList.remove('hidden');
            });
        }

        if (closeMobileMenu) {
            closeMobileMenu.addEventListener('click', () => {
                mobileSidebar.classList.add('hidden');
            });
        }

        if (mobileSidebar) {
            mobileSidebar.addEventListener('click', (e) => {
                if (e.target === mobileSidebar) {
                    mobileSidebar.classList.add('hidden');
                }
            });
        }
    </script>
</body>
</html>
