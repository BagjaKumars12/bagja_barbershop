@extends('layouts.kasir')

@section('title', 'Lihat Customers')
@section('header', 'Customers')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h2 class="text-xl font-semibold" :class="darkMode ? 'text-white' : 'text-gray-800'">Daftar Customer</h2>
    </div>
    <div class="relative max-w-md">
        <form method="GET" class="relative">
            <input type="text" name="search" placeholder="Cari customer..." value="{{ request('search') }}" 
                   class="w-full pl-10 pr-10 py-2 rounded-lg border focus:ring-2 focus:ring-[#D4AF37]"
                   :class="darkMode ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-300 text-gray-900'">
            <button type="submit" class="absolute left-3 top-3 text-gray-400"><i class="fas fa-search"></i></button>
        </form>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($customers as $customer)
        <div class="rounded-lg shadow p-4" :class="darkMode ? 'bg-gray-800' : 'bg-white'">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-full bg-[#D4AF37] flex items-center justify-center text-white font-bold">
                    {{ strtoupper(substr($customer->name, 0, 1)) }}
                </div>
                <div>
                    <h3 class="font-semibold" :class="darkMode ? 'text-white' : 'text-gray-900'">{{ $customer->name }}</h3>
                    <p class="text-sm" :class="darkMode ? 'text-gray-400' : 'text-gray-500'">{{ $customer->email ?? '-' }}</p>
                    <p class="text-xs" :class="darkMode ? 'text-gray-500' : 'text-gray-400'">{{ $customer->phone ?? '-' }}</p>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-8 text-gray-500">Tidak ada customer ditemukan.</div>
        @endforelse
    </div>
    <div class="mt-6">{{ $customers->appends(request()->query())->links() }}</div>
</div>
@endsection