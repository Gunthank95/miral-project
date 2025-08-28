@extends('layouts.app')

@section('title', 'Jadwal Proyek')

@push('styles')
<link href="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.css" rel="stylesheet">
<style>
    .gantt_delete_btn, .gantt_edit_btn { cursor: pointer; text-align: center; width: 100%; }
    .gantt_delete_btn { font-size: 18px; color: #e53935; }
    .gantt_edit_btn { font-size: 15px; color: #f59e0b; }
    .gantt_task_grid .gantt_grid_head_cell { font-weight: 600; }
    .gantt_tree_content { cursor: move; }
</style>
@endpush

@section('content')
<div class="p-4 sm:p-6" x-data="schedulePageData()">
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
                    <div x-show="selectedTasks.length > 0" x-cloak>
                        <button @click="confirmBatchDelete" class="bg-red-600 text-white px-4 py-2 rounded text-sm hover:bg-red-700">
                            Hapus (<span x-text="selectedTasks.length"></span>) Item
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

    {{-- Modal Tambah Tugas --}}
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
    
    {{-- Modal Edit Tugas --}}
    <div x-show="isEditModalOpen" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center" x-cloak>
        <div @click.away="isEditModalOpen = false" class="relative mx-auto p-5 border w-full max-w-lg shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg leading-6 font-medium text-gray-900 border-b pb-2 mb-4">Edit Tugas</h3>
                <form :action="editFormAction" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label for="edit_task_name" class="block text-sm font-medium text-gray-700">Nama Tugas</label>
                        <input type="text" name="task_name" id="edit_task_name" x-model="taskToEdit.text" required class="mt-1 w-full border rounded px-3 py-2">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="edit_start_date" class="block text-sm font-medium text-gray-700">Tanggal Mulai</label>
                            <input type="date" name="start_date" id="edit_start_date" x-model="taskToEdit.start_date_raw" required class="mt-1 w-full border rounded px-3 py-2">
                        </div>
                        <div>
                            <label for="edit_end_date" class="block text-sm font-medium text-gray-700">Tanggal Selesai</label>
                            <input type="date" name="end_date" id="edit_end_date" x-model="taskToEdit.end_date_raw" required class="mt-1 w-full border rounded px-3 py-2">
                        </div>
                    </div>
                    <div class="pt-4 flex justify-end space-x-2 border-t">
                        <button type="button" @click="isEditModalOpen = false" class="bg-gray-200 text-gray-800 px-4 py-2 rounded text-sm hover:bg-gray-300">Batal</button>
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.js"></script>
<script>
    function schedulePageData() {
        return {
            isModalOpen: false,
            isEditModalOpen: false,
            selectedTasks: [],
            editFormAction: '',
            taskToEdit: { id: null, text: '', start_date_raw: '', end_date_raw: '' },
            
            openEditModal(taskId) {
                const task = gantt.getTask(taskId);
                if (!task) return;
                this.taskToEdit.id = task.id;
                this.taskToEdit.text = task.text;
                // Konversi format tanggal DHTMLX ke format input HTML (YYYY-MM-DD)
                this.taskToEdit.start_date_raw = gantt.date.date_to_str("%Y-%m-%d")(task.start_date);
                const endDate = gantt.calculateEndDate({start_date: task.start_date, duration: task.duration, task: task});
                this.taskToEdit.end_date_raw = gantt.date.date_to_str("%Y-%m-%d")(endDate);
                this.editFormAction = `/schedule/${task.id}`;
                this.isEditModalOpen = true;
            },
            
            toggleTask(taskId) {
                const id = parseInt(taskId);
                const index = this.selectedTasks.indexOf(id);
                if (index > -1) {
                    this.selectedTasks.splice(index, 1);
                } else {
                    this.selectedTasks.push(id);
                }
            },
            
            confirmBatchDelete() {
                gantt.confirm({
                    title: "Konfirmasi Hapus",
                    text: `Anda yakin ingin menghapus ${this.selectedTasks.length} tugas yang dipilih?`,
                    ok: "Hapus", cancel: "Batal",
                    callback: (result) => {
                        if (result) { this.batchDelete(); }
                    }
                });
            },
            
            batchDelete() {
                fetch('{{ route('schedule.batch_delete') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json', 'Accept': 'application/json',
                    },
                    body: JSON.stringify({ ids: this.selectedTasks })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        gantt.batchUpdate(() => {
                            this.selectedTasks.forEach(id => {
                                if (gantt.isTaskExists(id)) gantt.deleteTask(id);
                            });
                        });
                        this.selectedTasks = [];
                    } else { gantt.alert("Gagal menghapus tugas."); }
                }).catch(error => gantt.alert("Terjadi error."));
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const alpineComponent = document.querySelector('[x-data]').__x;

        gantt.config.columns = [
            {name: "select", label: "<input type='checkbox' class='gantt_select_all'/>", width: 40, align: "center", 
                template: (task) => `<input type='checkbox' class='gantt_task_checkbox' data-id='${task.id}'>`
            },
            {name: "text", label: "Task Name", tree: true, width: '*', resize: true},
            {name: "start_date", label: "Start Date", align: "center", width: 100},
            {name: "end_date", label: "End Date", align: "center", width: 100, 
                template: (task) => {
                    if (!task.start_date) return '';
                    const endDate = gantt.calculateEndDate({start_date: task.start_date, duration: task.duration, task:task});
                    return gantt.templates.date_grid(endDate, task);
                }
            },
            {name: "duration", label: "Duration", align: "center", width: 80},
            {name: "add", label: "", width: 44}, 
        ];
        
        gantt.templates.grid_add = () => `<div class='gantt_delete_btn'>✖</div>`;
        gantt.config.sort = false;
        gantt.config.order_branch = true;
        gantt.config.order_branch_free = true;
        gantt.config.date_format = "%d-%m-%Y";
        gantt.config.grid_width = 750;
        gantt.config.open_tree_initially = true;

        gantt.init("gantt_here");
        
        gantt.attachEvent("onTaskDblClick", function(id,e){
            alpineComponent.getUnwrappedData().openEditModal(id);
            return false;
        });

        gantt.attachEvent("onAfterTaskDrag", function(id, mode, e){
            gantt.render(); // Re-render untuk mengupdate WBS
            const order = gantt.getTaskByTime().map(task => task.id);
            fetch('{{ route('schedule.update_order') }}', {
                method: 'POST',
                headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json', 'Accept': 'application/json'},
                body: JSON.stringify({ order: order })
            });
        });

        function updateSelectedCount() {
            const alpineData = alpineComponent.getUnwrappedData();
            alpineData.selectedTasks = [];
            document.querySelectorAll('.gantt_task_checkbox:checked').forEach(cb => {
                alpineData.selectedTasks.push(parseInt(cb.dataset.id));
            });
        }

        gantt.attachEvent("onTaskClick", function (id, e) {
            const target = e.target;
            if (target.classList.contains("gantt_delete_btn")) {
                gantt.confirm({
                    title: "Konfirmasi Hapus", text: "Anda yakin ingin menghapus tugas ini?",
                    ok: "Hapus", cancel: "Batal",
                    callback: function (result) {
                        if (result) {
                            fetch('/schedule/' + id, {
                                method: 'DELETE',
                                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                            }).then(r => r.json()).then(data => {
                                if (data.status === 'success') {
                                    gantt.deleteTask(id);
                                    updateSelectedCount();
                                }
                            });
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
        
        document.getElementById('batch-delete-btn').addEventListener('click', () => {
             alpineComponent.getUnwrappedData().confirmBatchDelete();
        });

        gantt.parse({!! $tasks_data !!});
    });
</script>
@endpush