@extends('layouts.kasir')

@section('title', 'Daftar Barbers')
@section('header', 'Barbers')

@section('content')
<div class="space-y-6" x-data="barberManager()">
    {{-- Header dan tombol tambah --}}
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-xl font-semibold" :class="darkMode ? 'text-white' : 'text-gray-800'">Kelola Barbers</h2>
            <p class="text-sm mt-1" :class="darkMode ? 'text-gray-400' : 'text-gray-600'">{{ $barbers->total() }} barbers terdaftar di sistem</p>
        </div>
    </div>

    {{-- Search --}}
    <div class="relative max-w-md">
        <form method="GET" action="{{ route('kasir.barbers') }}" class="relative">
            <input type="text" name="search" placeholder="Cari nama atau spesialisasi..." value="{{ request('search') }}"
                class="w-full pl-10 pr-10 py-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-[#D4AF37]"
                :class="darkMode ? 'bg-gray-700 border-gray-600 text-white placeholder-gray-400' : 'bg-white border-gray-300 text-gray-900 placeholder-gray-500'">
            <button type="submit" class="absolute left-3 top-3 text-gray-400"><i class="fas fa-search"></i></button>
            @if(request('search'))
                <a href="{{ route('kasir.barbers') }}" class="absolute right-3 top-3 text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></a>
            @endif
        </form>
    </div>

    {{-- Daftar Barbers (Grid Card) --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($barbers as $barber)
            <div class="rounded-lg shadow p-6 relative" :class="darkMode ? 'bg-gray-800' : 'bg-white'">
                {{-- Status badge di pojok kanan atas --}}
                <div class="absolute top-4 right-4 flex space-x-2">
                    <span class="px-2 py-1 text-xs rounded-full"
                        :class="{
                            'bg-green-100 text-green-800': {{ $barber->is_active ? 'true' : 'false' }},
                            'bg-red-100 text-red-800': {{ !$barber->is_active ? 'true' : 'false' }}
                        }">
                        {{ $barber->status_label }}
                    </span>
                </div>

                {{-- Foto dan Nama --}}
                <div class="flex items-center space-x-4">
                    @if($barber->image)
                        <img src="{{ asset('storage/barbers/' . $barber->image) }}" class="w-16 h-16 rounded-full object-cover">
                    @else
                        <div class="w-16 h-16 rounded-full bg-[#D4AF37] flex items-center justify-center text-white font-bold text-xl">
                            {{ strtoupper(substr($barber->name, 0, 1)) }}
                        </div>
                    @endif
                    <div>
                        <h3 class="text-lg font-bold" :class="darkMode ? 'text-white' : 'text-gray-900'">{{ $barber->name }}</h3>
                        <p class="text-sm mt-1" :class="darkMode ? 'text-gray-400' : 'text-gray-600'">{{ $barber->specialties ?? '-' }}</p>
                    </div>
                </div>

                {{-- Rating dan jumlah job --}}
                <div class="mt-4 flex items-center space-x-2">
                    <div class="flex items-center text-yellow-500">
                        <i class="fas fa-star"></i>
                        <span class="ml-1 text-sm" :class="darkMode ? 'text-gray-300' : 'text-gray-700'">{{ $barber->rating_formatted }}</span>
                    </div>
                    <span class="text-sm" :class="darkMode ? 'text-gray-400' : 'text-gray-500'">• {{ $barber->jobs_count }} Job</span>
                </div>

                {{-- Pengalaman --}}
                <div class="mt-3">
                    <p class="text-sm" :class="darkMode ? 'text-gray-400' : 'text-gray-500'">
                        <i class="fas fa-briefcase mr-1"></i> Pengalaman : {{ $barber->experience_years }} Tahun
                    </p>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-8" :class="darkMode ? 'text-gray-400' : 'text-gray-500'">
                <i class="fas fa-user-md fa-3x mb-2 opacity-50"></i>
                <p>Tidak ada barber ditemukan.</p>
                @if(request('search'))
                    <p class="text-xs mt-1">Coba kata kunci lain.</p>
                @endif
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="mt-6">{{ $barbers->appends(request()->query())->links() }}</div>

@push('scripts')
<script>
    let deleteBaseUrl = "{{ route('admin.barbers.destroy', ['id' => '__ID__']) }}";
    let storeUrl = "{{ route('admin.barbers.store') }}";
    let updateBaseUrl = "{{ route('admin.barbers.update', ['id' => '__ID__']) }}";

    function barberManager() {
        return {
            isModalOpen: false,
            modalTitle: '',
            modalSubmitText: '',
            form: { id: null, name: '', specialties: '', rating: 0, jobs_count: 0, experience_years: 0, is_active: '1' },
            storeUrl: storeUrl,
            get updateUrl() {
                return this.form.id ? updateBaseUrl.replace('__ID__', this.form.id) : '';
            },
            resetForm() {
                this.form = { id: null, name: '', specialties: '', rating: 0, jobs_count: 0, experience_years: 0, is_active: '1' };
            },
            openCreateModal() {
                this.resetForm();
                this.modalTitle = 'Tambah Barber Baru';
                this.modalSubmitText = 'Simpan';
                this.isModalOpen = true;
            },
            openEditModal(barber) {
                this.resetForm();
                this.form.id = barber.id;
                this.form.name = barber.name;
                this.form.specialties = barber.specialties;
                this.form.rating = barber.rating;
                this.form.jobs_count = barber.jobs_count;
                this.form.experience_years = barber.experience_years;
                this.form.is_active = barber.is_active ? '1' : '0';
                this.modalTitle = 'Edit Barber';
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
                    title: 'Hapus Barber?',
                    text: `Apakah Anda yakin ingin menghapus barber "${name}"?`,
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