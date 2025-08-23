@php
    $padding = $level * 20;
    $isTitle = is_null($item->volume); 
    $hasChildren = !empty($item->children) && $item->children->isNotEmpty();

    // Mengambil nilai progres langsung dari item, karena sudah diakumulasi di controller
    $progLalu = $item->previous_progress_weight ?? 0;
    $progPeriodeIni = $item->progress_weight_period ?? 0;
    $progTotal = $progLalu + $progPeriodeIni;

    // Mengambil nilai volume (hanya untuk item pekerjaan, bukan judul)
    $volLalu = $item->previous_progress_volume ?? 0;
    $volPeriodeIni = $item->progress_volume_period ?? 0;
    $volTotal = $volLalu + $volPeriodeIni;
@endphp

<tr class="border-t {{ $isTitle ? 'bg-gray-100 font-semibold' : 'bg-white' }}">
    {{-- Uraian Pekerjaan --}}
    <td class="text-left px-4 py-2 border" style="padding-left: {{ 16 + $padding }}px;">
        <span class="font-semibold">{{ $item->item_number }}</span> {{ $item->item_name }}
    </td>
    {{-- Satuan --}}
    <td class="text-center px-4 py-2 border">{{ $item->unit }}</td>
    {{-- Volume Kontrak --}}
    <td class="text-center px-4 py-2 border">{{ $isTitle ? '-' : number_format($item->volume, 2) }}</td>
    {{-- Bobot Kontrak --}}
    <td class="text-center px-4 py-2 border">{{ $item->weighting ? number_format($item->weighting, 2) . '%' : '-' }}</td>
    
    {{-- Kolom Volume --}}
    <td class="text-center px-2 py-1 border progress-details">{{ $isTitle ? '-' : number_format($volLalu, 2) }}</td>
    <td class="text-center px-2 py-1 border progress-details">{{ $isTitle ? '-' : number_format($volPeriodeIni, 2) }}</td>
    <td class="text-center px-2 py-1 border progress-details">{{ $isTitle ? '-' : number_format($volTotal, 2) }}</td>

    {{-- Kolom Bobot --}}
    <td class="text-center px-2 py-1 border progress-details">{{ number_format($progLalu, 2) }}%</td>
    <td class="text-center px-2 py-1 border progress-details">{{ number_format($progPeriodeIni, 2) }}%</td>
    <td class="text-center px-2 py-1 border progress-details font-bold">{{ number_format($progTotal, 2) }}%</td>
</tr>

@if ($hasChildren)
    @foreach ($item->children as $child)
        @include('daily_reports.partials._activity-row', ['item' => $child, 'level' => $level + 1])
    @endforeach
@endif