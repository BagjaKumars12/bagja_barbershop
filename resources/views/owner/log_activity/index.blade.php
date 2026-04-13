@extends('layouts.owner')   {{-- sesuaikan dengan layout owner Anda --}}

@section('title', 'Log Activity')
@section('header', 'Log Activity')

@section('content')
<div x-data="logManager()" x-init="init()" class="space-y-6">
    <div class="rounded-lg shadow p-6" :class="darkMode ? 'bg-gray-800' : 'bg-white'">
        <h3 class="text-lg font-semibold mb-4" :class="darkMode ? 'text-white' : 'text-gray-900'">Filter Log</h3>
        <form method="GET" activity="{{ route('owner.log_activity.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <input type="text" name="search" placeholder="Cari user/aktifitas/module..." value="{{ request('search') }}"
                   class="px-3 py-2 border rounded-lg focus:ring-2 focus:ring-[#D4AF37]"
                   :class="darkMode ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-300 text-gray-900'">
            
            <select name="role" class="px-3 py-2 border rounded-lg focus:ring-2 focus:ring-[#D4AF37]"
                    :class="darkMode ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-300 text-gray-900'">
                <option value="">Semua Role</option>
                @foreach($roles as $role)
                    <option value="{{ $role }}" {{ request('role') == $role ? 'selected' : '' }}>{{ ucfirst($role) }}</option>
                @endforeach
            </select>
            
            <select name="module" class="px-3 py-2 border rounded-lg focus:ring-2 focus:ring-[#D4AF37]"
                    :class="darkMode ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-300 text-gray-900'">
                <option value="">Semua Modul</option>
                @foreach($modules as $module)
                    <option value="{{ $module }}" {{ request('module') == $module ? 'selected' : '' }}>{{ $module }}</option>
                @endforeach
            </select>
            
            <input type="date" name="start_date" value="{{ request('start_date') }}" placeholder="Start Date"
                   class="px-3 py-2 border rounded-lg focus:ring-2 focus:ring-[#D4AF37]"
                   :class="darkMode ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-300 text-gray-900'">
            
            <input type="date" name="end_date" value="{{ request('end_date') }}" placeholder="End Date"
                   class="px-3 py-2 border rounded-lg focus:ring-2 focus:ring-[#D4AF37]"
                   :class="darkMode ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-300 text-gray-900'">
            
            <div class="flex gap-2 md:col-span-5">
                <button type="submit" class="px-4 py-2 bg-[#D4AF37] text-white rounded-lg hover:bg-[#b8942f] transition">Filter</button>
                <a href="{{ route('owner.log_activity.index') }}" class="px-4 py-2 border rounded-lg transition"
                   :class="darkMode ? 'border-gray-600 text-gray-300 hover:bg-gray-600' : 'border-gray-300 text-gray-700 hover:bg-gray-100'">Reset</a>
            </div>
        </form>
    </div>

    <div class="overflow-x-auto rounded-lg shadow" :class="darkMode ? 'bg-gray-800' : 'bg-white'">
        <table class="min-w-full divide-y" :class="darkMode ? 'divide-gray-700' : 'divide-gray-200'">
            <thead :class="darkMode ? 'bg-gray-700' : 'bg-gray-50'">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">Waktu</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">User</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">Role</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">Aktifitas</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">Modul</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">Deskripsi</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">IP</th>
                    <th class="px-4 py-3 text-center text-xs font-medium uppercase" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">Detail</th>
                </tr>
            </thead>
            <tbody class="divide-y" :class="darkMode ? 'divide-gray-700' : 'divide-gray-200'">
                @forelse($logs as $log)
                <tr :class="darkMode ? 'bg-gray-800' : 'bg-white'">
                    <td class="px-4 py-3 text-sm" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                    <td class="px-4 py-3 text-sm" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">{{ $log->user_name }}</td>
                    <td class="px-4 py-3 text-sm" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">{{ ucfirst($log->user_role) }}</td>
                    <td class="px-4 py-3 text-sm" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">
                        <span class="px-2 py-1 rounded-full text-xs 
                            @if($log->activity == 'CREATE') bg-green-100 text-green-800
                            @elseif($log->activity == 'UPDATE') bg-blue-100 text-blue-800
                            @elseif($log->activity == 'DELETE') bg-red-100 text-red-800
                            @elseif($log->activity == 'READ') bg-gray-100 text-gray-800
                            @elseif($log->activity == 'LOGIN') bg-purple-100 text-purple-800
                            @else bg-gray-100 text-gray-800
                            @endif">
                            {{ $log->activity }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">{{ $log->module }}</td>
                    <td class="px-4 py-3 text-sm max-w-md truncate" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">{{ Str::limit($log->description, 60) }}</td>
                    <td class="px-4 py-3 text-sm" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">{{ $log->ip_address }}</td>
                    <td class="px-4 py-3 text-center">
                        <a href="{{ route('owner.log_activity.show', $log->id) }}" class="text-blue-600 hover:text-blue-800">Lihat</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-6 text-gray-500">Belum ada aktivitas tercatat.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $logs->links() }}</div>
</div>

<script>
    function logManager() {
        return {
            darkMode: localStorage.getItem('theme') === 'dark',
            init() {
                const updateDarkMode = () => {
                    this.darkMode = document.documentElement.classList.contains('dark');
                };
                updateDarkMode();
                const observer = new MutationObserver(updateDarkMode);
                observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
                window.addEventListener('themeChanged', (e) => {
                    this.darkMode = e.detail.darkMode;
                });
            }
        }
    }
</script>
@endsection