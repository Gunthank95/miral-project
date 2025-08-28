@extends('layouts.app')

@section('title', 'Kurva S Proyek')

@section('content')
<div class="p-4 sm:p-6" x-data="sCurvePage({
    sCurveData: {{ json_encode($sCurveData) }},
    chartData: {{ json_encode($chartData) }}
})">
    <header class="bg-white shadow p-4 rounded-lg mb-6">
        <div class="flex flex-wrap justify-between items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Laporan Kurva S</h1>
                <p class="text-sm text-gray-500">
                    Proyek: {{ $package->project->name }} - Paket: {{ $package->name }}
                </p>
            </div>
            <button onclick="window.print()" class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700 flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                <span>Cetak Laporan</span>
            </button>
        </div>
    </header>
    
	<div class="bg-white shadow p-4 rounded-lg mb-6">
        <form action="{{ route('s-curve.index', $package->id) }}" method="GET" class="flex flex-wrap items-end gap-4">
            {{-- Input Tanggal Mulai --}}
            <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700">Tanggal Mulai</label>
                <input type="date" name="start_date" id="start_date" value="{{ $filterStartDate ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>
            {{-- Input Tanggal Selesai --}}
            <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700">Tanggal Selesai</label>
                <input type="date" name="end_date" id="end_date" value="{{ $filterEndDate ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>
            
            {{-- GANTI: Dropdown Hari Pelaporan --}}
            <div>
                <label for="reporting_day" class="block text-sm font-medium text-gray-700">Hari Pelaporan</label>
                <select name="reporting_day" id="reporting_day" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="0" @if($selectedReportingDay == 0) selected @endif>Minggu</option>
                    <option value="1" @if($selectedReportingDay == 1) selected @endif>Senin</option>
                    <option value="2" @if($selectedReportingDay == 2) selected @endif>Selasa</option>
                    <option value="3" @if($selectedReportingDay == 3) selected @endif>Rabu</option>
                    <option value="4" @if($selectedReportingDay == 4) selected @endif>Kamis</option>
                    <option value="5" @if($selectedReportingDay == 5) selected @endif>Jumat</option>
                    <option value="6" @if($selectedReportingDay == 6) selected @endif>Sabtu</option>
                </select>
            </div>
            
            {{-- Tombol Tampilkan --}}
            <div>
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-indigo-700">
                    Tampilkan
                </button>
            </div>
        </form>
    </div>
	
	
	
    @if(isset($error))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert">
            <p class="font-bold">Gagal Membuat Laporan</p>
            <p>{{ $error }}</p>
        </div>
    @else
        {{-- Grafik Kurva S --}}
        <div class="bg-white p-4 sm:p-6 rounded-lg shadow mb-6">
        <canvas id="sCurveChart" x-ref="chartCanvas"></canvas>
		</div>

        {{-- Tabel Rincian Data --}}
        <div class="bg-white rounded-lg shadow overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Minggu Ke-</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tgl Mulai</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tgl Selesai</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Rencana Mingguan (%)</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Rencana Kumulatif (%)</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Realisasi Kumulatif (%)</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Deviasi (%)</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
					<template x-for="(week, index) in sCurveData" :key="week.start_date_raw">
						<tr x-data="{ editing: false, value: week.planned_weekly.toFixed(4) }">
							<td class="px-6 py-4" x-text="week.week_label"></td>
							<td class="px-6 py-4" x-text="week.start_date"></td>
							<td class="px-6 py-4" x-text="week.end_date"></td>
							
							{{-- GANTI: Kolom Rencana Mingguan yang bisa diedit --}}
							<td class="px-6 py-4 text-right cursor-pointer" @click="editing = true">
								<div x-show="!editing" x-text="parseFloat(week.planned_weekly).toFixed(2)"></div>
								<input type="number" step="0.01" x-show="editing" x-model="value"
									   @click.away="editing = false"
									   @keydown.enter.prevent="savePlan(index, value)"
									   @keydown.escape.prevent="editing = false; value = week.planned_weekly.toFixed(4)"
									   class="text-right w-24 border rounded px-1 py-0" x-ref="editInput"
									   x-init="$watch('editing', val => { if(val) { $nextTick(() => $refs.editInput.focus()) } })">
							</td>
							
							<td class="px-6 py-4 text-right font-semibold" x-text="parseFloat(week.planned_cumulative).toFixed(2)"></td>
							<td class="px-6 py-4 text-right font-semibold" x-text="parseFloat(week.actual_cumulative).toFixed(2)"></td>
							<td class="px-6 py-4 text-right font-bold" x-text="parseFloat(week.deviation).toFixed(2)"
								:class="week.deviation < 0 ? 'text-red-600' : 'text-green-600'"></td>
						</tr>
					</template>
				</tbody>
            </table>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('sCurvePage', (initialData) => ({
        sCurveData: initialData.sCurveData,
        chartData: initialData.chartData,
        chart: null,
        init() {
            this.drawChart();
        },
        drawChart() {
            if (this.chart) {
                this.chart.destroy();
            }
            const ctx = this.$refs.chartCanvas.getContext('2d');
            this.chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: this.chartData.labels,
                    datasets: [
                        { label: 'Rencana Kumulatif (%)', data: this.chartData.planned, borderColor: 'rgb(59, 130, 246)', tension: 0.3 },
                        { label: 'Realisasi Kumulatif (%)', data: this.chartData.actual, borderColor: 'rgb(22, 163, 74)', tension: 0.3 }
                    ]
                },
                options: {
                    responsive: true,
                    scales: { y: { beginAtZero: true, max: 100, ticks: { callback: (value) => value + '%' } } }
                }
            });
        },
        async savePlan(index, newValue) {
            let week = this.sCurveData[index];
            
            try {
                const response = await fetch('{{ route("s-curve.store_plan", $package->id) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        week_start_date: week.start_date_raw,
                        weight: newValue
                    })
                });

                if (!response.ok) throw new Error('Network response was not ok.');
                
                const updatedData = await response.json();
                
                // Update data dan gambar ulang semuanya
                this.sCurveData = updatedData.sCurveData;
                this.chartData = updatedData.chartData;
                this.drawChart();
                
            } catch (error) {
                console.error('There was a problem with your fetch operation:', error);
                alert('Gagal menyimpan data.');
            }
        }
    }));
});
</script>
@endpush