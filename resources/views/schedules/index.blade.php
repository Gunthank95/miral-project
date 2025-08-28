@extends('layouts.app')

@section('title', 'Project Schedule')

@push('styles')
{{-- Menggunakan DHTMLX Gantt dari CDN --}}
<link href="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.css" rel="stylesheet">
<style>
    /* Styling untuk header kolom agar lebih tebal */
    .gantt_task_grid .gantt_grid_head_cell { 
        font-weight: 600; 
    }
    /* Kursor 'move' untuk item-item yang bisa di-drag */
    .gantt_tree_content { 
        cursor: move; 
    }
    /* Sembunyikan elemen dengan x-cloak sampai Alpine.js siap */
    [x-cloak] { 
        display: none !important; 
    }
    /* Styling untuk tombol aksi di dalam grid */
    .gantt_action_buttons { 
        display: flex; 
        justify-content: center; 
        align-items: center; 
        gap: 10px; 
    }
    .gantt_action_buttons i { 
        cursor: pointer; 
        font-style: normal; 
    }
    /* Ikon menggunakan emoji agar sederhana */
    .gantt_edit_icon:before { 
        content: "✏️"; 
        font-size: 16px; 
        color: #f59e0b; /* amber-500 */
    }
    .gantt_delete_icon:before { 
        content: "❌"; 
        font-size: 16px; 
        color: #e53935; /* red-600 */
    }
</style>
@endpush

