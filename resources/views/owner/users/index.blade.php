@extends('layouts.owner')

@section('title', 'Lihat Users')
@section('header', 'Lihat Users')

@section('content')
<div class="space-y-6" x-data="userManager()">
    {{-- Header dan tombol tambah --}}
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-xl font-semibold" :class="darkMode ? 'text-white' : 'text-gray-800'">Lihat Users</h2>
            <p class="text-sm mt-1" :class="darkMode ? 'text-gray-400' : 'text-gray-600'">{{ $users->total() }} user terdaftar di sistem</p>
        </div>
    </div>

    {{-- Search --}}
    <div class="relative max-w-md">
        <form method="GET" action="{{ route('admin.users') }}" class="relative">
            <input type="text" name="search" placeholder="Cari users..." value="{{ request('search') }}"
                class="w-full pl-10 pr-10 py-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-[#D4AF37]"
                :class="darkMode ? 'bg-gray-700 border-gray-600 text-white placeholder-gray-400' : 'bg-white border-gray-300 text-gray-900 placeholder-gray-500'">
            <button type="submit" class="absolute left-3 top-3 text-gray-400"><i class="fas fa-search"></i></button>
            @if(request('search'))
                <a href="{{ route('admin.users') }}" class="absolute right-3 top-3 text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></a>
            @endif
        </form>
    </div>

    {{-- Daftar Users --}}
    <div class="space-y-4">
        @forelse($users as $user)
            <div class="rounded-lg shadow p-4 flex items-center justify-between" :class="darkMode ? 'bg-gray-800' : 'bg-white'">
                <div class="flex items-center space-x-4">
                    {{-- Avatar --}}
                    @if($user->avatar)
                        <img src="{{ asset('storage/avatars/' . $user->avatar) }}" class="w-12 h-12 rounded-full object-cover">
                    @else
                        <div class="w-12 h-12 rounded-full bg-[#D4AF37] flex items-center justify-center text-white font-bold text-lg">
                            {{ strtoupper(substr($user->username, 0, 1)) }}
                        </div>
                    @endif
                    <div>
                        <p class="font-semibold" :class="darkMode ? 'text-white' : 'text-gray-900'">{{ $user->username }}</p>
                        <p class="text-sm" :class="darkMode ? 'text-gray-400' : 'text-gray-500'">{{ $user->email }}</p>
                        <p class="text-xs mt-1" :class="darkMode ? 'text-gray-500' : 'text-gray-400'">Role: {{ ucfirst($user->role) }}</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="px-3 py-1 rounded-full text-xs font-medium"
                            :class="{
                                'bg-green-100 text-green-800': {{ $user->is_active ? 'true' : 'false' }},
                                'bg-red-100 text-red-800': {{ !$user->is_active ? 'true' : 'false' }}
                            }">
                            {{ $user->is_active ? 'Aktif' : 'Non-aktif' }}
                    </span>
                </div>
            </div>
        @empty
            <div class="text-center py-8" :class="darkMode ? 'text-gray-400' : 'text-gray-500'">
                <i class="fas fa-users fa-3x mb-2 opacity-50"></i>
                <p>Tidak ada user ditemukan.</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="mt-6">{{ $users->appends(request()->query())->links() }}</div>

