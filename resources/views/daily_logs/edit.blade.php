@extends('layouts.app')

@section('title', 'Edit Aktivitas Pekerjaan')

@section('content')
<div class="p-4 sm:p-6">
    <header class="bg-white shadow p-4 rounded-lg mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Edit Aktivitas Pekerjaan</h1>
        <p class="text-sm text-gray-500">
            Untuk Laporan Tanggal: {{ \Carbon\Carbon::parse($report->report_date)->isoFormat('dddd, D MMMM YYYY') }}
        </p>
    </header>

    <main>
        <form id="daily-log-form" action="{{ route('daily_log.update', $activity->id) }}" method="POST" enctype="multipart/form-data" class="bg-white rounded shadow p-6">
            @csrf
            @method('PUT')
            <input type="hidden" name="deleted_photos" id="deleted_photos_input">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                {{-- Kolom Kiri --}}
                <div>
                     <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Item Pekerjaan</label>
                        <input type="text" class="mt-1 w-full border rounded px-3 py-2 bg-gray-100" value="{{ $activity->rabItem->item_number }} {{ $activity->rabItem->item_name }}" disabled>
                        <input type="hidden" name="rab_item_id" id="rab_item_id" value="{{ $activity->rab_item_id }}">
                         <div id="contract-info" class="hidden text-xs text-gray-500 mt-1 space-x-4">
                            <span>Volume Kontrak: <span id="contract-volume" class="font-semibold"></span></span>
                            <span>Bobot Kontrak: <span id="contract-weighting" class="font-semibold"></span>%</span>
                        </div>
                    </div>

                    {{-- BAGIAN PROGRES YANG LENGKAP --}}
                    <div class="mb-4 p-3 border rounded-lg">
                        <div class="flex items-center space-x-4 mb-2">
                            <label class="block text-sm font-medium text-gray-700">Input Progres Hari Ini:</label>
                            <div class="flex items-center space-x-2 text-sm">
                                <input type="radio" id="input_type_volume" name="input_type" value="volume" checked>
                                <label for="input_type_volume">Volume</label>
                            </div>
                            <div class="flex items-center space-x-2 text-sm">
                                <input type="radio" id="input_type_percent" name="input_type" value="percent">
                                <label for="input_type_percent">Persen (%)</label>
                            </div>
                        </div>
                        
                        <input type="hidden" name="progress_volume" id="progress_volume_hidden">
                        <input type="number" step="any" id="progress_input" value="{{ $activity->progress_volume }}" required class="w-full border rounded px-3 py-2">

                        <div id="progress-info" class="hidden text-xs text-gray-500 mt-2 space-y-1">
                            <div>Vol. Sebelumnya: <span id="previous-volume" class="font-semibold">0</span></div>
                            <div>Vol. Sampai Saat Ini: <span id="total-volume" class="font-semibold">0</span></div>
                            <div class="pt-1 border-t mt-1">Progres Sebelumnya: <span id="previous-progress-percent" class="font-semibold">0</span>%</div>
                            <div>Progres Sampai Saat Ini: <span id="total-progress-percent" class="font-semibold">0</span>%</div>
                        </div>
                        <div id="progress-over-limit-warning" class="hidden text-xs text-red-600 mt-1">Peringatan: Total progres melebihi 100%.</div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="manpower_count" class="block text-sm font-medium text-gray-700">Jumlah Tenaga Kerja</label>
                        <input type="number" name="manpower_count" id="manpower_count" value="{{ $activity->manpower_count }}" class="mt-1 w-full border rounded px-3 py-2" placeholder="Contoh: 10">
                    </div>
                </div>

                {{-- Kolom Kanan --}}
                <div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Peralatan Digunakan</label>
                        <div id="equipment-list" class="space-y-4 mt-1"></div>
                        <button type="button" id="add-equipment-btn" class="mt-2 text-sm text-blue-600 hover:underline">+ Tambah Alat</button>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Material Digunakan</label>
                        <div id="material-list" class="space-y-4 mt-1"></div>
                        <button type="button" id="add-material-btn" class="mt-2 text-sm text-blue-600 hover:underline">+ Tambah Material</button>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Dokumentasi Foto</label>
                        <div id="photo-preview-container" class="mt-2 text-sm text-gray-700 space-y-1"></div>
                        <div class="mt-2 flex items-center space-x-4">
                            <button type="button" id="capture-btn" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm">Ambil Foto (Kamera)</button>
                            <button type="button" id="gallery-btn" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm">Pilih dari Galeri</button>
                        </div>
                        <input type="file" id="photo-input" accept="image/*" class="hidden" multiple/>
                    </div>
                </div>
            </div>
            <div class="mt-6">
                <button type="submit" id="submit-btn" class="w-full bg-green-600 text-white py-2 rounded hover:bg-green-700">Simpan Perubahan</button>
            </div>
        </form>
    </main>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
	// ======================================================
    // MENGISI DATA AWAL DARI DATABASE
    // ======================================================
    const initialMaterials = @json($activity->materials ?? []);
    const initialEquipment = @json($activity->equipment ?? []);
    const initialPhotos = @json($activity->photos ?? []);
    
    // ======================================================
    // DEKLARASI VARIABEL UTAMA
    // ======================================================
    const form = document.getElementById('daily-log-form');
    const rabItemSelect = document.getElementById('rab_item_id');
    const progressInput = document.getElementById('progress_input');
    const progressVolumeHidden = document.getElementById('progress_volume_hidden');
    const inputTypeRadios = document.querySelectorAll('input[name="input_type"]');
    const contractInfoDiv = document.getElementById('contract-info');
    const contractVolumeSpan = document.getElementById('contract-volume');
    const contractWeightingSpan = document.getElementById('contract-weighting');
    const progressInfoDiv = document.getElementById('progress-info');
    const prevVolumeSpan = document.getElementById('previous-volume');
    const totalVolumeSpan = document.getElementById('total-volume');
    const prevProgressSpan = document.getElementById('previous-progress-percent');
    const totalProgressSpan = document.getElementById('total-progress-percent');
    const overLimitWarning = document.getElementById('progress-over-limit-warning');
    
    let totalContractVolume = 0;
    let totalContractWeighting = 0;
    let previousProgressVolume = 0;
    let currentInputType = 'volume';
    
    // Variabel untuk Material
    const materialList = document.getElementById('material-list');
    const addMaterialBtn = document.getElementById('add-material-btn');
    const materialsData = @json($materials ?? []);
    let materialCounter = 0;

    // Variabel untuk Peralatan
    const equipmentList = document.getElementById('equipment-list');
    const addEquipmentBtn = document.getElementById('add-equipment-btn');
    let equipmentCounter = 0;
    
    // Variabel untuk Foto
    const captureBtn = document.getElementById('capture-btn');
    const galleryBtn = document.getElementById('gallery-btn');
    const photoInput = document.getElementById('photo-input');
    const photoPreviewContainer = document.getElementById('photo-preview-container');
    const deletedPhotosInput = document.getElementById('deleted_photos_input');
    let accumulatedFiles = [];
    let deletedPhotoIds = [];

    // ======================================================
    // FUNGSI-FUNGSI
    // ======================================================

    function addMaterialRow(materialId = '', quantity = '', unit = '') {
        materialCounter++;
        const row = document.createElement('div');
        row.classList.add('p-2', 'border', 'rounded-lg', 'space-y-2', 'md:space-y-0', 'md:flex', 'md:space-x-2', 'md:items-center', 'material-row-container');
        let optionsHtml = '<option value="">-- Pilih Material --</option>';
        materialsData.forEach(material => {
            const isSelected = material.id == materialId ? 'selected' : '';
            optionsHtml += `<option value="${material.id}" data-unit="${material.unit}" ${isSelected}>${material.name}</option>`;
        });
        row.innerHTML = `
            <div class="w-full md:w-1/2"><select name="materials[${materialCounter}][id]" class="w-full border rounded px-3 py-2 text-sm">${optionsHtml}</select></div>
            <div class="w-full md:w-1/4"><input type="number" step="0.01" value="${quantity}" name="materials[${materialCounter}][quantity]" class="w-full border rounded px-3 py-2 text-sm" placeholder="Jumlah"></div>
            <div class="w-full md:w-1/4"><input type="text" value="${unit}" name="materials[${materialCounter}][unit]" class="w-full border rounded px-3 py-2 text-sm bg-gray-100" placeholder="Satuan" readonly></div>
            <button type="button" class="remove-btn text-red-500 font-bold p-2 md:p-0 w-full md:w-auto text-center">X</button>
        `;
        materialList.appendChild(row);
    }

    function addEquipmentRow(name = '', quantity = '', spec = '') {
        equipmentCounter++;
        const row = document.createElement('div');
        row.classList.add('p-2', 'border', 'rounded-lg', 'space-y-2', 'equipment-row-container');
        row.innerHTML = `
            <div class="flex items-center space-x-2">
                <input type="text" value="${name}" name="equipment[${equipmentCounter}][name]" class="w-1/2 border rounded px-3 py-2 text-sm" placeholder="Nama Alat">
                <input type="number" value="${quantity}" name="equipment[${equipmentCounter}][quantity]" class="w-1/4 border rounded px-3 py-2 text-sm" placeholder="Jumlah">
                <button type="button" class="remove-btn text-red-500 font-bold">X</button>
            </div>
            <input type="text" value="${spec}" name="equipment[${equipmentCounter}][specification]" class="w-full border rounded px-3 py-2 text-sm" placeholder="Spesifikasi (Opsional)">
        `;
        equipmentList.appendChild(row);
    }

    function renderPhotoPreview() {
        photoPreviewContainer.innerHTML = '';
        accumulatedFiles.forEach((photo, index) => {
            const previewElement = document.createElement('div');
            previewElement.classList.add('flex', 'justify-between', 'items-center', 'text-xs', 'text-gray-600');
            const fileName = photo.isNew ? photo.file.name : photo.file_path.split('/').pop();
            const imageUrl = photo.isNew ? URL.createObjectURL(photo.file) : `/storage/${photo.file_path}`;
            
            previewElement.innerHTML = `
                <div class="flex items-center space-x-2">
                    <img src="${imageUrl}" alt="preview" class="h-8 w-8 object-cover rounded">
                    <span>${fileName}</span>
                </div>
                <button type="button" class="remove-photo-btn text-red-500 font-bold" data-index="${index}">X</button>
            `;
            photoPreviewContainer.appendChild(previewElement);
        });
    }
    
    async function fetchProgressData() {
        const rabItemId = rabItemSelect.value;
        if (!rabItemId) { resetProgress(); return; }
        try {
            const response = await fetch(`/api/rab-item/${rabItemId}/progress`);
            if (!response.ok) throw new Error('Network response was not ok');
            const data = await response.json();
            totalContractVolume = parseFloat(data.total_contract_volume) || 0;
            totalContractWeighting = parseFloat(data.total_contract_weighting) || 0;
            const totalReportedVolume = parseFloat(data.previous_progress_volume) || 0;
            // Di halaman edit, progres sebelumnya adalah total progres dikurangi progres hari ini
            previousProgressVolume = totalReportedVolume - (parseFloat("{{ $activity->progress_volume }}") || 0);
            
            contractVolumeSpan.textContent = `${totalContractVolume.toLocaleString('id-ID')} ${data.unit || ''}`;
            contractWeightingSpan.textContent = totalContractWeighting.toFixed(2);
            contractInfoDiv.classList.remove('hidden');
            progressInfoDiv.classList.remove('hidden');
            updateAllDisplays();
        } catch (error) { console.error('Gagal mengambil progres:', error); resetProgress(); }
    }

    function updateAllDisplays() {
        const userInputValue = parseFloat(progressInput.value) || 0;
        let currentProgressVolume = 0;
        if (currentInputType === 'volume') {
            currentProgressVolume = userInputValue;
        } else {
            currentProgressVolume = (userInputValue / 100) * totalContractVolume;
        }
        progressVolumeHidden.value = currentProgressVolume;
        const totalProgressVolume = previousProgressVolume + currentProgressVolume;
        prevVolumeSpan.textContent = previousProgressVolume.toLocaleString('id-ID');
        totalVolumeSpan.textContent = totalProgressVolume.toLocaleString('id-ID');
        if (totalContractVolume > 0) {
            const prevProgressPercent = (previousProgressVolume / totalContractVolume) * 100;
            prevProgressSpan.textContent = prevProgressPercent.toFixed(2);
            const totalProgressPercent = (totalProgressVolume / totalContractVolume) * 100;
            totalProgressSpan.textContent = totalProgressPercent.toFixed(2);
            overLimitWarning.classList.toggle('hidden', totalProgressVolume <= totalContractVolume);
        } else {
            prevProgressSpan.textContent = '0.00';
            totalProgressSpan.textContent = '0.00';
        }
    }
    
    function resetProgress() {
        totalContractVolume = 0;
        totalContractWeighting = 0;
        previousProgressVolume = 0;
        contractInfoDiv.classList.add('hidden');
        progressInfoDiv.classList.add('hidden');
        overLimitWarning.classList.add('hidden');
        progressInput.value = '';
        progressVolumeHidden.value = '';
    }
    
    // ======================================================
    // LOGIKA KHUSUS UNTUK HALAMAN EDIT
    // ======================================================
    
    fetchProgressData();
    
    if (initialMaterials.length > 0) {
        initialMaterials.forEach(mat => addMaterialRow(mat.material_id, mat.quantity, mat.unit));
    } else { addMaterialRow(); }
    
    if (initialEquipment.length > 0) {
        initialEquipment.forEach(eq => addEquipmentRow(eq.name, eq.quantity, eq.specification));
    } else { addEquipmentRow(); }

    if (initialPhotos.length > 0) {
        accumulatedFiles = initialPhotos.map(photo => ({ isNew: false, ...photo }));
        renderPhotoPreview();
    }
    
    // ======================================================
    // EVENT LISTENERS
    // ======================================================
    if (addMaterialBtn) addMaterialBtn.addEventListener('click', () => addMaterialRow());
    if (materialList) {
        materialList.addEventListener('change', function(e) {
            if (e.target.tagName === 'SELECT' && e.target.closest('.material-row-container')) {
                const selectedOption = e.target.options[e.target.selectedIndex];
                const rowContainer = e.target.closest('.material-row-container');
                const unitInput = rowContainer.querySelector('input[name$="[unit]"]');
                if (unitInput) unitInput.value = selectedOption.dataset.unit || '';
            }
        });
        materialList.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-btn')) {
                e.target.closest('.material-row-container').remove();
            }
        });
    }
    
    if (addEquipmentBtn) addEquipmentBtn.addEventListener('click', () => addEquipmentRow());
    if (equipmentList) {
        equipmentList.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-btn')) {
                e.target.closest('.equipment-row-container').remove();
            }
        });
    }
    
    if(captureBtn) captureBtn.addEventListener('click', () => { /* ... */ });
    if(galleryBtn) galleryBtn.addEventListener('click', () => { /* ... */ });
    if(photoInput) photoInput.addEventListener('change', function(event) { /* ... */ });
    if(photoPreviewContainer) photoPreviewContainer.addEventListener('click', function(e) { /* ... */ });

    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            submitBtn.disabled = true;
            submitBtn.textContent = 'Menyimpan...';
            errorDiv.classList.add('hidden');
            errorDiv.innerHTML = '';

            const formData = new FormData(form);

            // 1. Hapus input file default agar tidak konflik
            formData.delete('photos[]');

            // 2. Tambahkan file-file baru yang sudah diakumulasi
            accumulatedFiles.forEach(file => {
                formData.append('new_photos[]', file);
            });

            // 3. Tambahkan ID foto yang akan dihapus
            deletedPhotos.forEach(photoId => {
                formData.append('deleted_photos[]', photoId);
            });
            
            // 4. Set method untuk update
            formData.append('_method', 'PUT');


            fetch(form.action, {
                method: 'POST', // Method harus POST untuk FormData dengan file
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = data.redirect_url;
                } else {
                    let errorHtml = '<p class="font-bold">Terjadi Kesalahan:</p><ul class="mt-2 list-disc list-inside text-sm">';
                    for (const key in data.errors) {
                        data.errors[key].forEach(error => {
                            errorHtml += `<li>${error}</li>`;
                        });
                    }
                    errorHtml += '</ul>';
                    errorDiv.innerHTML = errorHtml;
                    errorDiv.classList.remove('hidden');
                    
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Simpan Perubahan';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                errorDiv.innerHTML = 'Terjadi kesalahan pada server. Silakan coba lagi.';
                errorDiv.classList.remove('hidden');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Simpan Perubahan';
            });
        });
    }

    if (progressInput) progressInput.addEventListener('input', updateAllDisplays);
    inputTypeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            currentInputType = this.value;
            progressInput.value = '';
            updateAllDisplays();
        });
    });
});
</script>
@endpush