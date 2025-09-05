@extends('layouts.app')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
<style>
    .ts-control { border-radius: 0.375rem; border-color: #D1D5DB; padding: 0.5rem 0.75rem; }
    .ts-dropdown { font-size: 0.875rem; }
</style>
@endpush

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">

        <form action="{{ route('documents.store_submission', ['package' => $package->id]) }}" method="POST" class="space-y-8 bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            @csrf

            <h1 class="text-2xl font-bold text-gray-800 border-b pb-4">Form Pengajuan Shop Drawing</h1>

            {{-- Bagian 1: Informasi Surat Pengantar --}}
            <div class="space-y-4">
                <h2 class="text-lg font-semibold text-gray-700">Informasi Utama</h2>
                <div>
                    <label for="document_number" class="block text-sm font-medium text-gray-700">No. Dokumen (Surat Pengantar)</label>
                    <input type="text" name="document_number" id="document_number" value="{{ old('document_number') }}" class="mt-1 block w-full md:w-1/2 shadow-sm sm:text-sm border-gray-300 rounded-md" required>
                </div>
            </div>

            {{-- Bagian 2: Detail Gambar (Saat ini hanya untuk 1 gambar) --}}
            <div class="space-y-4 p-4 border rounded-md">
                <h2 class="text-lg font-semibold text-gray-700">Info Gambar</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="drawing_title" class="block text-sm font-medium text-gray-700">Judul Gambar</label>
                        <input type="text" name="drawing_title" id="drawing_title" value="{{ old('drawing_title') }}" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required>
                    </div>
                    <div>
                        <label for="drawing_number" class="block text-sm font-medium text-gray-700">No. Gambar</label>
                        <input type="text" name="drawing_number" id="drawing_number" value="{{ old('drawing_number') }}" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required>
                    </div>
                </div>
            </div>

            {{-- Bagian 3: Upload File --}}
            <div class="space-y-4">
                 <h2 class="text-lg font-semibold text-gray-700">Lampiran File</h2>
                <div>
                    <label for="files" class="block text-sm font-medium text-gray-700">Upload File (Bisa lebih dari satu)</label>
                    <input type="file" name="files[]" id="files" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" multiple required>
                </div>
            </div>

            {{-- Bagian 4: Item Pekerjaan Terkait --}}
			<div class="space-y-4" x-data="{ selectedRabItems: [] }">
				<h2 class="text-lg font-semibold text-gray-700">Pekerjaan Terkait (RAB)</h2>
				
				{{-- Dropdown untuk Sub Item Utama (Induk) --}}
				<div>
					<label for="main_rab_item" class="block text-sm font-medium text-gray-700 mb-1">Pilih Sub Item Utama</label>
					<select id="main_rab_item" placeholder="Pilih sub item untuk memuat pekerjaan..."></select>
				</div>
				
				{{-- Dropdown untuk Item Pekerjaan (Anak) --}}
				<div>
					<label for="rab_items" class="block text-sm font-medium text-gray-700 mb-1">Pilih Item Pekerjaan</label>
					<select id="rab_items" multiple placeholder="Pilih satu atau lebih item pekerjaan..."></select>
				</div>

				{{-- Area untuk menampilkan daftar item yang dipilih beserta statusnya --}}
				<div x-show="selectedRabItems.length > 0" class="space-y-3 border p-4 rounded-md bg-gray-50" x-cloak>
					<h3 class="text-md font-semibold text-gray-600">Status Kelengkapan Gambar</h3>
					<template x-for="item in selectedRabItems" :key="item.id">
						<div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center border-t pt-3">
							{{-- Nama Item Pekerjaan --}}
							<div class="md:col-span-2">
								<label class="block text-sm font-medium text-gray-800" x-text="item.name"></label>
								<input type="hidden" :name="'rab_items[' + item.id + '][id]'" :value="item.id">
							</div>
							{{-- Pilihan Status Kelengkapan --}}
							<div>
								<select :name="'rab_items[' + item.id + '][completion_status]'" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
									<option value="belum">Belum Lengkap</option>
									<option value="lengkap">Lengkap</option>
								</select>
							</div>
						</div>
					</template>
				</div>
			</div>

            {{-- Tombol Aksi --}}
            <div class="flex justify-end pt-6 border-t">
                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Ajukan Dokumen
                </button>
            </div>

        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inisialisasi dropdown anak (Item Pekerjaan)
    const tomSelectChild = new TomSelect('#rab_items', {
        plugins: ['remove_button'],
        valueField: 'id',
        labelField: 'name',
        searchField: ['name'],
        create: false,
    });
    tomSelectChild.disable(); // Nonaktifkan di awal

    // Inisialisasi dropdown induk (Sub Item Utama)
    new TomSelect('#main_rab_item', {
        options: [
            { id: '', name: '-- Pilih Sub Item Utama --'},
            @foreach ($mainRabItems as $item)
                { id: '{{ $item->id }}', name: '{{ $item->item_number }} - {{ $item->item_name }}' },
            @endforeach
        ],
        valueField: 'id',
        labelField: 'name',
        searchField: ['name'],
        onChange: (value) => {
            tomSelectChild.clear();
            tomSelectChild.clearOptions();
            if (!value) {
                tomSelectChild.disable();
                return;
            }
            
            // Ambil data anak dari server menggunakan fungsi load
            tomSelectChild.load(function(callback) {
                fetch(`/api/rab-items/${value}/children`)
                    .then(response => response.json())
                    .then(data => {
                        tomSelectChild.enable();
                        // Format data agar bisa dibaca oleh TomSelect (menggunakan 'name' dari API)
                        const formattedData = data.map(item => ({
                            id: item.id,
                            name: item.name 
                        }));
                        callback(formattedData);
                    }).catch(() => {
                        callback();
                    });
            });
        }
    });
});
</script>
@endpush