{{-- resources/views/layouts/auth.blade.php --}}
<!DOCTYPE html>
<html lang="en" x-data="themeMode()" x-init="initTheme()">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Login') - Bagja Barbershop</title>
    
    {{-- Tailwind CSS --}}
    <script src="https://cdn.tailwindcss.com"></script>
    
    {{-- Alpine JS untuk Theme Toggle --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    {{-- Font Awesome untuk Ikon --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        [x-cloak] { display: none !important; }
        
        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #D4AF37;
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #b8942f;
        }
        
        /* Smooth transitions for theme switching */
        * {
            transition: background-color 0.3s ease, border-color 0.3s ease, color 0.3s ease;
        }
    </style>
    
    @stack('styles')
</head>
<body class="min-h-screen font-sans antialiased relative" 
      :class="{ 'bg-[#1A1A1A]': !darkMode, 'bg-gray-900': darkMode }">
    
    {{-- Logo di Kiri Atas --}}
    <div class="absolute top-4 left-4 z-50" x-cloak>
        <a href="{{ route('login') }}" class="block">
            <img src="{{ asset('images/logo/Logo_Bagja_Barbershop-removebg-preview.png') }}" 
                 alt="Bagja Barbershop Logo"
                 class="h-16 w-auto transition-transform duration-300 hover:scale-110"
                 :class="darkMode ? 'brightness-90' : ''">
        </a>
    </div>

    {{-- Theme Toggle Button --}}
    <div class="absolute top-4 right-4 z-50" x-cloak>
        <button @click="toggleTheme()" 
                class="p-3 rounded-full shadow-lg transition-all duration-300 hover:scale-110"
                :class="darkMode ? 'bg-gray-800 text-yellow-400' : 'bg-white text-gray-800'">
            <i :class="darkMode ? 'fas fa-sun' : 'fas fa-moon'" class="text-xl"></i>
        </button>
    </div>

    {{-- Main Content --}}
    <main>
        @yield('content')
    </main>

    {{-- Theme Mode Script --}}
    <script>
        function themeMode() {
            return {
                darkMode: false,
                initTheme() {
                    // Check local storage or system preference
                    const savedTheme = localStorage.getItem('theme');
                    if (savedTheme) {
                        this.darkMode = savedTheme === 'dark';
                    } else {
                        this.darkMode = window.matchMedia('(prefers-color-scheme: dark)').matches;
                    }
                    this.applyTheme();
                },
                toggleTheme() {
                    this.darkMode = !this.darkMode;
                    this.applyTheme();
                },
                applyTheme() {
                    if (this.darkMode) {
                        document.documentElement.classList.add('dark');
                        localStorage.setItem('theme', 'dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                        localStorage.setItem('theme', 'light');
                    }
                }
            }
        }
    </script>

    @stack('scripts')
</body>
</html>