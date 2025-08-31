@extends('layouts.app')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
<style>
    /* Styling agar konsisten dengan form lain */
    .ts-control { border-radius: 0.375rem; border-color: #D1D5DB; padding: 0.5rem 0.75rem; }
    .ts-dropdown { font-size: 0.875rem; }
    .ts-input::placeholder { color: #9CA3AF; }
</style>
@endpush

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            <h1 class="text-2xl font-bold mb-6">Form Pengajuan Shop Drawing</h1>

            <form action="{{ route('documents.store', ['package' => $package->id]) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                <input type="hidden" name="package_id" value="{{ $package->id }}">
                <input type="hidden" name="category" value="Shop Drawing">

                {{-- No. Dokumen & No. Gambar --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="document_number" class="block text-sm font-medium text-gray-700">No. Dokumen (Surat Pengajuan)</label>
                        <input type="text" name="document_number" id="document_number" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required>
                    </div>
                    <div>
                        <label for="drawing_numbers" class="block text-sm font-medium text-gray-700">No. Gambar (pisahkan dengan koma jika lebih dari satu)</label>
                        <input type="text" name="drawing_numbers" id="drawing_numbers" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                    </div>
                </div>

                {{-- Untuk Pekerjaan (Dropdown RAB) --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Untuk Pekerjaan</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="main_rab_item_select" class="block text-xs font-medium text-gray-600 mb-1">Sub Item Utama</label>
                            <select id="main_rab_item_select">
								<option value="">-- Pilih Sub Item --</option>
								{{-- PERBAIKI: Tampilkan semua pilihan yang sudah disiapkan Controller --}}
								@foreach($mainRabItems as $item)
									<option value="{{ $item->id }}">{{ $item->item_name }}</option>
								@endforeach
							</select>
                        </div>
                        <div>
                            <label for="rab_item_id_select" class="block text-xs font-medium text-gray-600 mb-1">Item Pekerjaan (Bisa pilih lebih dari satu)</label>
                            <select name="rab_items[]" id="rab_item_id_select" multiple></select>
                        </div>
                    </div>
                </div>

                {{-- Ditujukan Kepada --}}
                <div>
                    <label for="addressed_to" class="block text-sm font-medium text-gray-700">Ditujukan Kepada</label>
                    <input type="text" name="addressed_to" id="addressed_to" value="Manajemen Konstruksi & Owner" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md bg-gray-100" readonly>
                </div>
                
                {{-- Judul & Deskripsi --}}
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700">Judul Dokumen</label>
                    <input type="text" name="title" id="title" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required>
                </div>
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Deskripsi (Opsional)</label>
                    <textarea name="description" id="description" rows="3" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"></textarea>
                </div>

                {{-- Upload File --}}
                <div>
                    <label for="file" class="block text-sm font-medium text-gray-700">Upload Dokumen (PDF)</label>
                    <input type="file" name="file" id="file" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" required>
                </div>

                {{-- Tombol Aksi --}}
                <div class="flex justify-end space-x-4 pt-4">
                    <a href="{{ route('documents.index', ['package' => $package->id]) }}" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Batal
                    </a>
                    <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                        Ajukan Dokumen
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
    const tomSelectMain = new TomSelect("#main_rab_item_select", {
        placeholder: 'Cari Sub Item Utama...',
    });

    const tomSelectChild = new TomSelect("#rab_item_id_select", {
        placeholder: 'Pilih Item Pekerjaan...',
        plugins: ['remove_button']
    });
    tomSelectChild.disable();

    tomSelectMain.on('change', async function(parentId) {
        tomSelectChild.clear();
        tomSelectChild.clearOptions();
        tomSelectChild.disable();

        if (!parentId) return;

        tomSelectChild.addOption({ value: '', text: 'Memuat...' });
        tomSelectChild.refreshOptions(false);

        try {
            const response = await fetch(`/api/rab-items/${parentId}/children`);
            const children = await response.json();
            
            tomSelectChild.clearOptions();
            children.forEach(option => {
                tomSelectChild.addOption({
                    value: option.id,
                    text: option.name,
                    disabled: option.is_title
                });
            });
            tomSelectChild.enable();
        } catch (error) {
            console.error('Gagal memuat item pekerjaan:', error);
            tomSelectChild.clearOptions();
            tomSelectChild.addOption({ value: '', text: 'Gagal memuat data' });
        }
    });
});
</script>
@endpush