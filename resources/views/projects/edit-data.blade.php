@extends('layouts.app')

@section('title', 'Edit Data Proyek - ' . $project->name)

@section('content')
<div class="p-4 sm:p-6">
    <header class="bg-white shadow p-4 rounded-lg mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Edit Data Utama Proyek</h1>
        <p class="text-sm text-gray-500">{{ $project->name }}</p>
    </header>

    <form action="{{ route('projects.update-data', $project->id) }}" method="POST" class="bg-white shadow-md rounded-lg p-6">
        @csrf
        @method('PATCH')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Kolom Kiri --}}
            <div class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Nama Proyek</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $project->name) }}" required class="mt-1 w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label for="location" class="block text-sm font-medium text-gray-700">Lokasi Proyek</label>
                    <input type="text" name="location" id="location" value="{{ old('location', $project->location) }}" required class="mt-1 w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700">Tanggal Mulai Proyek</label>
                    <input type="date" name="start_date" id="start_date" value="{{ old('start_date', $project->start_date) }}" class="mt-1 w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700">Rencana Selesai Proyek</label>
                    <input type="date" name="end_date" id="end_date" value="{{ old('end_date', $project->end_date) }}" class="mt-1 w-full border rounded px-3 py-2">
                </div>
            </div>
            
            {{-- Kolom Kanan --}}
            <div class="space-y-4">
                <div>
                    <label for="land_area" class="block text-sm font-medium text-gray-700">Luas Lahan (m²)</label>
                    <input type="number" step="0.01" name="land_area" id="land_area" value="{{ old('land_area', $project->land_area) }}" class="mt-1 w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label for="building_area" class="block text-sm font-medium text-gray-700">Luas Bangunan (m²)</label>
                    <input type="number" step="0.01" name="building_area" id="building_area" value="{{ old('building_area', $project->building_area) }}" class="mt-1 w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label for="floor_count" class="block text-sm font-medium text-gray-700">Jumlah Lantai</label>
                    <input type="number" name="floor_count" id="floor_count" value="{{ old('floor_count', $project->floor_count) }}" class="mt-1 w-full border rounded px-3 py-2">
                </div>
            </div>
        </div>

        <div class="mt-6 pt-6 border-t flex justify-end items-center space-x-4">
            <a href="{{ route('projects.data-proyek', $project->id) }}" class="text-sm text-gray-600 hover:underline">Batal</a>
            <button type="submit" class="bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection