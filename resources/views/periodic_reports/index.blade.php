@extends('layouts.app')

@section('title', 'Laporan Periodik')

@section('content')
<div class="p-4 sm:p-6">
    <header class="bg-white shadow p-4 rounded-lg mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Laporan Periodik (Mingguan/Bulanan)</h1>
                <p class="text-sm text-gray-500">
                    Proyek: {{ $package->project->name }} - Paket: {{ $package->name }}
                </p>
            </div>
        </div>
    </header>

    <main>
        {{-- Form Filter Tanggal --}}
        <div class="bg-white rounded shadow p-4 mb-6">
            <form method="GET" action="{{ route('periodic_reports.index', $package->id) }}" class="flex flex-wrap items-end gap-4">
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700">Tanggal Mulai</label>
                    <input type="date" name="start_date" id="start_date" value="{{ $startDate }}" required class="mt-1 border rounded px-3 py-1 text-sm">
                </div>
                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700">Tanggal Selesai</label>
                    <input type="date" name="end_date" id="end_date" value="{{ $endDate }}" required class="mt-1 border rounded px-3 py-1 text-sm">
                </div>
                <div>
                    <div class="flex items-center space-x-2">
						<button type="submit" class="bg-blue-600 text-white px-4 py-1 rounded text-sm hover:bg-blue-700">
							Tampilkan
						</button>
						<button type="submit" formaction="{{ route('periodic_reports.print', $package->id) }}" formtarget="_blank" class="bg-gray-600 text-white px-4 py-1 rounded text-sm hover:bg-gray-700">
							Cetak
						</button>
					</div>
                </div>
            </form>
        </div>

        @if ($startDate && $endDate)
            <div class="bg-white rounded shadow p-4">
                <div class="flex justify-between items-center mb-2 border-b pb-2">
                    <h2 class="text-xl font-semibold">Rangkuman Progres Periode {{ \Carbon\Carbon::parse($startDate)->isoFormat('D MMM Y') }} - {{ \Carbon\Carbon::parse($endDate)->isoFormat('D MMM Y') }}</h2>
                    <button id="toggle-progress-details" class="text-xs text-blue-600 hover:underline">Sembunyikan Detail</button>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-100">
                            <tr>
                                <th rowspan="2" class="text-left px-4 py-2 border">Uraian Pekerjaan</th>
                                <th rowspan="2" class="text-center px-4 py-2 border">Satuan</th>
                                <th rowspan="2" class="text-center px-4 py-2 border">Volume Kontrak</th>
                                <th rowspan="2" class="text-center px-4 py-2 border">Bobot Kontrak (%)</th>
                                <th colspan="3" class="text-center px-4 py-2 border progress-details">Volume</th>
                                <th colspan="3" class="text-center px-4 py-2 border progress-details">Bobot (%)</th>
                            </tr>
                            <tr class="progress-details">
                                <th class="text-center px-2 py-1 border font-normal">Lalu</th>
                                <th class="text-center px-2 py-1 border font-normal">Periode Ini</th>
                                <th class="text-center px-2 py-1 border font-normal">S.d Saat Ini</th>
                                <th class="text-center px-2 py-1 border font-normal">Lalu</th>
                                <th class="text-center px-2 py-1 border font-normal">Periode Ini</th>
                                <th class="text-center px-2 py-1 border font-normal">S.d Saat Ini</th>
                            </tr>
                        </thead>
                        <tbody>
						@forelse ($groupedActivities as $rab_item_id => $activities)
							@php
								$firstActivity = $activities->first();
								$rabItem = $firstActivity->rabItem; // Bisa jadi null
								$volLalu = $activities->previous_progress_volume;
								$volPeriodeIni = $activities->sum('progress_volume');
								$volTotal = $volLalu + $volPeriodeIni;

								$progLalu = ($rabItem && $rabItem->volume > 0) ? ($volLalu / $rabItem->volume) * $rabItem->weighting : 0;
								$progPeriodeIni = ($rabItem && $rabItem->volume > 0) ? ($volPeriodeIni / $rabItem->volume) * $rabItem->weighting : 0;
								$progTotal = $progLalu + $progPeriodeIni;
							@endphp
							<tr class="border-t">
								<td class="px-4 py-2 border">
									{{-- Pengecekan baru di sini --}}
									@if ($rabItem)
										<span class="font-semibold">{{ $rabItem->item_number }}</span> {{ $rabItem->item_name }}
									@else
										<span class="font-semibold text-orange-600">(Non-BOQ)</span> {{ $firstActivity->custom_work_name }}
									@endif
								</td>
								<td class="text-center px-4 py-2 border">{{ $rabItem->unit ?? '-' }}</td>
								<td class="text-center px-4 py-2 border">{{ $rabItem ? number_format($rabItem->volume, 2) : '-' }}</td>
								<td class="text-center px-4 py-2 border">{{ $rabItem ? number_format($rabItem->weighting, 2) . '%' : '-' }}</td>
								<td class="text-center px-2 py-1 border progress-details">{{ number_format($volLalu, 2) }}</td>
								<td class="text-center px-2 py-1 border progress-details">{{ number_format($volPeriodeIni, 2) }}</td>
								<td class="text-center px-2 py-1 border progress-details">{{ number_format($volTotal, 2) }}</td>
								<td class="text-center px-2 py-1 border progress-details">{{ number_format($progLalu, 2) }}%</td>
								<td class="text-center px-2 py-1 border progress-details">{{ number_format($progPeriodeIni, 2) }}%</td>
								<td class="text-center px-2 py-1 border progress-details">{{ number_format($progTotal, 2) }}%</td>
							</tr>
						@empty
							<tr><td colspan="10" class="text-center p-4 text-gray-500">Tidak ada aktivitas pekerjaan yang dilaporkan pada periode ini.</td></tr>
						@endforelse
					</tbody>
                    </table>
                </div>
				
				{{-- KARTU-KARTU RINGKASAN YANG BARU --}}
				<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
					{{-- Personil Kontraktor --}}
					<div class="bg-white rounded shadow p-4">
						<h3 class="font-semibold mb-2 border-b pb-2">Tim Kontraktor (Rata-rata)</h3>
						@php
							$allContractorPersonnel = $reports->flatMap->personnel->where('company_type', 'Kontraktor')->groupBy('role');
						@endphp
						<ul class="text-sm space-y-1">
							@forelse ($allContractorPersonnel as $role => $items)
								<li class="flex justify-between"><span>{{ $role }}</span> <span>{{ round($items->sum('count') / $reports->count()) }}</span></li>
							@empty
								<li class="text-gray-500">Tidak ada data.</li>
							@endforelse
						</ul>
					</div>
					{{-- Personil MK --}}
					<div class="bg-white rounded shadow p-4">
						<h3 class="font-semibold mb-2 border-b pb-2">Tim MK/Pengawas (Rata-rata)</h3>
						@php
							$allMkPersonnel = $reports->flatMap->personnel->where('company_type', 'MK')->groupBy('role');
						@endphp
						<ul class="text-sm space-y-1">
							 @forelse ($allMkPersonnel as $role => $items)
								<li class="flex justify-between"><span>{{ $role }}</span> <span>{{ round($items->sum('count') / $reports->count()) }}</span></li>
							@empty
								<li class="text-gray-500">Tidak ada data.</li>
							@endforelse
						</ul>
					</div>
					{{-- Material --}}
					<div class="bg-white rounded shadow p-4">
						<h3 class="font-semibold mb-2 border-b pb-2">Material Digunakan (Total)</h3>
						<ul class="text-sm space-y-1">
							@php $allMaterials = $reports->flatMap->activities->flatMap->materials->groupBy('material.name'); @endphp
							@forelse ($allMaterials as $name => $items)
								<li>{{ $name }} ({{ $items->sum('quantity') }} {{ $items->first()->unit }})</li>
							@empty
								<li class="text-gray-500">Tidak ada data.</li>
							@endforelse
						</ul>
					</div>
					{{-- Peralatan --}}
					<div class="bg-white rounded shadow p-4">
						<h3 class="font-semibold mb-2 border-b pb-2">Peralatan Digunakan</h3>
						<ul class="text-sm space-y-1">
							 @php $allEquipment = $reports->flatMap->activities->flatMap->equipment->groupBy('name'); @endphp
							@forelse ($allEquipment as $name => $items)
								<li>{{ $name }} ({{ $items->sum('quantity') }} unit)</li>
							@empty
								<li class="text-gray-500">Tidak ada data.</li>
							@endforelse
						</ul>
					</div>
				</div>
            </div>
        @else
            <div class="bg-white rounded shadow p-10 text-center">
                <h2 class="text-xl font-semibold text-gray-700">Silakan Pilih Rentang Tanggal</h2>
                <p class="text-gray-500 mt-2">Pilih tanggal mulai dan selesai, lalu klik "Tampilkan Laporan" untuk melihat rangkuman.</p>
            </div>
        @endif
    </main>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleBtn = document.getElementById('toggle-progress-details');
        const detailCells = document.querySelectorAll('.progress-details');

        if (toggleBtn) {
            toggleBtn.addEventListener('click', function() {
                const isHidden = detailCells[0].classList.contains('hidden');
                detailCells.forEach(cell => {
                    cell.classList.toggle('hidden');
                });
                this.textContent = isHidden ? 'Sembunyikan Detail' : 'Tampilkan Detail';
            });
        }
    });
</script>
@endpush