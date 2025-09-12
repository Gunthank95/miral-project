@extends('layouts.app')

@push('styles')
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

        <form 
            action="{{ route('documents.store_submission', ['package' => $package->id]) }}" 
            method="POST" 
            enctype="multipart/form-data"
            class="space-y-8 bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
			
			@if ($errors->any())
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                    <p class="font-bold">Terdapat kesalahan pada input Anda:</p>
                    <ul class="mt-2 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                    <p class="font-bold">Terjadi Kesalahan!</p>
                    <p>{{ session('error') }}</p>
                </div>
            @endif
            @csrf

            <h1 class="text-2xl font-bold text-gray-800 border-b pb-4">Form Pengajuan Shop Drawing</h1>

            {{-- Bagian 1: Informasi Surat Pengantar --}}
            <div class="space-y-4">
                <h2 class="text-lg font-semibold text-gray-700">Informasi Utama</h2>
                <div>
                    <label for="document_number" class="block text-sm font-medium text-gray-700">No. Dokumen (Surat Pengantar)</label>
                    <input type="text" name="document_number" id="document_number" value="{{ old('document_number') }}" class="mt-1 block w-full md:w-1/2 shadow-sm sm:text-sm border-gray-300 rounded-md px-4 py-2" required>
                </div>
            </div>

            {{-- Bagian 2: Info Gambar (Dinamis) --}}
            <div class="p-4 border rounded-md bg-gray-50">
                <div class="flex justify-between items-center mb-2">
                    <h2 class="text-lg font-semibold text-gray-700">Info Gambar</h2>
                    <button id="add-drawing-btn" type="button" class="text-sm font-medium text-blue-600 hover:text-blue-800">+ Tambah Gambar</button>
                </div>
                
                <div id="drawings-container" class="space-y-4">
                    {{-- Baris pertama akan ditambahkan oleh JavaScript --}}
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
				
				{{-- HAPUS dropdown "main_rab_item" yang lama --}}

				{{-- GANTI dengan dropdown ini --}}
				<div>
					<label for="rab_items" class="block text-sm font-medium text-gray-700 mb-1">Pilih Item Pekerjaan</label>
					<select id="rab_items" multiple placeholder="Pilih satu atau lebih item pekerjaan...">
						@foreach ($flatRabItems as $item)
							<option value="{{ $item['id'] }}" 
									data-data='{"id": "{{ $item['id'] }}", "name": "{!! addslashes($item['name']) !!}"}'
									@if($item['disabled']) disabled @endif>
								{!! $item['name'] !!}
							</option>
						@endforeach
					</select>
				</div>

				{{-- Area untuk menampilkan daftar item yang dipilih beserta statusnya --}}
				<div id="rab-status-container" class="space-y-3 border p-4 rounded-md bg-gray-50 hidden">
					<h3 class="text-md font-semibold text-gray-600">Status Kelengkapan Gambar</h3>
					<div id="rab-status-list">
						{{-- Daftar status akan di-render di sini oleh JavaScript --}}
					</div>
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
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // ======================================================
    // LOGIKA UNTUK INFO GAMBAR DINAMIS
    // ======================================================
    const drawingsContainer = document.getElementById('drawings-container');
    const addDrawingBtn = document.getElementById('add-drawing-btn');
    let drawingIndex = 0;

    function addDrawingRow() {
        const newRow = document.createElement('div');
        newRow.className = 'grid grid-cols-1 md:grid-cols-2 gap-4 items-start p-3 border-t';
        newRow.innerHTML = `
            <div>
                <label for="drawing_title_${drawingIndex}" class="block text-sm font-medium text-gray-700">Judul Gambar</label>
                <input name="drawings[${drawingIndex}][title]" id="drawing_title_${drawingIndex}" type="text" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md  px-4 py-2" required>
            </div>
            <div class="flex items-start space-x-2">
                <div class="flex-grow">
                    <label for="drawing_number_${drawingIndex}" class="block text-sm font-medium text-gray-700">No. Gambar</label>
                    <input name="drawings[${drawingIndex}][number]" id="drawing_number_${drawingIndex}" type="text" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md  px-4 py-2" required>
                </div>
                <button type="button" class="remove-drawing-btn text-red-500 hover:text-red-700 mt-7">
                    <svg xmlns="http://www.w.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg>
                </button>
            </div>
        `;
        drawingsContainer.appendChild(newRow);
        
        const removeBtn = newRow.querySelector('.remove-drawing-btn');
        if (drawingsContainer.children.length <= 1) {
            removeBtn.style.display = 'none'; // Sembunyikan tombol hapus jika hanya ada 1 baris
        } else {
             // Tampilkan semua tombol hapus jika lebih dari 1
            drawingsContainer.querySelectorAll('.remove-drawing-btn').forEach(btn => btn.style.display = 'block');
        }

        removeBtn.addEventListener('click', () => {
            newRow.remove();
            if (drawingsContainer.children.length <= 1) {
                const firstRemoveBtn = drawingsContainer.querySelector('.remove-drawing-btn');
                if (firstRemoveBtn) firstRemoveBtn.style.display = 'none';
            }
        });
        drawingIndex++;
    }

    addDrawingBtn.addEventListener('click', addDrawingRow);
    addDrawingRow(); // Tambahkan baris pertama saat halaman dimuat


    // ======================================================
    // LOGIKA UNTUK DROPDOWN HIRARKI (METODE MANUAL)
    // ======================================================
    const tomSelectMain = new TomSelect("#main_rab_item", { create: false });
    const tomSelectChild = new TomSelect("#rab_items", { 
        plugins: ['remove_button'],
        valueField: 'id',
        labelField: 'name',
        searchField: ['name']
    });

    const rabStatusContainer = document.getElementById('rab-status-container');
    const rabStatusList = document.getElementById('rab-status-list');

    tomSelectChild.disable();

    tomSelectMain.on('change', async function(parentId) {
        tomSelectChild.disable();
        tomSelectChild.clear();
        tomSelectChild.clearOptions();
        
        if (!parentId) {
            return;
        }

        tomSelectChild.addOption({ id: '', name: 'Memuat...' });

        try {
            const url = `${window.location.origin}/api/rab-items/${parentId}/children`;
            const response = await fetch(url);
            if (!response.ok) throw new Error('Gagal mengambil data dari server');
            
            const children = await response.json();

            tomSelectChild.clearOptions(); // Hapus 'Memuat...'
            children.forEach(option => {
                // Logika yang sudah ada dari perbaikan sebelumnya
                const displayText = option.item_number ? `${option.item_number} - ${option.name}` : option.name;

                // TAMBAHAN: "Menerjemahkan" kode &nbsp; menjadi spasi biasa
                const decodedText = new DOMParser().parseFromString(displayText, 'text/html').body.textContent;

                tomSelectChild.addOption({ 
                    id: option.id, 
                    name: decodedText // Gunakan teks yang sudah diterjemahkan
                });
            });
            tomSelectChild.enable();
        } catch (error) {
            console.error('Gagal memuat item pekerjaan:', error);
            tomSelectChild.clearOptions();
            tomSelectChild.addOption({ id: '', name: 'Gagal memuat data' });
        }
    });

    tomSelectChild.on('change', function(values) {
        rabStatusList.innerHTML = ''; // Kosongkan daftar setiap kali ada perubahan

        if (values && values.length > 0) {
            rabStatusContainer.classList.remove('hidden');
            
            values.forEach(itemId => {
                const option = tomSelectChild.getOption(itemId);
                const itemName = option.innerText.trim();

                const statusRow = document.createElement('div');
                statusRow.className = 'grid grid-cols-1 md:grid-cols-3 gap-4 items-center border-t pt-3';
                statusRow.innerHTML = `
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-800">${itemName}</label>
                        <input type="hidden" name="rab_items[${itemId}][id]" value="${itemId}">
                    </div>
                    <div>
                        <select name="rab_items[${itemId}][completion_status]" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            <option value="belum_lengkap">Belum Lengkap</option>
                            <option value="lengkap">Lengkap</option>
                        </select>
                    </div>
                `;
                rabStatusList.appendChild(statusRow);
            });
        } else {
            rabStatusContainer.classList.add('hidden');
        }
    });
});
</script>
@endpush