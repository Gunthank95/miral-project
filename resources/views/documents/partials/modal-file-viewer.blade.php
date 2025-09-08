<div x-show="fileModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div @click.away="fileModalOpen = false" class="bg-white rounded-lg shadow-xl p-6 w-full max-w-lg mx-4">
        <div class="flex justify-between items-center border-b pb-3 mb-4">
            <h2 class="text-xl font-bold">File Terlampir</h2>
            <button @click="fileModalOpen = false" class="text-gray-500 hover:text-gray-800 text-2xl">&times;</button>
        </div>
        <div class="space-y-2 max-h-60 overflow-y-auto">
            <template x-if="documentFiles.length > 0">
                <template x-for="file in documentFiles" :key="file.id">
                    <a :href="'/storage/' + file.file_path" target="_blank" class="block p-2 text-blue-600 hover:bg-gray-100 rounded" x-text="file.original_filename"></a>
                </template>
            </template>
            <template x-if="documentFiles.length === 0">
                <p class="text-gray-500 p-2">Tidak ada file yang dilampirkan.</p>
            </template>
        </div>
        <div class="mt-6 flex justify-end">
            <button type="button" @click="fileModalOpen = false" class="bg-gray-200 text-gray-800 py-2 px-4 rounded-md text-sm font-medium hover:bg-gray-300">Tutup</button>
        </div>
    </div>
</div>