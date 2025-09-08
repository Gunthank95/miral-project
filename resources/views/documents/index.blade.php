@extends('layouts.app')

@section('title', 'Approval Shop Drawing')

@section('content')
{{-- UBAH x-data DI SINI --}}
<div class="p-4 sm:p-6" x-data="shopDrawingApprovalPage">
    <header class="bg-white shadow p-4 rounded-lg mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Pusat Kendali Persetujuan Shop Drawing</h1>
                <p class="text-sm text-gray-500">Proyek: {{ $package->project->name }} - Paket: {{ $package->name }}</p>
            </div>
            @can('create', App\Models\Document::class)
            <a href="{{ route('documents.create_submission', ['package' => $package->id]) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                + Ajukan Dokumen
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
                                <td class="px-6 py-4 text-gray-700">{{ $document->user->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-center">
                                    @php
                                        $statusConfig = [
                                            'pending' => ['text' => 'Pending', 'color' => 'blue'],
                                            'revision' => ['text' => 'Revisi', 'color' => 'yellow'],
                                            'approved' => ['text' => 'Disetujui', 'color' => 'green'],
                                            'rejected' => ['text' => 'Ditolak', 'color' => 'red'],
                                        ];
                                        $config = $statusConfig[strtolower($document->status)] ?? ['text' => ucfirst($document->status), 'color' => 'gray'];
                                    @endphp
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{ $config['color'] }}-100 text-{{ $config['color'] }}-800">
                                        {{ $config['text'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center space-x-3">
                                        @can('review', $document)
                                            <button @click="openReviewModal('{{ route('documents.storeReview', ['package' => $package->id, 'shop_drawing' => $document->id]) }}', '{{ $document->document_number }}', {{ $document->id }})"
                                                    class="text-indigo-600 hover:text-indigo-900 font-bold text-sm">
                                                Review
                                            </button>
                                        @endcan
                                        @can('resubmit', $document)
                                            <a href="#" class="text-yellow-600 hover:text-yellow-900 font-bold text-sm">
                                                Revisi
                                            </a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            {{-- Baris Detail (Digabung ke sini) --}}
                            <tr x-show="openDetailId === {{ $document->id }}" x-cloak>
                                <td></td>
                                <td colspan="4" class="p-0">
                                    <div class="bg-gray-50 p-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div class="space-y-4">
                                            {{-- Daftar Gambar --}}
                                            <div>
                                                <h4 class="font-semibold text-sm text-gray-700 mb-2">Daftar Gambar</h4>
                                                <ul class="list-disc list-inside text-sm text-gray-600 space-y-1 bg-white p-3 rounded-md border">
                                                    @forelse ($document->drawingDetails as $drawing)
                                                        <li><span class="font-mono bg-gray-200 px-1 rounded text-xs">{{ $drawing->drawing_number }}</span> - {{ $drawing->drawing_title }}</li>
                                                    @empty
                                                        <li>Tidak ada detail gambar.</li>
                                                    @endforelse
                                                </ul>
                                            </div>
                                            {{-- Pekerjaan Terkait --}}
                                            <div>
                                                <h4 class="font-semibold text-sm text-gray-700 mb-2">Pekerjaan RAB Terkait</h4>
                                                <div class="space-y-2 text-sm">
                                                    @forelse ($document->rabItems as $item)
                                                        <div class="flex justify-between items-center p-2 bg-white rounded-md border">
                                                            <span class="text-gray-800">{{ $item->item_name }}</span>
                                                            @if(isset($item->pivot->completion_status))
                                                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $item->pivot->completion_status === 'lengkap' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">{{ ucfirst(str_replace('_', ' ', $item->pivot->completion_status)) }}</span>
                                                            @endif
                                                        </div>
                                                    @empty
                                                        <p class="text-gray-500 p-2">- Tidak ada pekerjaan terkait -</p>
                                                    @endforelse
                                                </div>
                                            </div>
                                        </div>
                                        {{-- Riwayat Persetujuan --}}
                                        <div>
                                            <h4 class="font-semibold text-sm text-gray-700 mb-2">Riwayat Persetujuan</h4>
                                            @if ($document->approvals->isNotEmpty())
                                                <table class="min-w-full text-sm bg-white rounded-md border">
                                                    <thead class="bg-gray-100"><tr><th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Tanggal</th><th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Oleh</th><th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Status</th></tr></thead>
                                                    <tbody class="divide-y">
                                                        @foreach ($document->approvals->sortByDesc('created_at') as $approval)
                                                            <tr><td class="px-3 py-2 text-gray-500">{{ $approval->created_at->format('d M Y, H:i') }}</td><td class="px-3 py-2 font-medium text-gray-800">{{ $approval->user->name ?? 'User' }}</td><td class="px-3 py-2"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">{{ $approval->status }}</span></td></tr>
                                                            @if($approval->notes)<tr class="bg-gray-50"><td colspan="3" class="px-3 py-1 text-xs text-gray-600 italic border-t">Catatan: "{{ $approval->notes }}"</td></tr>@endif
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            @else
                                                <p class="text-sm text-gray-500 p-3 bg-white rounded-md border">Belum ada riwayat persetujuan.</p>
                                            @endif
                                        </div>
                                    </div>
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
    
    {{-- MODAL REVIEW (Digabung ke sini) --}}
    <div x-show="reviewModal.open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div @click.away="closeReviewModal()" class="bg-gray-50 rounded-lg shadow-xl w-full max-w-4xl mx-4 max-h-[90vh] flex flex-col">
            <div class="bg-white p-4 border-b rounded-t-lg"><h2 id="modal-title" class="text-xl font-bold">Formulir Review Shop Drawing</h2><p class="text-sm text-gray-500">No. Surat: <strong x-text="reviewModal.documentTitle"></strong></p></div>
            <div class="p-6 overflow-y-auto space-y-6">
                <div x-show="reviewModal.loading" class="text-center py-10"><p class="text-gray-500">Memuat detail dokumen...</p></div>
                <div x-show="!reviewModal.loading" class="space-y-6">
                    <form :action="reviewModal.actionUrl" method="POST">
                        @csrf
                        <div><h3 class="font-semibold text-gray-800 border-b pb-2 mb-2">1. Hasil Pemeriksaan Gambar</h3><div class="space-y-4"><template x-for="drawing in reviewModal.details.drawings" :key="drawing.id"><div class="p-3 bg-white rounded-md border grid grid-cols-1 md:grid-cols-3 gap-4"><div class="md:col-span-1"><p class="text-sm font-medium" x-text="drawing.drawing_title"></p><p class="text-xs text-gray-500 font-mono" x-text="drawing.drawing_number"></p></div><div class="md:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-3"><div><label :for="'status_' + drawing.id" class="text-xs font-medium text-gray-600">Status</label><select :name="'drawings[' + drawing.id + '][status]'" :id="'status_' + drawing.id" class="mt-1 block w-full py-1 px-2 border border-gray-300 rounded-md text-sm" required><option value="approved">Disetujui</option><option value="revision">Revisi</option><option value="rejected">Ditolak</option></select></div><div><label :for="'notes_' + drawing.id" class="text-xs font-medium text-gray-600">Catatan</label><input type="text" :name="'drawings[' + drawing.id + '][notes]'" :id="'notes_' + drawing.id" class="mt-1 block w-full border border-gray-300 rounded-md text-sm py-1 px-2"></div></div></div></template></div></div>
                        <div><h3 class="font-semibold text-gray-800 border-b pb-2 mb-2">2. Verifikasi Kelengkapan Pekerjaan</h3><div class="space-y-2 text-sm"><template x-for="item in reviewModal.details.rab_items" :key="item.id"><div class="flex justify-between items-center p-2 bg-white rounded-md border"><span x-text="item.item_name"></span><select :name="'rab_items[' + item.id + '][completion_status]'" class="py-1 px-2 border border-gray-300 rounded-md text-xs"><option value="belum_lengkap" :selected="item.pivot.completion_status == 'belum_lengkap'">Belum Lengkap</option><option value="lengkap" :selected="item.pivot.completion_status == 'lengkap'">Lengkap</option></select></div></template></div></div>
                        <div class="bg-white p-4 rounded-md border"><h3 class="font-semibold text-gray-800 mb-2">3. Keputusan Akhir & Disposisi</h3><div class="space-y-4"><div><label for="overall_notes" class="block text-sm font-medium text-gray-700">Catatan Keseluruhan</label><textarea id="overall_notes" name="overall_notes" rows="3" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"></textarea></div><div><label for="disposition" class="block text-sm font-medium text-gray-700">Disposisi</label><select id="disposition" name="disposition" class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md" required><option value="">-- Pilih Tujuan --</option><option value="to_owner">Teruskan ke Owner</option><option value="final_approve">Finalisasi Persetujuan</option></select></div></div></div>
                        <div class="mt-6 flex justify-end space-x-3 pt-4 border-t"><button type="button" @click="closeReviewModal()" class="bg-white py-2 px-4 border border-gray-300 rounded-md">Batal</button><button type="submit" class="bg-indigo-600 py-2 px-4 text-white rounded-md">Simpan & Lanjutkan</button></div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('shopDrawingApprovalPage', () => ({
            reviewModal: {
                open: false,
                loading: true,
                actionUrl: '',
                documentTitle: '',
                documentId: null,
                details: { drawings: [], rab_items: [], history: [] }
            },
            openReviewModal(actionUrl, title, docId) {
                console.log('1. Tombol Review diklik.');
                this.reviewModal.actionUrl = actionUrl;
                this.reviewModal.documentTitle = title;
                this.reviewModal.documentId = docId;
                this.reviewModal.open = true;
                this.reviewModal.loading = true;
                const apiUrl = `/api/documents/${docId}/review-details`;
                console.log('2. Mengambil data dari API:', apiUrl);
                fetch(apiUrl, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => {
                    console.log('3. Mendapat respons dari API. Status:', response.status);
                    if (!response.ok) { throw new Error(`Gagal mengambil data, status: ${response.status}`); }
                    return response.json();
                })
                .then(data => {
                    console.log('4. Data berhasil di-parse:', data);
                    this.reviewModal.details = data;
                    this.reviewModal.loading = false;
                })
                .catch(error => {
                    console.error('5. Terjadi error saat fetch API:', error);
                    this.reviewModal.loading = false;
                    alert('Gagal memuat detail dokumen. Cek console (F12) untuk detail error.');
                    this.closeReviewModal();
                });
            },
            closeReviewModal() {
                this.reviewModal.open = false;
                this.reviewModal.details = { drawings: [], rab_items: [], history: [] };
            }
        }));
    });
</script>
@endpush