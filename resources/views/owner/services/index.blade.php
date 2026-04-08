@extends('layouts.owner')

@section('title', 'Lihat Service')
@section('header', 'Service')

@section('content')
<div class="space-y-6" x-data="serviceManager()">
    {{-- Header dan tombol tambah --}}
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-xl font-semibold" :class="darkMode ? 'text-white' : 'text-gray-800'">Lihat Service</h2>
            <p class="text-sm mt-1" :class="darkMode ? 'text-gray-400' : 'text-gray-600'">{{ $services->total() }} service terdaftar di sistem</p>
        </div>
    </div>

    {{-- Search --}}
    <div class="relative max-w-md">
        <form method="GET" action="{{ route('owner.services.index') }}" class="relative">
            <input type="text" name="search" placeholder="Cari service..." value="{{ request('search') }}"
                class="w-full pl-10 pr-10 py-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-[#D4AF37]"
                :class="darkMode ? 'bg-gray-700 border-gray-600 text-white placeholder-gray-400' : 'bg-white border-gray-300 text-gray-900 placeholder-gray-500'">
            <button type="submit" class="absolute left-3 top-3 text-gray-400"><i class="fas fa-search"></i></button>
            @if(request('search'))
                <a href="{{ route('owner.services.index') }}" class="absolute right-3 top-3 text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></a>
            @endif
        </form>
    </div>

    {{-- Tabel Services --}}
    <div class="overflow-x-auto rounded-lg shadow" :class="darkMode ? 'bg-gray-800' : 'bg-white'">
        <table class="min-w-full divide-y" :class="darkMode ? 'divide-gray-700' : 'divide-gray-200'">
            <thead :class="darkMode ? 'bg-gray-700' : 'bg-gray-50'">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">#</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">Nama Layanan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">Foto</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">Kategori</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">Durasi</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">Harga</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y" :class="darkMode ? 'divide-gray-700' : 'divide-gray-200'">
                @forelse($services as $index => $service)
                    <tr :class="darkMode ? 'bg-gray-800' : 'bg-white'">
                        <td class="px-6 py-4 whitespace-nowrap text-sm" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">{{ $services->firstItem() + $index }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm" :class="darkMode ? 'text-white' : 'text-gray-900'">{{ $service->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @if($service->image)
                                <img src="{{ asset('storage/services/' . $service->image) }}" class="w-10 h-10 rounded-full object-cover">
                            @else
                                <div class="w-10 h-10 rounded-full bg-gray-300 flex items-center justify-center">
                                    <i class="fas fa-cut text-gray-500"></i>
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">
                            {{ ucfirst($service->category) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">
                            {{ $service->duration_formatted }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">
                            {{ $service->price_formatted }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full"
                                  :class="{
                                      'bg-green-100 text-green-800': {{ $service->is_active ? 'true' : 'false' }},
                                      'bg-red-100 text-red-800': {{ !$service->is_active ? 'true' : 'false' }}
                                  }">
                                {{ $service->is_active ? 'Aktif' : 'Non-aktif' }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-4 text-center text-sm" :class="darkMode ? 'text-gray-400' : 'text-gray-500'">
                            <i class="fas fa-cut fa-2x mb-2 opacity-50"></i>
                            <p>Tidak ada service ditemukan.</p>
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
    <div class="mt-6">{{ $services->appends(request()->query())->links() }}</div>

@push('scripts')
<script>

    function serviceManager() {
        return {
            isModalOpen: false,
            modalTitle: '',
            modalSubmitText: '',
            form: { id: null, name: '', image: null, category: 'potong', duration: '', price: '', is_active: '1' },
            previewUrl: null,
            storeUrl: storeUrl,
            get updateUrl() {
                return this.form.id ? updateBaseUrl.replace('__ID__', this.form.id) : '';
            },
            handleImageChange(event) {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = (e) => this.previewUrl = e.target.result;
                    reader.readAsDataURL(file);
                } else {
                    this.previewUrl = null;
                }
            },
            resetForm() {
                this.form = { id: null, name: '', image: null, category: 'potong', duration: '', price: '', is_active: '1' };
                this.previewUrl = null;
                const fileInput = document.getElementById('image');
                if (fileInput) fileInput.value = '';
            },
            openCreateModal() {
                this.resetForm();
                this.modalTitle = 'Tambah Service Baru';
                this.modalSubmitText = 'Simpan';
                this.isModalOpen = true;
            },
            openEditModal(service) {
                this.resetForm();
                this.form.id = service.id;
                this.form.name = service.name;
                this.form.category = service.category;
                this.form.duration = service.duration;
                this.form.price = service.price;
                this.form.is_active = service.is_active ? '1' : '0';
                if (service.image) {
                    this.previewUrl = "{{ asset('storage/services') }}/" + service.image;
                }
                this.modalTitle = 'Edit Service';
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
                    title: 'Hapus Service?',
                    text: `Apakah Anda yakin ingin menghapus service "${name}"?`,
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