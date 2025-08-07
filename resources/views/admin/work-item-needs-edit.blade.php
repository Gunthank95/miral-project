@extends('layouts.admin')

@section('title', 'Edit Kebutuhan Material')

@section('admin_content')
<div>
    <div class="bg-white rounded shadow p-4">
        <h2 class="text-xl font-semibold">Edit Kebutuhan Material</h2>
        <p class="text-gray-600">Untuk Item Pekerjaan: <span class="font-bold">{{ $workItem->name }}</span></p>
        <p class="text-gray-600">Material: <span class="font-bold">{{ $need->material->name }}</span></p>

        <form action="{{ route('admin.work-items.materials.update', ['work_item' => $workItem->id, 'need' => $need->id]) }}" method="POST" class="mt-4">
            @csrf
            @method('PUT')
            <div class="mb-4">
                <label for="coefficient" class="block text-sm font-medium">Koefisien Baru</label>
                <input type="number" step="0.0001" name="coefficient" id="coefficient" required value="{{ $need->coefficient }}" class="mt-1 w-full border rounded px-3 py-2">
            </div>
            <div class="flex items-center space-x-4">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Simpan Perubahan</button>
                <a href="{{ route('admin.work-items.materials.index', $workItem->id) }}" class="text-gray-600 text-sm">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection