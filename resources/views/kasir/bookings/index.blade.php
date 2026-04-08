@extends('layouts.kasir')

@section('title', 'Daftar Booking')
@section('header', 'Booking')

@section('content')
<div class="space-y-6" x-data="bookingManager()">
    {{-- Header dan tombol tambah --}}
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
                        <td class="py-2">

                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-sm" :class="darkMode ? 'text-gray-400' : 'text-gray-500'">
                            <i class="fas fa-calendar-alt fa-2x mb-2 opacity-50"></i>
                            <p>Tidak ada booking ditemukan.</p>
                            @if(request('search'))
                                <p class="text-xs mt-1">Coba kata kunci lain.</p>
                            @endif
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
            confirmDelete(id, code) {
                const isDarkMode = document.documentElement.classList.contains('dark');
                Swal.fire({
                    title: 'Hapus Booking?',
                    text: `Apakah Anda yakin ingin menghapus booking ${code}?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: isDarkMode ? '#4b5563' : '#6c757d',
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal',
                    background: isDarkMode ? '#1f2937' : '#fff',
                    color: isDarkMode ? '#e5e7eb' : '#1f2937'
                }).then((result) => {
                    if (result.isConfirmed) {
                        let url = deleteBaseUrl.replace('__ID__', id);
                        let form = document.createElement('form');
                        form.method = 'POST';
                        form.action = url;
                        form.innerHTML = '@csrf @method("DELETE")';
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