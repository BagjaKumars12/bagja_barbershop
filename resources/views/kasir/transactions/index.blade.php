@extends('layouts.kasir')

@section('title', 'Transaksi')
@section('header', 'Transaksi')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6" x-data="transactionForm()" x-init="init()">
    {{-- Panel Kiri: Form Transaksi --}}
    <div class="lg:col-span-2 space-y-6">
        <div class="rounded-lg shadow p-6" :class="darkMode ? 'bg-gray-800' : 'bg-white'">
            <h3 class="text-lg font-semibold mb-4" :class="darkMode ? 'text-white' : 'text-gray-900'">Data Transaksi</h3>
            <form method="POST" action="{{ route('kasir.transactions.store') }}" id="transactionForm">
                @csrf

                <!-- Customer dengan Select2 + Tombol Tambah -->
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1" :class="darkMode ? 'text-gray-300' : 'text-gray-700'">Customer</label>
                    <div class="flex gap-2">
                        <select name="customer_id" id="customer_select" class="flex-1 px-3 py-2 border rounded-lg focus:ring-2 focus:ring-[#D4AF37]" required>
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

                <!-- Barber dengan Select2 -->
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1" :class="darkMode ? 'text-gray-300' : 'text-gray-700'">Barber</label>
                    <select name="barber_id" id="barber_select" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-[#D4AF37]" required>
                        <option value="">-- Pilih Barber --</option>
                        @foreach($barbers as $barber)
                            <option value="{{ $barber->id }}">{{ $barber->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Catatan -->
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1" :class="darkMode ? 'text-gray-300' : 'text-gray-700'">Catatan</label>
                    <textarea name="notes" rows="2" x-model="notes" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-[#D4AF37]"
                            :class="darkMode ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-300 text-gray-900'"
                            placeholder="Catatan Tambahan (opsional)"></textarea>
                </div>

                <!-- Pilih Service (Card Grid) -->
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-3" :class="darkMode ? 'text-gray-300' : 'text-gray-700'">Pilih Layanan</label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                        @foreach($services as $service)
                        <div @click="toggleService({{ $service->id }})"
                             class="service-card rounded-xl overflow-hidden shadow-md cursor-pointer transition-all duration-200 border-2"
                             :class="{
                                'border-[#D4AF37] bg-yellow-50 dark:bg-gray-700': isSelected({{ $service->id }}),
                                'border-transparent bg-white dark:bg-gray-800 hover:shadow-lg': !isSelected({{ $service->id }})
                             }">
                            {{-- Gambar Service (gunakan asset atau placeholder) --}}
                            <img src="{{ asset('storage/services/' . $service->image) }}"
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

                <!-- Hidden input untuk services_json -->
                <input type="hidden" name="services_json" x-model="servicesJson">

                <!-- Metode Pembayaran -->
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1" :class="darkMode ? 'text-gray-300' : 'text-gray-700'">Metode Pembayaran</label>
                    <div class="flex space-x-4">
                        <label class="inline-flex items-center">
                            <input type="radio" name="payment_method" value="cash" x-model="paymentMethod" class="mr-1" checked> Cash
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="payment_method" value="qris" x-model="paymentMethod" class="mr-1"> Qris
                        </label>
                    </div>
                </div>

                <!-- Jumlah Bayar & Kembalian -->
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1" :class="darkMode ? 'text-gray-300' : 'text-gray-700'">Jumlah Bayar</label>
                    <input type="number" name="paid_amount" x-model="paidAmount" @input="calculateChange"
                        class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-[#D4AF37]"
                        :class="darkMode ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-300 text-gray-900'"
                        placeholder="Masukkan jumlah uang" required>
                    <p class="mt-1 text-sm" :class="darkMode ? 'text-gray-400' : 'text-gray-600'">
                        Kembalian: <span x-text="'Rp ' + formatPrice(changeAmount)"></span>
                    </p>
                </div>

                <div class="mt-6 flex justify-end space-x-2">
                    <button type="button" @click="resetForm()" class="px-4 py-2 border rounded-lg transition"
                            :class="darkMode ? 'border-gray-600 text-gray-300 hover:bg-gray-600' : 'border-gray-300 text-gray-700 hover:bg-gray-100'">Reset</button>
                    <button type="submit" class="px-4 py-2 bg-[#D4AF37] text-white rounded-lg hover:bg-[#b8942f] transition">Proses Transaksi</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Panel Kanan: Ringkasan Pemesanan --}}
    <div class="space-y-6">
        <div class="rounded-lg shadow p-6 sticky top-6" :class="darkMode ? 'bg-gray-800' : 'bg-white'">
            <h3 class="text-lg font-semibold mb-4" :class="darkMode ? 'text-white' : 'text-gray-900'">Total Pemesanan</h3>
            <div class="space-y-3">
                <div>
                    <span class="text-sm font-medium" :class="darkMode ? 'text-gray-300' : 'text-gray-700'">Layanan :</span>
                    <ul class="mt-2 space-y-1 list-disc list-inside">
                        <template x-for="item in serviceList" :key="item.id">
                            <li class="text-sm" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">
                                <span x-text="getServiceName(item.id)"></span>
                                <span class="text-xs ml-1">(1x)</span>
                                <span class="text-xs ml-1">Rp <span x-text="formatPrice(getServicePrice(item.id))"></span></span>
                            </li>
                        </template>
                        <li x-show="serviceList.length === 0" class="text-sm text-gray-500">- Belum ada layanan dipilih -</li>
                    </ul>
                </div>
                <div class="flex justify-between border-t pt-2 mt-2" :class="darkMode ? 'border-gray-700' : 'border-gray-200'">
                    <span class="font-semibold" :class="darkMode ? 'text-white' : 'text-gray-900'">Total :</span>
                    <span class="font-bold text-[#D4AF37]" x-text="'Rp ' + formatPrice(totalPrice)"></span>
                </div>
            </div>
        </div>
    </div>

    {{-- Antrian Hari Ini (di bawah) --}}
    <div class="lg:col-span-3 mt-6">
        <div class="rounded-lg shadow p-6" :class="darkMode ? 'bg-gray-800' : 'bg-white'">
            <h3 class="text-lg font-semibold mb-4" :class="darkMode ? 'text-white' : 'text-gray-900'">Antrian Hari Ini</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr>
                            <th class="text-left py-2" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">No</th>
                            <th class="text-left py-2" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">Customer</th>
                            <th class="text-left py-2" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">Service</th>
                            <th class="text-left py-2" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">Barber</th>
                            <th class="text-left py-2" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">Status</th>
                            <th class="text-left py-2" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($queues as $index => $booking)
                        <tr class="border-b" :class="darkMode ? 'border-gray-700' : 'border-gray-200'">
                            <td class="py-2" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">{{ $index+1 }}</td>
                            <td class="py-2" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">{{ $booking->customer->name }}</td>
                            <td class="py-2" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">{{ $booking->services->pluck('name')->implode(', ') }}</td>
                            <td class="py-2" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">{{ $booking->barber->name }}</td>
                            <td class="py-2">
                                <span class="px-2 py-1 text-xs rounded-full 
                                    @if($booking->status == 'pending') bg-yellow-100 text-yellow-800
                                    @elseif($booking->status == 'confirmed') bg-blue-100 text-blue-800
                                    @endif">
                                    {{ $booking->status == 'pending' ? 'Menunggu' : 'Dikonfirmasi' }}
                                </span>
                            </td>
                            <td class="py-2">
                                <button @click="payBooking({{ $booking->id }})" 
                                        class="text-green-600 hover:text-green-800 transition"
                                        :class="darkMode ? 'text-green-400 hover:text-green-300' : 'text-green-600 hover:text-green-800'">
                                    <i class="fas fa-money-bill-wave"></i> Bayar
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-4 text-center text-gray-500">Tidak ada antrian hari ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Modal Tambah Customer --}}
<div id="customerModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeCustomerModal()"></div>
        <div class="relative bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full" :class="darkMode ? 'bg-gray-800' : 'bg-white'">
            <div class="px-6 py-4">
                <h3 class="text-lg font-medium leading-6" :class="darkMode ? 'text-white' : 'text-gray-900'">Tambah Customer Baru</h3>
                <form id="addCustomerForm" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium" :class="darkMode ? 'text-gray-300' : 'text-gray-700'">Nama Lengkap</label>
                        <input type="text" name="name" required class="mt-1 w-full px-3 py-2 border rounded-lg focus:ring-[#D4AF37] focus:border-[#D4AF37]" :class="darkMode ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-300'">
                    </div>
                    <div>
                        <label class="block text-sm font-medium" :class="darkMode ? 'text-gray-300' : 'text-gray-700'">No. Telepon</label>
                        <input type="text" name="phone" class="mt-1 w-full px-3 py-2 border rounded-lg focus:ring-[#D4AF37] focus:border-[#D4AF37]" :class="darkMode ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-300'">
                    </div>
                    <div>
                        <label class="block text-sm font-medium" :class="darkMode ? 'text-gray-300' : 'text-gray-700'">Email</label>
                        <input type="email" name="email" class="mt-1 w-full px-3 py-2 border rounded-lg focus:ring-[#D4AF37] focus:border-[#D4AF37]" :class="darkMode ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-300'">
                    </div>
                </form>
            </div>
            <div class="px-6 py-3 bg-gray-50 flex justify-end space-x-2" :class="darkMode ? 'bg-gray-900' : 'bg-gray-50'">
                <button type="button" @click="closeCustomerModal()" class="px-4 py-2 border rounded-lg" :class="darkMode ? 'border-gray-600 text-gray-300 hover:bg-gray-600' : 'border-gray-300 text-gray-700 hover:bg-gray-100'">Batal</button>
                <button type="button" @click="saveCustomer()" class="px-4 py-2 bg-[#D4AF37] text-white rounded-lg hover:bg-[#b8942f]">Simpan</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<!-- Select2 CSS & JS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    function transactionForm() {
        return {
            darkMode: localStorage.getItem('theme') === 'dark',
            servicesData: @json($services->map(fn($s) => ['id' => $s->id, 'name' => $s->name, 'price' => (float) $s->price])),
            selectedServices: [], // array of service ids
            customerId: '',
            barberId: '',
            notes: '',
            paymentMethod: 'cash',
            paidAmount: 0,
            changeAmount: 0,

            init() {
                // Inisialisasi Select2
                $('#customer_select').select2({
                    placeholder: '-- Pilih Customer --',
                    allowClear: true,
                    theme: this.darkMode ? 'dark' : 'default'
                }).on('change', (e) => { this.customerId = e.target.value; });
                
                $('#barber_select').select2({
                    placeholder: '-- Pilih Barber --',
                    allowClear: true,
                    theme: this.darkMode ? 'dark' : 'default'
                }).on('change', (e) => { this.barberId = e.target.value; });

                // Update tema select2 saat darkMode berubah
                window.addEventListener('themeChanged', (e) => {
                    this.darkMode = e.detail.darkMode;
                    // Re-initialize select2 dengan tema baru
                    $('#customer_select').select2('destroy').select2({
                        theme: this.darkMode ? 'dark' : 'default',
                        placeholder: '-- Pilih Customer --',
                        allowClear: true
                    });
                    $('#barber_select').select2('destroy').select2({
                        theme: this.darkMode ? 'dark' : 'default',
                        placeholder: '-- Pilih Barber --',
                        allowClear: true
                    });
                });
            },

            // Service toggling
            toggleService(id) {
                const index = this.selectedServices.indexOf(id);
                if (index === -1) {
                    this.selectedServices.push(id);
                } else {
                    this.selectedServices.splice(index, 1);
                }
            },
            isSelected(id) {
                return this.selectedServices.includes(id);
            },
            get serviceList() {
                return this.selectedServices.map(id => ({ id, quantity: 1 }));
            },
            get servicesJson() {
                return JSON.stringify(this.serviceList);
            },
            get totalPrice() {
                return this.selectedServices.reduce((sum, id) => {
                    let service = this.servicesData.find(s => s.id === id);
                    return sum + (service ? service.price : 0);
                }, 0);
            },
            getServiceName(id) {
                let service = this.servicesData.find(s => s.id === id);
                return service ? service.name : '';
            },
            getServicePrice(id) {
                let service = this.servicesData.find(s => s.id === id);
                return service ? service.price : 0;
            },
            formatPrice(price) {
                return price.toLocaleString('id-ID');
            },
            calculateChange() {
                let paid = parseFloat(this.paidAmount) || 0;
                this.changeAmount = paid >= this.totalPrice ? paid - this.totalPrice : 0;
            },
            resetForm() {
                this.selectedServices = [];
                this.customerId = '';
                this.barberId = '';
                this.notes = '';
                this.paymentMethod = 'cash';
                this.paidAmount = 0;
                this.changeAmount = 0;
                $('#customer_select').val(null).trigger('change');
                $('#barber_select').val(null).trigger('change');
            },

            // Modal Customer
            openCustomerModal() {
                $('#customerModal').removeClass('hidden');
            },
            closeCustomerModal() {
                $('#customerModal').addClass('hidden');
                $('#addCustomerForm')[0].reset();
            },
            saveCustomer() {
                let form = document.getElementById('addCustomerForm');
                let formData = new FormData(form);
                fetch("{{ route('kasir.customers.store') }}", {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.id) {
                        let newOption = new Option(data.name, data.id, false, true);
                        $('#customer_select').append(newOption).trigger('change');
                        this.closeCustomerModal();
                        Swal.fire('Berhasil', 'Customer baru ditambahkan', 'success');
                    } else {
                        Swal.fire('Gagal', data.message || 'Terjadi kesalahan', 'error');
                    }
                })
                .catch(err => {
                    Swal.fire('Error', 'Gagal menyimpan customer', 'error');
                });
            },

            // Proses pembayaran antrian dengan tema dinamis
            payBooking(bookingId) {
                let isDark = this.darkMode;
                Swal.fire({
                    background: isDark ? '#1f2937' : '#fff',
                    color: isDark ? '#e5e7eb' : '#000',
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

    // Untuk tombol bayar di antrian (jika menggunakan onclick biasa, kita panggil dari Alpine)
    window.payBookingFromAlpine = function(bookingId) {
        let component = document.querySelector('[x-data]').__x.$data;
        component.payBooking(bookingId);
    }
</script>
@endpush
@endsection