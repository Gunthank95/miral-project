@extends('layouts.admin')

@section('title', 'Manajemen Item Pekerjaan')

@section('admin_content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    {{-- Daftar Item Pekerjaan --}}
    <div class="md:col-span-2">
        <div class="bg-white rounded shadow p-4">
            <h2 class="text-xl font-semibold mb-4">Daftar Item Pekerjaan Standar</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-200">
                        <tr>
                            <th class="text-left px-4 py-2">Nama Pekerjaan</th>
                            <th class="text-left px-4 py-2">Satuan</th>
                            <th class="text-left px-4 py-2">Aksi</th> {{-- <-- HEADER BARU DITAMBAHKAN DI SINI --}}
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($workItems as $workItem)
                            <tr class="border-t">
                                <td class="px-4 py-2">{{ $workItem->name }}</td>
                                <td class="px-4 py-2">{{ $workItem->unit }}</td>
                                <td class="px-4 py-2"> {{-- <-- TOMBOL BARU DITAMBAHKAN DI SINI --}}
                                    <a href="{{ route('superadmin.work-items.materials.index', $workItem->id) }}" class="text-blue-600 hover:underline text-xs">
										Kelola Material
									</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                {{-- Colspan diubah menjadi 3 --}}
                                <td colspan="3" class="text-center p-4 text-gray-500">
                                    Belum ada data pekerjaan standar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Form Tambah Item Pekerjaan --}}
    <div>
        <div class="bg-white rounded shadow p-4">
            <h2 class="text-xl font-semibold mb-4">Tambah Item Baru</h2>
            @if (session('success'))
                <div class="bg-green-100 text-green-700 p-3 rounded mb-4">{{ session('success') }}</div>
            @endif
            <form action="{{ route('superadmin.work-items.store') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium">Nama Pekerjaan</label>
                    <input type="text" name="name" id="name" required class="mt-1 w-full border rounded px-3 py-2" placeholder="Contoh: Galian Tanah Pondasi">
                </div>
                <div class="mb-4">
                    <label for="unit" class="block text-sm font-medium">Satuan</label>
                    <input type="text" name="unit" id="unit" required class="mt-1 w-full border rounded px-3 py-2" placeholder="Contoh: m3">
                </div>
                <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded">Simpan</button>
            </form>
        </div>
    </div>
</div>
@endsection