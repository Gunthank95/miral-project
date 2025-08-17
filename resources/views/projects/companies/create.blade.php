@extends('layouts.app')

@section('title', 'Tambah Perusahaan ke Proyek')

@section('content')
<div class="p-4 sm:p-6">
    <header class="bg-white shadow p-4 rounded-lg mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Tambah Perusahaan Terlibat</h1>
        <p class="text-sm text-gray-500">Proyek: {{ $project->name }}</p>
    </header>

    <form action="{{ route('projects.companies.store', $project->id) }}" method="POST" class="bg-white shadow-md rounded-lg p-6">
        @csrf
        <div class="space-y-4">
            <div>
                <label for="company_id" class="block text-sm font-medium text-gray-700">Pilih Perusahaan</label>
                <select name="company_id" id="company_id" required class="mt-1 w-full border rounded px-3 py-2">
                    <option value="">Pilih dari perusahaan yang ada...</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}">{{ $company->name }} (Tipe: {{ $company->type }})</option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-500 mt-1">Jika perusahaan tidak ada, daftarkan dulu melalui menu lain.</p>
            </div>
            <div>
                <label for="role_in_project" class="block text-sm font-medium text-gray-700">Peran di Proyek</label>
                <input type="text" name="role_in_project" id="role_in_project" value="{{ old('role_in_project') }}" required class="mt-1 w-full border rounded px-3 py-2" placeholder="Contoh: Kontraktor Paket A, Perencana Struktur">
            </div>
            <div>
                <label for="contract_number" class="block text-sm font-medium text-gray-700">Nomor Kontrak</label>
                <input type="text" name="contract_number" id="contract_number" value="{{ old('contract_number') }}" class="mt-1 w-full border rounded px-3 py-2">
            </div>
            <div>
                <label for="contract_value" class="block text-sm font-medium text-gray-700">Nilai Kontrak</label>
                <input type="number" step="0.01" name="contract_value" id="contract_value" value="{{ old('contract_value') }}" class="mt-1 w-full border rounded px-3 py-2">
            </div>
            <div>
                <label for="contract_date" class="block text-sm font-medium text-gray-700">Tanggal Kontrak</label>
                <input type="date" name="contract_date" id="contract_date" value="{{ old('contract_date') }}" class="mt-1 w-full border rounded px-3 py-2">
            </div>
        </div>
        <div class="mt-6 pt-6 border-t flex justify-end items-center space-x-4">
            <a href="{{ route('projects.data-proyek', $project->id) }}" class="text-sm text-gray-600 hover:underline">Batal</a>
            <button type="submit" class="bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700">
                Tambahkan Perusahaan
            </button>
        </div>
    </form>
</div>
@endsection