@section('content')
<div class="p-4 sm:p-6" x-data="schedulePage()" x-init="initializeGantt()">
    <main class="flex-1">
        <header class="bg-white shadow p-4 rounded-lg mb-6">
            <div class="flex flex-wrap justify-between items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Project Schedule - Aplikasi Manajemen Proyek</h1>
                    <p class="text-sm text-gray-500">
                        Proyek: {{ $package->project->name }} - Paket: {{ $package->name }}
                    </p>
                </div>
                <div class="flex space-x-2 items-center">
                    {{-- TAMBAHKAN: Tombol hapus massal, hanya muncul jika ada item yang dipilih --}}
                    <div x-show="selectedTasks.length > 0" x-cloak>
                        <button @click="confirmBatchDelete" class="bg-red-600 text-white px-4 py-2 rounded text-sm hover:bg-red-700">
                            Hapus (<span x-text="selectedTasks.length"></span>) Item
                        </button>
                    </div>
                    {{-- Tombol Sync from RAB --}}
                    <form action="{{ route('schedules.import_from_rab', $package->id) }}" method="POST" onsubmit="return confirm('Anda yakin? Ini akan MENGGANTI jadwal saat ini dengan data terbaru dari RAB.');">
                        @csrf
                        <button type="submit" class="bg-gray-600 text-white px-4 py-2 rounded text-sm hover:bg-gray-700">
                            Sync from RAB
                        </button>
                    </form>
                    {{-- Tombol Tambah Tugas Baru --}}
                    <button @click="isModalOpen = true" class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700">
                        + Tambah Tugas Baru
                    </button>
                </div>
            </div>
        </header>

        {{-- Notifikasi Sukses --}}
        @if (session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                <p>{{ session('success') }}</p>
            </div>
        @endif
        
        {{-- Kontainer untuk Gantt Chart --}}
        <div class="bg-white rounded shadow" style="height: 65vh;">
            <div id="gantt_here" style='width:100%; height:100%;'></div>
        </div>
    </main>
    
    {{-- Memanggil modal dari file partial --}}
    @include('schedules.partials.modal-create')
    @include('schedules.partials.modal-edit')
</div>
@endsection

@push('scripts')
<script src="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.js"></script>

{{-- Mengambil data dari controller dan menyiapkannya untuk JavaScript --}}
<script>
    const ganttTasksData = {!! $tasks_data !!};
</script>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('schedulePage', () => ({
        isModalOpen: false,
        isEditModalOpen: false,
        selectedTasks: [],
        editFormAction: '',
        taskToEdit: { id: null, text: '', start_date_raw: '', end_date_raw: '' },

        openEditModal(taskId) {
            const task = gantt.getTask(taskId);
            if (!task) return;
            const startDate = gantt.date.date_to_str("%Y-%m-%d")(task.start_date);
            const endDate = gantt.date.date_to_str("%Y-%m-%d")(gantt.calculateEndDate({start_date: task.start_date, duration: task.duration, task: task}));
            
            this.taskToEdit = { id: task.id, text: task.text, start_date_raw: startDate, end_date_raw: endDate, };
            this.editFormAction = `/schedule/${task.id}`;
            this.isEditModalOpen = true;
        },

        updateSelection(target) {
            // Logika untuk "Pilih Semua"
            if (target && target.classList.contains('gantt_select_all')) {
                this.selectedTasks = [];
                if (target.checked) {
                    gantt.eachTask(task => this.selectedTasks.push(task.id));
                }
            } else {
                 // Logika untuk checkbox individual
                this.selectedTasks = [];
                gantt.eachTask(task => {
                    if (gantt.isSelected(task.id)) {
                        this.selectedTasks.push(task.id);
                    }
                });
            }
        },
        
        confirmBatchDelete() {
            gantt.confirm({
                title: "Konfirmasi Hapus",
                text: `Anda yakin ingin menghapus ${this.selectedTasks.length} tugas yang dipilih? Ini tidak bisa dibatalkan.`,
                ok: "Hapus", cancel: "Batal",
                callback: (result) => { if (result) this.batchDelete(); }
            });
        },

        batchDelete() {
            fetch('{{ route('schedules.batch_delete') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ ids: this.selectedTasks })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    gantt.batchUpdate(() => this.selectedTasks.forEach(id => gantt.isTaskExists(id) && gantt.deleteTask(id)));
                    this.selectedTasks = [];
                } else { gantt.alert("Gagal menghapus tugas."); }
            }).catch(err => gantt.alert("Terjadi kesalahan."));
        },

        initializeGantt() {
            const self = this;
            
            // GANTI: Kolom checkbox diubah untuk menggunakan template multi-select dari gantt
            gantt.config.columns = [
                { name: "wbs", label: "WBS", width: 40, align: "center", template: gantt.getWBSCode },
                { name: "text", label: "Nama Tugas", tree: true, width: '*', resize: true },
                { name: "start_date", label: "Tgl Mulai", align: "center", width: 100 },
                { name: "duration", label: "Durasi", align: "center", width: 80 },
                { name: "actions", label: "Aksi", width: 100, align: "center", template: (task) => `<div class='gantt_action_buttons'><i class='gantt_edit_icon'></i><i class='gantt_delete_icon'></i></div>` }
            ];
            
            // TAMBAHKAN: Aktifkan plugin multi-select
            gantt.plugins({ multiselect: true });

            gantt.config.order_branch = true;
            gantt.config.order_branch_free = true;
            gantt.config.date_format = "%d-%m-%Y";
            gantt.config.grid_width = 650;
            gantt.config.open_tree_initially = true;
            
            gantt.init("gantt_here");
            gantt.parse(ganttTasksData);
            
            // GANTI: Gunakan `attachEvent` untuk menangani klik, ini cara yang lebih aman
            gantt.attachEvent("onTaskClick", function(id, e){
                const target = e.target;
                if (target.classList.contains('gantt_edit_icon')) {
                    self.openEditModal(id);
                } else if (target.classList.contains('gantt_delete_icon')) {
                    gantt.confirm({
                        title: "Konfirmasi Hapus", text: `Hapus tugas "${gantt.getTask(id).text}"?`, ok: "Hapus", cancel: "Batal",
                        callback: (result) => {
                            if (result) {
                                fetch(`/schedule/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }})
                                .then(res => res.json())
                                .then(data => { if (data.status === 'success') { gantt.deleteTask(id); } });
                            }
                        }
                    });
                }
                return true; // Penting untuk membiarkan event klik default tetap berjalan
            });

            // GANTI: Event listener untuk menangani pemilihan item (untuk tombol hapus massal)
            gantt.attachEvent("onSelectionChanged", function(e){
                self.updateSelection();
            });
            
            // TAMBAHKAN: Event listener untuk header checkbox "pilih semua"
            gantt.attachEvent("onHeaderClick", function(name, e){
                if(name === "wbs"){ // Ganti dengan nama kolom checkbox jika berbeda
                    self.updateSelection(e.target);
                }
                return true;
            });

            // Event listener untuk drag-and-drop
            gantt.attachEvent("onAfterTaskMove", (id, parent, tindex) => {
                const tasks = [];
                gantt.eachTask(task => { tasks.push({ id: task.id, parent: task.parent }); });
                fetch('{{ route('schedules.update_order') }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
                    body: JSON.stringify({ order: tasks })
                }).catch(err => gantt.alert('Gagal menyimpan urutan.'));
            });
        }
    }));
});
</script>
@endpush