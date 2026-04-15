<!DOCTYPE html>
<html lang="en" x-data="themeMode()" x-init="initTheme()">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - Bagja Barbershop</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        [x-cloak] { display: none; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #D4AF37; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #b8942f; }
        * { transition: background-color 0.3s ease, border-color 0.3s ease, color 0.3s ease; }
    </style>
    @stack('styles')
</head>
<body class="font-sans antialiased" :class="darkMode ? 'bg-gray-900' : 'bg-gray-100'">
    <div class="flex h-screen overflow-hidden" x-data="{ activeMenu: '{{ request()->route()->getName() }}' }">
        {{-- Sidebar --}}
        <aside class="w-64 flex-shrink-0 flex flex-col overflow-y-auto" :class="darkMode ? 'bg-gray-800' : 'bg-white'">
            <div class="p-4">
                <div class="flex items-center justify-center mb-8">
                    <img src="{{ asset('images/logo/Logo_Bagja_Barbershop-removebg-preview.png') }}" alt="Logo" class="h-12 w-auto">
                </div>
                <nav class="space-y-2">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3 px-4 py-2 rounded-lg transition" :class="{ 'bg-[#D4AF37] text-white': activeMenu === 'admin.dashboard', 'text-gray-300 hover:bg-gray-700': darkMode && activeMenu !== 'admin.dashboard', 'text-gray-700 hover:bg-gray-100': !darkMode && activeMenu !== 'admin.dashboard' }">
                        <i class="fas fa-tachometer-alt w-5"></i><span>Dashboard</span>
                    </a>
                    <a href="{{ route('admin.users') }}" class="flex items-center space-x-3 px-4 py-2 rounded-lg transition" :class="{ 'bg-[#D4AF37] text-white': activeMenu === 'admin.users', 'text-gray-300 hover:bg-gray-700': darkMode && activeMenu !== 'admin.users', 'text-gray-700 hover:bg-gray-100': !darkMode && activeMenu !== 'admin.users' }">
                        <i class="fas fa-users w-5"></i><span>Users</span>
                    </a>
                    <a href="{{ route('admin.services.index') }}" class="flex items-center space-x-3 px-4 py-2 rounded-lg transition" :class="{ 'bg-[#D4AF37] text-white': activeMenu === 'admin.services.index', 'text-gray-300 hover:bg-gray-700': darkMode && activeMenu !== 'admin.services.index', 'text-gray-700 hover:bg-gray-100': !darkMode && activeMenu !== 'admin.services.index' }">
                        <i class="fas fa-cut w-5"></i><span>Service</span>
                    </a>
                    <a href="{{ route('admin.customers.index') }}" class="flex items-center space-x-3 px-4 py-2 rounded-lg transition" :class="{ 'bg-[#D4AF37] text-white': activeMenu === 'admin.customers.index', 'text-gray-300 hover:bg-gray-700': darkMode && activeMenu !== 'admin.customers.index', 'text-gray-700 hover:bg-gray-100': !darkMode && activeMenu !== 'admin.customers.index' }">
                        <i class="fas fa-user-friends w-5"></i><span>Customers</span>
                    </a>
                    <a href="{{ route('admin.barbers.index') }}" class="flex items-center space-x-3 px-4 py-2 rounded-lg transition" :class="{ 'bg-[#D4AF37] text-white': activeMenu === 'admin.barbers.index', 'text-gray-300 hover:bg-gray-700': darkMode && activeMenu !== 'admin.barbers.index', 'text-gray-700 hover:bg-gray-100': !darkMode && activeMenu !== 'admin.barbers.index' }">
                        <i class="fas fa-user-md w-5"></i><span>Barbers</span>
                    </a>
                    <a href="{{ route('admin.bookings.index') }}" class="flex items-center space-x-3 px-4 py-2 rounded-lg transition" :class="{ 'bg-[#D4AF37] text-white': activeMenu === 'admin.bookings.index', 'text-gray-300 hover:bg-gray-700': darkMode && activeMenu !== 'admin.bookings.index', 'text-gray-700 hover:bg-gray-100': !darkMode && activeMenu !== 'admin.bookings.index' }">
                        <i class="fas fa-calendar-alt w-5"></i><span>Booking</span>
                    </a>
                    <a href="{{ route('admin.reports.transactions') }}" class="flex items-center space-x-3 px-4 py-2 rounded-lg transition" :class="{ 'bg-[#D4AF37] text-white': activeMenu === 'admin.reports.transactions', 'text-gray-300 hover:bg-gray-700': darkMode && activeMenu !== 'admin.reports.transactions', 'text-gray-700 hover:bg-gray-100': !darkMode && activeMenu !== 'admin.bookings.index' }">
                        <i class="fas fa-chart-line w-5"></i><span>Laporan Transaksi</span>
                    </a>
                    <a href="{{ route('admin.log_activity.index') }}"
                    class="flex items-center space-x-3 px-4 py-2 rounded-lg transition"
                    :class="{
                          'bg-[#D4AF37] text-white': activeMenu === 'admin.log_activity.index',
                        'text-gray-300 hover:bg-gray-700': darkMode && activeMenu !== 'admin.log_activity.index',
                        'text-gray-700 hover:bg-gray-100': !darkMode && activeMenu !== 'admin.log_activity.index'
                        }">
                        <i class="fas fa-history w-5"></i>
                        <span>Log Activity</span>
                    </a>
                </nav>
                </nav>
            </div>

            {{-- Profil --}}
            <div class="mt-auto p-4 border-t" :class="darkMode ? 'border-gray-700' : 'border-gray-200'">
                <div class="flex items-center space-x-3">
                    @if(Auth::user()->avatar)
                        <img src="{{ asset('storage/avatars/' . Auth::user()->avatar) }}" class="w-8 h-8 rounded-full object-cover">
                    @else
                        <div class="w-8 h-8 rounded-full bg-[#D4AF37] flex items-center justify-center text-white font-bold">
                            {{ substr(Auth::user()->username, 0, 1) }}
                        </div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium truncate" :class="darkMode ? 'text-white' : 'text-gray-900'">{{ Auth::user()->username }}</p>
                        <p class="text-xs truncate" :class="darkMode ? 'text-gray-400' : 'text-gray-500'">{{ Auth::user()->email }}</p>
                    </div>
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="p-1 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700">
                            <i class="fas fa-chevron-down text-xs" :class="darkMode ? 'text-gray-400' : 'text-gray-600'"></i>
                        </button>
                        <div x-show="open" @click.away="open = false" class="absolute bottom-full right-0 mb-2 w-48 rounded-md shadow-lg py-1 z-10" :class="darkMode ? 'bg-gray-700' : 'bg-white'">
                            <button type="button" @click="confirmLogout" class="block w-full text-left px-4 py-2 text-sm" :class="darkMode ? 'text-gray-300 hover:bg-gray-600' : 'text-gray-700 hover:bg-gray-100'">
                                <i class="fas fa-sign-out-alt mr-2"></i>Logout
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        {{-- Main Content --}}
        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="flex justify-between items-center px-6 py-4 shadow-sm" :class="darkMode ? 'bg-gray-800' : 'bg-white'">
                <div><h2 class="text-xl font-semibold" :class="darkMode ? 'text-white' : 'text-gray-800'">@yield('header')</h2></div>
                <div class="flex items-center space-x-4">
                    <button @click="toggleTheme()" class="p-2 rounded-full transition" :class="darkMode ? 'bg-gray-700 text-yellow-400' : 'bg-gray-200 text-gray-800'">
                        <i :class="darkMode ? 'fas fa-sun' : 'fas fa-moon'" class="text-lg"></i>
                    </button>
                </div>
            </header>
            <main class="flex-1 overflow-y-auto p-6">
                @yield('content')
            </main>
        </div>
    </div>

    {{-- Form logout tersembunyi --}}
    <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display: none;">
        @csrf
    </form>

    {{-- Theme Script & SweetAlert Notifikasi --}}
    <script>
        function themeMode() {
            return {
                darkMode: false,
                initTheme() {
                    const saved = localStorage.getItem('theme');
                    if (saved) this.darkMode = saved === 'dark';
                    else this.darkMode = window.matchMedia('(prefers-color-scheme: dark)').matches;
                    this.applyTheme();
                    window.dispatchEvent(new CustomEvent('themeChanged', { detail: { darkMode: this.darkMode } }));
                },
                toggleTheme() {
                    this.darkMode = !this.darkMode;
                    this.applyTheme();
                    window.dispatchEvent(new CustomEvent('themeChanged', { detail: { darkMode: this.darkMode } }));
                },
                applyTheme() {
                    if (this.darkMode) {
                        document.documentElement.classList.add('dark');
                        localStorage.setItem('theme', 'dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                        localStorage.setItem('theme', 'light');
                    }
                },
                confirmLogout() {
                    const isDarkMode = this.darkMode;
                    Swal.fire({
                        title: 'Apakah Anda yakin?',
                        text: "Anda akan keluar dari sistem!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: isDarkMode ? '#4b5563' : '#6c757d',
                        confirmButtonText: 'Ya, keluar!',
                        cancelButtonText: 'Tidak',
                        background: isDarkMode ? '#1f2937' : '#fff',
                        color: isDarkMode ? '#e5e7eb' : '#1f2937'
                    }).then((result) => {
                        if (result.isConfirmed) document.getElementById('logout-form').submit();
                    });
                }
            }
        }

        // Notifikasi SweetAlert untuk session success dan error
        document.addEventListener('DOMContentLoaded', function() {
            const isDarkMode = document.documentElement.classList.contains('dark');
            
            @if(session('success'))
                Swal.fire({
                    title: 'Berhasil!',
                    text: '{{ session('success') }}',
                    icon: 'success',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    background: isDarkMode ? '#1f2937' : '#fff',
                    color: isDarkMode ? '#e5e7eb' : '#1f2937'
                });
            @endif

            @if($errors->any())
                Swal.fire({
                    title: 'Gagal!',
                    text: '{{ $errors->first() }}',
                    icon: 'error',
                    confirmButtonColor: '#D4AF37',
                    background: isDarkMode ? '#1f2937' : '#fff',
                    color: isDarkMode ? '#e5e7eb' : '#1f2937'
                });
            @endif
        });
    </script>

    @stack('scripts')
</body>
</html>