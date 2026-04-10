@extends('layouts.kasir')

@section('title', 'Lihat Service')
@section('header', 'Service')

@section('content')
<div class="space-y-6" x-data="serviceManager()" x-init="init()">
    {{-- Header dan tombol tambah --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="text-xl font-semibold" :class="darkMode ? 'text-white' : 'text-gray-800'">Lihat Service</h2>
            <p class="text-sm mt-1" :class="darkMode ? 'text-gray-400' : 'text-gray-600'">{{ $services->total() }} service terdaftar di sistem</p>
        </div>
    </div>

    {{-- Search --}}
    <div class="relative max-w-md">
        <form method="GET" action="{{ route('admin.services.index') }}" class="relative">
            <input type="text" name="search" placeholder="Cari service..." value="{{ request('search') }}"
                class="w-full pl-10 pr-10 py-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-[#D4AF37]"
                :class="darkMode ? 'bg-gray-700 border-gray-600 text-white placeholder-gray-400' : 'bg-white border-gray-300 text-gray-900 placeholder-gray-500'">
            <button type="submit" class="absolute left-3 top-3 text-gray-400"><i class="fas fa-search"></i></button>
            @if(request('search'))
                <a href="{{ route('admin.services.index') }}" class="absolute right-3 top-3 text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></a>
            @endif
        </form>
    </div>

    {{-- Grid Card Service --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @forelse($services as $service)
        <div class="rounded-xl overflow-hidden shadow-md transition-all duration-200 border hover:shadow-lg"
             :class="darkMode ? 'bg-gray-800 border-gray-700' : 'bg-white border-gray-200'">
            {{-- Gambar --}}
            <div class="h-40 w-full overflow-hidden bg-gray-200 dark:bg-gray-700">
                @if($service->image)
                    <img src="{{ asset('storage/services/' . $service->image) }}" alt="{{ $service->name }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center">
                        <i class="fas fa-cut text-4xl text-gray-400 dark:text-gray-500"></i>
                    </div>
                @endif
            </div>
            <div class="p-4">
                {{-- Nama & Kategori --}}
                <div class="flex justify-between items-start">
                    <h3 class="font-semibold text-lg" :class="darkMode ? 'text-white' : 'text-gray-900'">{{ $service->name }}</h3>
                    <span class="px-2 py-1 text-xs rounded-full"
                          :class="{
                              'bg-green-100 text-green-800': {{ $service->is_active ? 'true' : 'false' }},
                              'bg-red-100 text-red-800': {{ !$service->is_active ? 'true' : 'false' }}
                          }">
                        {{ $service->is_active ? 'Aktif' : 'Non-aktif' }}
                    </span>
                </div>
                <p class="text-sm mt-1" :class="darkMode ? 'text-gray-400' : 'text-gray-600'">
                    <i class="fas fa-tag mr-1"></i> {{ ucfirst($service->category) }}
                </p>
                <div class="mt-3 space-y-1">
                    <p class="text-sm" :class="darkMode ? 'text-gray-300' : 'text-gray-700'">
                        <i class="far fa-clock mr-1"></i> {{ $service->duration_formatted }}
                    </p>
                    <p class="text-lg font-bold text-[#D4AF37]">
                        {{ $service->price_formatted }}
                    </p>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-12" :class="darkMode ? 'text-gray-400' : 'text-gray-500'">
            <i class="fas fa-cut fa-3x mb-3 opacity-50"></i>
            <p>Tidak ada service ditemukan.</p>
            @if(request('search'))
                <p class="text-sm mt-1">Coba kata kunci lain.</p>
            @endif
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="mt-6">{{ $services->appends(request()->query())->links() }}</div>
</div>

@push('scripts')
<script>

    function serviceManager() {
        return {
            darkMode: localStorage.getItem('theme') === 'dark',
            isModalOpen: false,
            modalTitle: '',
            modalSubmitText: '',
            form: { id: null, name: '', image: null, category: 'potong', duration: '', price: '', is_active: '1' },
            previewUrl: null,
            storeUrl: storeUrl,
            get updateUrl() {
                return this.form.id ? updateBaseUrl.replace('__ID__', this.form.id) : '';
            },
            init() {
                // Deteksi perubahan tema
                const updateDarkMode = () => {
                    this.darkMode = document.documentElement.classList.contains('dark');
                };
                updateDarkMode();
                const observer = new MutationObserver(updateDarkMode);
                observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
                window.addEventListener('themeChanged', (e) => {
                    this.darkMode = e.detail.darkMode;
                });
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
            }
        }
    }
</script>
@endpush
@endsection