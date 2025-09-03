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
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            <h1 class="text-2xl font-bold mb-6">Edit Dokumen: {{ $shop_drawing->title }}</h1>

            <form action="{{ route('documents.update', ['package' => $package->id, 'shop_drawing' => $shop_drawing->id]) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                {{-- No. Dokumen & No. Gambar --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="document_number" class="block text-sm font-medium text-gray-700">No. Dokumen (Surat Pengajuan)</label>
                        {{-- PERBAIKI: Tambahkan 'value' untuk mengisi data --}}
                        <input type="text" name="document_number" id="document_number" value="{{ old('document_number', $shop_drawing->document_number) }}" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                    </div>
                    <div>
                        <label for="drawing_numbers" class="block text-sm font-medium text-gray-700">No. Gambar</label>
                        {{-- PERBAIKI: Tambahkan 'value' untuk mengisi data --}}
                        <input type="text" name="drawing_numbers" id="drawing_numbers" value="{{ old('drawing_numbers', $shop_drawing->drawing_numbers) }}" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                    </div>
                </div>

                {{-- Untuk Pekerjaan --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Untuk Pekerjaan</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="main_rab_item_select" class="block text-xs font-medium text-gray-600 mb-1">Sub Item Utama</label>
                            <select id="main_rab_item_select">
                                <option value="">-- Pilih Sub Item --</option>
                                @foreach($mainRabItems as $item)
                                    <option value="{{ $item->id }}">{{ $item->item_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="rab_item_id_select" class="block text-xs font-medium text-gray-600 mb-1">Item Pekerjaan</label>
                            <select name="rab_items[]" id="rab_item_id_select" multiple>
                                {{-- Opsi diisi JavaScript --}}
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Judul & Deskripsi --}}
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700">Judul Dokumen</label>
                    <input type="text" name="title" id="title" value="{{ old('title', $shop_drawing->title) }}" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required>
                </div>
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Deskripsi</label>
                    {{-- PERBAIKI: Isi textarea dengan data --}}
                    <textarea name="description" id="description" rows="3" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">{{ old('description', $shop_drawing->description) }}</textarea>
                </div>

                {{-- Tombol Aksi --}}
                <div class="flex justify-end space-x-4 pt-4">
                    <a href="{{ route('documents.index', ['package' => $package->id]) }}" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Batal
                    </a>
                    <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // PERBAIKI: Menggunakan logika JavaScript yang benar
    const tomSelectMain = new TomSelect("#main_rab_item_select", { placeholder: 'Cari Sub Item Utama...' });
    const tomSelectChild = new TomSelect("#rab_item_id_select", {
        placeholder: 'Pilih Item Pekerjaan...',
        plugins: ['remove_button'],
        // "Ajari" dropdown cara menampilkan spasi untuk hirarki
        render: {
            item: (data, escape) => '<div>' + data.text.replace(/&nbsp;/g, ' ') + '</div>',
            option: (data, escape) => '<div>' + data.text.replace(/&nbsp;/g, ' ') + '</div>',
        }
    });

    const initiallySelected = @json($selectedRabItems ?? []);
    let allChildOptions = {}; // Tempat menyimpan semua opsi anak

    // Fungsi untuk memuat semua item anak dan menyimpannya
    async function preloadAllChildItems() {
        tomSelectChild.disable();
        tomSelectChild.clearOptions();
        
        const mainOptions = tomSelectMain.options;
        const promises = []; // Kumpulan proses "fetch" data

        for (const parentId in mainOptions) {
            if (mainOptions.hasOwnProperty(parentId) && parentId) {
                const promise = fetch(`/api/rab-items/${parentId}/children`)
                    .then(response => response.json())
                    .then(children => {
                        allChildOptions[parentId] = children; // Simpan hasilnya
                    });
                promises.push(promise);
            }
        }
        
        // Tunggu sampai semua data anak berhasil diambil
        await Promise.all(promises);
        
        // SETELAH SEMUA OPSI TERSEDIA, baru kita isi dropdown dan atur item yang terpilih
        const allOptionsFlat = Object.values(allChildOptions).flat();
        updateChildOptions(allOptionsFlat);
        tomSelectChild.setValue(initiallySelected);
    }

    // Fungsi untuk mengupdate dropdown anak
    function updateChildOptions(options) {
        options.forEach(option => {
            if (!tomSelectChild.getOption(option.id)) {
                tomSelectChild.addOption({
                    value: option.id,
                    text: option.name,
                    disabled: option.is_title
                });
            }
        });
        tomSelectChild.enable();
    }

    // Panggil fungsi preload saat halaman pertama kali dimuat
    preloadAllChildItems();

});
</script>
@endpush