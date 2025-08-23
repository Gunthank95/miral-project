@extends('layouts.print')

@section('title', 'Cetak Laporan Periodik')

@section('content')
<div class="container mx-auto">
    <h1 class="text-2xl font-bold text-center mb-2">Laporan Progres Periodik</h1>
    <p class="text-center text-sm mb-1">Paket Pekerjaan: {{ $package->name }}</p>
    <p class="text-center text-sm mb-6">Periode: {{ \Carbon\Carbon::parse($startDate)->isoFormat('D MMMM YYYY') }} s/d {{ \Carbon\Carbon::parse($endDate)->isoFormat('D MMMM YYYY') }}</p>

    <table class="min-w-full text-xs" id="rab-table">
        <thead class="bg-gray-100">
            <tr>
                <th rowspan="2" class="text-left px-4 py-2 border">Uraian Pekerjaan</th>
                <th rowspan="2" class="px-2 py-1 border">Sat</th>
                <th rowspan="2" class="px-2 py-1 border">Volume Kontrak</th>
                <th rowspan="2" class="px-2 py-1 border">Bobot Kontrak (%)</th>
                <th colspan="3" class="px-2 py-1 border">Volume</th>
                <th colspan="3" class="px-2 py-1 border">Bobot (%)</th>
            </tr>
            <tr>
                <th class="px-2 py-1 border font-normal">s/d Periode Lalu</th>
                <th class="px-2 py-1 border font-normal">Periode Ini</th>
                <th class="px-2 py-1 border font-normal">s/d Periode Ini</th>
                <th class="px-2 py-1 border font-normal">s/d Periode Lalu</th>
                <th class="px-2 py-1 border font-normal">Periode Ini</th>
                <th class="px-2 py-1 border font-normal">s/d Periode Ini</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rabTree as $item)
                {{-- Panggil partial yang sama, tapi tanpa fungsi expand/collapse --}}
                @include('periodic_reports.partials._print-item-row', ['item' => $item, 'level' => 0])
            @empty
                <tr><td colspan="10" class="text-center p-4">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
        @if($rabTree->isNotEmpty())
        <tfoot class="bg-gray-200 font-bold">
            <tr>
                <td colspan="7" class="text-right px-4 py-2 border">TOTAL</td>
                <td class="text-center px-2 py-1 border">{{ number_format($rabTree->sum('bobot_lalu'), 4) }}%</td>
                <td class="text-center px-2 py-1 border">{{ number_format($rabTree->sum('bobot_periode_ini'), 4) }}%</td>
                <td class="text-center px-2 py-1 border">{{ number_format($rabTree->sum('bobot_lalu') + $rabTree->sum('bobot_periode_ini'), 4) }}%</td>
            </tr>
        </tfoot>
        @endif
    </table>
</div>
@endsection