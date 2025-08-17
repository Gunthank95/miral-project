@extends('layouts.app')

@section('title', 'Data Utama Proyek - ' . $project->name)

@section('content')
<div class="p-4 sm:p-6">
    <header class="bg-white shadow p-4 rounded-lg mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Data Utama Proyek</h1>
            <p class="text-sm text-gray-500">{{ $project->name }}</p>
        </div>
        {{-- GANTI: Tombol Edit akan muncul di sini JIKA diizinkan oleh Policy --}}
        @can('update', $project)
            <a href="{{ route('projects.edit-data', $project->id) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded transition duration-300">
                Edit Data
            </a>
        @endcan
    </header>

    <div class="space-y-6">
        {{-- BAGIAN 1: INFORMASI UMUM PROYEK --}}
        <div class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-lg font-semibold border-b pb-2 mb-4">1. Informasi Umum</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div>
                    <strong class="block text-gray-500">Nama Proyek:</strong>
                    <span class="text-gray-800">{{ $project->name }}</span>
                </div>
                <div>
                    <strong class="block text-gray-500">Lokasi:</strong>
                    <span class="text-gray-800">{{ $project->location }}</span>
                </div>
                <div>
                    <strong class="block text-gray-500">Nilai Proyek (dari RAB):</strong>
                    <span class="text-gray-800">Rp {{ number_format($projectValue, 2, ',', '.') }}</span>
                </div>
                <div>
                    <strong class="block text-gray-500">Tanggal Mulai:</strong>
                    <span class="text-gray-800">{{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->isoFormat('D MMMM YYYY') : '-' }}</span>
                </div>
                <div>
                    <strong class="block text-gray-500">Rencana Selesai:</strong>
                    <span class="text-gray-800">{{ $project->end_date ? \Carbon\Carbon::parse($project->end_date)->isoFormat('D MMMM YYYY') : '-' }}</span>
                </div>
                 <div>
                    <strong class="block text-gray-500">Paket Pekerjaan:</strong>
                    <span class="text-gray-800">{{ $project->packages->count() }} Paket</span>
                </div>
                <div>
                    <strong class="block text-gray-500">Luas Lahan:</strong>
                    <span class="text-gray-800">{{ $project->land_area ? number_format($project->land_area, 2, ',', '.') . ' m²' : '-' }}</span>
                </div>
                <div>
                    <strong class="block text-gray-500">Luas Bangunan:</strong>
                    <span class="text-gray-800">{{ $project->building_area ? number_format($project->building_area, 2, ',', '.') . ' m²' : '-' }}</span>
                </div>
                <div>
                    <strong class="block text-gray-500">Jumlah Lantai:</strong>
                    <span class="text-gray-800">{{ $project->floor_count ? $project->floor_count . ' Lantai' : '-' }}</span>
                </div>
            </div>
        </div>

        {{-- BAGIAN 2: INSTANSI YANG TERLIBAT --}}
        <div class="bg-white shadow-md rounded-lg p-6">
            <div class="flex justify-between items-center border-b pb-2 mb-4">
                <h2 class="text-lg font-semibold">2. Instansi & Personil Terlibat</h2>
                @can('update', $project)
                    <a href="{{ route('projects.companies.create', $project->id) }}" class="bg-green-500 hover:bg-green-600 text-white text-sm font-bold py-1 px-3 rounded transition duration-300">
                        + Tambah Perusahaan
                    </a>
                @endcan
            </div>
            <div class="space-y-4">
                @php
                function renderCompanyDetails($project, $company) {
                    // Menampilkan detail kontrak
                    echo '<div class="grid grid-cols-1 md:grid-cols-3 gap-x-4 gap-y-2 text-sm mb-3 mt-2 p-3 bg-gray-50 rounded">';
                        echo '<div><strong class="block text-gray-500 text-xs">No. Kontrak:</strong><span>' . ($company->pivot->contract_number ?? '-') . '</span></div>';
                        echo '<div><strong class="block text-gray-500 text-xs">Nilai Kontrak:</strong><span>Rp ' . number_format($company->pivot->contract_value ?? 0, 0, ',', '.') . '</span></div>';
                        echo '<div><strong class="block text-gray-500 text-xs">Tgl. Kontrak:</strong><span>' . ($company->pivot->contract_date ? \Carbon\Carbon::parse($company->pivot->contract_date)->isoFormat('D MMM YYYY') : '-') . '</span></div>';
                    echo '</div>';

                    // Header untuk tabel personil, termasuk tombol "Tambah Personil"
                    echo '<div class="flex justify-between items-center mt-4 mb-2">';
                        echo '<h4 class="text-sm font-semibold text-gray-600">Personil:</h4>';
                        // Tombol "Tambah Personil" hanya muncul untuk anggota perusahaan itu sendiri
                        if (auth()->user()->can('updateCompanyDetails', [$project, $company])) {
                            echo '<a href="' . route('personnel.create', ['project' => $project->id, 'company' => $company->id]) . '" class="bg-blue-500 hover:bg-blue-600 text-white text-xs font-bold py-1 px-2 rounded transition duration-300">+ Tambah Personil</a>';
                        }
                    echo '</div>';

                    // Tabel Personil
                    if ($company->personnel && $company->personnel->count() > 0) {
                        echo '<div class="overflow-x-auto"><table class="min-w-full text-xs">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-2 py-1 text-left">Nama</th>
                                        <th class="px-2 py-1 text-left">Jabatan</th>
                                        <th class="px-2 py-1 text-left">No. Telp</th>
                                        <th class="px-2 py-1 text-left">Email</th>';
                        // Kolom Aksi hanya muncul untuk anggota perusahaan itu sendiri
                        if (auth()->user()->can('updateCompanyDetails', [$project, $company])) {
                           echo '<th class="px-2 py-1 text-left">Aksi</th>';
                        }
                        echo '          </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">';
                        foreach ($company->personnel as $person) {
                            echo '<tr>';
                            echo '<td class="px-2 py-1">' . ($person->name ?? '-') . '</td>';
                            echo '<td class="px-2 py-1">' . ($person->position ?? '-') . '</td>';
                            echo '<td class="px-2 py-1">' . ($person->phone_number ?? '-') . '</td>';
                            echo '<td class="px-2 py-1">' . ($person->email ?? '-') . '</td>';
                            // Tombol Aksi hanya muncul untuk anggota perusahaan itu sendiri
                            if (auth()->user()->can('updateCompanyDetails', [$project, $company])) {
                                echo '<td class="px-2 py-1 whitespace-nowrap">
                                        <a href="' . route('personnel.edit', ['project' => $project->id, 'company' => $company->id, 'personnel' => $person->id]) . '" class="text-yellow-600 hover:underline mr-2">Edit</a>
                                        <form action="' . route('personnel.destroy', ['project' => $project->id, 'company' => $company->id, 'personnel' => $person->id]) . '" method="POST" class="inline" onsubmit="return confirm(\'Anda yakin ingin menghapus personil ini?\');">
                                            ' . csrf_field() . '
                                            ' . method_field('DELETE') . '
                                            <button type="submit" class="text-red-600 hover:underline">Hapus</button>
                                        </form>
                                      </td>';
                            }
                            echo '</tr>';
                        }
                        echo '</tbody></table></div>';
                    } else {
                        echo '<p class="text-xs text-gray-400 mt-2">Belum ada data personil untuk perusahaan ini.</p>';
                    }
                }
                @endphp

                @forelse($project->companies as $company)
                    @can('viewCompanyDetails', [$project, $company])
                        <div class="border-l-4 {{ $company->pivot->role_in_project == 'Owner' ? 'border-blue-500' : 'border-gray-400' }} pl-4 py-2">
                            <div class="flex justify-between items-start">
                                <h3 class="font-bold text-md text-gray-800">{{ $company->pivot->role_in_project }}: {{ $company->name }}</h3>
                                @can('updateCompanyDetails', [$project, $company])
                                <a href="{{ route('projects.companies.edit', ['project' => $project->id, 'company' => $company->id]) }}" class="text-xs text-yellow-600 hover:underline">Edit Detail Perusahaan</a>
                                @endcan
                            </div>
                            @php renderCompanyDetails($project, $company); @endphp
                        </div>
                    @endcan
                @empty
                    <p class="text-sm text-gray-500">Belum ada perusahaan yang ditambahkan ke proyek ini.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection