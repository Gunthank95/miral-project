//_summary-content.blade.php
@if ($report)
    <div class="space-y-6">
        {{-- Aktivitas Pekerjaan --}}
        <div class="bg-white rounded shadow p-4">
            <div class="flex justify-between items-center mb-2 border-b pb-2">
                <h2 class="text-xl font-semibold">Aktivitas Pekerjaan</h2>
                <button id="toggle-progress-details" class="text-xs text-blue-600 hover:underline">Tampilkan Detail Progres</button>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-100">
                        <tr>
                            <th rowspan="2" class="text-left px-4 py-2 border">Uraian Pekerjaan</th>
                            <th rowspan="2" class="text-center px-4 py-2 border">Satuan</th>
                            <th colspan="3" class="text-center px-4 py-2 border progress-details hidden">Volume</th>
                            <th colspan="3" class="text-center px-4 py-2 border progress-details hidden">Bobot (%)</th>
                        </tr>
                        <tr class="progress-details hidden">
                            <th class="text-center px-2 py-1 border font-normal">Lalu</th>
                            <th class="text-center px-2 py-1 border font-normal">Hari Ini</th>
                            <th class="text-center px-2 py-1 border font-normal">S.d Hari Ini</th>
                            <th class="text-center px-2 py-1 border font-normal">Lalu</th>
                            <th class="text-center px-2 py-1 border font-normal">Hari Ini</th>
                            <th class="text-center px-2 py-1 border font-normal">S.d Hari Ini</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($report->activities as $activity)
                            @php
                                $rabItem = $activity->rabItem;
                                $volLalu = $activity->previous_progress_volume;
                                $volHariIni = $activity->progress_volume;
                                $volTotal = $volLalu + $volHariIni;
                                
                                $progLalu = ($rabItem->volume > 0) ? ($volLalu / $rabItem->volume) * $rabItem->weighting : 0;
                                $progHariIni = ($rabItem->volume > 0) ? ($volHariIni / $rabItem->volume) * $rabItem->weighting : 0;
                                $progTotal = $progLalu + $progHariIni;
                            @endphp
                            <tr class="border-t">
                                <td class="px-4 py-2 border">{{ $rabItem->item_name }}</td>
                                <td class="text-center px-4 py-2 border">{{ $rabItem->unit }}</td>
                                <td class="text-center px-2 py-1 border progress-details hidden">{{ number_format($volLalu, 2) }}</td>
                                <td class="text-center px-2 py-1 border progress-details hidden">{{ number_format($volHariIni, 2) }}</td>
                                <td class="text-center px-2 py-1 border progress-details hidden">{{ number_format($volTotal, 2) }}</td>
                                <td class="text-center px-2 py-1 border progress-details hidden">{{ number_format($progLalu, 2) }}%</td>
                                <td class="text-center px-2 py-1 border progress-details hidden">{{ number_format($progHariIni, 2) }}%</td>
                                <td class="text-center px-2 py-1 border progress-details hidden">{{ number_format($progTotal, 2) }}%</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center p-4 text-gray-500">Tidak ada aktivitas pekerjaan yang dilaporkan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            {{-- Personil Kontraktor --}}
            <div class="bg-white rounded shadow p-4">
                <h3 class="font-semibold mb-2 border-b pb-2">Tim Kontraktor</h3>
                <ul class="text-sm space-y-1">
                    @forelse ($report->personnel->where('company_type', 'Kontraktor') as $p)
                        <li class="flex justify-between"><span>{{ $p->role }}</span> <span>{{ $p->count }}</span></li>
                    @empty
                        <li class="text-gray-500">Tidak ada data.</li>
                    @endforelse
                </ul>
                <div class="font-bold border-t mt-2 pt-1 flex justify-between"><span>TOTAL</span><span>{{ $report->personnel->where('company_type', 'Kontraktor')->sum('count') }}</span></div>
            </div>
            {{-- Personil MK --}}
            <div class="bg-white rounded shadow p-4">
                <h3 class="font-semibold mb-2 border-b pb-2">Tim MK/Pengawas</h3>
                <ul class="text-sm space-y-1">
                    @forelse ($report->personnel->where('company_type', 'MK') as $p)
                        <li class="flex justify-between"><span>{{ $p->role }}</span> <span>{{ $p->count }}</span></li>
                    @empty
                        <li class="text-gray-500">Tidak ada data.</li>
                    @endforelse
                </ul>
                <div class="font-bold border-t mt-2 pt-1 flex justify-between"><span>TOTAL</span><span>{{ $report->personnel->where('company_type', 'MK')->sum('count') }}</span></div>
            </div>
            {{-- Material --}}
            <div class="bg-white rounded shadow p-4">
                 <h3 class="font-semibold mb-2 border-b pb-2">Material Digunakan</h3>
                <ul class="text-sm space-y-1">
                    @php $allMaterials = $report->activities->flatMap->materials->groupBy('material.name'); @endphp
                    @forelse ($allMaterials as $name => $items)
                        <li>{{ $name }} ({{ $items->sum('quantity') }} {{ $items->first()->unit }})</li>
                    @empty
                        <li class="text-gray-500">Tidak ada data.</li>
                    @endforelse
                </ul>
            </div>
            {{-- Peralatan --}}
             <div class="bg-white rounded shadow p-4">
                 <h3 class="font-semibold mb-2 border-b pb-2">Peralatan Digunakan</h3>
                <ul class="text-sm space-y-1">
                    @php $allEquipment = $report->activities->flatMap->equipment->groupBy('name'); @endphp
                    @forelse ($allEquipment as $name => $items)
                        <li>{{ $name }} ({{ $items->sum('quantity') }} unit)</li>
                    @empty
                         <li class="text-gray-500">Tidak ada data.</li>
                    @endforelse
                </ul>
            </div>
            {{-- Cuaca --}}
            <div class="bg-white rounded shadow p-4">
                <h3 class="font-semibold mb-2 border-b pb-2">Cuaca</h3>
                <ul class="text-sm space-y-1">
                    @forelse ($report->weather as $w)
                        <li class="flex justify-between"><span>Jam {{ \Carbon\Carbon::parse($w->time)->format('H:i') }}</span> <span>{{ $w->condition }}</span></li>
                    @empty
                        <li class="text-gray-500">Tidak ada data.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
