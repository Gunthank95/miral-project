@extends('layouts.app')

@section('title', 'Project Schedule')

@push('styles')
<link href="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.css" rel="stylesheet">
<style>
    .gantt_task_grid .gantt_grid_head_cell { font-weight: 600; }
    .gantt_tree_content { cursor: move; }
    [x-cloak] { display: none !important; }
    .gantt_action_buttons { display: flex; justify-content: center; align-items: center; gap: 10px; }
    .gantt_action_buttons i { cursor: pointer; font-style: normal; }
    .gantt_edit_icon:before { content: "✏️"; font-size: 16px; color: #f59e0b; }
    .gantt_delete_icon:before { content: "❌"; font-size: 16px; color: #e53935; }
</style>
@endpush

@section('content')
{{-- GANTI: `x-init` tidak lagi memuat data secara langsung --}}
<div class="p-4 sm:p-6" x-data="schedulePage()" x-init="initializeGantt()">
    <main class="flex-1">
        <header class="bg-white shadow p-4 rounded-lg mb-6">
            <div class="flex flex-wrap justify-between items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Project Schedule</h1>
                    <p class="text-sm text-gray-500">
                        Project: {{ $package->project->name }} - Package: {{ $package->name }}
                    </p>
                </div>
                <div class="flex space-x-2 items-center">
                    <div x-show="selectedTasks.length > 0" x-cloak>
                        <button @click="confirmBatchDelete" class="bg-red-600 text-white px-4 py-2 rounded text-sm hover:bg-red-700">
                            Delete (<span x-text="selectedTasks.length"></span>) Items
                        </button>
                    </div>
                    <form action="{{ route('schedule.import_from_rab', $package->id) }}" method="POST" onsubmit="return confirm('Are you sure? This will REPLACE the current schedule with the latest data from the RAB.');">
                        @csrf
                        <button type="submit" class="bg-gray-600 text-white px-4 py-2 rounded text-sm hover:bg-gray-700">
                            Sync from RAB
                        </button>
                    </form>
                    <button @click="isModalOpen = true" class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700">
                        + Add New Task
                    </button>
                </div>
            </div>
        </header>

        @if (session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                <p>{{ session('success') }}</p>
            </div>
        @endif
        
        <div class="bg-white rounded shadow" style="height: 65vh;">
            <div id="gantt_here" style='width:100%; height:100%;'></div>
        </div>
    </main>
    
    @include('schedules.partials.modal-create')
    @include('schedules.partials.modal-edit')
</div>
@endsection

@push('scripts')
<script src="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.js"></script>

{{-- TAMBAHKAN: Mendefinisikan data di dalam tag <script> yang aman --}}
<script>
    const ganttTasksData = @json($tasks_data);
</script>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('schedulePage', () => ({
            isModalOpen: false, isEditModalOpen: false, selectedTasks: [], editFormAction: '',
            taskToEdit: { id: null, text: '', start_date_raw: '', end_date_raw: '' },

            openEditModal(taskId) {
                const task = gantt.getTask(taskId);
                if (!task) return;
                this.taskToEdit = {
                    id: task.id, text: task.text,
                    start_date_raw: gantt.date.date_to_str("%Y-%m-%d")(task.start_date),
                    end_date_raw: gantt.date.date_to_str("%Y-%m-%d")(gantt.calculateEndDate({start_date: task.start_date, duration: task.duration}))
                };
                this.editFormAction = `/schedule/${task.id}`;
                this.isEditModalOpen = true;
            },
            updateSelection() {
                this.selectedTasks = Array.from(document.querySelectorAll('.gantt_task_checkbox:checked')).map(cb => parseInt(cb.dataset.id));
                const selectAllCheckbox = document.querySelector(".gantt_select_all");
                if (selectAllCheckbox) {
                    const totalTasks = gantt.getTaskCount();
                    selectAllCheckbox.checked = totalTasks > 0 && this.selectedTasks.length === totalTasks;
                }
            },
            confirmBatchDelete() {
                gantt.confirm({
                    title: "Confirm Delete", text: `Are you sure you want to delete ${this.selectedTasks.length} selected task(s)?`,
                    ok: "Delete", cancel: "Cancel", callback: (result) => { if (result) this.batchDelete(); }
                });
            },
            batchDelete() {
                fetch('{{ route('schedule.batch_delete') }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ ids: this.selectedTasks })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        gantt.batchUpdate(() => this.selectedTasks.forEach(id => gantt.isTaskExists(id) && gantt.deleteTask(id)));
                        this.selectedTasks = []; this.updateSelection();
                    } else gantt.alert("Failed to delete tasks.");
                }).catch(err => gantt.alert("An error occurred."));
            },

            // GANTI: Inisialisasi tidak lagi menerima argumen, tapi mengambil dari variabel global
            initializeGantt() {
                gantt.config.columns = [
                    { name: "select", label: "<input type='checkbox' class='gantt_select_all' title='Select all'/>", width: 40, align: "center", template: (task) => `<input type='checkbox' class='gantt_task_checkbox' data-id='${task.id}'>`},
                    { name: "text", label: "Task Name", tree: true, width: '*', resize: true },
                    { name: "start_date", label: "Start Date", align: "center", width: 100 },
                    { name: "duration", label: "Duration", align: "center", width: 80 },
                    { name: "actions", label: "Actions", width: 100, align: "center", template: (task) => `<div class='gantt_action_buttons'><i class='gantt_edit_icon' data-task-id='${task.id}'></i><i class='gantt_delete_icon' data-task-id='${task.id}'></i></div>` }
                ];

                gantt.config.order_branch = true; gantt.config.order_branch_free = true;
                gantt.config.date_format = "%d-%m-%Y"; gantt.config.grid_width = 650;
                gantt.config.open_tree_initially = true;
                
                gantt.init("gantt_here");
                // GANTI: Ambil data dari variabel `ganttTasksData`
                gantt.parse(ganttTasksData);

                const self = this;
                gantt.attachEvent("onTaskDblClick", (id) => { self.openEditModal(id); return false; });
                gantt.attachEvent("onAfterTaskMove", (id, parent, tindex) => {
                    const tasks = [];
                    gantt.eachTask(task => { tasks.push({ id: task.id, parent: task.parent }); });
                    fetch('{{ route('schedule.update_order') }}', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
                        body: JSON.stringify({ order: tasks })
                    }).catch(err => gantt.alert('Failed to save order.'));
                });
                
                gantt.getGridNode().addEventListener('click', function(e) {
                    const target = e.target;
                    if (target.classList.contains('gantt_task_checkbox')) { setTimeout(() => self.updateSelection(), 50); } 
                    else if (target.classList.contains('gantt_edit_icon')) { self.openEditModal(target.dataset.taskId); } 
                    else if (target.classList.contains('gantt_delete_icon')) {
                        const taskId = target.dataset.taskId;
                        gantt.confirm({
                            title: "Confirm Delete", text: `Delete task "${gantt.getTask(taskId).text}"?`,
                            ok: "Delete", cancel: "Cancel",
                            callback: (result) => {
                                if (result) {
                                    fetch(`/schedule/${taskId}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }})
                                    .then(res => res.json())
                                    .then(data => { if (data.status === 'success') { gantt.deleteTask(taskId); self.updateSelection(); }});
                                }
                            }
                        });
                    }
                    else if (target.classList.contains('gantt_select_all')) {
                        document.querySelectorAll('.gantt_task_checkbox').forEach(cb => cb.checked = target.checked);
                        self.updateSelection();
                    }
                });
            }
        }));
    });
</script>
@endpush