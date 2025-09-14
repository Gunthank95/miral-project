@extends('layouts.app')

@section('title', 'Formulir Pengajuan Shop Drawing')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
<style>
    .ts-control { border-radius: 0.375rem; border-color: #D1D5DB; padding: 0.5rem 0.75rem; }
    .ts-dropdown { font-size: 0.875rem; }
    [x-cloak] { display: none !important; }
</style>
@endpush

@section('content')
<div class="container mx-auto px-4 py-8" x-data="submissionForm()">
    <div class="max-w-4xl mx-auto">
        <form action="{{ route('documents.store_submission', ['package' => $package->id]) }}" method="POST" enctype="multipart/form-data" class="space-y-8 bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            @csrf
            
            @if ($errors->any())
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                <p class="font-bold">Terdapat kesalahan:</p>
                <ul class="mt-2 list-disc list-inside">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
            @endif

            <h1 class="text-2xl font-bold text-gray-800">Formulir Pengajuan Shop Drawing</h1>
            
            {{-- Bagian 1 & 2 (Detail Dokumen & Gambar) --}}
            <div class="space-y-4 border-b pb-6">
                <h2 class="text-lg font-semibold text-gray-700">Informasi Dokumen</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="document_number" class="block text-sm font-medium text-gray-700">No. Surat Pengantar</label>
                        <input type="text" id="document_number" name="document_number" value="{{ old('document_number') }}" required class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                    </div>
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700">Judul Utama Pengajuan</label>
                        <input type="text" id="title" name="title" value="{{ old('title') }}" required class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                    </div>
                </div>
                <div>
                    <label for="files" class="block text-sm font-medium text-gray-700">File Shop Drawing (PDF)</label>
                    <input type="file" name="files[]" id="files" multiple required class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                </div>
            </div>
            <div class="space-y-4 border-b pb-6">
                <h2 class="text-lg font-semibold text-gray-700">Detail Gambar</h2>
                <template x-for="(drawing, index) in drawings" :key="index">
                    <div class="flex items-end space-x-4">
                        <div class="flex-grow">
                            <div class="flex space-x-2">
                                <input type="text" :name="`drawings[${index}][number]`" x-model="drawing.number" placeholder="No. Gambar" class="mt-1 block w-1/3 shadow-sm sm:text-sm border-gray-300 rounded-md" required>
                                <input type="text" :name="`drawings[${index}][title]`" x-model="drawing.title" placeholder="Judul Gambar" class="mt-1 block w-2/3 shadow-sm sm:text-sm border-gray-300 rounded-md" required>
                            </div>
                        </div>
                        <button type="button" @click="removeDrawing(index)" class="bg-red-100 text-red-600 p-2 rounded-full hover:bg-red-200 focus:outline-none" title="Hapus Gambar">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                        </button>
                    </div>
                </template>
                <button type="button" @click="addDrawing()" class="text-sm bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-4 rounded-lg">+ Tambah Detail Gambar</button>
            </div>

            {{-- Bagian 3: Item Pekerjaan Terkait --}}
            <div class="space-y-4">
                <h2 class="text-lg font-semibold text-gray-700">Pekerjaan Terkait (RAB)</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="main_rab_item" class="block text-sm font-medium text-gray-700 mb-1">Sub Item Pekerjaan Utama</label>
                        <select id="main_rab_item" placeholder="Pilih sub item utama...">
                            <option value="">Pilih sub item utama...</option>
                            @foreach ($mainRabItems as $item)
                                <option value="{{ $item->id }}">{{ $item->item_number }} {{ $item->item_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="rab_items" class="block text-sm font-medium text-gray-700 mb-1">Item Pekerjaan</label>
                        <select id="rab_items" multiple disabled placeholder="Pilih item pekerjaan..."></select>
                    </div>
                </div>
                <div id="rab-status-container" class="space-y-3 border p-4 rounded-md bg-gray-50 hidden">
                    <h3 class="text-md font-semibold text-gray-600">Status Kelengkapan Gambar</h3>
                    <div id="rab-status-list"></div>
                </div>
            </div>

            <div class="flex justify-end pt-5">
                <button type="submit" class="bg-indigo-600 text-white font-bold py-2 px-6 rounded-lg hover:bg-indigo-700">Ajukan Dokumen</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('submissionForm', () => ({
            drawings: [{ number: '', title: '' }],
            addDrawing() { this.drawings.push({ number: '', title: '' }); },
            removeDrawing(index) { if (this.drawings.length > 1) this.drawings.splice(index, 1); }
        }));
    });

    document.addEventListener('DOMContentLoaded', function () {
        const rabStatusContainer = document.getElementById('rab-status-container');
        const rabStatusList = document.getElementById('rab-status-list');

        // Inisialisasi dropdown anak
        const tomSelectChild = new TomSelect('#rab_items', {
            dropdownParent: 'body',
			plugins: ['remove_button'],
            valueField: 'id',
            labelField: 'name',
            searchField: 'name',
            
            // ADOPSI CARA DARI DAILY LOGS: Tambahkan blok render ini
            render: {
                // Untuk daftar pilihan (dropdown), biarkan HTML &nbsp; dirender oleh browser
                option: function(data, escape) {
                    return `<div>${data.name}</div>`;
                },
                // Untuk item yang sudah dipilih, ganti &nbsp; menjadi spasi biasa
                item: function(data, escape) {
                    // Trik yang Anda temukan kita terapkan di sini
                    return `<div>${data.name.replace(/&nbsp;/g, ' ')}</div>`;
                }
            },
			
            onChange: function(values) {
                rabStatusList.innerHTML = '';
                if (values && values.length > 0) {
                    rabStatusContainer.classList.remove('hidden');
                    values.forEach(itemId => {
                        const option = this.getOption(itemId);
                        if (!option) return;
                        const itemName = option.innerText.trim();
                        const statusRow = document.createElement('div');
                        statusRow.className = 'grid grid-cols-1 md:grid-cols-3 gap-4 items-center border-t pt-3 mt-3 first:mt-0 first:border-t-0';
                        statusRow.innerHTML = `
                            <div class="md:col-span-2"><label class="block text-sm font-medium text-gray-800">${itemName}</label><input type="hidden" name="rab_items[${itemId}][id]" value="${itemId}"></div>
                            <div><select name="rab_items[${itemId}][completion_status]" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"><option value="lengkap">Lengkap</option><option value="belum_lengkap" selected>Belum Lengkap</option></select></div>`;
                        rabStatusList.appendChild(statusRow);
                    });
                } else {
                    rabStatusContainer.classList.add('hidden');
                }
            }
        });
		
		tomSelectChild.disable(); // Tetap nonaktifkan di awal

        // Inisialisasi dropdown induk
        new TomSelect('#main_rab_item', {
            dropdownParent: 'body',
			onChange: (value) => {
                tomSelectChild.clear();
                tomSelectChild.clearOptions();
                if (!value) {
                    tomSelectChild.disable();
                    return;
                }

                // ==========================================================
                // ==== PERUBAHAN UTAMA - PENDEKATAN BARU YANG LEBIH KUAT ====
                // ==========================================================
                const apiUrl = `/api/rab-items/${value}/children`;
                
                // Aktifkan dan beri tahu pengguna bahwa data sedang dimuat
                tomSelectChild.enable();
                tomSelectChild.setTextboxValue('Memuat item pekerjaan...');

                fetch(apiUrl)
                    .then(response => response.json())
                    .then(data => {
                        tomSelectChild.clearOptions(); // Hapus opsi 'memuat...'
                        tomSelectChild.addOptions(data); // Tambahkan opsi baru dari API
                        tomSelectChild.setTextboxValue(''); // Kosongkan kotak input
                    }).catch(error => {
                        console.error("Gagal memuat item pekerjaan:", error);
                        tomSelectChild.setTextboxValue('Gagal memuat data');
                        tomSelectChild.disable();
                    });
            }
        });
    });
</script>
@endpush