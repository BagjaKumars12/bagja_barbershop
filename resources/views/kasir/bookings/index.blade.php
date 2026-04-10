@extends('layouts.kasir')

@section('title', 'Daftar Booking')
@section('header', 'Booking')

@section('content')
<div class="space-y-6" x-data="bookingManager()" x-init="init()">
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-xl font-semibold" :class="darkMode ? 'text-white' : 'text-gray-800'">Kelola Booking</h2>
            <p class="text-sm mt-1" :class="darkMode ? 'text-gray-400' : 'text-gray-600'">{{ $todayCount }} booking aktif hari ini</p>
        </div>
        <a href="{{ route('kasir.bookings.create') }}" class="px-4 py-2 bg-[#D4AF37] text-white rounded-lg hover:bg-[#b8942f] transition flex items-center space-x-2">
            <i class="fas fa-plus"></i><span>Tambah Booking</span>
        </a>
    </div>

    {{-- Search --}}
    <div class="relative max-w-md">
        <form method="GET" action="{{ route('kasir.bookings.index') }}" class="relative">
            <input type="text" name="search" placeholder="Cari nama atau nomor HP..." value="{{ request('search') }}"
                class="w-full pl-10 pr-10 py-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-[#D4AF37]"
                :class="darkMode ? 'bg-gray-700 border-gray-600 text-white placeholder-gray-400' : 'bg-white border-gray-300 text-gray-900 placeholder-gray-500'">
            <button type="submit" class="absolute left-3 top-3 text-gray-400"><i class="fas fa-search"></i></button>
            @if(request('search'))
                <a href="{{ route('kasir.bookings.index') }}" class="absolute right-3 top-3 text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></a>
            @endif
        </form>
    </div>

    {{-- Tabel Booking --}}
    <div class="overflow-x-auto rounded-lg shadow" :class="darkMode ? 'bg-gray-800' : 'bg-white'">
        <table class="min-w-full divide-y" :class="darkMode ? 'divide-gray-700' : 'divide-gray-200'">
            <thead :class="darkMode ? 'bg-gray-700' : 'bg-gray-50'">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">Barber</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">Service</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">Tanggal & Waktu</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">Total</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y" :class="darkMode ? 'divide-gray-700' : 'divide-gray-200'">
                @forelse($bookings as $booking)
                    <tr :class="darkMode ? 'bg-gray-800' : 'bg-white'">
                        <td class="px-6 py-4 whitespace-nowrap text-sm" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">{{ $booking->booking_code }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">{{ $booking->customer->name ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">{{ $booking->barber->name ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">
                            {{ $booking->services->pluck('name')->implode(', ') ?: '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">{{ $booking->booking_time_formatted }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full"
                                  :class="{
                                      'bg-yellow-100 text-yellow-800': '{{ $booking->status }}' === 'pending',
                                      'bg-blue-100 text-blue-800': '{{ $booking->status }}' === 'confirmed',
                                      'bg-green-100 text-green-800': '{{ $booking->status }}' === 'completed',
                                      'bg-red-100 text-red-800': '{{ $booking->status }}' === 'cancelled'
                                  }">
                                {{ $booking->status_label['label'] }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            {{-- Tombol Bayar untuk status pending atau confirmed --}}
                            @if(in_array($booking->status, ['pending', 'confirmed']))
                                <button @click="payBooking({{ $booking->id }})"
                                        class="text-green-600 hover:text-green-800 transition"
                                        :class="darkMode ? 'text-green-400 hover:text-green-300' : 'text-green-600 hover:text-green-800'">
                                    <i class="fas fa-money-bill-wave"></i> Bayar
                                </button>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-4 text-center text-sm" :class="darkMode ? 'text-gray-400' : 'text-gray-500'">
                            <i class="fas fa-calendar-alt fa-2x mb-2 opacity-50"></i>
                            <p>Tidak ada booking ditemukan.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-6">{{ $bookings->appends(request()->query())->links() }}</div>
</div>

@push('scripts')
<script>
    function bookingManager() {
        return {
            darkMode: localStorage.getItem('theme') === 'dark',
            init() {
                window.addEventListener('themeChanged', (e) => {
                    this.darkMode = e.detail.darkMode;
                });
            },
            payBooking(bookingId) {
                let isDark = this.darkMode;
                Swal.fire({
                    title: 'Proses Pembayaran',
                    html: `
                        <div class="text-left">
                            <p>Total: <span id="modalTotal"></span></p>
                            <label>Metode Pembayaran:</label>
                            <select id="payment_method" class="swal2-input">
                                <option value="cash">Cash</option>
                                <option value="qris">Qris</option>
                            </select>
                            <label>Jumlah Bayar:</label>
                            <input type="number" id="paid_amount" class="swal2-input" placeholder="Masukkan jumlah bayar">
                            <p>Kembalian: <span id="change">0</span></p>
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'Bayar',
                    cancelButtonText: 'Batal',
                    background: isDark ? '#1f2937' : '#fff',
                    color: isDark ? '#e5e7eb' : '#000',
                    confirmButtonColor: '#D4AF37',
                    didOpen: () => {
                        fetch(`/kasir/booking/${bookingId}/total`)
                            .then(res => res.json())
                            .then(data => {
                                document.getElementById('modalTotal').innerText = 'Rp ' + data.total.toLocaleString('id-ID');
                                window.currentTotal = data.total;
                            });
                        const paidInput = document.getElementById('paid_amount');
                        const changeSpan = document.getElementById('change');
                        paidInput.addEventListener('input', function() {
                            let paid = parseFloat(paidInput.value) || 0;
                            let change = paid - (window.currentTotal || 0);
                            changeSpan.innerText = change >= 0 ? change.toLocaleString('id-ID') : 'Kurang';
                        });
                    },
                    preConfirm: () => {
                        const payment_method = document.getElementById('payment_method').value;
                        const paid_amount = document.getElementById('paid_amount').value;
                        if (!paid_amount || parseFloat(paid_amount) < window.currentTotal) {
                            Swal.showValidationMessage('Jumlah bayar kurang dari total!');
                            return false;
                        }
                        return { payment_method, paid_amount };
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        let form = document.createElement('form');
                        form.method = 'POST';
                        form.action = `/kasir/transactions/${bookingId}/pay`;
                        form.innerHTML = `
                            @csrf
                            <input name="payment_method" value="${result.value.payment_method}">
                            <input name="paid_amount" value="${result.value.paid_amount}">
                        `;
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            }
        }
    }
</script>
@endpush
@endsection