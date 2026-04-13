@extends('layouts.owner')

@section('title', 'Detail Log')
@section('header', 'Detail Log Activity')

@section('content')
<div class="rounded-lg shadow p-6" :class="darkMode ? 'bg-gray-800' : 'bg-white'">
    <div class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div :class="darkMode ? 'text-gray-300' : 'text-gray-600'"><strong>ID Log:</strong> {{ $log->id }}</div>
            <div :class="darkMode ? 'text-gray-300' : 'text-gray-600'"><strong>Waktu:</strong> {{ $log->created_at->format('d/m/Y H:i:s') }}</div>
            <div :class="darkMode ? 'text-gray-300' : 'text-gray-600'"><strong>User:</strong> {{ $log->user_name }}</div>
            <div :class="darkMode ? 'text-gray-300' : 'text-gray-600'"><strong>Role:</strong> {{ ucfirst($log->user_role) }}</div>
            <div :class="darkMode ? 'text-gray-300' : 'text-gray-600'"><strong>Aksi:</strong> {{ $log->action }}</div>
            <div :class="darkMode ? 'text-gray-300' : 'text-gray-600'"><strong>Modul:</strong> {{ $log->module }}</div>
            <div :class="darkMode ? 'text-gray-300' : 'text-gray-600'"><strong>IP Address:</strong> {{ $log->ip_address }}</div>
            <div :class="darkMode ? 'text-gray-300' : 'text-gray-600'"><strong>User Agent:</strong> <span class="break-all">{{ $log->user_agent }}</span></div>
            <div class="col-span-2" :class="darkMode ? 'text-gray-300' : 'text-gray-600'"><strong>Deskripsi:</strong><br> {{ $log->description }}</div>
        </div>
    </div>
    <div class="mt-6">
        <a href="{{ route('owner.log_activity.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">Kembali</a>
    </div>
</div>
@endsection