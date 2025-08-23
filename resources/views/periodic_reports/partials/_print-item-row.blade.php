@php
    $padding = $level * 20;
    $isTitle = is_null($item->volume);

    $volLalu = $item->volume_lalu ?? 0;
    $volPeriodeIni = $item->volume_periode_ini ?? 0;
    $volSdPeriodeIni = $volLalu + $volPeriodeIni;

    $bobotLalu = $item->bobot_lalu ?? 0;
    $bobotPeriodeIni = $item->bobot_periode_ini ?? 0;
    $bobotSdPeriodeIni = $bobotLalu + $bobotPeriodeIni;
@endphp

<tr class="border-t {{ $isTitle ? 'bg-gray-100 font-semibold' : 'bg-white' }}">
    <td class="text-left px-4 py-2 border" style="padding-left: {{ 16 + $padding }}px;">
        <span class="font-semibold">{{ $item->item_number }}</span> {{ $item->item_name }}
    </td>
    <td class="text-center px-4 py-2 border">{{ $item->unit }}</td>
    <td class="text-center px-4 py-2 border">{{ $isTitle ? '-' : number_format($item->volume, 2) }}</td>
    <td class="text-center px-4 py-2 border">{{ $item->weighting ? number_format($item->weighting, 4) . '%' : '-' }}</td>
    
    <td class="text-center px-2 py-1 border">{{ $isTitle ? '-' : number_format($volLalu, 4) }}</td>
    <td class="text-center px-2 py-1 border">{{ $isTitle ? '-' : number_format($volPeriodeIni, 4) }}</td>
    <td class="text-center px-2 py-1 border">{{ $isTitle ? '-' : number_format($volSdPeriodeIni, 4) }}</td>

    <td class="text-center px-2 py-1 border">{{ number_format($bobotLalu, 4) }}%</td>
    <td class="text-center px-2 py-1 border">{{ number_format($bobotPeriodeIni, 4) }}%</td>
    <td class="text-center px-2 py-1 border font-bold">{{ number_format($bobotSdPeriodeIni, 4) }}%</td>
</tr>

@if ($item->children->isNotEmpty())
    @foreach ($item->children as $child)
        @include('periodic_reports.partials._print-item-row', ['item' => $child, 'level' => $level + 1])
    @endforeach
@endif