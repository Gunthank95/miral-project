@extends('layouts.admin')

@section('title', 'Manajemen Material')

@section('admin_content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    {{-- Kolom Kiri: Daftar Material --}}
    <div class="md:col-span-2">
        <div class="bg-white rounded shadow p-4">
            <h2 class="text-xl font-semibold mb-4">Daftar Material</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-200">
                        <tr>
                            <th class="text-left px-4 py-2">Nama Material</th>
                            <th class="text-left px-4 py-2">Satuan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($materials as $material)
                            <tr class="border-t">
                                <td class="px-4 py-2">{{ $material->name }}</td>
                                <td class="px-4 py-2">{{ $material->unit }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-center p-4 text-gray-500">
                                    Belum ada data material.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Kolom Kanan: Form Tambah Material --}}
    <div>
        <div class="bg-white rounded shadow p-4">
            <h2 class="text-xl font-semibold mb-4">Tambah Material Baru</h2>

            @if (session('success'))
                <div class="bg-green-100 text-green-700 p-3 rounded mb-4">{{ session('success') }}</div>
            @endif
            
            <form action="{{ route('superadmin.materials.store') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700">Nama Material</label>
                    <input type="text" name="name" id="name" required class="mt-1 w-full border rounded px-3 py-2" placeholder="Contoh: Semen Portland">
                </div>
                <div class="mb-4">
                    <label for="unit" class="block text-sm font-medium text-gray-700">Satuan</label>
                    <input type="text" name="unit" id="unit" required class="mt-1 w-full border rounded px-3 py-2" placeholder="Contoh: Sak, m3, Batang">
                </div>
                <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">
                    Simpan
                </button>
            </form>
        </div>
    </div>
</div>
@endsection