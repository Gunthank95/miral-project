@extends('layouts.app')

@section('title', 'Manajemen Dokumen')

@section('content')
<div class="p-4 sm:p-6">
    <header class="bg-white shadow p-4 rounded-lg mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Manajemen Dokumen</h1>
                <p class="text-sm text-gray-500">
                    Proyek: {{ $package->project->name }} - Paket: {{ $package->name }}
                </p>
            </div>
            {{-- Tombol Upload Global Dihapus dari Sini --}}
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
                    <a href="{{ route('documents.index', ['package' => $package->id, 'category' => $key]) }}" 
                       class="inline-block p-4 border-b-2 rounded-t-lg 
                              {{ $activeCategory == $key 
                                 ? 'text-blue-600 border-blue-600 active' 
                                 : 'border-transparent hover:text-gray-600 hover:border-gray-300' }}">
                        {{ $name }}
                    </a>
                </li>
                @endforeach
            </ul>
        </div>

        {{-- Header Konten Tab dengan Tombol Upload Kontekstual --}}
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold text-gray-700">Daftar Dokumen: {{ $categories[$activeCategory] }}</h2>
            <a href="{{ route('documents.create', ['package' => $package->id, 'category' => $activeCategory]) }}" class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700">
                + Unggah {{ $categories[$activeCategory] }} Baru
            </a>
			<a href="{{ route('documents.createSubmission', ['package' => $package->id]) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
				+ Ajukan Shop Drawing
			</a>
        </div>

        {{-- Konten Tab (Tabel Dokumen) --}}
        <div class="bg-white rounded shadow overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="text-left px-4 py-2">Nama Dokumen</th>
                        <th class="text-center px-4 py-2">Revisi</th>
                        <th class="text-left px-4 py-2">Status</th>
                        <th class="text-left px-4 py-2">Diajukan oleh</th>
                        <th class="text-left px-4 py-2">Tanggal</th>
                        <th class="text-left px-4 py-2">Aksi</th>
                    </tr>
                </thead>
                {{-- GANTI SELURUH BLOK <tbody> UNTUK SHOP DRAWING --}}
				<tbody>
					@forelse($documentsByCategory['Shop Drawing'] ?? [] as $document)
						<tr class="border-t">
							<td class="py-2 px-3 border">
								<a href="{{ asset('storage/' . $document->file_path) }}" target="_blank" class="text-blue-600 hover:underline font-semibold">{{ $document->title }}</a>
								<p class="text-xs text-gray-600 mt-1">{{ $document->description }}</p>
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
								<p class="font-medium">{{ $document->document_number }}</p>
								<p class="text-xs text-gray-500">{{ $document->created_at->isoFormat('D MMM YYYY') }}</p>
							</td>
							<td class="py-2 px-3 border text-center align-top">
								<span class="px-2 py-1 text-xs font-semibold rounded-full 
									@if($document->status == 'pending') bg-yellow-100 text-yellow-800
									@elseif($document->status == 'approved' || $document->status == 'disetujui') bg-green-100 text-green-800
									@elseif($document->status == 'rejected' || $document->status == 'ditolak') bg-red-100 text-red-800
									@else bg-gray-100 text-gray-800 @endif">
									{{ ucfirst($document->status) }}
								</span>
							</td>
							<td class="py-2 px-3 border text-center align-top">
								<div class="flex justify-center items-center space-x-2">
									<a href="{{ route('approvals.index') }}" class="text-green-600 hover:text-green-800" title="Proses Persetujuan">
										<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
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
					@empty
						<tr><td colspan="5" class="text-center py-4">Belum ada Shop Drawing yang diunggah.</td></tr>
					@endforelse
				</tbody>
			</table>
        </div>
    </main>
</div>
@endsection