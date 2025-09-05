@extends('layouts.app')

@section('title', 'Manajemen Dokumen')

@section('content')
{{-- "Saklar" utama untuk semua interaksi di halaman ini (modal dan tab) --}}
<div class="p-4 sm:p-6" x-data="{ reviewModalOpen: false, actionUrl: '', documentTitle: '', activeTab: 'shop_drawing' }">
    <header class="bg-white shadow p-4 rounded-lg mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Manajemen Dokumen</h1>
                <p class="text-sm text-gray-500">Proyek: {{ $package->project->name }} - Paket: {{ $package->name }}</p>
            </div>
        </div>
    </header>

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    <main>
        
        {{-- KONTEN TAB --}}
        <div class="bg-white p-4 rounded-b-lg shadow">
            {{-- Tab Shop Drawing --}}
            <div x-show="activeTab === 'shop_drawing'">
				@can('create', App\Models\Document::class)
                <div class="flex justify-end mb-4">
                    <a href="{{ route('documents.create_submission', ['package' => $package->id]) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
						+ Ajukan Shop Drawing
					</a>
                </div>
				@endcan
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
						<thead class="bg-gray-100">
							<tr>
								<th class="text-left px-4 py-2 w-[5%]"></th> <th class="text-center px-4 py-2 w-[15%]">Tanggal</th>
								<th class="text-left px-4 py-2 w-[30%]">Judul / No. Dokumen</th>
								<th class="text-left px-4 py-2 w-[30%]">Untuk Pekerjaan</th>
								<th class="text-center px-4 py-2 w-[5%]">Rev.</th>
								<th class="text-center px-4 py-2 w-[15%]">Status</th>
								<th class="text-center px-4 py-2 w-[15%]">Aksi</th>
							</tr>
						</thead>
                        <tbody x-data="{ openDetailId: null }">
							@forelse ($documentsByCategory[$activeCategory] ?? [] as $document)
								{{-- ======================== Baris Utama Dokumen ======================== --}}
								<tr class="hover:bg-gray-50">
									{{-- PERBAIKAN: Lebar kolom diatur di sini (TD) dan di header (TH) --}}
									<td class="px-4 py-2 text-center">
										<button @click="openDetailId = (openDetailId === {{ $document->id }}) ? null : {{ $document->id }}" class="text-indigo-600 hover:text-indigo-900 text-lg font-bold w-5 h-5 flex items-center justify-center" title="Lihat Detail">
											<span x-show="openDetailId !== {{ $document->id }}" style="display: none;">▶</span>
											<span x-show="openDetailId === {{ $document->id }}" style="display: none;">▼</span>
										</button>
									</td>
									{{-- Sisa kolom lainnya --}}
									<td class="px-4 py-2 text-center">
										{{ optional($document->created_at)->format('d M Y') }}
									</td>
									<td class="px-4 py-2">
										<div class="font-medium text-indigo-600">{{ $document->title }}</div>
										<div class="text-xs text-gray-500">No. Dok: {{ $document->document_number ?: '-' }}</div>
									</td>
									<td class="px-4 py-2">
										@forelse ($document->rabItems->take(2) as $item)
											<span class="block text-xs">{{ $item->item_name }}</span>
										@empty
											-
										@endforelse
										@if ($document->rabItems->count() > 2)
											<span class="text-xs text-gray-400">...dan lainnya</span>
										@endif
									</td>
									<td class="text-center px-4 py-2">
										{{ $document->revision }}
									</td>
									<td class="text-center px-4 py-2">
										 @php
											$statusConfig = [
												'pending' => ['text' => 'Pengajuan', 'color' => 'blue'],
												'revision' => ['text' => 'Revisi', 'color' => 'yellow'],
												'approved' => ['text' => 'Disetujui', 'color' => 'green'],
												'rejected' => ['text' => 'Ditolak', 'color' => 'red'],
												'superseded' => ['text' => 'Digantikan', 'color' => 'gray'],
											];
											$config = $statusConfig[$document->status] ?? ['text' => ucfirst($document->status), 'color' => 'gray'];
										@endphp
										<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{ $config['color'] }}-100 text-{{ $config['color'] }}-800">
											{{ $config['text'] }}
										</span>
									</td>
									<td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
										<div class="flex justify-center items-center space-x-3">
											<a href="{{ Storage::url($document->file_path) }}" target="_blank" class="text-gray-500 hover:text-blue-700" title="Lihat File">
												<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M10 12a2 2 0 100-4 2 2 0 000 4z" /><path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.022 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" /></svg>
											</a>
											@if ($document->status == 'revision')
												@can('create', App\Models\Document::class)
													<a href="{{ route('documents.createRevision', ['package' => $package->id, 'document' => $document->id]) }}" class="text-green-600 hover:text-green-800" title="Unggah Revisi">
														<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.707-10.293a1 1 0 00-1.414-1.414l-3 3a1 1 0 000 1.414l3 3a1 1 0 001.414-1.414L9.414 11H13a1 1 0 100-2H9.414l1.293-1.293z" clip-rule="evenodd" /></svg>
													</a>
												@endcan
											@endif
											 @can('review', $document)
												<button @click="reviewModalOpen = true; actionUrl = '{{ route('documents.storeReview', ['package' => $package->id, 'document' => $document->id]) }}'; documentTitle = '{{ addslashes($document->title) }}'" class="text-blue-600 hover:text-blue-800" title="Review Dokumen">
													<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" /><path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd" /></svg>
												</button>
											@endcan
											@if(in_array($document->status, ['pending', 'revision']))
												<a href="{{ route('documents.edit', ['package' => $package->id, 'shop_drawing' => $document->id]) }}" class="text-yellow-600 hover:text-yellow-800" title="Edit Dokumen">
													<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" /></svg>
												</a>
											@endif
											 <form action="{{ route('documents.destroy', ['package' => $package->id, 'shop_drawing' => $document->id]) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus dokumen ini?');">
												@csrf
												@method('DELETE')
												<button type="submit" class="text-red-600 hover:text-red-800" title="Hapus">
													<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
												</button>
											</form>
										 </div>
									</td>
								</tr>
								{{-- Baris Detail Riwayat --}}
								<tr x-show="openDetailId === {{ $document->id }}" x-cloak style="display: none;">
									<td></td>
									<td colspan="6" class="p-0">
										<div class="bg-gray-50 p-4">
											@if ($document->approvals->isNotEmpty())
												<table class="min-w-full text-sm">
													<thead class="bg-gray-100">
														<tr>
															<th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
															<th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Oleh</th>
															<th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
															<th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Catatan</th>
														</tr>
													</thead>
													<tbody class="bg-white">
														@foreach ($document->approvals->sortByDesc('created_at') as $approval)
															<tr>
																<td class="px-3 py-2 whitespace-nowrap text-gray-500">{{ $approval->created_at->format('d M Y, H:i') }}</td>
																<td class="px-3 py-2 font-medium text-gray-800">{{ $approval->user->name ?? 'User' }}</td>
																<td class="px-3 py-2">
																	@php
																		$approvalStatusConfig = [
																			'pending' => ['text' => 'Pengajuan', 'color' => 'blue'],
																			'revision' => ['text' => 'Revisi', 'color' => 'yellow'],
																			'approved' => ['text' => 'Disetujui', 'color' => 'green'],
																			'rejected' => ['text' => 'Ditolak', 'color' => 'red'],
																		];
																		$appConfig = $approvalStatusConfig[$approval->status] ?? ['text' => ucfirst($approval->status), 'color' => 'gray'];
																	@endphp
																	<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{ $appConfig['color'] }}-100 text-{{ $appConfig['color'] }}-800">
																		{{ $appConfig['text'] }}
																	</span>
																</td>
																<td class="px-3 py-2 text-gray-500 italic">"{{ $approval->notes ?: '-' }}"</td>
															</tr>
														@endforeach
													</tbody>
												</table>
											@else
												<p class="text-sm text-gray-500 p-3">Belum ada riwayat persetujuan untuk dokumen ini.</p>
											@endif
										</div>
									</td>
								</tr>
							@empty
								<tr>
									<td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
										Belum ada dokumen yang diajukan.
									</td>
								</tr>
							@endforelse
						</tbody>
                    </table>
                </div>
            </div>
            {{-- Tab lain akan kita buat nanti --}}
        </div>
    </main>

    <div x-show="reviewModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div @click.away="reviewModalOpen = false" class="bg-white rounded-lg shadow-xl p-6 w-full max-w-lg mx-4">
            <h2 class="text-xl font-bold mb-4">Formulir Review Dokumen</h2>
            <p class="text-sm mb-4">Dokumen: <strong x-text="documentTitle"></strong></p>
            <form :action="actionUrl" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">Status Review MK</label>
                        <select id="status" name="status" class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md" required>
                            <option value="Disetujui">Disetujui</option>
                            <option value="Disetujui dengan catatan">Disetujui dengan catatan</option>
                            <option value="Revisi">Revisi</option>
                            <option value="Ditolak">Ditolak</option>
                        </select>
                    </div>
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700">Catatan</label>
                        <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Lanjutkan ke Owner?</label>
                        <div class="mt-2 space-y-2">
                            <div class="flex items-center"><input id="continue_yes" name="continue_to_owner" type="radio" value="1" checked class="h-4 w-4 text-indigo-600 border-gray-300"><label for="continue_yes" class="ml-3 block text-sm font-medium text-gray-700">Ya</label></div>
                            <div class="flex items-center"><input id="continue_no" name="continue_to_owner" type="radio" value="0" class="h-4 w-4 text-indigo-600 border-gray-300"><label for="continue_no" class="ml-3 block text-sm font-medium text-gray-700">Tidak</label></div>
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" @click="reviewModalOpen = false" class="bg-white py-2 px-4 border border-gray-300 rounded-md">Batal</button>
                    <button type="submit" class="bg-indigo-600 py-2 px-4 text-white rounded-md">Simpan Review</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection