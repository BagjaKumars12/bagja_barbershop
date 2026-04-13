@extends('layouts.owner')

@section('title', 'Laporan Transaksi')
@section('header', 'Laporan Transaksi')

@section('content')
<div class="space-y-6" x-data="reportManager()" x-init="init()">
    <div class="flex justify-between items-center">
        <h2 class="text-xl font-semibold" :class="darkMode ? 'text-white' : 'text-gray-800'">Laporan Transaksi</h2>
        <div class="flex gap-2">
            <a href="{{ route('owner.reports.transactions.export.excel', request()->query()) }}" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                <i class="fas fa-file-excel"></i> Export Excel
            </a>
            <a href="{{ route('owner.reports.transactions.export.pdf', request()->query()) }}" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                <i class="fas fa-file-pdf"></i> Export PDF
            </a>
        </div>
    </div>

    {{-- Filter --}}
    <div class="p-4 rounded-lg shadow" :class="darkMode ? 'bg-gray-800' : 'bg-white'">
        <form method="GET" action="{{ route('owner.reports.transactions') }}" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium mb-1" :class="darkMode ? 'text-gray-300' : 'text-gray-700'">Cari</label>
                <input type="text" name="search" placeholder="Customer / Kode Booking" value="{{ request('search') }}"
                    class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-[#D4AF37]"
                    :class="darkMode ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-300 text-gray-900'">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1" :class="darkMode ? 'text-gray-300' : 'text-gray-700'">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}"
                    class="px-3 py-2 border rounded-lg focus:ring-2 focus:ring-[#D4AF37]"
                    :class="darkMode ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-300 text-gray-900'">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1" :class="darkMode ? 'text-gray-300' : 'text-gray-700'">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}"
                    class="px-3 py-2 border rounded-lg focus:ring-2 focus:ring-[#D4AF37]"
                    :class="darkMode ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-300 text-gray-900'">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1" :class="darkMode ? 'text-gray-300' : 'text-gray-700'">Metode</label>
                <select name="payment_method" class="px-3 py-2 border rounded-lg focus:ring-2 focus:ring-[#D4AF37]"
                    :class="darkMode ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-300 text-gray-900'">
                    <option value="">Semua</option>
                    <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                    <option value="qris" {{ request('payment_method') == 'qris' ? 'selected' : '' }}>Qris</option>
                </select>
            </div>
            <div>
                <button type="submit" class="px-4 py-2 bg-[#D4AF37] text-white rounded-lg hover:bg-[#b8942f] transition">Filter</button>
                <a href="{{ route('owner.reports.transactions') }}" class="ml-2 px-4 py-2 border rounded-lg transition"
                   :class="darkMode ? 'border-gray-600 text-gray-300 hover:bg-gray-600' : 'border-gray-300 text-gray-700 hover:bg-gray-100'">Reset</a>
            </div>
        </form>
    </div>

    {{-- Ringkasan --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="rounded-lg shadow p-4" :class="darkMode ? 'bg-gray-800' : 'bg-white'">
            <p class="text-sm" :class="darkMode ? 'text-gray-400' : 'text-gray-600'">Total Transaksi</p>
            <p class="text-2xl font-bold" :class="darkMode ? 'text-white' : 'text-gray-900'">{{ $totalTransactions }}</p>
        </div>
        <div class="rounded-lg shadow p-4" :class="darkMode ? 'bg-gray-800' : 'bg-white'">
            <p class="text-sm" :class="darkMode ? 'text-gray-400' : 'text-gray-600'">Total Pendapatan</p>
            <p class="text-2xl font-bold text-[#D4AF37]">Rp {{ number_format($totalAmount, 0, ',', '.') }}</p>
        </div>
        <div class="rounded-lg shadow p-4" :class="darkMode ? 'bg-gray-800' : 'bg-white'">
            <p class="text-sm" :class="darkMode ? 'text-gray-400' : 'text-gray-600'">Rata-rata per Transaksi</p>
            <p class="text-2xl font-bold" :class="darkMode ? 'text-white' : 'text-gray-900'">Rp {{ number_format($totalTransactions > 0 ? $totalAmount / $totalTransactions : 0, 0, ',', '.') }}</p>
        </div>
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
                    <td class="px-6 py-4 whitespace-nowrap text-sm" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">{{ $transaction->transaction_code }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">{{ $transaction->booking->booking_code ?? '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">{{ $transaction->booking->customer->name ?? '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">{{ $transaction->booking->barber->name ?? '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">{{ $transaction->booking->services->pluck('name')->implode(', ') ?: '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-[#D4AF37]">Rp {{ number_format($transaction->amount, 0, ',', '.') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">{{ ucfirst($transaction->payment_method) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">{{ $transaction->paid_at ? $transaction->paid_at->format('d/m/Y H:i') : '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <a href="{{ route('owner.transactions.receipt', $transaction->id) }}" class="text-blue-600 hover:text-blue-800">
                            <i class="fas fa-print"></i> Struk
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-6 py-4 text-center text-sm">Tidak ada data</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $transactions->appends(request()->query())->links() }}</div>
</div>

@push('scripts')
<script>
    function reportManager() {
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