@php
    $padding = $level * 20;
    $isTitle = is_null($item->volume); 
    $hasChildren = isset($item->children) && $item->children->isNotEmpty();

    // Mengambil nilai progres langsung dari item
    $progLalu = $item->previous_progress_weight ?? 0;
    $progPeriodeIni = $item->progress_weight_period ?? 0;
    $progTotal = $progLalu + $progPeriodeIni;

    // Mengambil nilai volume
    $volLalu = $item->previous_progress_volume ?? 0;
    $volPeriodeIni = $item->progress_volume_period ?? 0;
    $volTotal = $volLalu + $volPeriodeIni;

    $itemProgress = $item->item_progress ?? 0;
@endphp

<tr class="border-t {{ $isTitle ? 'bg-gray-100 font-semibold' : 'bg-white' }}">
    {{-- Uraian Pekerjaan --}}
    <td class="text-left px-4 py-2 border" style="padding-left: {{ 16 + $padding }}px;">
        <span class="font-semibold">{{ $item->item_number }}</span> {{ $item->item_name }}
    </td>
    {{-- Satuan --}}
    <td class="text-center px-4 py-2 border">{{ $item->unit }}</td>
    {{-- Volume Kontrak (Tambahkan class contract-column) --}}
    <td class="text-right px-4 py-2 border contract-column">{{ $isTitle ? '' : number_format($item->volume, 2) }}</td>
    {{-- Bobot Kontrak (Tambahkan class contract-column) --}}
    <td class="text-right px-4 py-2 border contract-column">{{ $item->weighting ? number_format($item->weighting, 2) . '%' : '' }}</td>
    
    {{-- Volume Lalu (Tambahkan class detail-column) --}}
    <td class="text-right px-2 py-1 border detail-column">{{ $isTitle ? '' : number_format($volLalu, 2) }}</td>
    {{-- Volume Periode Ini (Tambahkan class detail-column) --}}
    <td class="text-right px-2 py-1 border detail-column">{{ $isTitle ? '' : number_format($volPeriodeIni, 2) }}</td>
    {{-- Volume S.d Saat Ini --}}
    <td class="text-right px-2 py-1 border">{{ $isTitle ? '' : number_format($volTotal, 2) }}</td>

    {{-- Bobot Lalu (Tambahkan class detail-column) --}}
    <td class="text-right px-2 py-1 border detail-column">{{ number_format($progLalu, 2) }}%</td>
    {{-- Bobot Periode Ini (Tambahkan class detail-column) --}}
    <td class="text-right px-2 py-1 border detail-column">{{ number_format($progPeriodeIni, 2) }}%</td>
    {{-- Bobot S.d Saat Ini --}}
    <td class="text-right px-2 py-1 border font-bold">{{ number_format($progTotal, 2) }}%</td>

    {{-- Kolom Progress --}}
    <td class="text-right px-2 py-1 border font-bold">{{ $isTitle ? '' : number_format($itemProgress, 2) . '%' }}</td>
</tr>

@if ($hasChildren)
    @foreach ($item->children as $child)
        @include('daily_reports.partials._activity-row', ['item' => $child, 'level' => $level + 1])
    @endforeach
@endif