@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8" x-data="{ openModal: false, actionUrl: '', documentTitle: '' }">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Persetujuan Dokumen</h2>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
        <h1 class="text-2xl font-bold mb-6">Persetujuan Dokumen</h1>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="py-2 px-3 border text-left">Judul Dokumen</th>
                        <th class="py-2 px-3 border text-left">Kategori</th>
                        <th class="py-2 px-3 border text-left">Pengunggah</th>
                        <th class="py-2 px-3 border text-center">Tanggal Unggah</th>
                        <th class="py-2 px-3 border text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendingDocuments as $document)
                        <tr class="border-t">
                            <td class="py-2 px-3 border">
                                <a href="{{ asset('storage/' . $document->file_path) }}" target="_blank" class="text-blue-600 hover:underline font-semibold">{{ $document->title }}</a>
                                <p class="text-gray-500 text-xs">{{ $document->description }}</p>
                            </td>
                            <td class="py-2 px-3 border">{{ $document->category }}</td>
                            <td class="py-2 px-3 border">{{ $document->user->name ?? 'N/A' }}</td>
                            <td class="py-2 px-3 border text-center">{{ $document->created_at->isoFormat('D MMM YYYY, HH:mm') }}</td>
                            <td class="py-2 px-3 border text-center">
                                {{-- Tombol "Periksa" untuk membuka modal --}}
                                <button @click="openModal = true; actionUrl = '{{ route('approvals.storeReview', $document->id) }}'; documentTitle = '{{ $document->title }}'" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-3 rounded text-xs">
                                    Periksa
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center py-4 border">Tidak ada dokumen yang memerlukan persetujuan saat ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div x-show="openModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" style="display: none;">
        <div @click.away="openModal = false" class="bg-white rounded-lg shadow-xl p-6 w-full max-w-lg mx-4">
            <h2 class="text-xl font-bold mb-4">Pemeriksaan Dokumen</h2>
            <p class="text-sm mb-4">Anda sedang memeriksa: <strong x-text="documentTitle"></strong></p>
            
            <form :action="actionUrl" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="space-y-4">
                    {{-- Status Pemeriksaan --}}
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">Status Hasil Pemeriksaan</label>
                        <select id="status" name="status" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" required>
                            <option value="Revisi Diperlukan">Revisi Diperlukan</option>
                            <option value="Disetujui">Disetujui</option>
                            <option value="Ditolak">Ditolak</option>
                        </select>
                    </div>

                    {{-- Catatan Teks --}}
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700">Catatan (Opsional)</label>
                        <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"></textarea>
                    </div>

                    {{-- Upload File Hasil Review --}}
                    <div>
                        <label for="reviewed_file" class="block text-sm font-medium text-gray-700">Unggah File Hasil Pemeriksaan (PDF/Gambar)</label>
                        <input type="file" id="reviewed_file" name="reviewed_file" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" required>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" @click="openModal = false" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">Batal</button>
                    <button type="submit" class="bg-blue-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-blue-700">Simpan Hasil Pemeriksaan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection