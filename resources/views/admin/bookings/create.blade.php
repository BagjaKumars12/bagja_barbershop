@extends('layouts.admin')

@section('title', 'Tambah Booking')
@section('header', 'Booking')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6" x-data="bookingForm()" x-init="init()">
    {{-- Panel Kiri: Form --}}
    <div class="lg:col-span-2 space-y-6">
        <div class="rounded-lg shadow p-6" :class="darkMode ? 'bg-gray-800' : 'bg-white'">
            <h3 class="text-lg font-semibold mb-4" :class="darkMode ? 'text-white' : 'text-gray-900'">Data Booking</h3>
            <form method="POST" action="{{ route('admin.bookings.store') }}" id="bookingForm">
                @csrf

                {{-- Customer + tombol tambah (sama seperti di transaksi) --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1" :class="darkMode ? 'text-gray-300' : 'text-gray-700'">Customer</label>
                    <div class="flex gap-2">
                        <select name="customer_id" id="customer_select" class="flex-1 px-3 py-2 border rounded-lg focus:ring-2 focus:ring-[#D4AF37]"
                                :class="darkMode ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-300 text-gray-900'" required>
                            <option value="">-- Pilih Customer --</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                            @endforeach
                        </select>
                        <button type="button" @click="openCustomerModal()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            <i class="fas fa-plus"></i> Baru
                        </button>
                    </div>
                </div>

                {{-- Barber --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1" :class="darkMode ? 'text-gray-300' : 'text-gray-700'">Barber</label>
                    <select name="barber_id" id="barber_select" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-[#D4AF37]"
                            :class="darkMode ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-300 text-gray-900'" required>
                        <option value="">-- Pilih Barber --</option>
                        @foreach($barbers as $barber)
                            <option value="{{ $barber->id }}">{{ $barber->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Tanggal & Waktu --}}
                <div class="mt-4">
                    <label class="block text-sm font-medium mb-1">Tanggal & Waktu</label>
                    <input type="datetime-local" name="booking_time" x-model="bookingTime" 
                        class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-[#D4AF37]"
                        :class="darkMode ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-300 text-gray-900'" required>
                    <p class="text-xs mt-1 text-gray-500">Jam operasional: 09:00 - 21:00 (WIB)</p>
                </div>

                {{-- Metode Pembayaran --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1" :class="darkMode ? 'text-gray-300' : 'text-gray-700'">Metode Pembayaran</label>
                    <select name="payment_method" x-model="paymentMethod" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-[#D4AF37]"
                            :class="darkMode ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-300 text-gray-900'" required>
                        <option value="cash">Cash</option>
                        <option value="card">Qris</option>
                    </select>
                </div>

                {{-- Catatan --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1" :class="darkMode ? 'text-gray-300' : 'text-gray-700'">Catatan</label>
                    <textarea name="notes" rows="2" x-model="notes" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-[#D4AF37]"
                              :class="darkMode ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-300 text-gray-900'"
                              placeholder="Catatan Tambahan (opsional)"></textarea>
                </div>

                {{-- Pilih Layanan (Card Grid) --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-3" :class="darkMode ? 'text-gray-300' : 'text-gray-700'">Pilih Layanan</label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                        @foreach($services as $service)
                        <div @click="toggleService({{ $service->id }})"
                             class="service-card rounded-xl overflow-hidden shadow-md cursor-pointer transition-all duration-200 border-2"
                             :class="isSelected({{ $service->id }})
                                ? 'border-[#D4AF37] ' + (darkMode ? 'bg-gray-700' : 'bg-yellow-50')
                                : 'border-transparent hover:shadow-lg ' + (darkMode ? 'bg-gray-700' : 'bg-white')">
                            <img src="{{ $service->image ? asset('storage/services/' . $service->image) : 'https://placehold.co/300x200?text='.urlencode($service->name) }}"
                                 alt="{{ $service->name }}"
                                 class="w-full h-32 object-cover">
                            <div class="p-3">
                                <h4 class="font-semibold" :class="darkMode ? 'text-white' : 'text-gray-800'">{{ $service->name }}</h4>
                                <p class="text-sm text-[#D4AF37] font-bold">Rp {{ number_format($service->price,0,',','.') }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Hidden inputs untuk service_ids --}}
                <template x-for="id in selectedIds" :key="id">
                    <input type="hidden" name="service_ids[]" :value="id">
                </template>

                <div class="mt-6 flex justify-end space-x-2">
                    <button type="button" @click="resetForm()" class="px-4 py-2 border rounded-lg transition"
                    :class="darkMode ? 'border-gray-600 text-gray-300 hover:bg-gray-600' : 'border-gray-300 text-gray-700 hover:bg-gray-100'">Reset</button>
                    <button type="submit" class="px-4 py-2 bg-[#D4AF37] text-white rounded-lg hover:bg-[#b8942f] transition">Simpan Booking</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Panel Kanan: Ringkasan --}}
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
                                <span class="text-xs ml-1">(1x)</span>
                                <span class="text-xs ml-1">Rp <span x-text="formatPrice(service.price)"></span></span>
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

        {{-- Modal Tambah Customer (Alpine.js) --}}
    <div x-show="isCustomerModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="isCustomerModalOpen" @click="closeCustomerModal()" class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"></div>
            <div x-show="isCustomerModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
                 :class="darkMode ? 'bg-gray-800' : 'bg-white'">
                <div class="px-6 pt-6 pb-4">
                    <h3 class="text-lg font-medium leading-6" :class="darkMode ? 'text-white' : 'text-gray-900'">Tambah Customer Baru</h3>
                    <div class="mt-4 space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1" :class="darkMode ? 'text-gray-300' : 'text-gray-700'">Nama Lengkap</label>
                            <input type="text" x-model="customerForm.name" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-[#D4AF37]"
                                   :class="darkMode ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-300 text-gray-900'"
                                   placeholder="Masukkan nama lengkap">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1" :class="darkMode ? 'text-gray-300' : 'text-gray-700'">No. Telepon</label>
                            <input type="text" x-model="customerForm.phone" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-[#D4AF37]"
                                   :class="darkMode ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-300 text-gray-900'"
                                   placeholder="Contoh: 08xxxxxxxxxx">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1" :class="darkMode ? 'text-gray-300' : 'text-gray-700'">Email</label>
                            <input type="email" x-model="customerForm.email" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-[#D4AF37]"
                                   :class="darkMode ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-300 text-gray-900'"
                                   placeholder="name@example.com">
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 flex justify-end space-x-3" :class="darkMode ? 'bg-gray-700' : 'bg-gray-50'">
                    <button type="button" @click="closeCustomerModal()"
                            class="px-4 py-2 border rounded-lg transition"
                            :class="darkMode ? 'border-gray-600 text-gray-300 hover:bg-gray-600' : 'border-gray-300 text-gray-700 hover:bg-gray-100'">Batal</button>
                    <button type="button" @click="saveCustomer()"
                            class="px-4 py-2 bg-[#D4AF37] text-white rounded-lg hover:bg-[#b8942f] transition">Simpan</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    function bookingForm() {
        return {
            servicesData: @json($services->map(fn($s) => ['id' => $s->id, 'name' => $s->name, 'price' => (float) $s->price])),
            selectedIds: [],
            selectedServices: [],
            customerId: '',
            barberId: '',
            bookingTime: '',
            paymentMethod: 'cash',
            notes: '',
            isCustomerModalOpen: false,
            customerForm: { name: '', email: '', phone: '' },

            init() {
                $('#customer_select').select2({
                    placeholder: '-- Pilih Customer --',
                    allowClear: true,
                }).on('change', (e) => { this.customerId = e.target.value; });
                $('#barber_select').select2({
                    placeholder: '-- Pilih Barber --',
                    allowClear: true,
                }).on('change', (e) => { this.barberId = e.target.value; });

                window.addEventListener('themeChanged', (e) => {
                    this.darkMode = e.detail.darkMode;

                    // Destroy & reinit select2
                    $('#customer_select, #barber_select').each(function() {
                        $(this).select2('destroy').select2({
                            placeholder: $(this).data('placeholder'),
                            allowClear: true,
                        });
                    });
                });
            },

            toggleService(id) {
                const service = this.servicesData.find(s => s.id === id);
                if (!service) return;
                const index = this.selectedIds.indexOf(id);
                if (index === -1) {
                    this.selectedIds.push(id);
                    this.selectedServices.push({ id: service.id, name: service.name, price: service.price });
                } else {
                    this.selectedIds.splice(index, 1);
                    this.selectedServices = this.selectedServices.filter(s => s.id !== id);
                }
            },
            isSelected(id) {
                return this.selectedIds.includes(id);
            },
            get totalPrice() {
                return this.selectedServices.reduce((sum, s) => sum + s.price, 0);
            },
            formatPrice(price) {
                return price.toLocaleString('id-ID');
            },
            resetForm() {
                this.selectedIds = [];
                this.selectedServices = [];
                this.bookingTime = '';
                this.paymentMethod = 'cash';
                this.notes = '';
                // Reset select2
                $('#customer_select').val('').trigger('change');
                $('#barber_select').val('').trigger('change');
            },
            // Modal Customer (Alpine.js)
            openCustomerModal() {
                this.customerForm = { name: '', email: '', phone: '' };
                this.isCustomerModalOpen = true;
            },
            closeCustomerModal() {
                this.isCustomerModalOpen = false;
                this.customerForm = { name: '', email: '', phone: '' };
            },
            saveCustomer() {
                if (!this.customerForm.name.trim()) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Nama wajib diisi!',
                        confirmButtonColor: '#D4AF37',
                        background: this.darkMode ? '#1f2937' : '#fff',
                        color: this.darkMode ? '#e5e7eb' : '#1f2937'
                    });
                    return;
                }
                fetch("{{ route('admin.customers.quick-store') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(this.customerForm)
                })
                .then(res => {
                    const contentType = res.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        throw new Error('Response bukan JSON. Status: ' + res.status);
                    }
                    return res.json().then(data => ({ ok: res.ok, status: res.status, data }));
                })
                .then(({ ok, status, data }) => {
                    if (ok && data.id) {
                        // Tambah option baru ke select tanpa jQuery
                        const select = document.getElementById('customer_select');
                        const newOption = document.createElement('option');
                        newOption.value = data.id;
                        newOption.text = data.name;
                        newOption.selected = true;
                        select.appendChild(newOption);
                        // Trigger Select2 update jika sudah ter-load
                        if (typeof $ !== 'undefined' && $(select).data('select2')) {
                            $(select).trigger('change');
                        }
                        this.customerId = data.id;
                        this.closeCustomerModal();
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: 'Customer baru ditambahkan.',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true,
                            background: this.darkMode ? '#1f2937' : '#fff',
                            color: this.darkMode ? '#e5e7eb' : '#1f2937'
                        });
                    } else if (status === 422 && data.errors) {
                        let pesan = Object.values(data.errors).flat().join('\n');
                        Swal.fire({
                            icon: 'error',
                            title: 'Validasi Gagal!',
                            text: pesan,
                            confirmButtonColor: '#D4AF37',
                            background: this.darkMode ? '#1f2937' : '#fff',
                            color: this.darkMode ? '#e5e7eb' : '#1f2937'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: data.message || 'Terjadi kesalahan.',
                            confirmButtonColor: '#D4AF37',
                            background: this.darkMode ? '#1f2937' : '#fff',
                            color: this.darkMode ? '#e5e7eb' : '#1f2937'
                        });
                    }
                })
                .catch(err => {
                    console.error('saveCustomer error:', err);
                    // Cek apakah customer berhasil disimpan meski response error
                    // dengan reload data customer via AJAX
                    fetch("{{ route('admin.customers.index') }}", {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                    }).then(() => {}).catch(() => {});

                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: err.message || 'Terjadi kesalahan.',
                        confirmButtonColor: '#D4AF37',
                        background: this.darkMode ? '#1f2937' : '#fff',
                        color: this.darkMode ? '#e5e7eb' : '#1f2937'
                    });
                });
            }
        }
    }
</script>
@endpush
@endsection