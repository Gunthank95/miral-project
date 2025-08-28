{{-- Modal untuk Tambah Tugas Baru --}}
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