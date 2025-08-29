@extends('layouts.app')

@section('title', 'Laporan Periodik')

@section('content')
<div class="container mx-auto" x-data="periodicReport()">
    <div class="bg-white rounded shadow p-6">
        <h1 class="text-2xl font-semibold mb-4 border-b pb-2">Laporan Progres Periodik</h1>
        
        {{-- FORM FILTER UTAMA --}}
        <form method="GET" action="{{ route('periodic_reports.index', $package) }}" class="mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700">Tanggal Mulai:</label>
                    <input type="date" id="start_date" name="start_date" value="{{ $startDate }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700">Tanggal Selesai:</label>
                    <input type="date" id="end_date" name="end_date" value="{{ $endDate }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
                <div class="col-span-1 md:col-span-2 lg:col-span-1">
                    <label for="filter" class="block text-sm font-medium text-gray-700">Filter Tampilan:</label>
                    <select name="filter" id="filter" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="all" @if($filter == 'all') selected @endif>Tampilkan Semua Item</option>
                        <option value="this_period" @if($filter == 'this_period') selected @endif>Item Dikerjakan Periode Ini</option>
                        <option value="until_now" @if($filter == 'until_now') selected @endif>Item Dikerjakan s/d Saat Ini</option>
                    </select>
                </div>
                <div class="flex space-x-2">
                    <button type="submit" class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Terapkan
                    </button>
                    <a href="{{ request()->fullUrlWithQuery(['print' => 'true']) }}" target="_blank" class="w-full text-center inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Cetak
                    </a>
                </div>
            </div>
        </form>

        {{-- TABEL PEKERJAAN --}}
        <div class="overflow-x-auto">
            <table class="min-w-full text-xs" id="rab-table">
                <thead class="bg-gray-100">
					<tr>
						<th rowspan="2" class="py-2 px-4 border w-4/12">Uraian Pekerjaan</th>
						<th rowspan="2" class="py-2 px-4 border w-1/12">Satuan</th>
						<th rowspan="2" class="py-2 px-4 border w-1/12 contract-column">Volume Kontrak</th>
						<th rowspan="2" class="py-2 px-4 border w-1/12 contract-column">Bobot Kontrak (%)</th>

						{{-- TAMBAHKAN ID di sini --}}
						<th colspan="3" class="py-2 px-4 border" id="volume-header">Volume</th>
						<th colspan="3" class="py-2 px-4 border" id="weight-header">Bobot (%)</th>

						<th rowspan="2" class="py-2 px-4 border w-1/12">Progress</th>
					</tr>
					<tr>
						<th class="py-2 px-4 border detail-column w-1/12">Lalu</th>
						<th class="py-2 px-4 border detail-column w-1/12">Periode Ini</th>
						<th class="py-2 px-4 border w-1/12">S.d Saat Ini</th>

						<th class="py-2 px-4 border detail-column w-1/12">Lalu</th>
						<th class="py-2 px-4 border detail-column w-1/12">Periode Ini</th>
						<th class="py-2 px-4 border w-1/12">S.d Saat Ini</th>
					</tr>
				</thead>
                <tbody>
                    @forelse ($rabTree as $item)
                        @include('periodic_reports.partials._item-row', ['item' => $item, 'level' => 0])
                    @empty
                        <tr><td colspan="10" class="text-center p-4 text-gray-500">Tidak ada data pekerjaan yang sesuai dengan filter.</td></tr>
                    @endforelse
                </tbody>
                {{-- TAMBAHKAN: Baris Total di Footer Tabel --}}
                @if($rabTree->isNotEmpty())
                <tfoot class="bg-gray-200 font-bold">
                    <tr>
                        <td colspan="7" class="text-right px-4 py-2 border">TOTAL PROGRES KESELURUHAN</td>
                        <td class="text-center px-2 py-1 border">{{ number_format($rabTree->sum('bobot_lalu'), 4) }}%</td>
                        <td class="text-center px-2 py-1 border">{{ number_format($rabTree->sum('bobot_periode_ini'), 4) }}%</td>
                        <td class="text-center px-2 py-1 border">{{ number_format($rabTree->sum('bobot_lalu') + $rabTree->sum('bobot_periode_ini'), 4) }}%</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>

        <hr class="my-6">
        
        {{-- TAMBAHKAN: Kartu-kartu Sumber Daya --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mt-6">
            {{-- Personil --}}
            <div class="bg-white rounded shadow p-4">
                <h3 class="font-semibold mb-2 border-b pb-2">Sumber Daya Manusia</h3>
                <ul class="text-sm space-y-1">
                    @php 
                        $groupedPersonnel = $allPersonnel->groupBy('role')->map(function($group) {
                            return $group->sum('count');
                        });
                    @endphp
                    @forelse ($groupedPersonnel as $role => $count)
                        <li class="flex justify-between"><span>{{ $role }}</span> <span>{{ $count }}</span></li>
                    @empty
                        <li class="text-gray-500">Tidak ada data.</li>
                    @endforelse
                </ul>
            </div>
            {{-- Material --}}
            <div class="bg-white rounded shadow p-4">
                 <h3 class="font-semibold mb-2 border-b pb-2">Material Digunakan</h3>
                <ul class="text-sm space-y-1">
                    @php 
                        $groupedMaterials = $allMaterials->groupBy('material.name')->map(function($group) {
                            return $group->sum('quantity') . ' ' . $group->first()->unit;
                        });
                    @endphp
                    @forelse ($groupedMaterials as $name => $quantity)
                        <li>{{ $name }} ({{ $quantity }})</li>
                    @empty
                        <li class="text-gray-500">Tidak ada data.</li>
                    @endforelse
                </ul>
            </div>
            {{-- Peralatan --}}
             <div class="bg-white rounded shadow p-4">
                 <h3 class="font-semibold mb-2 border-b pb-2">Peralatan Digunakan</h3>
                <ul class="text-sm space-y-1">
                    @php 
                        $groupedEquipment = $allEquipment->groupBy('name')->map(function($group) {
                            return $group->sum('quantity');
                        });
                    @endphp
                    @forelse ($groupedEquipment as $name => $quantity)
                        <li>{{ $name }} ({{ $quantity }} unit)</li>
                    @empty
                         <li class="text-gray-500">Tidak ada data.</li>
                    @endforelse
                </ul>
            </div>
            {{-- Cuaca --}}
            <div class="bg-white rounded shadow p-4">
                <h3 class="font-semibold mb-2 border-b pb-2">Rekap Cuaca</h3>
                <ul class="text-sm space-y-1">
                    @php 
                        $groupedWeather = $allWeather->groupBy('condition')->map(function($group) {
                            return $group->count();
                        });
                    @endphp
                    @forelse ($groupedWeather as $condition => $count)
                        <li class="flex justify-between"><span>{{ $condition }}</span> <span>{{ $count }} kali</span></li>
                    @empty
                        <li class="text-gray-500">Tidak ada data.</li>
                    @endforelse
                </ul>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
{{-- TAMBAHKAN: Script untuk Expand/Collapse --}}
<script>
    function periodicReport() {
        return {
            toggle(itemId) {
                const rows = document.querySelectorAll(`#rab-table tbody tr[data-parent-id='${itemId}']`);
                let allChildrenHidden = true;
                rows.forEach(row => {
                    if (!row.classList.contains('hidden')) {
                        allChildrenHidden = false;
                    }
                    row.classList.toggle('hidden');
                    const childId = row.getAttribute('data-id');
                    const icon = document.querySelector(`.toggle-icon[data-id='${childId}']`);
                    if (icon && !row.classList.contains('hidden')) {
                         // Saat parent dibuka, pastikan anak-anaknya tertutup
                        const grandchildren = document.querySelectorAll(`#rab-table tbody tr[data-parent-id='${childId}']`);
                        grandchildren.forEach(gc => gc.classList.add('hidden'));
                        icon.textContent = '+';
                    }
                });
                
                const icon = document.querySelector(`.toggle-icon[data-id='${itemId}']`);
                if (icon) {
                    icon.textContent = allChildrenHidden ? '-' : '+';
                }
            }
        }
    }
</script>
@endpush