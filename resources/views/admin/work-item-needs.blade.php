@extends('layouts.admin')

@section('title', 'Kebutuhan Material')

@section('admin_content')
<div>
    <div class="bg-white rounded shadow p-4 mb-6">
        <h2 class="text-xl font-semibold">Kelola Kebutuhan Material</h2>
        <p class="text-gray-600">Untuk Item Pekerjaan: <span class="font-bold">{{ $workItem->name }}</span> (Satuan: {{ $workItem->unit }})</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        {{-- Kolom Kiri: Daftar Kebutuhan Material --}}
        <div class="md:col-span-2">
            <div class="bg-white rounded shadow p-4">
                <h3 class="text-lg font-semibold mb-4">Daftar Kebutuhan Material Standar</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-200">
                            <tr>
                                <th class="text-left px-4 py-2">Nama Material</th>
                                <th class="text-center px-4 py-2">Koefisien</th>
                                <th class="text-left px-4 py-2">Satuan</th>
                                <th class="text-left px-4 py-2">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($needs as $need)
                                <tr class="border-t">
                                    <td class="px-4 py-2">{{ $need->material->name }}</td>
                                    <td class="text-center px-4 py-2">{{ $need->coefficient }}</td>
                                    <td class="px-4 py-2">{{ $need->material->unit }}</td>
                                    <td class="px-4 py-2 flex items-center space-x-2">
                                        <a href="{{ route('admin.work-items.materials.edit', ['work_item' => $workItem->id, 'need' => $need->id]) }}" class="text-blue-600 hover:underline text-xs">Edit</a>
                                        <form action="{{ route('admin.work-items.materials.destroy', ['work_item' => $workItem->id, 'need' => $need->id]) }}" method="POST" onsubmit="return confirm('Anda yakin ingin menghapus material ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:underline text-xs">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center p-4 text-gray-500">
                                        Belum ada kebutuhan material untuk item pekerjaan ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Kolom Kanan: Form Tambah Kebutuhan Material --}}
        <div>
            <div class="bg-white rounded shadow p-4">
                <h3 class="text-lg font-semibold mb-4">Tambah Material</h3>
                @if (session('success'))
                    <div class="bg-green-100 text-green-700 p-3 rounded mb-4">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="bg-red-100 text-red-700 p-3 rounded mb-4">{{ session('error') }}</div>
                @endif
                <form action="{{ route('admin.work-items.materials.store', $workItem->id) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="material_id" class="block text-sm font-medium">Material</label>
                        <select name="material_id" id="material_id" required class="mt-1 w-full border rounded px-3 py-2">
                            <option value="">-- Pilih Material --</option>
                            @foreach ($materials as $material)
                                <option value="{{ $material->id }}">{{ $material->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="coefficient" class="block text-sm font-medium">Koefisien</label>
                        <input type="number" step="0.0001" name="coefficient" id="coefficient" required class="mt-1 w-full border rounded px-3 py-2" placeholder="Contoh: 8.5">
                    </div>
                    <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded">Simpan</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection