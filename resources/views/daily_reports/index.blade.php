@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    {{-- Header Halaman --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800">Ringkasan Laporan Harian</h1>
            <p class="text-sm text-gray-600">Paket Pekerjaan: {{ $package->name }}</p>
        </div>
	</div>

    {{-- Form Filter Tanggal dengan Panah Navigasi --}}
    <div class="bg-white shadow-md rounded-lg p-4 mb-6">
        <form action="{{ route('daily_reports.index', $package->id) }}" method="GET" class="flex items-center justify-between space-x-4">
            
            
            <div class="flex items-center space-x-2">
				{{-- Tombol Hari Sebelumnya --}}
				<a href="{{ route('daily_reports.index', ['package' => $package->id, 'date' => $selectedDate->copy()->subDay()->format('Y-m-d')]) }}" class="p-2 bg-gray-200 hover:bg-gray-300 rounded">
					<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
				</a>
				
					<input type="date" name="date" value="{{ $selectedDate->format('Y-m-d') }}" class="border rounded px-1 py-2 text-sm">
				
				{{-- Tombol Hari Berikutnya --}}
				<a href="{{ route('daily_reports.index', ['package' => $package->id, 'date' => $selectedDate->copy()->addDay()->format('Y-m-d')]) }}" class="p-2 bg-gray-200 hover:bg-gray-300 rounded">
					<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
				</a>	

				<button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm">
					Tampilkan
				</button>
			</div>
			<div class="flex items-center space-x-2">
				{{-- TAMBAHKAN: Tombol Aksi (Buat/Edit) --}}
				@if($report)
					<a href="{{ route('daily_reports.edit', ['package' => $package->id, 'daily_report' => $report->id]) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded inline-flex items-center">
						<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
						Edit Laporan
					</a>
				@else
					<a href="{{ route('daily_reports.create', ['package' => $package->id, 'date' => $selectedDate->format('Y-m-d')]) }}" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded inline-flex items-center">
						<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
						Buat Laporan
					</a>
				@endif
				<a href="{{ route('package.show', $package->id) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded inline-flex items-center">
					<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.707-10.293a1 1 0 00-1.414-1.414l-3 3a1 1 0 000 1.414l3 3a1 1 0 001.414-1.414L9.414 11H13a1 1 0 100-2H9.414l1.293-1.293z" clip-rule="evenodd" /></svg>
					Kembali
				</a>
			</div>
        </form>
    </div>

    {{-- Konten Utama --}}
    <div id="report-content">
        @if($report)
            {{-- Tabel Ringkasan Progres --}}
		<div class="bg-white p-6 rounded-lg shadow-md mb-6">
			<div class="flex justify-between items-center mb-4">
				<h2 class="text-xl font-bold text-gray-800">Ringkasan Progres Pekerjaan</h2>
				<button id="toggle-details-btn" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm">
					Sembunyikan Detail
				</button>
			</div>
			<div class="overflow-x-auto">
				<table id="progress-table" class="min-w-full bg-white border border-gray-200 text-sm">
					{{-- THEAD --}}
					<thead class="bg-gray-100">
						<tr>
							<th rowspan="2" class="py-2 px-2 border w-4/12">Uraian Pekerjaan</th>
							<th rowspan="2" class="py-2 px-2 border w-1/12">Satuan</th>
							<th rowspan="2" class="py-2 px-2 border w-1/12 contract-column">Volume Kontrak</th>
							<th rowspan="2" class="py-2 px-2 border w-1/12 contract-column">Bobot Kontrak (%)</th>
							<th colspan="3" class="py-2 px-2 border" id="volume-header">Volume</th>
							<th colspan="3" class="py-2 px-2 border" id="weight-header">Bobot (%)</th>
							<th rowspan="2" class="py-2 px-2 border w-1/12">Progress</th>
						</tr>
						<tr>
							<th class="py-1 px-2 border w-1/12">Lalu</th>
							<th class="py-1 px-2 border w-1/12">Periode Ini</th>
							<th class="py-1 px-2 border w-1/12">S.d Saat Ini</th>
							<th class="py-1 px-2 border detail-column w-1/12">Lalu</th>
							<th class="py-1 px-2 border detail-column w-1/12">Periode Ini</th>
							<th class="py-1 px-2 border w-1/12">S.d Saat Ini</th>
						</tr>
					</thead>
					{{-- TBODY --}}
					<tbody>
						@if($activityTree->isNotEmpty())
							@foreach($activityTree as $item)
								@include('daily_reports.partials._activity-row', ['item' => $item, 'level' => 0])
							@endforeach
						@else
							<tr><td colspan="11" class="text-center py-4">Tidak ada data aktivitas untuk ditampilkan.</td></tr>
						@endif
					</tbody>
				</table>
			</div>
		</div>

		{{-- Kartu-Kartu Informasi --}}
		<div class="bg-white p-4 rounded-lg shadow-md mb-4">
			<h3 class="font-bold border-b pb-2 mb-1">Kartu-Kartu Informasi</h3>
		</div>
		
		{{-- TAMBAHKAN: Kartu Total Progres --}}
		<div class="bg-indigo-100 border-l-4 border-indigo-500 text-indigo-700 p-4 rounded-lg shadow-md mb-2 mt-4" role="alert">
		  <p class="font-bold text-lg">Total Progres Proyek (S.d Hari Ini)</p>
		  <p class="text-3xl font-semibold">{{ number_format($totalProgress, 2) }}%</p>
		</div>
			
		<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 py-4">
			{{-- Personil Kontraktor --}}
			<div class="bg-white p-4 rounded-lg shadow-md">
                <h3 class="font-bold border-b pb-2 mb-2 flex items-center text-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-500" viewBox="0 0 20 20" fill="currentColor"><path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" /></svg>
                    Personil Kontraktor
                </h3>
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
            <div class="bg-white p-4 rounded-lg shadow-md">
                <h3 class="font-bold border-b pb-2 mb-2 flex items-center text-gray-700">
                     <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-green-500" viewBox="0 0 20 20" fill="currentColor"><path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" /></svg>
                    Personil MK
                </h3>
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
			<div class="bg-white p-4 rounded-lg shadow-md">
                <h3 class="font-bold border-b pb-2 mb-2 flex items-center text-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                    Material
                </h3>
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
            <div class="bg-white p-4 rounded-lg shadow-md">
                <h3 class="font-bold border-b pb-2 mb-2 flex items-center text-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" /></svg>
                    Peralatan
                </h3>
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
			<div class="bg-white p-4 rounded-lg shadow-md">
                <h3 class="font-bold border-b pb-2 mb-2 flex items-center text-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-yellow-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 2a.75.75 0 01.75.75v1.5a.75.75 0 01-1.5 0v-1.5A.75.75 0 0110 2zM5.22 5.22a.75.75 0 011.06 0l1.06 1.06a.75.75 0 01-1.06 1.06L5.22 6.28a.75.75 0 010-1.06zM2 10a.75.75 0 01.75-.75h1.5a.75.75 0 010 1.5h-1.5A.75.75 0 012 10zm11.78 1.06a.75.75 0 011.06-1.06l1.06-1.06a.75.75 0 011.06 1.06l-1.06 1.06a.75.75 0 01-1.06 0zM10 18a.75.75 0 01-.75-.75v-1.5a.75.75 0 011.5 0v1.5A.75.75 0 0110 18zm3.72-2.22a.75.75 0 010-1.06l1.06-1.06a.75.75 0 011.06 1.06l-1.06 1.06a.75.75 0 01-1.06 0zM15 10a.75.75 0 01.75-.75h1.5a.75.75 0 010 1.5h-1.5A.75.75 0 0115 10zm-9.78 1.06a.75.75 0 011.06-1.06l1.06-1.06a.75.75 0 111.06 1.06l-1.06 1.06a.75.75 0 01-1.06 0zM10 12a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" /></svg>
                    Cuaca
                </h3>
				<ul class="text-sm space-y-2">
					@forelse ($report->weather as $w)
						<li class="flex justify-between"><span>Jam {{ \Carbon\Carbon::parse($w->time)->format('H:i') }}</span> <span>{{ $w->condition }}</span></li>
					@empty
						<li class="text-gray-500">Tidak ada data.</li>
					@endforelse
				</ul>
			</div>
		</div>
        @else
            @include('daily_reports.partials._report-not-found')
        @endif
    </div>
</div>
@endsection