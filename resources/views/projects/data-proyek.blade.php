@extends('layouts.app')

@section('title', 'Data Utama Proyek - ' . $project->name)

@section('content')
<div class="p-4 sm:p-6">
    <header class="bg-white shadow p-4 rounded-lg mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Data Utama Proyek</h1>
        <p class="text-sm text-gray-500">{{ $project->name }}</p>
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
            <h2 class="text-lg font-semibold border-b pb-2 mb-4">2. Instansi & Personil Terlibat</h2>
            <div class="space-y-4">
                {{-- Helper untuk menampilkan detail perusahaan dan personil --}}
                @php
                function renderCompanyDetails($company) {
                    echo '<div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm mb-3">';
                    // Tampilkan detail perusahaan jika ada
                    echo '</div>';

                    // Tabel Personil
                    if ($company->personnel && $company->personnel->count() > 0) {
                        echo '<h4 class="text-sm font-semibold text-gray-600 mt-3 mb-2">Personil:</h4>';
                        echo '<div class="overflow-x-auto"><table class="min-w-full text-xs">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-2 py-1 text-left">Nama</th>
                                        <th class="px-2 py-1 text-left">Jabatan</th>
                                        <th class="px-2 py-1 text-left">No. Telp</th>
                                        <th class="px-2 py-1 text-left">Email</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">';
                        foreach ($company->personnel as $person) {
                            echo '<tr>';
                            echo '<td class="px-2 py-1">' . ($person->name ?? '-') . '</td>';
                            echo '<td class="px-2 py-1">' . ($person->position ?? '-') . '</td>';
                            echo '<td class="px-2 py-1">' . ($person->phone_number ?? '-') . '</td>';
                            echo '<td class="px-2 py-1">' . ($person->email ?? '-') . '</td>';
                            echo '</tr>';
                        }
                        echo '</tbody></table></div>';
                    } else {
                        echo '<p class="text-xs text-gray-400 mt-2">Belum ada data personil untuk perusahaan ini.</p>';
                    }
                }
                @endphp

                @if(isset($companiesByRole['Owner']))
                    @foreach($companiesByRole['Owner'] as $owner)
                        <div class="border-l-4 border-blue-500 pl-4 py-2">
                            <h3 class="font-bold text-md text-gray-800">Pemilik (Owner): {{ $owner->name }}</h3>
                            @php renderCompanyDetails($owner); @endphp
                        </div>
                    @endforeach
                @endif
                
                @foreach($project->companies as $company)
                    @if($company->pivot->role_in_project != 'Owner')
                        <div class="border-l-4 border-gray-400 pl-4 py-2">
                            <h3 class="font-bold text-md text-gray-800">{{ $company->pivot->role_in_project }}: {{ $company->name }}</h3>
                            @php renderCompanyDetails($company); @endphp
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection