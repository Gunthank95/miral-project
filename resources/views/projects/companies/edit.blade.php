@extends('layouts.app')

@section('title', 'Edit Data Perusahaan di Proyek')

@section('content')
<div class="p-4 sm:p-6">
    <header class="bg-white shadow p-4 rounded-lg mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Edit Data Perusahaan: {{ $company->name }}</h1>
        <p class="text-sm text-gray-500">Proyek: {{ $project->name }}</p>
    </header>

    <form action="{{ route('projects.companies.update', ['project' => $project->id, 'company' => $company->id]) }}" method="POST" class="bg-white shadow-md rounded-lg p-6">
        @csrf
        @method('PATCH')
        <div class="space-y-4">
            <div>
                <label for="role_in_project" class="block text-sm font-medium text-gray-700">Peran di Proyek</label>
                <input type="text" name="role_in_project" id="role_in_project" value="{{ old('role_in_project', $pivotData->role_in_project) }}" required class="mt-1 w-full border rounded px-3 py-2">
            </div>
            <div>
                <label for="contract_number" class="block text-sm font-medium text-gray-700">Nomor Kontrak</label>
                <input type="text" name="contract_number" id="contract_number" value="{{ old('contract_number', $pivotData->contract_number) }}" class="mt-1 w-full border rounded px-3 py-2">
            </div>
            <div>
                <label for="contract_value" class="block text-sm font-medium text-gray-700">Nilai Kontrak</label>
                <input type="number" step="0.01" name="contract_value" id="contract_value" value="{{ old('contract_value', $pivotData->contract_value) }}" class="mt-1 w-full border rounded px-3 py-2">
            </div>
            <div>
                <label for="contract_date" class="block text-sm font-medium text-gray-700">Tanggal Kontrak</label>
                <input type="date" name="contract_date" id="contract_date" value="{{ old('contract_date', $pivotData->contract_date) }}" class="mt-1 w-full border rounded px-3 py-2">
            </div>
        </div>
        <div class="mt-6 pt-6 border-t flex justify-end items-center space-x-4">
            <a href="{{ route('projects.data-proyek', $project->id) }}" class="text-sm text-gray-600 hover:underline">Batal</a>
            <button type="submit" class="bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection