@extends('layouts.admin')

@section('title', 'Dashboard')
@section('header', 'Dashboard')

@section('content')
<div class="space-y-6">
    {{-- Greeting and Date --}}
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold" :class="darkMode ? 'text-white' : 'text-gray-900'">
                Halo, {{ Auth::user()->username }}!
            </h1>
            <p class="text-sm" :class="darkMode ? 'text-gray-400' : 'text-gray-600'">
                {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
            </p>
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="rounded-lg shadow p-6" :class="darkMode ? 'bg-gray-800' : 'bg-white'">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm" :class="darkMode ? 'text-gray-400' : 'text-gray-500'">Transaksi hari ini</p>
                    <p class="text-2xl font-bold" :class="darkMode ? 'text-white' : 'text-gray-900'">{{ $todayTransactions }}</p>
                </div>
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-receipt"></i>
                </div>
            </div>
        </div>

        <div class="rounded-lg shadow p-6" :class="darkMode ? 'bg-gray-800' : 'bg-white'">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm" :class="darkMode ? 'text-gray-400' : 'text-gray-500'">Customer hari ini</p>
                    <p class="text-2xl font-bold" :class="darkMode ? 'text-white' : 'text-gray-900'">{{ $todayCustomers }}</p>
                </div>
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>

        <div class="rounded-lg shadow p-6" :class="darkMode ? 'bg-gray-800' : 'bg-white'">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm" :class="darkMode ? 'text-gray-400' : 'text-gray-500'">User yang aktif</p>
                    <p class="text-2xl font-bold" :class="darkMode ? 'text-white' : 'text-gray-900'">{{ $activeUsers }}</p>
                </div>
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <i class="fas fa-user-check"></i>
                </div>
            </div>
        </div>

        <div class="rounded-lg shadow p-6" :class="darkMode ? 'bg-gray-800' : 'bg-white'">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm" :class="darkMode ? 'text-gray-400' : 'text-gray-500'">Booking hari ini</p>
                    <p class="text-2xl font-bold" :class="darkMode ? 'text-white' : 'text-gray-900'">{{ $todayBookings }}</p>
                </div>
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <i class="fas fa-calendar-check"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Transactions --}}
    <div class="rounded-lg shadow overflow-hidden" :class="darkMode ? 'bg-gray-800' : 'bg-white'">
        <div class="px-6 py-4 border-b" :class="darkMode ? 'border-gray-700' : 'border-gray-200'">
            <h3 class="text-lg font-semibold" :class="darkMode ? 'text-white' : 'text-gray-900'">Laporan Transaksi Hari Ini</h3>
        </div>
        <div class="p-6">
            {{-- Search form dan per page selector --}}
            <div class="mb-4 flex flex-col md:flex-row justify-between gap-4">
                <div class="relative flex-1 max-w-md">
                    <form method="GET" action="{{ route('admin.dashboard') }}" class="relative">
                        <input type="text" name="search" 
                               placeholder="Cari berdasarkan nama pelanggan atau layanan..." 
                               value="{{ request('search') }}"
                               class="w-full pl-10 pr-10 py-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-[#D4AF37]"
                               :class="darkMode ? 'bg-gray-700 border-gray-600 text-white placeholder-gray-400' : 'bg-white border-gray-300 text-gray-900 placeholder-gray-500'">
                        <button type="submit" class="absolute left-3 top-3 text-gray-400">
                            <i class="fas fa-search"></i>
                        </button>
                        @if(request('search'))
                            <a href="{{ route('admin.dashboard') }}" 
                               class="absolute right-3 top-3 text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                    </form>
                </div>
                <div class="flex items-center space-x-2">
                    <label class="text-sm" :class="darkMode ? 'text-gray-400' : 'text-gray-600'">Tampilkan:</label>
                    <select onchange="window.location.href=this.value" 
                            class="px-3 py-1 rounded border focus:ring-2 focus:ring-[#D4AF37]"
                            :class="darkMode ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-300 text-gray-900'">
                        @php
                            $perPage = request('per_page', 5);
                        @endphp
                        <option value="{{ route('admin.dashboard', array_merge(request()->query(), ['per_page' => 5])) }}" {{ $perPage == 5 ? 'selected' : '' }}>5</option>
                        <option value="{{ route('admin.dashboard', array_merge(request()->query(), ['per_page' => 10])) }}" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                        <option value="{{ route('admin.dashboard', array_merge(request()->query(), ['per_page' => 50])) }}" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                    </select>
                </div>
            </div>

            {{-- Transactions List --}}
            <div class="space-y-4">
                @forelse($transactions as $transaction)
                    <div class="flex items-center justify-between py-3 border-b" :class="darkMode ? 'border-gray-700' : 'border-gray-100'">
                        <div class="flex-1">
                            <p class="font-medium" :class="darkMode ? 'text-white' : 'text-gray-900'">
                                {{ $transaction->customer?->name ?? 'Pelanggan tidak diketahui' }}
                            </p>
                            <p class="text-sm" :class="darkMode ? 'text-gray-400' : 'text-gray-500'">
                                {{ $transaction->service?->name ?? 'Layanan tidak diketahui' }} • Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm" :class="darkMode ? 'text-gray-400' : 'text-gray-500'">
                                <i class="far fa-clock mr-1"></i> {{ $transaction->paid_at->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                @empty
                    <p class="text-center py-4 text-gray-500">
                        @if(request('search'))
                            Tidak ditemukan transaksi dengan kata kunci "{{ request('search') }}".
                        @else
                            Belum ada transaksi hari ini.
                        @endif
                    </p>
                @endforelse
            </div>

            {{-- Pagination Links --}}
            <div class="mt-6">
                {{ $transactions->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection