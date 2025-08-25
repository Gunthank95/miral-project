@extends('layouts.app')

@section('title', 'Tambah Aktivitas Pekerjaan')

@section('content')
<div class="p-4 sm:p-6">
    <header class="bg-white shadow p-4 rounded-lg mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Tambah Aktivitas Pekerjaan</h1>
        <p class="text-sm text-gray-500">
            Untuk Laporan Tanggal: {{ \Carbon\Carbon::parse($report->report_date)->isoFormat('dddd, D MMMM YYYY') }}
        </p>
    </header>

    <main>
        <form id="daily-log-form" action="{{ route('daily_log.store', $package->id) }}" method="POST" enctype="multipart/form-data" class="bg-white rounded shadow p-6">
            @csrf
            <input type="hidden" name="daily_report_id" value="{{ $report->id }}">
			@if ($errors->any())
				<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
					<p class="font-bold">Terjadi Kesalahan:</p>
					<ul class="mt-2 list-disc list-inside text-sm">
						@foreach ($errors->all() as $error)
							<li>{{ $error }}</li>
						@endforeach
					</ul>
				</div>
			@endif
            <div id="validation-errors" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert"></div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                {{-- Kolom Kiri --}}
                <div>
                     <div class="mb-4">
                        <label for="main_rab_item_select" class="block text-sm font-medium text-gray-700">Sub Utama Pekerjaan</label>
                        <select id="main_rab_item_select" placeholder="Cari sub utama...">
                            <option value="">-- Pilih Sub Utama --</option>
                            @foreach ($mainRabItems as $item)
                                <option value="{{ $item->id }}">{{ $item->item_number }} {{ $item->item_name }}</option>
                            @endforeach
                            <option value="" disabled>──────────</option>
                            <option value="custom">Pekerjaan di luar BOQ</option>
                        </select>
                    </div>

                    <div id="rab-item-wrapper" class="mb-4">
                        <label for="rab_item_id_select" class="block text-sm font-medium text-gray-700">Item Pekerjaan</label>
                        <select name="rab_item_id" id="rab_item_id_select" placeholder="Pilih Sub Utama terlebih dahulu...">
                            <option value="">-- Pilih Item Pekerjaan --</option>
                        </select>
                        <div id="duplicate-warning" class="hidden text-xs text-red-600 mt-1">Pekerjaan ini sudah dilaporkan.</div>
                        <div id="contract-info" class="hidden text-xs text-gray-500 mt-1 space-x-4">
                            <span>Volume Kontrak: <span id="contract-volume" class="font-semibold"></span></span>
                            <span>Bobot Kontrak: <span id="contract-weighting" class="font-semibold"></span>%</span>
                        </div>
                    </div>

                    <div id="custom-work-wrapper" class="mb-4 hidden">
                        <label for="custom_work_name" class="block text-sm font-medium text-gray-700">Nama Pekerjaan Kustom</label>
                        <input type="text" name="custom_work_name" id="custom_work_name" value="{{ old('custom_work_name') }}" class="mt-1 w-full border rounded px-3 py-2" placeholder="Ketik nama pekerjaan baru...">
                    </div>

                    <div class="mb-4 p-3 border rounded-lg">
                        <div class="flex items-center space-x-4 mb-2">
                            <label class="block text-sm font-medium text-gray-700">Input Progres Hari Ini:</label>
                            <div class="flex items-center space-x-2 text-sm">
                                <input type="radio" id="input_type_volume" name="input_type" value="volume">
                                <label for="input_type_volume">Volume</label>
                            </div>
                            <div class="flex items-center space-x-2 text-sm">
                                <input type="radio" id="input_type_percent" name="input_type" value="percent" checked>
                                <label for="input_type_percent">Persen (%)</label>
                            </div>
                        </div>
                        
                        <input type="hidden" name="progress_volume" id="progress_volume_hidden">
                        <input type="number" step="any" id="progress_input" required class="w-full border rounded px-3 py-2" placeholder="Masukkan nilai progres...">

                        <div id="progress-info" class="hidden text-xs text-gray-500 mt-2 space-y-1">
                            <div>Vol. Sebelumnya: <span id="previous-volume" class="font-semibold">0</span></div>
                            <div>Vol. Sampai Saat Ini: <span id="total-volume" class="font-semibold">0</span></div>
                            <div class="pt-1 border-t mt-1">Progres Sebelumnya: <span id="previous-progress-percent" class="font-semibold">0</span>%</div>
                            <div>Progres Sampai Saat Ini: <span id="total-progress-percent" class="font-semibold">0</span>%</div>
                        </div>
                        <div id="progress-over-limit-warning" class="hidden text-xs text-red-600 mt-1">Peringatan: Total progres melebihi 100%.</div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Tenaga Kerja</label>
                        <div id="manpower-list" class="space-y-2 mt-1"></div>
                        <button type="button" id="add-manpower-btn" class="mt-2 text-sm text-blue-600 hover:underline">+ Tambah Tenaga Kerja</button>
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
                        <div class="flex items-center justify-between">
                            <label class="block text-sm font-medium text-gray-700">Material Digunakan</label>
                            <button type="button" id="open-material-modal-btn" class="text-sm text-blue-600 hover:underline">(+) Tambah Material Baru</button>
                        </div>
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
                <button type="submit" id="submit-btn" class="w-full bg-blue-600 text-white py-2 rounded">Simpan Aktivitas Pekerjaan</button>
            </div>
        </form>
    </main>
</div>

{{-- Modal untuk Tambah Material Baru --}}
<div id="add-material-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Tambah Material Baru</h3>
            <div class="mt-2 px-7 py-3">
                <form id="new-material-form">
                    <div class="mb-4 text-left">
                        <label for="new_material_name" class="block text-sm font-medium text-gray-700">Nama Material</label>
                        <input type="text" name="name" id="new_material_name" required class="mt-1 w-full border rounded px-3 py-2">
                    </div>
                    <div class="mb-4 text-left">
                        <label for="new_material_unit" class="block text-sm font-medium text-gray-700">Satuan</label>
                        <input type="text" name="unit" id="new_material_unit" required class="mt-1 w-full border rounded px-3 py-2">
                    </div>
                </form>
            </div>
            <div class="items-center px-4 py-3">
                <button id="save-new-material-btn" class="px-4 py-2 bg-blue-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-blue-600">Simpan</button>
                <button id="close-material-modal-btn" class="mt-2 px-4 py-2 bg-gray-200 text-gray-800 text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-300">Batal</button>
            </div>
            <div id="modal-error-message" class="hidden text-red-500 text-xs mt-2"></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ======================================================
    // KONFIGURASI & INISIALISASI TOM SELECT
    // ======================================================
    const tomSelectConfig = {
        create: false,
        plugins: ['dropdown_input'],
        render: {
            option: (data, escape) => `<div class="p-2">${data.text}</div>`,
            item: (data, escape) => `<div>${data.text.replace(/&nbsp;/g, ' ')}</div>`
        }
    };
    const tomSelectMain = new TomSelect("#main_rab_item_select", { ...tomSelectConfig, placeholder: 'Cari sub utama...' });
    const tomSelectChild = new TomSelect("#rab_item_id_select", { ...tomSelectConfig, placeholder: 'Pilih Sub Utama dahulu...' });
    tomSelectChild.disable();
    
    // ======================================================
    // DEKLARASI VARIABEL UTAMA
    // ======================================================
    const form = document.getElementById('daily-log-form');
    const errorDiv = document.getElementById('validation-errors');
    const submitBtn = document.getElementById('submit-btn');
    const reportId = "{{ $report->id }}";
    const packageId = "{{ $package->id }}";
    const rabItemWrapper = document.getElementById('rab-item-wrapper');
    const customWorkWrapper = document.getElementById('custom-work-wrapper');
    const customWorkInput = document.getElementById('custom_work_name');
    const rabItemSelectEl = document.getElementById('rab_item_id_select');
	const rabItemSelect = document.getElementById('rab_item_id_select');
    const materialList = document.getElementById('material-list');
    const addMaterialBtn = document.getElementById('add-material-btn');
    const materialsData = @json($materials ?? []);
    let materialCounter = 0;
    const equipmentList = document.getElementById('equipment-list');
    const addEquipmentBtn = document.getElementById('add-equipment-btn');
    let equipmentCounter = 0;
    const manpowerList = document.getElementById('manpower-list');
    const addManpowerBtn = document.getElementById('add-manpower-btn');
    const manpowerRoles = ['Pekerja', 'Mandor', 'Kepala Tukang', 'Tukang', 'Pembantu Tukang', 'Operator', 'Pembantu Operator', 'Mekanik', 'Pembantu Mekanik', 'Supir', 'Pembantu Supir'];
    let manpowerCounter = 0;
    const captureBtn = document.getElementById('capture-btn');
    const galleryBtn = document.getElementById('gallery-btn');
    const photoInput = document.getElementById('photo-input');
    const photoPreviewContainer = document.getElementById('photo-preview-container');
    let accumulatedFiles = [];
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
    const duplicateWarningDiv = document.getElementById('duplicate-warning');
    let totalContractVolume = 0;
    let totalContractWeighting = 0;
    let previousProgressVolume = 0;
    let currentInputType = 'percent';
    const materialModal = document.getElementById('add-material-modal');
    const openMaterialModalBtn = document.getElementById('open-material-modal-btn');
    const closeMaterialModalBtn = document.getElementById('close-material-modal-btn');
    const saveNewMaterialBtn = document.getElementById('save-new-material-btn');
    const newMaterialForm = document.getElementById('new-material-form');
    const modalErrorMessage = document.getElementById('modal-error-message');

    // ======================================================
    // FUNGSI-FUNGSI
    // ======================================================
    function addManpowerRow(role = '', quantity = '') {
        manpowerCounter++;
        const row = document.createElement('div');
        row.classList.add('flex', 'items-center', 'space-x-2', 'manpower-row-container');
        let optionsHtml = '<option value="">-- Pilih Jabatan --</option>';
        manpowerRoles.forEach(r => {
            const isSelected = r === role ? 'selected' : '';
            optionsHtml += `<option value="${r}" ${isSelected}>${r}</option>`;
        });
        row.innerHTML = `
            <select name="manpower[${manpowerCounter}][role]" class="w-1/2 border rounded px-3 py-2 text-sm">${optionsHtml}</select>
            <input type="number" value="${quantity}" name="manpower[${manpowerCounter}][quantity]" class="w-1/2 border rounded px-3 py-2 text-sm" placeholder="Jumlah">
            <button type="button" class="remove-btn text-red-500 font-bold">X</button>
        `;
        manpowerList.appendChild(row);
    }

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
            <div class="w-full md:w-1/2"><select name="materials[${materialCounter}][id]">${optionsHtml}</select></div>
            <div class="w-full md:w-1/4"><input type="number" step="0.01" value="${quantity}" name="materials[${materialCounter}][quantity]" class="w-full border rounded px-3 py-2 text-sm" placeholder="Jumlah"></div>
            <div class="w-full md:w-1/4"><input type="text" value="${unit}" name="materials[${materialCounter}][unit]" class="w-full border rounded px-3 py-2 text-sm bg-gray-100" placeholder="Satuan" readonly></div>
            <button type="button" class="remove-btn text-red-500 font-bold p-2 md:p-0 w-full md:w-auto text-center">X</button>
        `;
        materialList.appendChild(row);
        const newSelect = row.querySelector('select[name^="materials"]');
        if (newSelect) { new TomSelect(newSelect, { ...tomSelectConfig, placeholder: 'Cari material...' }); }
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
        accumulatedFiles.forEach((file, index) => {
            const previewElement = document.createElement('div');
            previewElement.classList.add('flex', 'justify-between', 'items-center', 'text-xs', 'text-gray-600');
            previewElement.innerHTML = `
                <span>${file.name}</span>
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
            previousProgressVolume = parseFloat(data.previous_progress_volume) || 0;
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
        } else { // percent
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
    
    async function fetchLastActivityData(rabItemId) {
        try {
            const response = await fetch(`/api/rab-item/${rabItemId}/last-activity/${packageId}`);
            const data = await response.json();

            document.getElementById('material-list').innerHTML = '';
            document.getElementById('equipment-list').innerHTML = '';

            if (data.found) {
                if (data.materials && data.materials.length > 0) {
                    data.materials.forEach(mat => addMaterialRow(mat.material_id, mat.quantity, mat.unit));
                } else {
                    addMaterialRow();
                }
                
                if (data.equipment && data.equipment.length > 0) {
                    data.equipment.forEach(eq => addEquipmentRow(eq.name, eq.quantity, eq.specification));
                } else {
                    addEquipmentRow();
                }
            } else {
                resetAndAddInitialRows();
            }
        } catch (error) {
            console.error('Gagal mengambil data aktivitas terakhir:', error);
            resetAndAddInitialRows();
        }
    }
    function resetAndAddInitialRows() {
        materialList.innerHTML = ''; equipmentList.innerHTML = ''; manpowerList.innerHTML = '';
        addMaterialRow(); addEquipmentRow(); addManpowerRow();
    }

    // ======================================================
    // EVENT LISTENERS
    // ======================================================
    if (addManpowerBtn) addManpowerBtn.addEventListener('click', () => addManpowerRow());
    if (manpowerList) {
        manpowerList.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-btn')) {
                e.target.closest('.manpower-row-container').remove();
            }
        });
    }
    
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
            if (e.target.classList.contains('remove-btn') && e.target.closest('.material-row-container')) {
                e.target.closest('.material-row-container').remove();
            }
        });
    }

    if (addEquipmentBtn) addEquipmentBtn.addEventListener('click', () => addEquipmentRow());
    if (equipmentList) {
        equipmentList.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-btn') && e.target.closest('.equipment-row-container')) {
                e.target.closest('.equipment-row-container').remove();
            }
        });
    }
    
    if(captureBtn) captureBtn.addEventListener('click', () => {
        photoInput.setAttribute('capture', 'environment');
        photoInput.click();
    });
    if(galleryBtn) galleryBtn.addEventListener('click', () => {
        photoInput.removeAttribute('capture');
        photoInput.click();
    });

    if(photoInput) photoInput.addEventListener('change', function(event) {
        for (const file of event.target.files) {
            accumulatedFiles.push(file);
        }
        renderPhotoPreview();
        event.target.value = '';
    });
    if(photoPreviewContainer) photoPreviewContainer.addEventListener('click', function(e) {
        if(e.target.classList.contains('remove-photo-btn')) {
            const indexToRemove = parseInt(e.target.dataset.index, 10);
            accumulatedFiles.splice(indexToRemove, 1);
            renderPhotoPreview();
        }
    });
    
    tomSelectMain.on('change', async function(value) {
        if (value === 'custom') {
            rabItemWrapper.classList.add('hidden');
            customWorkWrapper.classList.remove('hidden');
            tomSelectChild.clear(); tomSelectChild.disable();
            rabItemSelectEl.required = false;
            customWorkInput.required = true;
            resetProgress();
        } else {
            rabItemWrapper.classList.remove('hidden');
            customWorkWrapper.classList.add('hidden');
            rabItemSelectEl.required = true;
            customWorkInput.required = false;
            customWorkInput.value = '';

            const parentId = value;
            tomSelectChild.disable(); tomSelectChild.clear(); tomSelectChild.clearOptions();
            tomSelectChild.addOption({value: '', text: 'Memuat...'});
            if (!parentId) {
                tomSelectChild.addOption({value: '', text: '-- Pilih Item Pekerjaan --'});
                tomSelectChild.clear();
                return;
            }
            try {
                const response = await fetch(`/api/rab-items/${parentId}/children`);
                const children = await response.json();
                tomSelectChild.clearOptions();
                tomSelectChild.addOption({value: '', text: '-- Pilih Item Pekerjaan --'});
                children.forEach(option => {
                    tomSelectChild.addOption({ value: option.id, text: option.name, disabled: option.is_title });
                });
                tomSelectChild.enable();
            } catch (error) {
                console.error('Gagal memuat item pekerjaan:', error);
                tomSelectChild.addOption({value: '', text: 'Gagal memuat'});
            }
        }
    });

    tomSelectChild.on('change', async function(rabItemId) {
        if (submitBtn) submitBtn.disabled = false;
        duplicateWarningDiv.classList.add('hidden');
        if (!rabItemId) {
            resetProgress();
            resetAndAddInitialRows();
            return;
        }
        await fetchProgressData(rabItemId); 
        try {
            const response = await fetch(`/api/daily-reports/${reportId}/check-activity/${rabItemId}`);
            const data = await response.json();
            if (data.is_duplicate) {
                duplicateWarningDiv.classList.remove('hidden');
                if (submitBtn) submitBtn.disabled = true;
            } else {
                fetchLastActivityData(rabItemId);
            }
        } catch (error) {
            console.error('Gagal memeriksa duplikasi:', error);
        }
    });
    
    if (progressInput) progressInput.addEventListener('input', updateAllDisplays);
    if (inputTypeRadios) inputTypeRadios.forEach(radio => { radio.addEventListener('change', function() { /* ... */ }); });
    
    // Event Listener untuk Modal Material
    if (openMaterialModalBtn) openMaterialModalBtn.addEventListener('click', () => materialModal.classList.remove('hidden'));
    if (closeMaterialModalBtn) closeMaterialModalBtn.addEventListener('click', () => materialModal.classList.add('hidden'));
    if (saveNewMaterialBtn) {
        saveNewMaterialBtn.addEventListener('click', async () => {
            const formData = new FormData(newMaterialForm);
            modalErrorMessage.textContent = '';
            modalErrorMessage.classList.add('hidden');
            try {
                const response = await fetch("{{ route('admin.materials.store.modal') }}", {
                    method: 'POST', body: formData,
                    headers: { 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value, 'Accept': 'application/json' }
                });
                const data = await response.json();
                if (!response.ok) {
                    let errorMsg = data.message || 'Terjadi kesalahan.';
                    if (data.errors) { errorMsg += ': ' + Object.values(data.errors).flat().join(' '); }
                    throw new Error(errorMsg);
                }
                if (data.success) {
                    materialsData.push(data.material);
                    document.querySelectorAll('select[name^="materials"]').forEach(selectEl => {
                        if (selectEl.tomselect) { selectEl.tomselect.addOption(data.material); }
                    });
                    const lastMaterialRow = Array.from(materialList.querySelectorAll('.material-row-container')).pop();
                    if (lastMaterialRow) {
                        const lastSelectTom = lastMaterialRow.querySelector('.ts-control').tomselect;
                        lastSelectTom.setValue(data.material.id);
                    }
                    materialModal.classList.add('hidden');
                    newMaterialForm.reset();
                }
            } catch (error) {
                console.error('Gagal menyimpan material baru:', error);
                modalErrorMessage.textContent = error.message;
                modalErrorMessage.classList.remove('hidden');
            }
        });
    }

    // Inisialisasi form
    resetAndAddInitialRows();
});
</script>
@endpush