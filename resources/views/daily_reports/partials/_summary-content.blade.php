@if ($report)
    <div class="space-y-6">
        {{-- Aktivitas Pekerjaan --}}
        <div class="bg-white p-6 rounded-lg shadow-lg">
			<div class="flex justify-between items-center mb-4">
				<h2 class="text-xl font-bold text-gray-800">Ringkasan Progres Pekerjaan</h2>
				{{-- TAMBAHKAN: Tombol Sembunyikan Detail --}}
				<button id="toggle-details-btn" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm">
					Sembunyikan Detail
				</button>
			</div>

			<div class="overflow-x-auto">
				<table id="progress-table" class="min-w-full bg-white border border-gray-200 text-sm">
					<thead class="bg-gray-100">
						<tr>
							<th rowspan="2" class="py-2 px-4 border w-5/12">Uraian Pekerjaan</th>
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
						@if($activityTree->isNotEmpty())
							@foreach($activityTree as $item)
								@include('daily_reports.partials._activity-row', ['item' => $item, 'level' => 0])
							@endforeach
						@else
							<tr>
								<td colspan="11" class="text-center py-4">Tidak ada data aktivitas untuk ditampilkan.</td>
							</tr>
						@endif
					</tbody>
				</table>
			</div>
		</div>

        {{-- (Bagian lain dari file ini tetap sama) --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            {{-- Personil Kontraktor --}}
            <div class="bg-white rounded shadow p-4">
                <h3 class="font-semibold mb-2 border-b pb-2">Tim Kontraktor</h3>
                <ul class="text-sm space-y-1">
                    @forelse ($report->personnel->where('company_type', 'Kontraktor') as $p)
                        <li class="flex justify-between"><span>{{ $p->role }}</span> <span>{{ $p->count }}</span></li>
                    @empty
                        <li class="text-gray-500">Tidak ada data.</li>
                    @endforelse
                </ul>
                <div class="font-bold border-t mt-2 pt-1 flex justify-between"><span>TOTAL</span><span>{{ $report->personnel->where('company_type', 'Kontraktor')->sum('count') }}</span></div>
            </div>
            {{-- Personil MK --}}
            <div class="bg-white rounded shadow p-4">
                <h3 class="font-semibold mb-2 border-b pb-2">Tim MK/Pengawas</h3>
                <ul class="text-sm space-y-1">
                    @forelse ($report->personnel->where('company_type', 'MK') as $p)
                        <li class="flex justify-between"><span>{{ $p->role }}</span> <span>{{ $p->count }}</span></li>
                    @empty
                        <li class="text-gray-500">Tidak ada data.</li>
                    @endforelse
                </ul>
                <div class="font-bold border-t mt-2 pt-1 flex justify-between"><span>TOTAL</span><span>{{ $report->personnel->where('company_type', 'MK')->sum('count') }}</span></div>
            </div>
            {{-- Material --}}
            <div class="bg-white rounded shadow p-4">
                 <h3 class="font-semibold mb-2 border-b pb-2">Material Digunakan</h3>
                <ul class="text-sm space-y-1">
                    @php $allMaterials = $report->activities->flatMap->materials->groupBy('material.name'); @endphp
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
                    @php $allEquipment = $report->activities->flatMap->equipment->groupBy('name'); @endphp
                    @forelse ($allEquipment as $name => $items)
                        <li>{{ $name }} ({{ $items->sum('quantity') }} unit)</li>
                    @empty
                         <li class="text-gray-500">Tidak ada data.</li>
                    @endforelse
                </ul>
            </div>
            {{-- Cuaca --}}
            <div class="bg-white rounded shadow p-4">
                <h3 class="font-semibold mb-2 border-b pb-2">Cuaca</h3>
                <ul class="text-sm space-y-1">
                    @forelse ($report->weather as $w)
                        <li class="flex justify-between"><span>Jam {{ \Carbon\Carbon::parse($w->time)->format('H:i') }}</span> <span>{{ $w->condition }}</span></li>
                    @empty
                        <li class="text-gray-500">Tidak ada data.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
@else
    <div class="bg-white rounded shadow p-10 text-center">
        <h2 class="text-xl font-semibold text-gray-700">Laporan Tidak Ditemukan</h2>
        <p class="text-gray-500 mt-2">Belum ada laporan untuk tanggal {{ \Carbon\Carbon::parse($selectedDate)->isoFormat('D MMMM YYYY') }}.</p>
    </div>
@endif