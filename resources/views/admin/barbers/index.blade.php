@extends('layouts.admin')

@section('title', 'Kelola Barbers')
@section('header', 'Barbers')

@section('content')
<div class="space-y-6" x-data="barberManager()">
    {{-- Header dan tombol tambah --}}
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-xl font-semibold" :class="darkMode ? 'text-white' : 'text-gray-800'">Kelola Barbers</h2>
            <p class="text-sm mt-1" :class="darkMode ? 'text-gray-400' : 'text-gray-600'">{{ $barbers->total() }} barbers terdaftar di sistem</p>
        </div>
        <button @click="openCreateModal()" class="px-4 py-2 bg-[#D4AF37] text-white rounded-lg hover:bg-[#b8942f] transition flex items-center space-x-2">
            <i class="fas fa-plus"></i><span>Tambah Barber</span>
        </button>
    </div>

    {{-- Search --}}
    <div class="relative max-w-md">
        <form method="GET" action="{{ route('admin.barbers.index') }}" class="relative">
            <input type="text" name="search" placeholder="Cari nama atau spesialisasi..." value="{{ request('search') }}"
                class="w-full pl-10 pr-10 py-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-[#D4AF37]"
                :class="darkMode ? 'bg-gray-700 border-gray-600 text-white placeholder-gray-400' : 'bg-white border-gray-300 text-gray-900 placeholder-gray-500'">
            <button type="submit" class="absolute left-3 top-3 text-gray-400"><i class="fas fa-search"></i></button>
            @if(request('search'))
                <a href="{{ route('admin.barbers.index') }}" class="absolute right-3 top-3 text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></a>
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

                {{-- Tombol edit/hapus --}}
                <div class="absolute bottom-4 right-4 flex space-x-2">
                    <button @click="openEditModal({{ $barber }})" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button @click="confirmDelete({{ $barber->id }}, '{{ addslashes($barber->name) }}')" class="text-gray-400 hover:text-red-600">
                        <i class="fas fa-trash-alt"></i>
                    </button>
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

    {{-- MODAL CREATE/EDIT --}}
    <div x-show="isModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="isModalOpen" @click="closeModal()" class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"></div>
            <div x-show="isModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full" :class="darkMode ? 'bg-gray-800' : 'bg-white'">
                <form method="POST" :action="form.id ? updateUrl : storeUrl" id="barberForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="_method" x-bind:value="form.id ? 'PUT' : 'POST'">
                    <div class="px-6 pt-6 pb-4">
                        <h3 class="text-lg font-medium leading-6" :class="darkMode ? 'text-white' : 'text-gray-900'"><span x-text="modalTitle"></span></h3>
                        <div class="mt-4 space-y-4">
                            {{-- Foto --}}
                            <div>
                                <label class="block text-sm font-medium mb-1" :class="darkMode ? 'text-gray-300' : 'text-gray-700'">Foto</label>
                                <div class="flex items-center space-x-4">
                                    <template x-if="previewUrl">
                                        <img :src="previewUrl" class="w-16 h-16 rounded-full object-cover">
                                    </template>
                                    <template x-else>
                                        <div class="w-16 h-16 rounded-full bg-gray-300 flex items-center justify-center">
                                            <i class="fas fa-user text-gray-500"></i>
                                        </div>
                                    </template>
                                    <input type="file" name="image" id="image" @change="handleImageChange"
                                        class="block text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-[#D4AF37] file:text-white hover:file:bg-[#b8942f]"
                                        accept="image/*">
                                </div>
                                <p class="text-xs mt-1" :class="darkMode ? 'text-gray-400' : 'text-gray-500'">Format: JPG, PNG, GIF (max 2MB)</p>
                            </div>
                            {{-- Nama --}}
                            <div>
                                <label class="block text-sm font-medium mb-1" :class="darkMode ? 'text-gray-300' : 'text-gray-700'">Nama</label>
                                <input type="text" name="name" x-model="form.name" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-[#D4AF37]" :class="darkMode ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-300 text-gray-900'" required>
                            </div>
                            {{-- Spesialisasi --}}
                            <div>
                                <label class="block text-sm font-medium mb-1" :class="darkMode ? 'text-gray-300' : 'text-gray-700'">Spesialisasi</label>
                                <input type="text" name="specialties" x-model="form.specialties" placeholder="Contoh: Fade, Classic Cut" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-[#D4AF37]" :class="darkMode ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-300 text-gray-900'">
                            </div>
                            {{-- Rating --}}
                            <div>
                                <label class="block text-sm font-medium mb-1" :class="darkMode ? 'text-gray-300' : 'text-gray-700'">Rating (0-5)</label>
                                <input type="number" name="rating" x-model="form.rating" step="0.1" min="0" max="5" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-[#D4AF37]" :class="darkMode ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-300 text-gray-900'">
                            </div>
                            {{-- Jumlah Job --}}
                            <div>
                                <label class="block text-sm font-medium mb-1" :class="darkMode ? 'text-gray-300' : 'text-gray-700'">Jumlah Job</label>
                                <input type="number" name="jobs_count" x-model="form.jobs_count" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-[#D4AF37]" :class="darkMode ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-300 text-gray-900'">
                            </div>
                            {{-- Pengalaman --}}
                            <div>
                                <label class="block text-sm font-medium mb-1" :class="darkMode ? 'text-gray-300' : 'text-gray-700'">Pengalaman (tahun)</label>
                                <input type="number" name="experience_years" x-model="form.experience_years" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-[#D4AF37]" :class="darkMode ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-300 text-gray-900'">
                            </div>
                            {{-- Status --}}
                            <div>
                                <label class="block text-sm font-medium mb-1" :class="darkMode ? 'text-gray-300' : 'text-gray-700'">Status</label>
                                <select name="is_active" x-model="form.is_active"
                                    class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-[#D4AF37]"
                                    :class="darkMode ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-300 text-gray-900'">
                                    <option value="1">Aktif</option>
                                    <option value="0">Non-aktif</option>
                                </select>
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
    let deleteBaseUrl = "{{ route('admin.barbers.destroy', ['id' => '__ID__']) }}";
    let storeUrl = "{{ route('admin.barbers.store') }}";
    let updateBaseUrl = "{{ route('admin.barbers.update', ['id' => '__ID__']) }}";

    function barberManager() {
        return {
            isModalOpen: false,
            modalTitle: '',
            modalSubmitText: '',
            form: { id: null, name: '', specialties: '', rating: 0, jobs_count: 0, experience_years: 0, is_active: '1' },
            previewUrl: null,
            storeUrl: storeUrl,
            get updateUrl() {
                return this.form.id ? updateBaseUrl.replace('__ID__', this.form.id) : '';
            },
            handleImageChange(event) {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.previewUrl = e.target.result;
                    };
                    reader.readAsDataURL(file);
                } else {
                    this.previewUrl = null;
                }
            },
            resetForm() {
                this.form = { id: null, name: '', specialties: '', rating: 0, jobs_count: 0, experience_years: 0, is_active: '1' };
                this.previewUrl = null;
                const fileInput = document.getElementById('image');
                if (fileInput) fileInput.value = '';
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
                if (barber.image) {
                    this.previewUrl = "{{ asset('storage/barbers') }}/" + barber.image;
                } else {
                    this.previewUrl = null;
                }
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