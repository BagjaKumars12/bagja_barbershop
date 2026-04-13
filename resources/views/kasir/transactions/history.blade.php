@extends('layouts.kasir')

@section('title', 'Riwayat Transaksi')
@section('header', 'Riwayat Transaksi')

@section('content')
<div class="space-y-6" x-data="historyManager()" x-init="init()">
    <div class="flex justify-between items-center">
        <h2 class="text-xl font-semibold" :class="darkMode ? 'text-white' : 'text-gray-800'">Riwayat Transaksi</h2>
    </div>

    {{-- Filter & Search --}}
    <div class="flex flex-col sm:flex-row gap-4">
        <div class="relative flex-1">
            <form method="GET" action="{{ route('kasir.transactions.history') }}" class="relative">
                <input type="text" name="search" placeholder="Cari customer atau kode booking..." value="{{ request('search') }}"
                    class="w-full pl-10 pr-10 py-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-[#D4AF37]"
                    :class="darkMode ? 'bg-gray-700 border-gray-600 text-white placeholder-gray-400' : 'bg-white border-gray-300 text-gray-900 placeholder-gray-500'">
                <button type="submit" class="absolute left-3 top-3 text-gray-400"><i class="fas fa-search"></i></button>
                @if(request('search'))
                    <a href="{{ route('kasir.transactions.history') }}" class="absolute right-3 top-3 text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></a>
                @endif
            </form>
        </div>
        <div class="flex gap-2">
            <input type="date" name="start_date" form="filterForm" value="{{ request('start_date') }}" 
                class="px-3 py-2 border rounded-lg focus:ring-2 focus:ring-[#D4AF37]"
                :class="darkMode ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-300 text-gray-900'">
            <span class="self-center">-</span>
            <input type="date" name="end_date" form="filterForm" value="{{ request('end_date') }}"
                class="px-3 py-2 border rounded-lg focus:ring-2 focus:ring-[#D4AF37]"
                :class="darkMode ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-300 text-gray-900'">
            <button type="submit" form="filterForm" class="px-4 py-2 bg-[#D4AF37] text-white rounded-lg hover:bg-[#b8942f] transition">Filter</button>
            <a href="{{ route('kasir.transactions.history') }}" class="px-4 py-2 border rounded-lg transition"
                :class="darkMode ? 'border-gray-600 text-gray-300 hover:bg-gray-600' : 'border-gray-300 text-gray-700 hover:bg-gray-100'">Reset</a>
        </div>
        <form id="filterForm" method="GET" action="{{ route('kasir.transactions.history') }}" class="hidden"></form>
    </div>

    {{-- Tabel Transaksi --}}
    <div class="overflow-x-auto rounded-lg shadow" :class="darkMode ? 'bg-gray-800' : 'bg-white'">
        <table class="min-w-full divide-y" :class="darkMode ? 'divide-gray-700' : 'divide-gray-200'">
            <thead :class="darkMode ? 'bg-gray-700' : 'bg-gray-50'">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">Kode Transaksi</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">Kode Booking</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">Barber</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">Layanan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">Total</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">Metode</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">Tanggal</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y" :class="darkMode ? 'divide-gray-700' : 'divide-gray-200'">
                @forelse($transactions as $transaction)
                <tr :class="darkMode ? 'bg-gray-800' : 'bg-white'">
                    <td class="px-6 py-4 whitespace-nowrap text-sm" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">{{ $transaction->transaction_code  }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">{{ $transaction->booking->booking_code ?? '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">{{ $transaction->booking->customer->name ?? '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">{{ $transaction->booking->barber->name ?? '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">{{ $transaction->booking->services->pluck('name')->implode(', ') ?: '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-[#D4AF37]">Rp {{ number_format($transaction->amount, 0, ',', '.') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">{{ ucfirst($transaction->payment_method) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">{{ $transaction->paid_at ? $transaction->paid_at->format('d/m/Y H:i') : '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <a href="{{ route('kasir.transactions.receipt', $transaction->id) }}"
                           class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                            <i class="fas fa-print"></i> Struk
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-6 py-4 text-center text-sm" :class="darkMode ? 'text-gray-400' : 'text-gray-500'">
                        <i class="fas fa-receipt fa-2x mb-2 opacity-50"></i>
                        <p>Belum ada transaksi.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $transactions->appends(request()->query())->links() }}</div>
</div>

@push('scripts')
<script>
    function historyManager() {
        return {
            darkMode: localStorage.getItem('theme') === 'dark',
            init() {
                const updateDarkMode = () => {
                    this.darkMode = document.documentElement.classList.contains('dark');
                };
                updateDarkMode();
                const observer = new MutationObserver(updateDarkMode);
                observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
                window.addEventListener('themeChanged', (e) => {
                    this.darkMode = e.detail.darkMode;
                });
            }
        }
    }
</script>
@endpush
@endsection