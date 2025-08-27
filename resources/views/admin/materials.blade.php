@extends('layouts.admin')

@section('title', 'Manajemen Material')

@section('admin_content')
{{-- TAMBAHKAN: Inisialisasi Alpine.js untuk kontrol modal --}}
<div x-data="{ 
    isModalOpen: false, 
    editFormAction: '', 
    materialId: null, 
    materialName: '', 
    materialUnit: '' 
}">
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
                                <th class="text-left px-4 py-2">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($materials as $material)
                                <tr class="border-t">
                                    <td class="px-4 py-2">{{ $material->name }}</td>
                                    <td class="px-4 py-2">{{ $material->unit }}</td>
                                    <td class="px-4 py-2 whitespace-nowrap">
                                        {{-- GANTI: Tombol Edit untuk membuka modal --}}
                                        <button @click="
                                            isModalOpen = true;
                                            materialId = {{ $material->id }};
                                            materialName = '{{ addslashes($material->name) }}';
                                            materialUnit = '{{ addslashes($material->unit) }}';
                                            editFormAction = '{{ route('superadmin.materials.update', $material->id) }}';
                                        " class="text-yellow-600 hover:underline mr-3 text-xs">Edit</button>
                                        
                                        <form action="{{ route('superadmin.materials.destroy', $material->id) }}" method="POST" class="inline" onsubmit="return confirm('Anda yakin ingin menghapus material ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:underline text-xs">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center p-4 text-gray-500">
                                        Belum ada data material.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Kolom Kanan: Form Tambah Material (Tidak Berubah) --}}
        <div>
            <div class="bg-white rounded shadow p-4">
                <h2 class="text-xl font-semibold mb-4">Tambah Material Baru</h2>

                @if (session('success'))
                    <div class="bg-green-100 text-green-700 p-3 rounded mb-4">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="bg-red-100 text-red-700 p-3 rounded mb-4">{{ session('error') }}</div>
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

    {{-- TAMBAHKAN: Struktur HTML untuk Modal Edit --}}
    <div x-show="isModalOpen" 
         class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center" 
         x-cloak>
        <div @click.away="isModalOpen = false" class="relative mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg leading-6 font-medium text-gray-900 border-b pb-2">Edit Material</h3>
                <form :action="editFormAction" method="POST" class="mt-4 space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label for="edit_material_name" class="block text-sm font-medium text-gray-700">Nama Material</label>
                        <input type="text" name="name" id="edit_material_name" x-model="materialName" required class="mt-1 w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label for="edit_material_unit" class="block text-sm font-medium text-gray-700">Satuan</label>
                        <input type="text" name="unit" id="edit_material_unit" x-model="materialUnit" required class="mt-1 w-full border rounded px-3 py-2">
                    </div>
                    <div class="items-center px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t pt-4">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 sm:ml-3 sm:w-auto sm:text-sm">
                            Simpan Perubahan
                        </button>
                        <button type="button" @click="isModalOpen = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection