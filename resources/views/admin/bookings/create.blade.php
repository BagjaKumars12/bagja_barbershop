@extends('layouts.admin')

@section('title', 'Tambah Booking')
@section('header', 'Booking')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6" x-data="bookingForm()" x-init="init()">
    {{-- Panel Kiri (list service) --}}
    <div class="lg:col-span-2 space-y-6">
        <div class="rounded-lg shadow p-6" :class="darkMode ? 'bg-gray-800' : 'bg-white'">
            <h3 class="text-lg font-semibold mb-4" :class="darkMode ? 'text-white' : 'text-gray-900'">Pilihan Service</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach($services as $service)
                <label class="flex items-center space-x-3 p-3 rounded-lg cursor-pointer transition"
                       :class="{
                           'bg-[#b8942f] text-white': isServiceSelected({{ $service->id }}),
                           'bg-gray-100 text-gray-800': !isServiceSelected({{ $service->id }}) && !darkMode,
                           'bg-gray-700 text-gray-300': !isServiceSelected({{ $service->id }}) && darkMode
                       }"
                       @click="toggleService({{ $service->id }}, '{{ addslashes($service->name) }}', {{ $service->price }}, '{{ $service->image ? asset('storage/services/' . $service->image) : '' }}')">
                    <div class="flex items-center space-x-3 w-full">
                        @if($service->image)
                            <img src="{{ asset('storage/services/' . $service->image) }}" class="w-12 h-12 rounded-full object-cover">
                        @else
                            <div class="w-12 h-12 rounded-full bg-gray-300 flex items-center justify-center">
                                <i class="fas fa-cut text-gray-500"></i>
                            </div>
                        @endif
                        <div>
                            <p class="font-medium">{{ $service->name }}</p>
                            <p class="text-sm">Rp {{ number_format($service->price, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </label>
                @endforeach
            </div>
        </div>

        {{-- Form Utama --}}
        <div class="rounded-lg shadow p-6" :class="darkMode ? 'bg-gray-800' : 'bg-white'">
            <h3 class="text-lg font-semibold mb-4" :class="darkMode ? 'text-white' : 'text-gray-900'">Data Booking</h3>
            <form method="POST" action="{{ route('admin.bookings.store') }}">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1" :class="darkMode ? 'text-gray-300' : 'text-gray-700'">Customer</label>
                        <select name="customer_id" x-model="customerId" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-[#D4AF37]" 
                                :class="darkMode ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-300 text-gray-900'" required>
                            <option value="">-- Pilih Customer --</option>
                            @foreach($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" :class="darkMode ? 'text-gray-300' : 'text-gray-700'">Barber</label>
                        <select name="barber_id" x-model="barberId" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-[#D4AF37]"
                                :class="darkMode ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-300 text-gray-900'" required>
                            <option value="">-- Pilih Barber --</option>
                            @foreach($barbers as $barber)
                            <option value="{{ $barber->id }}">{{ $barber->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-medium mb-1" :class="darkMode ? 'text-gray-300' : 'text-gray-700'">Tanggal & Waktu</label>
                    <input type="datetime-local" name="booking_time" x-model="bookingTime" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-[#D4AF37]"
                           :class="darkMode ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-300 text-gray-900'" required>
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-medium mb-1" :class="darkMode ? 'text-gray-300' : 'text-gray-700'">Metode Pembayaran</label>
                    <select name="payment_method" x-model="paymentMethod" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-[#D4AF37]"
                            :class="darkMode ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-300 text-gray-900'" required>
                        <option value="cash">Cash</option>
                        <option value="card">Kartu</option>
                        <option value="transfer">Transfer</option>
                    </select>
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-medium mb-1" :class="darkMode ? 'text-gray-300' : 'text-gray-700'">Catatan</label>
                    <textarea name="notes" rows="2" x-model="notes" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-[#D4AF37]"
                              :class="darkMode ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-300 text-gray-900'" placeholder="Catatan Tambahan (opsional)"></textarea>
                </div>

                {{-- Input hidden untuk service_ids yang dipilih --}}
                <template x-for="id in selectedIds" :key="id">
                    <input type="hidden" name="service_ids[]" :value="id">
                </template>

                <div class="mt-6 flex justify-end space-x-3">
                    <a href="{{ route('admin.bookings.index') }}" class="px-4 py-2 border rounded-lg transition"
                       :class="darkMode ? 'border-gray-600 text-gray-300 hover:bg-gray-600' : 'border-gray-300 text-gray-700 hover:bg-gray-100'">Batal</a>
                    <button type="submit" class="px-4 py-2 bg-[#D4AF37] text-white rounded-lg hover:bg-[#b8942f] transition">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Panel Kanan (Ringkasan) --}}
    <div class="space-y-6">
        <div class="rounded-lg shadow p-6 sticky top-6" :class="darkMode ? 'bg-gray-800' : 'bg-white'">
            <h3 class="text-lg font-semibold mb-4" :class="darkMode ? 'text-white' : 'text-gray-900'">Total Pemesanan</h3>
            <div class="space-y-3">
                <div>
                    <span class="text-sm font-medium" :class="darkMode ? 'text-gray-300' : 'text-gray-700'">Layanan :</span>
                    <ul class="mt-2 space-y-1 list-disc list-inside">
                        <template x-for="service in selectedServices" :key="service.id">
                            <li class="text-sm" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">
                                <span x-text="service.name"></span>
                                <span class="text-xs ml-1">(Rp <span x-text="formatPrice(service.price)"></span>)</span>
                            </li>
                        </template>
                        <li x-show="selectedServices.length === 0" class="text-sm text-gray-500">- Belum ada layanan dipilih -</li>
                    </ul>
                </div>
                <div class="flex justify-between border-t pt-2 mt-2" :class="darkMode ? 'border-gray-700' : 'border-gray-200'">
                    <span class="font-semibold" :class="darkMode ? 'text-white' : 'text-gray-900'">Total :</span>
                    <span class="font-bold text-[#D4AF37]" x-text="'Rp ' + formatPrice(totalPrice)"></span>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function bookingForm() {
        return {
            darkMode: localStorage.getItem('theme') === 'dark',
            selectedIds: [],
            selectedServices: [],
            customerId: '',
            barberId: '',
            bookingTime: '',
            paymentMethod: 'cash',
            notes: '',
            init() {
                // Sinkronkan darkMode awal
                this.darkMode = localStorage.getItem('theme') === 'dark';
                // Dengarkan event dari layout
                window.addEventListener('themeChanged', (e) => {
                    this.darkMode = e.detail.darkMode;
                });
            },
            isServiceSelected(id) {
                return this.selectedIds.includes(id);
            },
            toggleService(id, name, price, image) {
                price = parseFloat(price);
                if (this.isServiceSelected(id)) {
                    this.selectedIds = this.selectedIds.filter(i => i !== id);
                    this.selectedServices = this.selectedServices.filter(s => s.id !== id);
                } else {
                    this.selectedIds.push(id);
                    this.selectedServices.push({ id, name, price, image });
                }
                this.selectedServices.sort((a,b) => a.name.localeCompare(b.name));
            },
            get totalPrice() {
                return this.selectedServices.reduce((sum, s) => sum + s.price, 0);
            },
            formatPrice(price) {
                return price.toLocaleString('id-ID');
            }
        }
    }
</script>
@endpush
@endsection