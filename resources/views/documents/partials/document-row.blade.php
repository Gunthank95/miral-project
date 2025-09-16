<tr class="border-t bg-white hover:bg-gray-50" x-data="{ open: false }">
    {{-- Kolom Expand --}}
    <td class="pl-4 pr-3 py-2 whitespace-nowrap text-sm font-medium text-gray-900">
        <button @click="open = !open" class="text-gray-500 hover:text-gray-700">
            <svg class="w-5 h-5 transition-transform duration-200" :class="{'rotate-90': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
        </button>
    </td>

    {{-- Kolom Info Pengajuan --}}
    <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500">
        <div class="font-medium text-gray-900">{{ $document->title }}</div>
        <div class="text-xs">{{ $document->transmittal_no }} | Diajukan: {{ $document->created_at->isoFormat('D MMM YYYY') }}</div>
		
		<div class="text-xs text-red-500">Dibuat oleh: {{ optional($document->user)->name ?? 'User Tidak Ditemukan' }}</div>
		
    </td>

    {{-- Kolom Status --}}
    <td class="px-3 py-2 whitespace-nowrap text-center text-sm">
        @php
            $statusConfig = [
                'pending' => ['color' => 'blue', 'text' => 'Pending'],
                'revision' => ['color' => 'yellow', 'text' => 'Revision'],
                'rejected' => ['color' => 'red', 'text' => 'Rejected'],
                'owner_rejected' => ['color' => 'red', 'text' => 'Ditolak Owner'],
                'menunggu_persetujuan_owner' => ['color' => 'purple', 'text' => 'Review Owner'],
                'approved' => ['color' => 'green', 'text' => 'Approved'],
                'submitted' => ['color' => 'gray', 'text' => 'Submitted'],
            ];
            $config = $statusConfig[$document->status] ?? ['color' => 'gray', 'text' => Str::title(str_replace('_', ' ', $document->status))];
        @endphp
        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{ $config['color'] }}-100 text-{{ $config['color'] }}-800">
            {{ $config['text'] }}
        </span>
    </td>

    {{-- Kolom Aksi --}}
    <td class="px-4 py-2 whitespace-nowrap text-right text-sm font-medium">
        <div class="flex items-center justify-end space-x-3">
            
            {{-- Tombol Lihat File --}}
            @if($document->files->isNotEmpty())
            <button @click="$dispatch('open-file-viewer', { path: '{{ asset('storage/' . $document->files->first()->file_path) }}', title: '{{ $document->title }}' })" class="text-gray-500 hover:text-gray-800" title="Lihat File">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
            </button>
            @endif

            {{-- Tombol Review (Untuk MK & Owner) --}}
            @can('review', $document)
                <button @click="$dispatch('open-review-modal', { id: {{ $document->id }}, title: '{{ $document->transmittal_no }}' })" class="px-3 py-1 text-sm bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                    Review
                </button>
            @endcan

            {{-- Tombol Revisi (Untuk Kontraktor) --}}
            @can('resubmit', $document)
                 <a href="{{ route('documents.resubmit', ['package' => $package->id, 'document' => $document->id]) }}" class="px-3 py-1 text-sm bg-yellow-500 text-white rounded-md hover:bg-yellow-600">Revisi</a>
            @endcan

            {{-- Tombol Edit (Untuk Kontraktor) --}}
            @can('update', $document)
                <a href="{{ route('documents.edit', ['package' => $package->id, 'shop_drawing' => $document->id]) }}" class="text-gray-500 hover:text-blue-600" title="Edit Pengajuan">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                </a>
            @endcan
            
            {{-- Tombol Hapus (Untuk Kontraktor) --}}
            @can('delete', $document)
                <form action="{{ route('documents.destroy', ['package' => $package->id, 'document' => $document->id]) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus dokumen ini? Aksi ini tidak dapat dibatalkan.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-gray-500 hover:text-red-600" title="Hapus Pengajuan">
                         <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>
                </form>
            @endcan
        </div>
    </td>
</tr>

{{-- Baris Detail (saat di-expand) --}}
<tr x-show="open" x-collapse>
    @include('documents.partials.detail-view', ['document' => $document])
</tr>