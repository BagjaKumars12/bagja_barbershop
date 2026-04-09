@extends('layouts.kasir')

@section('title', 'Lihat Customers')
@section('header', 'Customers')

@section('content')
<div class="space-y-6" x-data="customerManager()">
    {{-- Header dan tombol tambah --}}
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-xl font-semibold" :class="darkMode ? 'text-white' : 'text-gray-800'">Lihat Customers</h2>
            <p class="text-sm mt-1" :class="darkMode ? 'text-gray-400' : 'text-gray-600'">{{ $customers->total() }} customers terdaftar di sistem</p>
        </div>
        <button @click="openCreateModal()" class="px-4 py-2 bg-[#D4AF37] text-white rounded-lg hover:bg-[#b8942f] transition flex items-center space-x-2">
            <i class="fas fa-plus"></i><span>Tambah Customer</span>
        </button>
    </div>

    {{-- Search --}}
    <div class="relative max-w-md">
        <form method="GET" action="{{ route('kasir.customers.index') }}" class="relative">
            <input type="text" name="search" placeholder="Cari nama atau nomor HP..." value="{{ request('search') }}"
                class="w-full pl-10 pr-10 py-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-[#D4AF37]"
                :class="darkMode ? 'bg-gray-700 border-gray-600 text-white placeholder-gray-400' : 'bg-white border-gray-300 text-gray-900 placeholder-gray-500'">
            <button type="submit" class="absolute left-3 top-3 text-gray-400"><i class="fas fa-search"></i></button>
            @if(request('search'))
                <a href="{{ route('kasir.customers.index') }}" class="absolute right-3 top-3 text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></a>
            @endif
        </form>
    </div>

    {{-- Tabel Customers --}}
    <div class="overflow-x-auto rounded-lg shadow" :class="darkMode ? 'bg-gray-800' : 'bg-white'">
        <table class="min-w-full divide-y" :class="darkMode ? 'divide-gray-700' : 'divide-gray-200'">
            <thead :class="darkMode ? 'bg-gray-700' : 'bg-gray-50'">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">#</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">Nama</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">No. Hp</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">Total Kunjungan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">Kunjungan Terakhir</th>
                </tr>
            </thead>
            <tbody class="divide-y" :class="darkMode ? 'divide-gray-700' : 'divide-gray-200'">
                @forelse($customers as $index => $customer)
                    <tr :class="darkMode ? 'bg-gray-800' : 'bg-white'">
                        <td class="px-6 py-4 whitespace-nowrap text-sm" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">{{ $customers->firstItem() + $index }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm" :class="darkMode ? 'text-white' : 'text-gray-900'">{{ $customer->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">{{ $customer->email ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">{{ $customer->phone ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">{{ $customer->total_visits }}x</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">{{ $customer->last_visit_formatted }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-sm" :class="darkMode ? 'text-gray-400' : 'text-gray-500'">
                            <i class="fas fa-users fa-2x mb-2 opacity-50"></i>
                            <p>Tidak ada customer ditemukan.</p>
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
    <div class="mt-6">{{ $customers->appends(request()->query())->links() }}</div>

    {{-- MODAL CREATE/EDIT --}}
    <div x-show="isModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="isModalOpen" @click="closeModal()" class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"></div>
            <div x-show="isModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full" :class="darkMode ? 'bg-gray-800' : 'bg-white'">
                <form method="POST" :action="form.id ? updateUrl : storeUrl" id="customerForm">
                    @csrf
                    <input type="hidden" name="_method" x-bind:value="form.id ? 'PUT' : 'POST'">
                    <div class="px-6 pt-6 pb-4">
                        <h3 class="text-lg font-medium leading-6" :class="darkMode ? 'text-white' : 'text-gray-900'"><span x-text="modalTitle"></span></h3>
                        <div class="mt-4 space-y-4">
                            {{-- Nama --}}
                            <div>
                                <label class="block text-sm font-medium mb-1" :class="darkMode ? 'text-gray-300' : 'text-gray-700'">Nama</label>
                                <input type="text" name="name" x-model="form.name" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-[#D4AF37]" :class="darkMode ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-300 text-gray-900'" required>
                            </div>
                            {{-- Email --}}
                            <div>
                                <label class="block text-sm font-medium mb-1" :class="darkMode ? 'text-gray-300' : 'text-gray-700'">Email</label>
                                <input type="email" name="email" x-model="form.email" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-[#D4AF37]" :class="darkMode ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-300 text-gray-900'">
                            </div>
                            {{-- No. HP --}}
                            <div>
                                <label class="block text-sm font-medium mb-1" :class="darkMode ? 'text-gray-300' : 'text-gray-700'">No. HP</label>
                                <input type="text" name="phone" x-model="form.phone" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-[#D4AF37]" :class="darkMode ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-300 text-gray-900'">
                            </div>
                        </div>
                    </div>
                    <div class="px-6 py-4 flex justify-end space-x-3" :class="darkMode ? 'bg-gray-700' : 'bg-gray-50'">
                        <button type="button" @click="closeModal()" class="px-4 py-2 border rounded-lg transition" :class="darkMode ? 'border-gray-600 text-gray-300 hover:bg-gray-600' : 'border-gray-300 text-gray-700 hover:bg-gray-100'">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-[#D4AF37] text-white rounded-lg hover:bg-[#b8942f] transition"><span x-text="modalSubmitText"></span></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let storeUrl = "{{ route('kasir.customers.store') }}";

    function customerManager() {
        return {
            isModalOpen: false,
            modalTitle: '',
            modalSubmitText: '',
            form: { id: null, name: '', email: '', phone: '', total_visits: 0, last_visit: '' },
            storeUrl: storeUrl,
            get updateUrl() {
                return this.form.id ? updateBaseUrl.replace('__ID__', this.form.id) : '';
            },
            resetForm() {
                this.form = { id: null, name: '', email: '', phone: '', total_visits: 0, last_visit: '' };
            },
            openCreateModal() {
                this.resetForm();
                this.modalTitle = 'Tambah Customer Baru';
                this.modalSubmitText = 'Simpan';
                this.isModalOpen = true;
            },
            openEditModal(customer) {
                this.resetForm();
                this.form.id = customer.id;
                this.form.name = customer.name;
                this.form.email = customer.email;
                this.form.phone = customer.phone;
                this.form.total_visits = customer.total_visits;
                this.form.last_visit = customer.last_visit ? customer.last_visit.substring(0,10) : '';
                this.modalTitle = 'Edit Customer';
                this.modalSubmitText = 'Update';
                this.isModalOpen = true;
            },
            closeModal() {
                this.isModalOpen = false;
                this.resetForm();
            },
            confirmDelete(id, name) {
                const isDarkMode = document.documentElement.classList.contains('dark');
                Swal.fire({
                    title: 'Hapus Customer?',
                    text: `Apakah Anda yakin ingin menghapus customer "${name}"?`,
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