{{-- MODAL CREATE/EDIT --}}
<div x-show="isModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        {{-- Backdrop --}}
        <div x-show="isModalOpen" @click="closeModal()"
             class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
        </div>

        {{-- Modal Panel --}}
        <div x-show="isModalOpen"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="inline-block align-bottom rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full relative z-10"
             :class="darkMode ? 'bg-gray-800' : 'bg-white'">

            {{-- Form untuk create/update --}}
            <form method="POST" :action="form.id ? updateUrl : storeUrl" id="userForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="_method" x-bind:value="form.id ? 'PUT' : 'POST'">

                <div class="px-6 pt-6 pb-4">
                    <h3 class="text-lg font-medium leading-6" :class="darkMode ? 'text-white' : 'text-gray-900'">
                        <span x-text="modalTitle"></span>
                    </h3>
                    <div class="mt-4 space-y-4">
                        {{-- Foto Profil --}}
                        <div>
                            <label class="block text-sm font-medium mb-1" :class="darkMode ? 'text-gray-300' : 'text-gray-700'">Foto Profil</label>
                            <div class="flex items-center space-x-4">
                                {{-- Preview gambar --}}
                                <template x-if="previewUrl">
                                    <img :src="previewUrl" class="w-16 h-16 rounded-full object-cover">
                                </template>
                                <template x-else>
                                    <div class="w-16 h-16 rounded-full bg-gray-300 flex items-center justify-center">
                                        <i class="fas fa-user text-gray-500"></i>
                                    </div>
                                </template>
                                {{-- Input file --}}
                                <input type="file" name="avatar" id="avatar" @change="handleAvatarChange"
                                    class="block text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-[#D4AF37] file:text-white hover:file:bg-[#b8942f]"
                                    accept="image/*">
                            </div>
                            <p class="text-xs mt-1" :class="darkMode ? 'text-gray-400' : 'text-gray-500'">Format: JPG, PNG, GIF (max 2MB)</p>
                        </div>
                        {{-- Username --}}
                        <div>
                            <label class="block text-sm font-medium mb-1" :class="darkMode ? 'text-gray-300' : 'text-gray-700'">Username</label>
                            <input type="text" name="username" x-model="form.username"
                                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-[#D4AF37] focus:border-transparent"
                                :class="darkMode ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-300 text-gray-900'"
                                required>
                        </div>
                        {{-- Email --}}
                        <div>
                            <label class="block text-sm font-medium mb-1" :class="darkMode ? 'text-gray-300' : 'text-gray-700'">Email</label>
                            <input type="email" name="email" x-model="form.email"
                                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-[#D4AF37] focus:border-transparent"
                                :class="darkMode ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-300 text-gray-900'"
                                required>
                        </div>
                        {{-- Password --}}
                        <div>
                            <label class="block text-sm font-medium mb-1" :class="darkMode ? 'text-gray-300' : 'text-gray-700'">Password</label>
                            <input type="password" name="password" x-model="form.password"
                                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-[#D4AF37] focus:border-transparent"
                                :class="darkMode ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-300 text-gray-900'"
                                :required="!form.id">
                            <p x-show="form.id" class="text-xs mt-1" :class="darkMode ? 'text-gray-400' : 'text-gray-500'">
                                Kosongkan jika tidak ingin mengubah password
                            </p>
                        </div>
                        {{-- Role --}}
                        <div>
                            <label class="block text-sm font-medium mb-1" :class="darkMode ? 'text-gray-300' : 'text-gray-700'">Role</label>
                            <select name="role" x-model="form.role"
                                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-[#D4AF37] focus:border-transparent"
                                :class="darkMode ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-300 text-gray-900'"
                                required>
                                <option value="admin">Admin</option>
                                <option value="kasir">Kasir</option>
                                <option value="owner">Owner</option>
                            </select>
                        </div>
                        {{-- Status --}}
                        <div>
                            <label class="block text-sm font-medium mb-1" :class="darkMode ? 'text-gray-300' : 'text-gray-700'">Status</label>
                            <select name="is_active" x-model="form.is_active"
                                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-[#D4AF37]"
                                :class="darkMode ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-300 text-gray-900'"
                                :disabled="form.id == {{ Auth::id() }}">
                                <option value="1">Aktif</option>
                                <option value="0">Non-aktif</option>
                            </select>
                            <p x-show="form.id == {{ Auth::id() }}" class="text-xs mt-1 text-yellow-500">
                                Anda tidak dapat mengubah status sendiri.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 flex justify-end space-x-3" :class="darkMode ? 'bg-gray-700' : 'bg-gray-50'">
                    <button type="button" @click="closeModal()"
                        class="px-4 py-2 border rounded-lg transition"
                        :class="darkMode ? 'border-gray-600 text-gray-300 hover:bg-gray-600' : 'border-gray-300 text-gray-700 hover:bg-gray-100'">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-[#D4AF37] text-white rounded-lg hover:bg-[#b8942f] transition">
                        <span x-text="modalSubmitText"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let deleteBaseUrl = "{{ route('admin.users.destroy', ['id' => '__ID__']) }}";
    let storeUrl = "{{ route('admin.users.store') }}";
    let updateBaseUrl = "{{ route('admin.users.update', ['id' => '__ID__']) }}";
    
    function userManager() {
        return {
            isModalOpen: false,
            isDeleteModalOpen: false,
            modalTitle: '',
            modalSubmitText: '',
            form: { id: null, username: '', email: '', password: '', role: 'kasir', is_active: '1' },
            deleteUrl: '',
            deleteUserName: '',
            previewUrl: null,          // <-- tambahkan properti preview
            storeUrl: storeUrl,
            get updateUrl() {
                return this.form.id ? updateBaseUrl.replace('__ID__', this.form.id) : '';
            },

            // Event handler untuk preview foto
            handleAvatarChange(event) {
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
                this.form = { id: null, username: '', email: '', password: '', role: 'kasir', is_active: '1' };
                this.previewUrl = null;
                const fileInput = document.getElementById('avatar');
                if (fileInput) fileInput.value = '';
            },

            openCreateModal() {
                this.resetForm();
                this.modalTitle = 'Tambah User Baru';
                this.modalSubmitText = 'Simpan';
                this.isModalOpen = true;
            },

            openEditModal(user) {
                this.resetForm();
                this.form.id = user.id;
                this.form.username = user.username;
                this.form.email = user.email;
                this.form.role = user.role;
                this.form.is_active = user.is_active ? '1' : '0';
                // Tampilkan preview avatar jika ada
                if (user.avatar) {
                    this.previewUrl = "{{ asset('storage/avatars') }}/" + user.avatar;
                } else {
                    this.previewUrl = null;
                }
                this.modalTitle = 'Edit User';
                this.modalSubmitText = 'Update';
                this.isModalOpen = true;
            },

            openDeleteModal(userId, userName) {
                this.deleteUserName = userName;
                this.deleteUrl = deleteBaseUrl.replace('__ID__', userId);
                this.isDeleteModalOpen = true;
            },

            closeModal() {
                this.isModalOpen = false;
                this.resetForm();
            },

            closeDeleteModal() {
                this.isDeleteModalOpen = false;
            },

            confirmDelete(userId, userName) {
                const isDarkMode = document.documentElement.classList.contains('dark');
                Swal.fire({
                    title: 'Hapus User?',
                    text: `Apakah Anda yakin ingin menghapus user "${userName}"?`,
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
                        let url = deleteBaseUrl.replace('__ID__', userId);
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
