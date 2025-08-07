@extends('layouts.app')

@section('title', 'Detail Proyek: ' . $project->name)

@section('content')
<div class="p-4 sm:p-6">
    <header class="bg-white shadow p-4 rounded-lg mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ $project->name }}</h1>
            <p class="text-sm text-gray-500">{{ $project->location }}</p>
        </div>
    </header>

    <main>
        <h2 class="text-xl font-semibold mb-4 text-gray-700">Daftar Paket Pekerjaan</h2>
        <div class="bg-white rounded shadow overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="text-left px-4 py-2 w-8/12">Nama Paket</th>
                        <th class="text-left px-4 py-2 w-4/12">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($packages as $package)
                        <tr class="border-t">
                            <td class="px-4 py-2">{{ $package->name }}</td>
                            <td class="px-4 py-2">
                                <div class="flex space-x-4">
                                    <a href="{{ route('rab.index', $package->id) }}" class="text-blue-600 hover:underline">
                                        Kelola RAB
                                    </a>
                                    {{-- PERBAIKAN: Link ini sekarang mengarah ke daftar laporan --}}
                                    <a href="{{ route('daily_reports.index', $package->id) }}" class="text-green-600 hover:underline">
                                        Laporan Harian
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="text-center px-4 py-4 text-gray-500">
                                Belum ada paket pekerjaan untuk proyek ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </main>
</div>
@endsection