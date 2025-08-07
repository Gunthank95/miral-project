@extends('layouts.app')

@section('title', 'Detail Paket Pekerjaan')

@section('content')
    <div class="p-4 sm:p-6">
        <header class="bg-white shadow p-4 rounded-lg mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Paket: {{ $package->name }}</h1>
            <p class="text-sm text-gray-500">
                Proyek: {{ $project->name }}
            </p>
        </header>

        <main>
            <div class="bg-white rounded shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Detail Paket</h2>
                <p>Deskripsi: {{ $package->description ?? 'Tidak ada deskripsi.' }}</p>
                
                {{-- Di sini Anda bisa menambahkan daftar laporan harian, RAB, dll. --}}
            </div>
        </main>
    </div>
@endsection