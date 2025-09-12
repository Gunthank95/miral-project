@extends('layouts.app')

@section('title', 'Ajukan Revisi Shop Drawing')

@push('styles')
{{-- Style khusus jika diperlukan --}}
<style>
    [x-cloak] { display: none !important; }
</style>
@endpush

@section('content')
<div class="p-4 sm:p-6" x-data="revisionForm()">
    <header class="bg-white shadow p-4 rounded-lg mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Ajukan Revisi Shop Drawing</h1>
                <p class="text-sm text-gray-500">Proyek: {{ $package->project->name }} - Paket: {{ $package->name }}</p>
                <p class="text-sm text-gray-500 mt-2">Revisi untuk Dokumen: <span class="font-semibold">{{ $shop_drawing->document_number }} - {{ $shop_drawing->title }} (Rev. {{ $shop_drawing->revision }})</span></p>
            </div>
            <a href="{{ route('documents.index', ['package' => $package->id]) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded">
                Kembali
            </a>
        </div>
    </header>

    <main class="bg-white p-6 rounded-lg shadow-md">
        <form action="{{ route('documents.store_revision', ['package' => $package->id, 'parent_document' => $shop_drawing->id]) }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            @if ($errors->any())
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                    <p class="font-bold">Terdapat kesalahan:</p>
                    <ul class="mt-2 list-disc list-inside">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            {{-- Info Dokumen --}}
            <div class="mb-6 border-b pb-4">
                <h2 class="text-xl font-semibold mb-4 text-gray-700">Informasi Dokumen Revisi</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="document_number" class="block text-sm font-medium text-gray-700">No. Surat Pengantar</label>
                        <input type="text" id="document_number" name="document_number"
                            class="mt-1 block w-full border rounded-md shadow-sm py-2 px-3"
                            value="{{ old('document_number', $shop_drawing->document_number) }}" required>
                    </div>
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700">Judul Utama Pengajuan</label>
                        <input type="text" id="title" name="title"
                            class="mt-1 block w-full border rounded-md shadow-sm py-2 px-3"
                            value="{{ old('title', $shop_drawing->title) }}" required>
                    </div>
                    <input type="hidden" name="category" value="shop_drawing">
                </div>
            </div>

            {{-- Unggah File PDF --}}
            <div class="mb-6 border-b pb-4">
                <h2 class="text-xl font-semibold mb-4 text-gray-700">Unggah File PDF Revisi</h2>
                <input type="file" name="files[]" multiple
                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                <p class="mt-2 text-xs text-gray-500">Unggah satu atau lebih file PDF baru untuk revisi ini.</p>
            </div>

            {{-- Detail Gambar --}}
            <div class="mb-6 border-b pb-4">
                <h2 class="text-xl font-semibold mb-4 text-gray-700">Detail Gambar dalam Revisi</h2>
                <template x-for="(drawing, index) in drawings" :key="index">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4 items-end bg-gray-50 p-4 rounded-md">
                        <div>
                            <label :for="`drawing_number_${index}`" class="block text-sm font-medium text-gray-700">No. Gambar</label>
                            <input type="text" :id="`drawing_number_${index}`" :name="`drawings[${index}][number]`" class="mt-1 block w-full border rounded-md shadow-sm py-2 px-3" x-model="drawing.number" required>
                        </div>
                        <div class="md:col-span-2">
                            <label :for="`drawing_title_${index}`" class="block text-sm font-medium text-gray-700">Judul Gambar</label>
                            <div class="flex">
                                <input type="text" :id="`drawing_title_${index}`" :name="`drawings[${index}][title]`" class="mt-1 block w-full border rounded-md shadow-sm py-2 px-3" x-model="drawing.title" required>
                                <button type="button" @click="removeDrawing(index)" class="ml-2 p-2 text-red-600 hover:text-red-800" title="Hapus Gambar">
                                     <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1H9a1 1 0 00-1 1v3m-4 0h16" /></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
                <button type="button" @click="addDrawing()" class="mt-4 bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-md text-sm">+ Tambah Gambar</button>
            </div>

            {{-- Item RAB Terkait --}}
            <div class="mb-6 border-b pb-4">
                <h2 class="text-xl font-semibold mb-4 text-gray-700">Item RAB Terkait</h2>
                <template x-for="(rabItem, index) in rabItems" :key="index">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4 items-center bg-gray-50 p-4 rounded-md">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Nama Pekerjaan</label>
                            <input type="text" :value="rabItem.item_name" class="mt-1 block w-full border rounded-md shadow-sm py-2 px-3 bg-gray-100" readonly>
                            <input type="hidden" :name="`rab_items[${index}][id]`" :value="rabItem.id">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Status Kelengkapan</label>
                            <select :name="`rab_items[${index}][completion_status]`" class="mt-1 block w-full border rounded-md shadow-sm py-2 px-3" :value="rabItem.pivot.completion_status">
                                <option value="lengkap">Lengkap</option>
                                <option value="belum_lengkap">Belum Lengkap</option>
                            </select>
                        </div>
                    </div>
                </template>
                 <div x-show="rabItems.length === 0" class="text-gray-500 text-sm">Tidak ada item RAB yang terkait.</div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-md">Ajukan Revisi</button>
            </div>
        </form>
    </main>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('revisionForm', () => ({
            {{-- PERBAIKAN DI SINI: Mengubah 'fn()' menjadi 'function()' dan memperbaiki logika ternary --}}
            drawings: @json(
                $shop_drawing->drawingDetails->isNotEmpty()
                ? $shop_drawing->drawingDetails->map(function($dd) { return ['number' => $dd->drawing_number, 'title' => $dd->drawing_title]; })
                : [['number' => '', 'title' => '']]
            ),
            rabItems: @json($shop_drawing->rabItems),

            addDrawing() {
                this.drawings.push({ number: '', title: '' });
            },
            removeDrawing(index) {
                if (this.drawings.length > 1) {
                    this.drawings.splice(index, 1);
                } else {
                    alert('Minimal harus ada satu detail gambar.');
                }
            }
        }));
    });
</script>
@endpush