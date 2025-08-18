@extends('layouts.app')

@section('title', 'Edit Data Perusahaan di Proyek')

@section('content')
<div class="p-4 sm:p-6">
    <header class="bg-white shadow p-4 rounded-lg mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Edit Data Perusahaan: {{ $company->name }}</h1>
        <p class="text-sm text-gray-500">Proyek: {{ $project->name }}</p>
    </header>

    {{-- TAMBAHKAN: enctype untuk upload file --}}
    <form action="{{ route('projects.companies.update', ['project' => $project->id, 'company' => $company->id]) }}" method="POST" enctype="multipart/form-data" class="bg-white shadow-md rounded-lg p-6">
        @csrf
        @method('PATCH')
        
        {{-- TAMBAHKAN: Bagian Upload Logo --}}
        <div class="flex items-center space-x-6 mb-6 pb-6 border-b">
            <img id="logo-preview" class="h-20 w-20 object-contain rounded-md bg-gray-100 p-1" src="{{ $company->logo_url }}" alt="{{ $company->name }}">
            <div>
                {{-- Diubah dari label menjadi span dan diberi margin bawah --}}
                <span class="block text-sm font-medium text-gray-700 mb-2">Logo Perusahaan</span>
                                <input type="file" name="logo" id="logo" class="hidden" onchange="previewLogo()">
				<p class="text-xs text-gray-500 mt-2 py-1">PNG, JPG (MAX. 2MB)</p>
                <label for="logo" class="cursor-pointer bg-white py-1 px-3 border border-gray-300 rounded-md shadow-sm text-sm leading-4 font-medium text-gray-700 hover:bg-gray-50">
                    Pilih File
                </label>
                
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Kolom Kiri: Detail Kontak Perusahaan --}}
            <div class="space-y-4">
                <div>
                    <h3 class="text-lg font-medium text-gray-900 border-b pb-2">Detail Kontak Perusahaan</h3>
                </div>
                <div>
                    <label for="address" class="block text-sm font-medium text-gray-700">Alamat</label>
                    <textarea name="address" id="address" rows="3" class="mt-1 w-full border rounded px-3 py-2">{{ old('address', $company->address) }}</textarea>
                </div>
                <div>
                    <label for="phone_number" class="block text-sm font-medium text-gray-700">Nomor Telepon</label>
                    <input type="text" name="phone_number" id="phone_number" value="{{ old('phone_number', $company->phone_number) }}" class="mt-1 w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email Perusahaan</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $company->email) }}" class="mt-1 w-full border rounded px-3 py-2">
                </div>
            </div>

            {{-- Kolom Kanan: Detail Kontrak di Proyek Ini --}}
            <div class="space-y-4">
                 <div>
                    <h3 class="text-lg font-medium text-gray-900 border-b pb-2">Detail di Proyek Ini</h3>
                </div>
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
                {{-- TAMBAHKAN: Form Tanggal Mulai dan Selesai Kontrak --}}
                <div>
                    <label for="start_date_contract" class="block text-sm font-medium text-gray-700">Tanggal Mulai Kontrak</label>
                    <input type="date" name="start_date_contract" id="start_date_contract" value="{{ old('start_date_contract', $pivotData->start_date_contract) }}" class="mt-1 w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label for="end_date_contract" class="block text-sm font-medium text-gray-700">Tanggal Selesai Kontrak</label>
                    <input type="date" name="end_date_contract" id="end_date_contract" value="{{ old('end_date_contract', $pivotData->end_date_contract) }}" class="mt-1 w-full border rounded px-3 py-2">
                </div>
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

{{-- TAMBAHKAN: Script untuk pratinjau logo --}}
@push('scripts')
<script>
    function previewLogo() {
        const file = document.getElementById('logo').files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('logo-preview').src = e.target.result;
            }
            reader.readAsDataURL(file);
        }
    }
</script>
@endpush
@endsection