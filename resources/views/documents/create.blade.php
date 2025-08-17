@extends('layouts.app')

@section('title', 'Unggah ' . $categoryName)

@section('content')
<div class="p-4 sm:p-6">
    <header class="bg-white shadow p-4 rounded-lg mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Unggah Dokumen Baru</h1>
        <p class="text-sm text-gray-500">
            Proyek: {{ $package->project->name }} - Paket: {{ $package->name }}
        </p>
    </header>

    <main>
        <form action="{{ route('documents.store', $package->id) }}" method="POST" enctype="multipart/form-data" class="bg-white rounded shadow p-6">
            @csrf
            {{-- Input tersembunyi untuk mengirim kategori yang aktif --}}
            <input type="hidden" name="category" value="{{ $categoryName }}">

            <div class="space-y-4">
                <div class="bg-gray-50 p-2 rounded-md">
                    <p class="text-sm font-semibold text-gray-700">Kategori Dokumen: {{ $categoryName }}</p>
                </div>

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Nama / Judul Dokumen</label>
                    <input type="text" name="name" id="name" required class="mt-1 w-full border rounded px-3 py-2" placeholder="Contoh: Shop Drawing Pondasi Tipe A">
                </div>
                
                <div>
                    <label for="document_file" class="block text-sm font-medium text-gray-700">Pilih File</label>
                    <input type="file" name="document_file" id="document_file" required class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"/>
                </div>
            </div>

            <div class="pt-6 border-t mt-6 flex items-center space-x-4">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700">
                    Unggah Dokumen
                </button>
                <a href="{{ route('documents.index', ['package' => $package->id, 'category' => $categoryKey]) }}" class="text-sm text-gray-600">Batal</a>
            </div>
        </form>
    </main>
</div>
@endsection