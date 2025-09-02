<tr class="border-t bg-white">
    <td class="py-2 px-3 border font-semibold">
        <a href="{{ asset('storage/' . $document->file_path) }}" target="_blank" class="text-blue-600 hover:underline">{{ $document->title }}</a>
    </td>
    <td class="py-2 px-3 border align-top">
        @if($document->rabItems->isNotEmpty())
            <ul class="list-disc list-inside text-xs space-y-1">
                @foreach($document->rabItems as $rabItem)
                    <li>{{ $rabItem->item_name }}</li>
                @endforeach
            </ul>
        @endif
    </td>
    <td class="py-2 px-3 border align-top">
        <p>{{ $document->document_number }}</p>
        <p class="text-xs text-gray-500">{{ $document->created_at->isoFormat('D MMM YYYY') }}</p>
    </td>
    <td class="py-2 px-3 border text-center align-top">
        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
            Diajukan
        </span>
    </td>
    <td class="py-2 px-3 border text-center align-top">
        <div class="flex justify-center items-center space-x-2">
            <button @click="reviewModalOpen = true; actionUrl = '{{ route('documents.storeReview', $document->id) }}'; documentTitle = '{{ addslashes($document->title) }}'" class="text-blue-600 hover:text-blue-800" title="Review Dokumen">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" /><path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd" /></svg>
            </button>
            <a href="#" class="text-yellow-600 hover:text-yellow-800" title="Edit Dokumen">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" /></svg>
            </a>
            <form action="{{ route('documents.destroy', ['package' => $package->id, 'document' => $document->id]) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus dokumen ini?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-red-600 hover:text-red-800" title="Hapus">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                </button>
            </form>
        </div>
    </td>
</tr>