@else
    {{-- TAMPILKAN INI JIKA LAPORAN TIDAK DITEMUKAN --}}
    <div class="bg-white rounded shadow p-10 text-center">
        <h2 class="text-xl font-semibold text-gray-700">Laporan Tidak Ditemukan</h2>
        <p class="text-gray-500 mt-2">Belum ada laporan untuk tanggal {{ \Carbon\Carbon::parse($selectedDate)->isoFormat('D MMMM YYYY') }}.</p>
    </div>
@endif

















// create.blade.php
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
            <div id="validation-errors" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert"></div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                {{-- Kolom Kiri --}}
                <div>
                     <div class="mb-4">
                        <label for="main_rab_item_select" class="block text-sm font-medium text-gray-700">Sub Utama Pekerjaan</label>
                        <select id="main_rab_item_select" required>
                            <option value="">-- Pilih Sub Utama --</option>
                            @foreach ($mainRabItems as $item)
                                <option value="{{ $item->id }}">{{ $item->item_number }} {{ $item->item_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="rab_item_id_select" class="block text-sm font-medium text-gray-700">Item Pekerjaan</label>
                        <select name="rab_item_id" id="rab_item_id_select" required disabled>
                            <option value="">-- Pilih Item Pekerjaan --</option>
                        </select>
                        <div id="duplicate-warning" class="hidden text-xs text-red-600 mt-1">Pekerjaan ini sudah dilaporkan. Silakan edit data yang sudah ada.</div>
                        <div id="contract-info" class="hidden text-xs text-gray-500 mt-1 space-x-4">
                            <span>Volume Kontrak: <span id="contract-volume" class="font-semibold"></span></span>
                            <span>Bobot Kontrak: <span id="contract-weighting" class="font-semibold"></span>%</span>
                        </div>
                    </div>

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
                        <label for="manpower_count" class="block text-sm font-medium text-gray-700">Jumlah Tenaga Kerja</label>
                        <input type="number" name="manpower_count" id="manpower_count" class="mt-1 w-full border rounded px-3 py-2" placeholder="Contoh: 10">
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
                        <div class="mt-2 flex items-center space-x-4">
                            <button type="button" id="capture-btn" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm">Ambil Foto (Kamera)</button>
                            <button type="button" id="gallery-btn" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm">Pilih dari Galeri</button>
                        </div>
                        <input type="file" id="photo-input" accept="image/*" class="hidden" multiple/>
                        <div id="photo-preview-container" class="mt-2 text-sm text-gray-700 space-y-1"></div>
                    </div>
                </div>
            </div>
            <div class="mt-6">
                <button type="submit" id="submit-btn" class="w-full bg-blue-600 text-white py-2 rounded">Simpan Aktivitas Pekerjaan</button>
            </div>
        </form>
    </main>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ======================================================
    // INISIALISASI TOM SELECT
    // ======================================================
    const tomSelectMain = new TomSelect("#main_rab_item_select", { create: false, sortField: { field: "text", direction: "asc" } });
    const tomSelectChild = new TomSelect("#rab_item_id_select", { create: false, sortField: { field: "text", direction: "asc" } });
    
    // ======================================================
    // DEKLARASI VARIABEL UTAMA
    // ======================================================
    const form = document.getElementById('daily-log-form');
    const errorDiv = document.getElementById('validation-errors');
    const submitBtn = document.getElementById('submit-btn');
    const reportId = "{{ $report->id }}";
    const packageId = "{{ $package->id }}";
    const rabItemSelect = document.getElementById('rab_item_id_select');

    // Variabel untuk Material
    const materialList = document.getElementById('material-list');
    const addMaterialBtn = document.getElementById('add-material-btn');
    const materialsData = @json($materials ?? []);
    let materialCounter = 0;
    const equipmentList = document.getElementById('equipment-list');
    const addEquipmentBtn = document.getElementById('add-equipment-btn');
    let equipmentCounter = 0;
    const captureBtn = document.getElementById('capture-btn');
    const galleryBtn = document.getElementById('gallery-btn');
    const photoInput = document.getElementById('photo-input');
    const photoPreviewContainer = document.getElementById('photo-preview-container');
    let accumulatedFiles = [];
    const duplicateWarningDiv = document.getElementById('duplicate-warning');
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

    // ======================================================
    // FUNGSI-FUNGSI
    // ======================================================

    function addMaterialRow(materialId = '', quantity = '', unit = '') {
        materialCounter++;
        const row = document.createElement('div');
        row.classList.add('p-2', 'border', 'rounded-lg', 'space-y-2', 'md:space-y-0', 'md:flex', 'md:space-x-2', 'md:items-center', 'material-row-container');
        let optionsHtml = '<option value="">-- Pilih Material --</option>';
        if (materialsData.length > 0) {
            materialsData.forEach(function(material) {
                const isSelected = material.id == materialId ? 'selected' : '';
                optionsHtml += `<option value="${material.id}" data-unit="${material.unit}" ${isSelected}>${material.name}</option>`;
            });
        }
        row.innerHTML = `
            <div class="w-full md:w-1/2"><select name="materials[${materialCounter}][id]">${optionsHtml}</select></div>
            <div class="w-full md:w-1/4"><input type="number" step="0.01" value="${quantity}" name="materials[${materialCounter}][quantity]" class="w-full border rounded px-3 py-2 text-sm" placeholder="Jumlah"></div>
            <div class="w-full md:w-1/4"><input type="text" value="${unit}" name="materials[${materialCounter}][unit]" class="w-full border rounded px-3 py-2 text-sm bg-gray-100" placeholder="Satuan" readonly></div>
            <button type="button" class="remove-btn text-red-500 font-bold p-2 md:p-0 w-full md:w-auto text-center">X</button>
        `;
        materialList.appendChild(row);
        const newSelect = row.querySelector('select[name^="materials"]');
        if (newSelect) {
            new TomSelect(newSelect, { create: false, sortField: { field: "text", direction: "asc" } });
        }
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
    
    // PERBAIKAN: Menambahkan kembali fungsi yang hilang
    function resetAndAddInitialRows() {
        document.getElementById('material-list').innerHTML = '';       document.getElementById('equipment-list').innerHTML = '';
        addMaterialRow();
        addEquipmentRow();
    }

    // ======================================================
    // EVENT LISTENERS
    // ======================================================
    
    if (tomSelectMain) {
        tomSelectMain.on('change', async function(parentId) {
            tomSelectChild.disable();
            tomSelectChild.clear();
            tomSelectChild.clearOptions();
            tomSelectChild.addOption({value: '', text: 'Memuat...'});
            if (!parentId) {
                tomSelectChild.addOption({value: '', text: '-- Pilih Item Pekerjaan --'});
                tomSelectChild.clear();
                return;
            }
            try {
                const response = await fetch(`/api/rab-items/${parentId}/children`);
                const children = await response.json();
                tomSelectChild.clear();
                tomSelectChild.addOption({value: '', text: '-- Pilih Item Pekerjaan --'});
                children.forEach(option => {
                    tomSelectChild.addOption({
                        value: option.id,
                        text: option.name.replace(/&nbsp;/g, ' '),
                        disabled: option.is_title
                    });
                });
                tomSelectChild.enable();
            } catch (error) {
                console.error('Gagal memuat item pekerjaan:', error);
                tomSelectChild.addOption({value: '', text: 'Gagal memuat'});
            }
        });
    }

    if (rabItemSelect) {
        rabItemSelect.addEventListener('change', async function() {
            const rabItemId = this.value;
            duplicateWarningDiv.classList.add('hidden');
            if (submitBtn) submitBtn.disabled = false;
            if (!rabItemId) { resetProgress(); resetAndAddInitialRows(); return; }
            await fetchProgressData(); 
            try {
                const response = await fetch(`/api/daily-reports/${reportId}/check-activity/${rabItemId}`);
                const data = await response.json();
                if (data.is_duplicate) {
                    duplicateWarningDiv.classList.remove('hidden');
                    if (submitBtn) submitBtn.disabled = true;
                } else { fetchLastActivityData(rabItemId); }
            } catch (error) { console.error('Gagal memeriksa duplikasi:', error); }
        });
    }

    // ... (sisa event listener lainnya) ...
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

    if (form) form.addEventListener('submit', function(event) {
        event.preventDefault();
        errorDiv.classList.add('hidden');
        
        const formData = new FormData(form);
        formData.delete('photos[]');
        accumulatedFiles.forEach(file => {
            formData.append('photos[]', file);
        });
        
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: { 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value, 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                window.location.href = data.redirect_url;
            } else {
                let errorHtml = '<ul>';
                for (const key in data.errors) {
                    data.errors[key].forEach(error => {
                        errorHtml += `<li>${error}</li>`;
                    });
                }
                errorHtml += '</ul>';
                errorDiv.innerHTML = errorHtml;
                errorDiv.classList.remove('hidden');
            }
        })
        .catch(error => {
			console.error('Error:', error);
			errorDiv.innerHTML = '<ul><li>Terjadi kesalahan koneksi. Silakan coba lagi.</li></ul>';
			errorDiv.classList.remove('hidden');
		})
    });
	
    if (progressInput) progressInput.addEventListener('input', updateAllDisplays);
    
    inputTypeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            currentInputType = this.value;
            progressInput.value = '';
            updateAllDisplays();
        });
    });

    resetAndAddInitialRows();
});
</script>
@endpush