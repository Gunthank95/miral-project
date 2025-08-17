@extends('layouts.print')

@section('title', 'Laporan Periodik - ' . $package->project->name)

@section('content')
<div class="text-center mb-6">
    <h1 class="text-2xl font-bold">LAPORAN PERIODIK</h1>
    <h2 class="text-xl font-semibold">{{ $package->project->name }}</h2>
    <p class="text-sm">Paket: {{ $package->name }}</p>
    <p class="text-sm">Periode: {{ \Carbon\Carbon::parse($startDate)->isoFormat('D MMM YYYY') }} s/d {{ \Carbon\Carbon::parse($endDate)->isoFormat('D MMM YYYY') }}</p>
</div>

@if ($groupedActivities->isNotEmpty())
    <div class="bg-white rounded shadow p-4">
        <h2 class="text-lg font-semibold mb-2 border-b pb-2">Rangkuman Progres Pekerjaan</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full text-xs">
                <thead class="bg-gray-100">
                    <tr>
                        <th rowspan="2" class="text-left px-2 py-1 border">Uraian Pekerjaan</th>
                        <th rowspan="2" class="text-center px-2 py-1 border">Satuan</th>
                        <th rowspan="2" class="text-center px-2 py-1 border">Volume Kontrak</th>
                        <th rowspan="2" class="text-center px-2 py-1 border">Bobot Kontrak (%)</th>
                        <th colspan="3" class="text-center px-2 py-1 border">Volume</th>
                        <th colspan="3" class="text-center px-2 py-1 border">Bobot (%)</th>
                    </tr>
                    <tr>
                        <th class="text-center px-1 py-1 border font-normal">Lalu</th>
                        <th class="text-center px-1 py-1 border font-normal">Periode Ini</th>
                        <th class="text-center px-1 py-1 border font-normal">S.d Saat Ini</th>
                        <th class="text-center px-1 py-1 border font-normal">Lalu</th>
                        <th class="text-center px-1 py-1 border font-normal">Periode Ini</th>
                        <th class="text-center px-1 py-1 border font-normal">S.d Saat Ini</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($groupedActivities as $rab_item_id => $activities)
                        @php
                            $rabItem = $activities->first()->rabItem;
                            $volLalu = $activities->previous_progress_volume;
                            $volPeriodeIni = $activities->sum('progress_volume');
                            $volTotal = $volLalu + $volPeriodeIni;

                            $progLalu = ($rabItem && $rabItem->volume > 0) ? ($volLalu / $rabItem->volume) * $rabItem->weighting : 0;
                            $progPeriodeIni = ($rabItem && $rabItem->volume > 0) ? ($volPeriodeIni / $rabItem->volume) * $rabItem->weighting : 0;
                            $progTotal = $progLalu + $progPeriodeIni;
                        @endphp
                        <tr class="border-t">
                            <td class="px-2 py-1 border">{{ $rabItem->item_name ?? 'Pekerjaan Kustom' }}</td>
                            <td class="text-center px-2 py-1 border">{{ $rabItem->unit ?? '-' }}</td>
                            <td class="text-center px-2 py-1 border">{{ $rabItem ? number_format($rabItem->volume, 2) : '-' }}</td>
                            <td class="text-center px-2 py-1 border">{{ $rabItem ? number_format($rabItem->weighting, 2) . '%' : '-' }}</td>
                            <td class="text-center px-2 py-1 border">{{ number_format($volLalu, 2) }}</td>
                            <td class="text-center px-2 py-1 border">{{ number_format($volPeriodeIni, 2) }}</td>
                            <td class="text-center px-2 py-1 border">{{ number_format($volTotal, 2) }}</td>
                            <td class="text-center px-2 py-1 border">{{ number_format($progLalu, 2) }}%</td>
                            <td class="text-center px-2 py-1 border">{{ number_format($progPeriodeIni, 2) }}%</td>
                            <td class="text-center px-2 py-1 border">{{ number_format($progTotal, 2) }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@else
    <p class="text-center">Tidak ada data untuk periode yang dipilih.</p>
@endif

<script>
    window.onload = function() {
        window.print();
    }
</script>
@endsection