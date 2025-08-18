@extends('layouts.app')

@section('title', 'Daftar Proyek Baru')

@section('content')
<div class="p-4 sm:p-6">
    <header class="bg-white shadow p-4 rounded-lg mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Pendaftaran Proyek Baru</h1>
        <p class="text-sm text-gray-500">Isi detail proyek, paket pekerjaan, dan perusahaan yang terlibat.</p>
    </header>

    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Oops! Terjadi kesalahan.</strong>
            <ul class="mt-2 list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <form action="{{ route('projects.store') }}" method="POST" class="bg-white rounded shadow p-6 space-y-6">
        @csrf
        {{-- Bagian 1: Detail Proyek --}}
        <div>
            <h2 class="text-lg font-semibold border-b pb-2 mb-4">1. Informasi Proyek</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="project_name" class="block text-sm font-medium text-gray-700">Nama Proyek</label>
                    <input type="text" name="project_name" id="project_name" value="{{ old('project_name', $prefilledProjectName ?? '') }}" required class="mt-1 w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label for="owner_company" class="block text-sm font-medium text-gray-700">Nama Perusahaan Owner</label>
                    {{-- GANTI: Logika pengisian otomatis --}}
                    @if(isset($userCompany) && $userCompany->type == 'owner')
                        <input type="text" name="owner_company" id="owner_company" value="{{ $userCompany->name }}" readonly class="mt-1 w-full border rounded px-3 py-2 bg-gray-100">
                    @else
                        <input type="text" name="owner_company" id="owner_company" value="{{ old('owner_company') }}" required class="mt-1 w-full border rounded px-3 py-2">
                    @endif
                </div>
                <div class="md:col-span-2">
                    <label for="project_location" class="block text-sm font-medium text-gray-700">Lokasi Proyek</label>
                    <input type="text" name="project_location" id="project_location" value="{{ old('project_location') }}" required class="mt-1 w-full border rounded px-3 py-2">
                </div>
            </div>
        </div>

        {{-- Bagian 2: Paket Pekerjaan (Dinamis) --}}
        <div>
            <h2 class="text-lg font-semibold border-b pb-2 mb-4">2. Paket Pekerjaan & Perusahaan Terlibat</h2>
            <div id="packages-container" class="space-y-4">
                @if (old('packages'))
                    @foreach (old('packages') as $key => $package)
                        <div class="p-4 border rounded-lg space-y-3 package-row">
                            <div class="flex justify-between items-center">
                                <h3 class="font-semibold text-md">Paket</h3>
                                <button type="button" class="remove-package-btn text-red-500 text-xs hover:underline">Hapus Paket Ini</button>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600">Nama Paket</label>
                                <input type="text" name="packages[{{ $key }}][name]" value="{{ $package['name'] }}" required class="mt-1 w-full border rounded px-3 py-2 text-sm">
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600">Perusahaan MK/Pengawas</label>
                                    <input type="text" name="packages[{{ $key }}][mk_company]" value="{{ $package['mk_company'] }}" required class="mt-1 w-full border rounded px-3 py-2 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600">Perusahaan Kontraktor</label>
                                    <input type="text" name="packages[{{ $key }}][contractor_company]" value="{{ $package['contractor_company'] }}" required class="mt-1 w-full border rounded px-3 py-2 text-sm">
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
            <button type="button" id="add-package-btn" class="mt-4 text-sm text-blue-600 hover:underline">+ Tambah Paket Pekerjaan</button>
        </div>

        <div class="pt-6 border-t">
            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">
                Daftarkan Proyek
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const packagesContainer = document.getElementById('packages-container');
    const addPackageBtn = document.getElementById('add-package-btn');
    let packageCounter = {{ count(old('packages', [])) }};

    // GANTI: logika pengisian otomatis untuk kontraktor
    const userCompany = @json($userCompany ?? null);
    
    function addPackageRow() {
        packageCounter++;
        const row = document.createElement('div');
        row.classList.add('p-4', 'border', 'rounded-lg', 'space-y-3', 'package-row');
        row.innerHTML = `
            <div class="flex justify-between items-center">
                <h3 class="font-semibold text-md">Paket</h3>
                <button type="button" class="remove-package-btn text-red-500 text-xs hover:underline">Hapus Paket Ini</button>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600">Nama Paket</label>
                <input type="text" name="packages[${packageCounter}][name]" required class="mt-1 w-full border rounded px-3 py-2 text-sm" placeholder="Contoh: Pekerjaan Struktur">
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600">Perusahaan MK/Pengawas</label>
                    <input type="text" name="packages[${packageCounter}][mk_company]" required class="mt-1 w-full border rounded px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600">Perusahaan Kontraktor</label>
                    <input type="text" name="packages[${packageCounter}][contractor_company]" required class="mt-1 w-full border rounded px-3 py-2 text-sm">
                </div>
            </div>
        `;
        packagesContainer.appendChild(row);

        const contractorInput = row.querySelector('input[name*="[contractor_company]"]');

        if (userCompany && userCompany.type === 'contractor') {
            contractorInput.value = userCompany.name;
            contractorInput.readOnly = true;
            contractorInput.classList.add('bg-gray-100');
        }
    }
    
    addPackageBtn.addEventListener('click', addPackageRow);
    
    packagesContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-package-btn')) {
            e.target.closest('.package-row').remove();
        }
    });

    if (packageCounter === 0) {
        addPackageRow();
    }
});
</script>
@endpush