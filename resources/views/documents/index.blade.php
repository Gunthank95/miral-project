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
                <tbody>
                    @forelse ($documents as $document)
                        <tr class="border-t">
                            <td class="px-4 py-2 font-semibold">{{ $document->name }}</td>
                            <td class="text-center px-4 py-2">{{ $document->revision }}</td>
                            <td class="px-4 py-2">
                                @if ($document->status == 'pending')
                                    <span class="bg-yellow-200 text-yellow-800 text-xs font-semibold px-2.5 py-0.5 rounded">Pending</span>
                                @elseif ($document->status == 'approved')
                                    <span class="bg-green-200 text-green-800 text-xs font-semibold px-2.5 py-0.5 rounded">Approved</span>
                                @else
                                    <span class="bg-red-200 text-red-800 text-xs font-semibold px-2.5 py-0.5 rounded">Rejected</span>
                                @endif
                            </td>
                            <td class="px-4 py-2">{{ $document->user->name ?? 'N/A' }}</td>
                            <td class="px-4 py-2">{{ $document->created_at->isoFormat('D MMM YYYY') }}</td>
                            <td class="px-4 py-2">
                                <a href="#" class="text-blue-600 hover:underline text-xs">Lihat Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center p-4 text-gray-500">
                                Belum ada dokumen untuk kategori ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </main>
</div>
@endsection