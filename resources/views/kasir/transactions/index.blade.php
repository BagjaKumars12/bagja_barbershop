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

                {{-- Customer --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1" :class="darkMode ? 'text-gray-300' : 'text-gray-700'">Customer</label>
                    <select name="customer_id" x-model="customerId" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-[#D4AF37]"
                            :class="darkMode ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-300 text-gray-900'" required>
                        <option value="">-- Pilih Customer --</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Barber --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1" :class="darkMode ? 'text-gray-300' : 'text-gray-700'">Barber</label>
                    <select name="barber_id" x-model="barberId" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-[#D4AF37]"
                            :class="darkMode ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-300 text-gray-900'" required>
                        <option value="">-- Pilih Barber --</option>
                        @foreach($barbers as $barber)
                            <option value="{{ $barber->id }}">{{ $barber->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Catatan --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1" :class="darkMode ? 'text-gray-300' : 'text-gray-700'">Catatan</label>
                    <textarea name="notes" rows="2" x-model="notes" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-[#D4AF37]"
                              :class="darkMode ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-300 text-gray-900'"
                              placeholder="Catatan Tambahan (opsional)"></textarea>
                </div>

                {{-- Pilih Service (Grid Card seperti booking) --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1" :class="darkMode ? 'text-gray-300' : 'text-gray-700'">Pilih Service</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($services as $service)
                        <div class="border rounded-lg p-3 flex justify-between items-center"
                             :class="darkMode ? 'border-gray-700 bg-gray-700' : 'border-gray-200 bg-gray-50'">
                            <div>
                                <p class="font-medium" :class="darkMode ? 'text-white' : 'text-gray-800'">{{ $service->name }}</p>
                                <p class="text-sm" :class="darkMode ? 'text-gray-400' : 'text-gray-600'">Rp {{ number_format($service->price,0,',','.') }}</p>
                            </div>
                            <div class="flex items-center space-x-2">
                                <button type="button" @click="decreaseQuantity({{ $service->id }})" 
                                        class="w-8 h-8 rounded-full bg-red-500 text-white hover:bg-red-600 flex items-center justify-center">-</button>
                                <span class="w-8 text-center font-bold" :class="darkMode ? 'text-gray-300' : 'text-gray-600'" x-text="quantities[{{ $service->id }}] || 0"></span>
                                <button type="button" @click="increaseQuantity({{ $service->id }})" 
                                        class="w-8 h-8 rounded-full bg-green-500 text-white hover:bg-green-600 flex items-center justify-center">+</button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Hidden inputs untuk services --}}
                <template x-for="(item, index) in serviceList" :key="item.id">
                    <input type="hidden" name="services[__INDEX__][id]" :value="item.id" x-model="item.id">
                    <input type="hidden" name="services[__INDEX__][quantity]" :value="item.quantity" x-model="item.quantity">
                </template>

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
                                <span class="text-xs ml-1">(<span x-text="item.quantity"></span>x)</span>
                                <span class="text-xs ml-1">Rp <span x-text="formatPrice(getServicePrice(item.id) * item.quantity)"></span></span>
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

    {{-- Antrian Hari Ini (di bawah, full width) --}}
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
                                <button onclick="payBooking({{ $booking->id }})" class="text-green-600 hover:text-green-800">
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

@push('scripts')
<script>
    function transactionForm() {
        return {
            darkMode: localStorage.getItem('theme') === 'dark',
            quantities: @json($services->mapWithKeys(fn($s) => [$s->id => 0])),
            servicesData: @json($services->map(fn($s) => ['id' => $s->id, 'name' => $s->name, 'price' => (float) $s->price])),
            customerId: '',
            barberId: '',
            notes: '',
            init() {
                window.addEventListener('themeChanged', (e) => {
                    this.darkMode = e.detail.darkMode;
                });
            },
            get serviceList() {
                let list = [];
                for (let [id, qty] of Object.entries(this.quantities)) {
                    if (qty > 0) {
                        list.push({ id: parseInt(id), quantity: qty });
                    }
                }
                return list;
            },
            get totalPrice() {
                return this.serviceList.reduce((sum, item) => {
                    let service = this.servicesData.find(s => s.id === item.id);
                    return sum + (service.price * item.quantity);
                }, 0);
            },
            increaseQuantity(id) {
                this.quantities[id] = (this.quantities[id] || 0) + 1;
            },
            decreaseQuantity(id) {
                if (this.quantities[id] > 0) this.quantities[id]--;
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
            resetForm() {
                for (let id in this.quantities) {
                    this.quantities[id] = 0;
                }
                this.customerId = '';
                this.barberId = '';
                this.notes = '';
            }
        }
    }

    function payBooking(bookingId) {
        Swal.fire({
            title: 'Proses Pembayaran',
            html: `
                <div class="text-left">
                    <p>Total: <span id="modalTotal"></span></p>
                    <label>Metode Pembayaran:</label>
                    <select id="payment_method" class="swal2-input">
                        <option value="cash">Cash</option>
                        <option value="card">Kartu</option>
                        <option value="transfer">Transfer</option>
                    </select>
                    <label>Jumlah Bayar:</label>
                    <input type="number" id="paid_amount" class="swal2-input" placeholder="Masukkan jumlah bayar">
                    <p>Kembalian: <span id="change">0</span></p>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Bayar',
            cancelButtonText: 'Batal',
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
</script>
@endpush
@endsection