@extends('layouts.app')

@section('title', 'Pusat Kendali Persetujuan Shop Drawing')

@section('content')
<div class="p-4 sm:p-6" x-data="shopDrawingApprovalPage">
    <header class="bg-white shadow p-4 rounded-lg mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Pusat Kendali Persetujuan Shop Drawing</h1>
                <p class="text-sm text-gray-500">Proyek: {{ $package->project->name }} - Paket: {{ $package->name }}</p>
            </div>
            @can('create', App\Models\Document::class)
            <a href="{{ route('documents.create_submission', ['package' => $package->id]) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                + Ajukan Shop Drawing
            </a>
            @endcan
        </div>
    </header>

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

   <main>
        <div class="bg-white p-4 rounded-lg shadow">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="w-10"></th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Surat Pengantar</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Diajukan Oleh</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody x-data="{ openDetailId: null }">
                        @forelse ($documentsByCategory['shop_drawing'] ?? [] as $document)
                            {{-- Baris Utama --}}
                            <tr class="border-t">
                                <td class="pl-4">
                                    <button @click="openDetailId = (openDetailId === {{ $document->id }}) ? null : {{ $document->id }}" class="text-indigo-600 hover:text-indigo-900 text-xl font-bold w-6 h-6 flex items-center justify-center">
                                        <span x-show="openDetailId !== {{ $document->id }}">▶</span>
                                        <span x-show="openDetailId === {{ $document->id }}" x-cloak>▼</span>
                                    </button>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900">{{ $document->title }}</div>
                                    <div class="text-xs text-gray-500">No: {{ $document->document_number ?: '-' }} | {{ $document->created_at->isoFormat('D MMM YYYY') }}</div>
                                </td>
                                <td class="px-6 py-4 text-gray-700">
                                    {{-- Kode ini sekarang akan tampil karena berada di file yang benar --}}
                                    {{ $document->user->name ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @php
                                        $statusConfig = [
                                            'pending' => ['text' => 'Pending', 'color' => 'blue'],
                                            'revision' => ['text' => 'Revisi', 'color' => 'yellow'],
                                            'approved' => ['text' => 'Disetujui', 'color' => 'green'],
                                            'rejected' => ['text' => 'Ditolak', 'color' => 'red'],
											'menunggu_persetujuan_owner' => ['text' => 'Menunggu Persetujuan Owner', 'color' => 'purple'],
                                        ];
                                        $config = $statusConfig[strtolower($document->status)] ?? ['text' => ucfirst($document->status), 'color' => 'gray'];
                                    @endphp
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{ $config['color'] }}-100 text-{{ $config['color'] }}-800">
                                        {{ $config['text'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
									<div class="flex items-center justify-center space-x-4">
										{{-- Tombol Lihat File --}}
										@if($document->files->isNotEmpty())
											<button @click="openFileViewerModal({{ $document->files }})" class="text-gray-500 hover:text-indigo-600" title="Lihat File">
												<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2-2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd" /></svg>
											</button>
										@endif

										{{-- Tombol Review --}}
										@can('review', $document)
											<button @click="openReviewModal('{{ route('documents.storeReview', ['package' => $package->id, 'shop_drawing' => $document->id]) }}', '{{ $document->document_number }}', {{ $document->id }})"
													class="text-indigo-600 hover:text-indigo-900 font-bold text-sm">
												Review
											</button>
										@endcan
										
										{{-- Tombol Revisi --}}
										@can('resubmit', $document)
											<a href="{{ route('documents.revise', ['package' => $package->id, 'document' => $document->id]) }}"
											   class="text-yellow-600 hover:text-yellow-900 font-bold text-sm"
											   title="Ajukan Revisi">
												Revisi
											</a>
										@endcan

										{{-- Tombol Edit --}}
										@can('update', $document)
											{{-- PERBAIKAN DI SINI: 'document' diubah menjadi 'shop_drawing' --}}
											<a href="{{ route('documents.edit', ['package' => $package->id, 'shop_drawing' => $document]) }}" class="text-yellow-600 hover:text-yellow-800" title="Edit Dokumen">
												<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" /></svg>
											</a>
										@endcan

										{{-- Tombol Hapus --}}
										@can('delete', $document)
											{{-- PERBAIKAN DI SINI: 'document' diubah menjadi 'shop_drawing' --}}
											<form action="{{ route('documents.destroy', ['package' => $package->id, 'shop_drawing' => $document]) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus dokumen ini?');">
												@csrf
												@method('DELETE')
												<button type="submit" class="text-red-600 hover:text-red-800" title="Hapus">
													<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
												</button>
											</form>
										@endcan
									</div>
								</td>
                            </tr>
                            {{-- Baris Detail --}}
                            <tr x-show="openDetailId === {{ $document->id }}" x-cloak>
                                <td></td>
                                <td colspan="4" class="p-0">
                                    @include('documents.partials.detail-view', ['document' => $document])
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center py-10 text-gray-500">Belum ada dokumen Shop Drawing yang diajukan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    @include('documents.partials.modal-file-viewer')
    @include('documents.partials.modal-review')

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('shopDrawingApprovalPage', () => ({
            fileModalOpen: false, 
            documentFiles: [],
            reviewModal: {
                open: false,
                loading: true,
                actionUrl: '',
                documentTitle: '',
                documentId: null,
                details: { 
                    drawings: [], 
                    rab_items: [], 
                    history: [],
                    document_status: ''
                },
            },
            openFileViewerModal(files) {
                this.documentFiles = files;
                this.fileModalOpen = true;
            },
            openReviewModal(actionUrl, title, docId) {
                this.reviewModal.open = true;
                this.reviewModal.loading = true;
                this.reviewModal.actionUrl = actionUrl;
                this.reviewModal.documentTitle = title;
                this.reviewModal.documentId = docId;
                
                const apiUrl = `/api/documents/${docId}/review-details`;
                
                fetch(apiUrl)
                .then(response => {
                    if (!response.ok) { throw new Error('Gagal mengambil data dari server.'); }
                    return response.json();
                })
                .then(data => {
                    this.reviewModal.details = data; 
                    this.reviewModal.loading = false;
                })
                .catch(error => {
                    console.error('Error saat fetch API:', error);
                    this.reviewModal.loading = false;
                    alert('Gagal memuat detail dokumen. Silakan coba lagi.');
                    this.closeReviewModal();
                });
            },
            closeReviewModal() {
                this.reviewModal.open = false;
            }
        }));
    });
</script>
@endpush