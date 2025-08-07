@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="p-4 sm:p-6">
    @if($activeProject)
        {{-- TAMPILKAN INI JIKA ADA PROYEK YANG AKTIF --}}
        <header class="bg-white shadow p-4 rounded-lg mb-6">
            <div class="flex flex-wrap justify-between items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Proyek Aktif: {{ $activeProject->name }}</h1>
                    <p class="text-sm text-gray-500">{{ $activeProject->location }}</p>
                </div>
                {{-- Tombol-tombol Aksi Utama --}}
                <div class="flex space-x-2">
                    <a href="{{ route('rab.index', $activeProject->id) }}" class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700">
                        Kelola RAB
                    </a>
                    <a href="{{ route('daily_reports.index', $activeProject->id) }}" class="bg-green-600 text-white px-4 py-2 rounded text-sm hover:bg-green-700">
                        Laporan Harian
                    </a>
                </div>
            </div>
        </header>

        <main>
            <div class="bg-white rounded shadow p-4">
                <h2 class="text-xl font-semibold">Selamat Datang di Dashboard</h2>
                <p class="text-gray-600 mt-2">Pilih proyek lain dari dropdown di atas untuk mengganti konteks, atau gunakan tombol aksi untuk mulai bekerja pada proyek ini.</p>
            </div>
        </main>
    @else
        {{-- TAMPILKAN INI JIKA USER BELUM PUNYA PROYEK SAMA SEKALI --}}
        <div class="text-center p-10 bg-white rounded-lg shadow">
            <h1 class="text-2xl font-bold text-gray-700">Selamat Datang!</h1>
            <p class="mt-2 text-gray-500">Anda saat ini belum ditugaskan ke proyek manapun. Silakan hubungi administrator.</p>
        </div>
    @endif
</div>
@endsection