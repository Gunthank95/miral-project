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
        {{-- Navigasi Tab --}}
        <div class="mb-4 border-b border-gray-200">
            <ul class="flex flex-wrap -mb-px text-sm font-medium text-center text-gray-500">
                @foreach ($categories as $key => $name)
                <li class="mr-2">
                    <a href="#" @click.prevent="activeTab = '{{ $key }}'"
                       :class="{ 'text-blue-600 border-blue-600 active': activeTab === '{{ $key }}', 'border-transparent hover:text-gray-600 hover:border-gray-300': activeTab !== '{{ $key }}' }"
                       class="inline-block p-4 border-b-2 rounded-t-lg">
                        {{ $name }}
                    </a>
                </li>
                @endforeach
            </ul>
        </div>

        {{-- KONTEN TAB --}}
        <div class="bg-white p-4 rounded-b-lg shadow">
            {{-- Tab Shop Drawing --}}
            <div x-show="activeTab === 'shop_drawing'">
                <div class="flex justify-end mb-4">
                    <a href="{{ route('documents.createSubmission', ['package' => $package->id]) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                        + Ajukan Shop Drawing
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white text-sm table-fixed">
                        <colgroup>
                            <col style="width: 30%;"><col style="width: 25%;"><col style="width: 20%;"><col style="width: 10%;"><col style="width: 15%;">
                        </colgroup>
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="py-2 px-3 border text-left">Judul Dokumen</th>
                                <th class="py-2 px-3 border text-left">Untuk Pekerjaan</th>
                                <th class="py-2 px-3 border text-left">No. Dokumen & Tgl. Upload</th>
                                <th class="py-2 px-3 border text-center">Status</th>
                                <th class="py-2 px-3 border text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
							@forelse($documentsByCategory['Shop Drawing'] ?? [] as $document)
								@php
									// Ambil data pengajuan awal (induk) dari koleksi approvals
									$parentApproval = $document->approvals->whereNull('parent_id')->first();
								@endphp

								{{-- Baris Induk (Pengajuan Awal Kontraktor) --}}
								@if($parentApproval)
									<tr class="border-t bg-white">
										<td class="py-2 px-3 border font-semibold">
											<a href="{{ asset('storage/' . $document->file_path) }}" target="_blank" class="text-blue-600 hover:underline">{{ $document->title }}</a>
											<p class="text-xs text-gray-600 mt-1">{{ $document->description ?? '' }}</p>
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
											<p class="text-xs text-gray-500">{{ $parentApproval->created_at->isoFormat('D MMM YYYY') }} oleh {{ $parentApproval->user->name ?? 'Kontraktor' }}</p>
										</td>
										<td class="py-2 px-3 border text-center align-top">
											<span class="px-2 py-1 text-xs font-semibold rounded-full 
												@if($document->status == 'pending') bg-yellow-100 text-yellow-800
												@elseif(in_array($document->status, ['approved', 'disetujui', 'Disetujui', 'Disetujui dengan catatan'])) bg-green-100 text-green-800
												@elseif(in_array($document->status, ['rejected', 'ditolak', 'Ditolak', 'Revisi'])) bg-orange-100 text-orange-800
												@else bg-blue-100 text-blue-800 @endif">
												{{ ucfirst($document->status) }}
											</span>
										</td>
										<td class="py-2 px-3 border text-center align-top">
											<div class="flex justify-center items-center space-x-2">
												<button @click="reviewModalOpen = true; actionUrl = '{{ route('documents.storeReview', $document->id) }}'; documentTitle = '{{ addslashes($document->title) }}'" class="text-blue-600 hover:text-blue-800" title="Review Dokumen">
													<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" /><path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd" /></svg>
												</button>

												<a href="{{ route('documents.edit', ['package' => $package->id, 'document' => $document->id]) }}" class="text-yellow-600 hover:text-yellow-800" title="Edit Dokumen">
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

									{{-- PERBAIKI: Tampilkan Baris Anak (Hasil Review) dengan benar --}}
									@foreach($parentApproval->children as $childApproval)
									<tr class="border-t bg-gray-50">
										<td class="py-2 px-3 border pl-8 text-sm">
											<p class="font-semibold">
												@if($childApproval->reviewed_file_path)
													<a href="{{ asset('storage/' . $childApproval->reviewed_file_path) }}" target="_blank" class="text-indigo-600 hover:underline">
														Review dari {{ $childApproval->user->name ?? 'MK' }}
													</a>
												@else
													Review dari {{ $childApproval->user->name ?? 'MK' }}
												@endif
											</p>
											<p class="text-xs text-gray-600 italic">"{{ $childApproval->notes }}"</p>
										</td>
										<td></td> {{-- Kolom kosong untuk perataan --}}
										<td class="py-2 px-3 border text-xs text-gray-500 align-top">{{ $childApproval->created_at->isoFormat('D MMM YYYY') }}</td>
										<td class="py-2 px-3 border text-center align-top">
											<span class="px-2 py-1 text-xs font-semibold rounded-full 
												@if(in_array($childApproval->status, ['Disetujui', 'Disetujui dengan catatan'])) bg-green-100 text-green-800
												@elseif($childApproval->status == 'Revisi') bg-yellow-100 text-yellow-800
												@else bg-red-100 text-red-800 @endif">
												{{ $childApproval->status }}
											</span>
										</td>
										<td class="py-2 px-3 border text-center align-top">-</td>
									</tr>
									@endforeach
								@endif
							@empty
								<tr><td colspan="5" class="text-center py-4">Belum ada Shop Drawing yang diunggah.</td></tr>
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