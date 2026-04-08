{{-- resources/views/auth/login.blade.php --}}
@extends('layouts.auth')

@section('title', 'Login')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        {{-- Form Login dengan background putih --}}
        <div class="bg-white rounded-2xl shadow-2xl p-8 space-y-6"
             :class="darkMode ? 'bg-gray-800' : 'bg-white'">
            
            {{-- Header Form dengan Text Login --}}
            <div class="text-center">
                <div class="mb-4">
                    <span class="inline-block px-4 py-1 text-sm font-semibold tracking-wider uppercase rounded-full"
                          :class="darkMode ? 'bg-[#D4AF37] text-gray-900' : 'bg-[#D4AF37] text-white'">
                        Login
                    </span>
                </div>
                <h2 class="text-2xl font-bold"
                    :class="darkMode ? 'text-white' : 'text-gray-900'">
                    Selamat Datang Kembali
                </h2>
                <p class="mt-1 text-sm"
                   :class="darkMode ? 'text-gray-400' : 'text-gray-600'">
                    Silakan masuk ke akun Anda
                </p>
            </div>

            {{-- Alert untuk error --}}
            @if($errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-500"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700">
                                {{ $errors->first() }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Session Status --}}
            @if(session('status'))
                <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle text-green-500"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-700">
                                {{ session('status') }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Login Form --}}
            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf

                {{-- Email Field --}}
                <div>
                    <label for="email" 
                           class="block text-sm font-medium mb-2"
                           :class="darkMode ? 'text-gray-300' : 'text-gray-700'">
                        <i class="fas fa-envelope mr-2"></i>Alamat Email
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-envelope text-gray-400"></i>
                        </div>
                        <input type="email" 
                               name="email" 
                               id="email" 
                               value="{{ old('email') }}" {{-- Ini akan kosong setelah logout --}}
                               placeholder="name@example.com"
                               class="block w-full pl-10 pr-3 py-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D4AF37] focus:border-transparent transition duration-150"
                               :class="darkMode ? 'bg-gray-700 border-gray-600 text-white placeholder-gray-400' : 'bg-white border-gray-300 text-gray-900 placeholder-gray-500'"
                               required 
                               autofocus>
                    </div>
                    @error('email')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password Field --}}
                <div>
                    <label for="password" 
                           class="block text-sm font-medium mb-2"
                           :class="darkMode ? 'text-gray-300' : 'text-gray-700'">
                        <i class="fas fa-lock mr-2"></i>Password
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                        <input type="password" 
                               name="password" 
                               id="password" 
                               placeholder="Masukkan Password Anda"
                               class="block w-full pl-10 pr-10 py-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D4AF37] focus:border-transparent transition duration-150"
                               :class="darkMode ? 'bg-gray-700 border-gray-600 text-white placeholder-gray-400' : 'bg-white border-gray-300 text-gray-900 placeholder-gray-500'"
                               required>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <button type="button" 
                                    onclick="togglePassword()" 
                                    class="text-gray-400 hover:text-gray-600 focus:outline-none">
                                <i class="fas fa-eye" id="togglePasswordIcon"></i>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Remember Me & Forgot Password --}}
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input type="checkbox" 
                               name="remember" 
                               id="remember" 
                               class="h-4 w-4 rounded border-gray-300 text-[#D4AF37] focus:ring-[#D4AF37]">
                        <label for="remember" 
                               class="ml-2 block text-sm cursor-pointer select-none"
                               :class="darkMode ? 'text-gray-300' : 'text-gray-700'">
                            Ingat Aku
                        </label>
                    </div>

                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" 
                           class="text-sm hover:underline transition"
                           :class="darkMode ? 'text-[#D4AF37] hover:text-yellow-300' : 'text-[#D4AF37] hover:text-yellow-600'">
                            Forgot password?
                        </a>
                    @endif
                </div>

                {{-- Login Button --}}
                <button type="submit" 
                        class="w-full bg-[#D4AF37] hover:bg-[#b8942f] text-white font-bold py-3 px-4 rounded-lg transition duration-300 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#D4AF37]">
                    <i class="fas fa-sign-in-alt mr-2"></i>Login
                </button>
            </form>
        </div>

        {{-- Footer --}}
        <div class="text-center text-xs"
             :class="darkMode ? 'text-gray-400' : 'text-gray-400'">
            &copy; {{ date('Y') }} Bagja Barbershop. All rights reserved.
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Toggle Password Visibility
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('togglePasswordIcon');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.classList.remove('fa-eye');
            toggleIcon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            toggleIcon.classList.remove('fa-eye-slash');
            toggleIcon.classList.add('fa-eye');
        }
    }

    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        const alerts = document.querySelectorAll('.bg-red-50, .bg-green-50');
        alerts.forEach(alert => {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        });
    }, 5000);

    // Hapus old input setelah page load (pastikan form kosong)
    window.addEventListener('load', function() {
        // Hapus value dari email field jika ada
        document.getElementById('email').value = '';
    });
</script>
@endpush