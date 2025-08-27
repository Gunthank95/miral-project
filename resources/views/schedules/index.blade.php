@extends('layouts.app')

@section('title', 'Jadwal Proyek')

@push('styles')
<link href="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.css" rel="stylesheet">
<style>
    .gantt_delete_btn { cursor: pointer; color: #e53935; font-size: 16px; font-weight: bold; text-align: center; line-height: 28px; }
    .gantt_task_grid .gantt_grid_head_cell { font-weight: 600; }
    .gantt_tree_content { cursor: move; }
</style>
@endpush

@section('content')
<div class="p-4 sm:p-6" x-data="{ isModalOpen: false, selectedTaskCount: 0 }">
    <main class="flex-1">
        <header class="bg-white shadow p-4 rounded-lg mb-6">
            <div class="flex flex-wrap justify-between items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Jadwal Proyek</h1>
                    <p class="text-sm text-gray-500">
                        Proyek: {{ $package->project->name }} - Paket: {{ $package->name }}
                    </p>
                </div>
                <div class="flex space-x-2 items-center">
                    <div x-show="selectedTaskCount > 0" x-cloak>
                        <button id="batch-delete-btn" class="bg-red-600 text-white px-4 py-2 rounded text-sm hover:bg-red-700">
                            Hapus (<span x-text="selectedTaskCount"></span>) Item
                        </button>
                    </div>
                    <form action="{{ route('schedule.import_from_rab', $package->id) }}" method="POST" onsubmit="return confirm('Anda yakin? Ini akan menghapus jadwal lama (yang berasal dari RAB) dan mengimpor ulang sesuai RAB terbaru.');">
                        @csrf
                        <button type="submit" class="bg-gray-600 text-white px-4 py-2 rounded text-sm hover:bg-gray-700">
                            Sinkronkan dari RAB
                        </button>
                    </form>
                    <button @click="isModalOpen = true" class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700">
                        + Tambah Tugas Baru
                    </button>
                </div>
            </div>
        </header>

        @if (session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                <p>{{ session('success') }}</p>
            </div>
        @endif
        
        <div class="bg-white rounded shadow" style="height: 600px;">
            <div id="gantt_here" style='width:100%; height:100%;'></div>
        </div>
    </main>

    {{-- Modal Tambah Tugas (Tidak ada perubahan) --}}
    <div x-show="isModalOpen" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center" x-cloak>
        <div @click.away="isModalOpen = false" class="relative mx-auto p-5 border w-full max-w-lg shadow-lg rounded-md bg-white">
            <form action="{{ route('schedule.store', $package->id) }}" method="POST" class="space-y-4">
                @csrf
                <h3 class="text-lg leading-6 font-medium text-gray-900 border-b pb-2 mb-4">Tambah Tugas Baru</h3>
                <div>
                    <label for="task_name" class="block text-sm font-medium text-gray-700">Nama Tugas</label>
                    <input type="text" name="task_name" id="task_name" required class="mt-1 w-full border rounded px-3 py-2">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700">Tanggal Mulai</label>
                        <input type="date" name="start_date" id="start_date" required class="mt-1 w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700">Tanggal Selesai</label>
                        <input type="date" name="end_date" id="end_date" required class="mt-1 w-full border rounded px-3 py-2">
                    </div>
                </div>
                <div class="pt-4 flex justify-end space-x-2 border-t">
                    <button type="button" @click="isModalOpen = false" class="bg-gray-200 text-gray-800 px-4 py-2 rounded text-sm hover:bg-gray-300">Batal</button>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700">Simpan Tugas</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const alpineEl = document.querySelector('[x-data]');
    
    // --- PERBAIKAN: KONFIGURASI KOLOM ---
    gantt.config.columns = [
        { name: "select", label: "<input type='checkbox' class='gantt_select_all'/>", width: 40, align: "center", template: (task) => `<input type='checkbox' class='gantt_task_checkbox' data-id='${task.id}'>`},
        { name: "text", label: "Task Name", tree: true, width: '*', resize: true },
        { name: "start_date", label: "Start Date", align: "center", width: 90 },
        { name: "duration", label: "Duration", align: "center", width: 70 },
        { name: "add", label: "", width: 44 } // Kolom stabil untuk tombol aksi
    ];

    // Mengubah ikon kolom 'add' menjadi ikon Hapus (✖)
    gantt.templates.grid_add = () => "<div class='gantt_delete_btn'>✖</div>";
    
    // Konfigurasi lainnya
    gantt.config.sort = false;
    gantt.config.order_branch = true;
    gantt.config.order_branch_free = true;
    gantt.config.date_format = "%d-%m-%Y";
    gantt.config.grid_width = 600;
    gantt.config.open_tree_initially = true;

    gantt.init("gantt_here");
	
	// --- EVENT HANDLERS ---
    function updateSelectedCount() {
        const count = document.querySelectorAll('.gantt_task_checkbox:checked').length;
        if (alpineEl && alpineEl.__x) {
            alpineEl.__x.setData({ selectedTaskCount: count });
        }
    }
    
    function updateSelectedCount() {
        const count = document.querySelectorAll('.gantt_task_checkbox:checked').length;
        if (alpineEl && alpineEl.__x) {
            alpineEl.__x.setData({ selectedTaskCount: count });
        }
    }

    gantt.attachEvent("onTaskClick", function (id, e) {
        const target = e.target;
        if (target.classList.contains("gantt_delete_btn")) {
            gantt.confirm({
                title: "Konfirmasi Hapus",
                text: "Anda yakin ingin menghapus tugas ini?",
                ok: "Hapus", cancel: "Batal",
                callback: function (result) {
                    if (result) {
                        const taskId = gantt.locate(e);
                        fetch('/schedule/' + taskId, {
                            method: 'DELETE',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                        }).then(response => response.json()).then(data => {
                            if (data.status === 'success') {
                                gantt.deleteTask(taskId);
                                updateSelectedCount();
                            } else { gantt.alert("Gagal menghapus tugas."); }
                        }).catch(error => gantt.alert("Terjadi error."));
                    }
                }
            });
        } 
        else if (target.classList.contains('gantt_task_checkbox')) {
            setTimeout(updateSelectedCount, 50);
        }
        return true;
    });

    gantt.attachEvent("onGridHeaderCheckboxClick", function(name, e){
        if (name === 'select') {
            const checkboxes = document.querySelectorAll('.gantt_task_checkbox');
            checkboxes.forEach(cb => cb.checked = e.target.checked);
            updateSelectedCount();
        }
    });
    
    document.getElementById('batch-delete-btn').addEventListener('click', function() {
        const checked = document.querySelectorAll('.gantt_task_checkbox:checked');
        const idsToDelete = Array.from(checked).map(cb => cb.dataset.id);

        if (idsToDelete.length === 0) return;

        gantt.confirm({
            title: "Konfirmasi Hapus",
            text: `Anda yakin ingin menghapus ${idsToDelete.length} tugas yang dipilih?`,
            ok: "Hapus", cancel: "Batal",
            callback: function (result) {
                if (result) {
                    fetch('{{ route('schedule.batch_delete') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ ids: idsToDelete })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            gantt.batchUpdate(() => {
                                idsToDelete.forEach(id => {
                                    if(gantt.isTaskExists(id)) gantt.deleteTask(id);
                                });
                            });
                            updateSelectedCount();
                        } else { gantt.alert("Gagal menghapus tugas."); }
                    }).catch(error => gantt.alert("Terjadi error."));
                }
            }
        });
    });

    gantt.parse({!! $tasks_data !!});
});
</script>
@endpush