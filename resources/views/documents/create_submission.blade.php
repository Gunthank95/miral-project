@extends('layouts.app')

@push('styles')
{{-- Tidak ada perubahan di sini, biarkan seperti semula --}}
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
<style>
    .ts-control { border-radius: 0.375rem; border-color: #D1D5DB; padding: 0.5rem 0.75rem; }
    .ts-dropdown { font-size: 0.875rem; }
    [x-cloak] { display: none !important; }
</style>
@endpush

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">

        {{-- KITA MULAI PERUBAHAN DARI SINI --}}
        <form 
            action="{{ route('documents.store_submission', ['package' => $package->id]) }}" 
            method="POST" 
            enctype="multipart/form-data"
            class="space-y-8 bg-white overflow-hidden shadow-xl sm:rounded-lg p-6"
            x-data="shopDrawingForm()" 
            x-init="initTomSelects({{ json_encode($mainRabItems) }})"
        >
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

            {{-- Bagian 2: Info Gambar (Dinamis) --}}
            <div class="p-4 border rounded-md bg-gray-50">
                <div class="flex justify-between items-center mb-2">
                    <h2 class="text-lg font-semibold text-gray-700">Info Gambar</h2>
                    <button @click.prevent="addDrawing()" type="button" class="text-sm font-medium text-blue-600 hover:text-blue-800">+ Tambah Gambar</button>
                </div>
                
                <div class="space-y-4">
                    <template x-for="(drawing, index) in drawings" :key="index">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-start p-3 border-t">
                            <div>
                                <label :for="'drawing_title_' + index" class="block text-sm font-medium text-gray-700">Judul Gambar</label>
                                <input x-model="drawing.title" :name="'drawings[' + index + '][title]'" :id="'drawing_title_' + index" type="text" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required>
                            </div>
                            <div class="flex items-start space-x-2">
                                <div class="flex-grow">
                                    <label :for="'drawing_number_' + index" class="block text-sm font-medium text-gray-700">No. Gambar</label>
                                    <input x-model="drawing.number" :name="'drawings[' + index + '][number]'" :id="'drawing_number_' + index" type="text" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required>
                                </div>
                                <button x-show="drawings.length > 1" @click.prevent="removeDrawing(index)" type="button" class="text-red-500 hover:text-red-700 mt-7">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Bagian 3: Upload File --}}
            <div class="space-y-4">
                <h2 class="text-lg font-semibold text-gray-700">Lampiran File</h2>
                <div>
                    <label for="files" class="block text-sm font-medium text-gray-700">Upload File (PDF, bisa lebih dari satu)</label>
                    <input type="file" name="files[]" id="files" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" multiple required>
                </div>
            </div>

            {{-- Bagian 4: Item Pekerjaan Terkait --}}
			<div class="space-y-4">
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
									<option value="belum_lengkap">Belum Lengkap</option>
									<option value="lengkap">Lengkap</option>
								</select>
							</div>
						</div>
					</template>
				</div>
			</div>

            {{-- Tombol Aksi --}}
            <div class="flex justify-end pt-6 border-t space-x-3">
                <a href="{{ route('documents.index', ['package' => $package->id]) }}" class="bg-gray-200 text-gray-800 py-2 px-4 rounded-md text-sm font-medium hover:bg-gray-300">Batal</a>
                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Ajukan Dokumen
                </button>
            </div>

        </form>
    </div>
</div>
@endsection

@push('scripts')
{{-- Tidak ada perubahan di sini, biarkan seperti semula --}}
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

{{-- INI ADALAH "MESIN" JAVASCRIPT BARU KITA --}}
<script>
    function shopDrawingForm() {
        return {
            // Data untuk form dinamis "Info Gambar"
            drawings: [{ title: '', number: '' }],
            
            // Data untuk form dinamis "Status Kelengkapan"
            selectedRabItems: [],

            // Variabel untuk menyimpan object TomSelect
            tomSelectMain: null,
            tomSelectChild: null,

            // Fungsi untuk menambahkan baris gambar baru
            addDrawing() {
                this.drawings.push({ title: '', number: '' });
            },
            
            // Fungsi untuk menghapus baris gambar
            removeDrawing(index) {
                this.drawings.splice(index, 1);
            },
            
            // Fungsi utama untuk inisialisasi semua dropdown
            initTomSelects(mainRabItemsData) {
                // Inisialisasi dropdown anak (Item Pekerjaan)
                this.tomSelectChild = new TomSelect('#rab_items', {
                    plugins: ['remove_button'],
                    valueField: 'id',
                    labelField: 'name',
                    searchField: ['name'],
                    create: false,
                    onChange: (values) => {
                        // Setiap kali dropdown anak berubah, update daftar "Status Kelengkapan"
                        this.selectedRabItems = values.map(value => {
                            const option = this.tomSelectChild.getOption(value);
                            return { id: value, name: option.innerText.trim() };
                        });
                    }
                });
                this.tomSelectChild.disable(); // Nonaktifkan di awal

                // Inisialisasi dropdown induk (Sub Item Utama)
                this.tomSelectMain = new TomSelect('#main_rab_item', {
                    options: mainRabItemsData.map(item => ({ value: item.id, text: `${item.item_number} - ${item.item_name}` })),
                    placeholder: 'Pilih sub item untuk memuat pekerjaan...',
                    onChange: (value) => {
                        // Hapus semua pilihan dan data sebelumnya
                        this.tomSelectChild.clear();
                        this.tomSelectChild.clearOptions();
                        this.selectedRabItems = [];

                        if (!value) {
                            this.tomSelectChild.disable();
                            return;
                        }
                        
                        // Ambil data anak dari server menggunakan API yang sudah ada
                        this.tomSelectChild.load((callback) => {
                            fetch(`/api/rab-items/${value}/children`)
                                .then(response => response.json())
                                .then(data => {
                                    this.tomSelectChild.enable();
                                    // Format data agar bisa dibaca oleh TomSelect
                                    const formattedData = data.map(item => ({
                                        value: item.id,
                                        text: `${item.item_number} - ${item.name}` 
                                    }));
                                    callback(formattedData);
                                }).catch(() => {
                                    callback();
                                });
                        });
                    }
                });
            }
        }
    }
</script>
@endpush