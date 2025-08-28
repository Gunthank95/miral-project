{{-- Modal untuk Edit Tugas --}}
<div x-show="isEditModalOpen" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center" x-cloak>
    <div @click.away="isEditModalOpen = false" class="relative mx-auto p-5 border w-full max-w-lg shadow-lg rounded-md bg-white">
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