@php
    $padding = $level * 20;
    $isTitle = is_null($item->volume);
    $hasChildren = $item->children->isNotEmpty();
@endphp

<tr class="border-t {{ $isTitle ? 'bg-gray-50 font-semibold' : '' }}" 
    data-id="{{ $item->id }}" 
    data-parent-id="{{ $item->parent_id }}">
    
    <td class="px-4 py-2" style="padding-left: {{ 16 + $padding }}px;">
        @if ($hasChildren)
            <button class="toggle-btn text-blue-500 mr-2 w-6 text-left" 
                    data-target-id="{{ $item->id }}" 
                    aria-expanded="false">[+]</button>
        @else
            <span class="inline-block w-6"></span>
        @endif
        
        {{ $item->item_number }} {{ $item->item_name }}
    </td>
    <td class="text-center px-4 py-2">{{ $item->volume }}</td>
    <td class="px-4 py-2">{{ $item->unit }}</td>
    <td class="text-right px-4 py-2">
        {{ $isTitle ? '' : number_format($item->unit_price, 2, ',', '.') }}
    </td>
    <td class="text-right px-4 py-2">
        {{ number_format($item->subtotal, 2, ',', '.') }}
    </td>
    {{-- SEL BARU UNTUK MENAMPILKAN BOBOT --}}
    <td class="text-center px-4 py-2">
        {{-- Tampilkan bobot hanya jika item memiliki harga --}}
        {{ $item->subtotal > 0 ? number_format($item->weighting, 2, ',', '.') . '%' : '' }}
    </td>
</tr>

@if ($item->children->isNotEmpty())
    @foreach ($item->children as $child)
        @include('rab._item-row', ['item' => $child, 'level' => $level + 1])
    @endforeach
@endif