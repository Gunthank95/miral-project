@if ($report)
    <div class="space-y-6">
        {{-- Aktivitas Pekerjaan --}}
        <div class="bg-white rounded shadow p-4">
            <div class="flex justify-between items-center mb-2 border-b pb-2">
                <h2 class="text-xl font-semibold">Aktivitas Pekerjaan</h2>
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
                        {{-- GANTI: Logika looping diubah untuk memanggil partial rekursif --}}
                        @forelse ($activityTree as $item)
                            @include('daily_reports.partials._activity-row', ['item' => $item, 'level' => 0])
                        @empty
                            <tr><td colspan="10" class="text-center p-4 text-gray-500">Tidak ada aktivitas pekerjaan yang dilaporkan.</td></tr>
                        @endforelse
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