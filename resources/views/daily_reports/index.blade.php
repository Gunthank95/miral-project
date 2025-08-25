@extends('layouts.app')

@section('title', 'Ringkasan Laporan Harian')

@section('content')
<div class="p-4 sm:p-6">
    <header class="bg-white shadow p-4 rounded-lg mb-6">
        <div class="flex flex-wrap justify-between items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Ringkasan Laporan Harian</h1>
                <p class="text-sm text-gray-500">
                    Proyek: {{ $package->project->name }} - Paket: {{ $package->name }}
                </p>
            </div>
            {{-- Ini adalah baris filter tanggal --}}
            <div class="flex items-center space-x-2">
                <button id="prev-day-btn" class="p-2 rounded-full hover:bg-gray-200" title="Hari Sebelumnya">◄</button>
                <input type="date" name="date" id="date-input" value="{{ $selectedDate }}" class="border rounded px-3 py-1 text-sm focus:ring-blue-500 focus:border-blue-500">
                <button id="next-day-btn" class="p-2 rounded-full hover:bg-gray-200" title="Hari Berikutnya">►</button>
            </div>
        </div>
    </header>

    <div class="flex justify-between items-center mb-4">
        <h2 id="date-header" class="text-lg font-semibold text-gray-700">
            {{ \Carbon\Carbon::parse($selectedDate)->isoFormat('dddd, D MMMM YYYY') }}
        </h2>
        <a href="{{ route('daily_reports.create', ['package' => $package->id, 'date' => $selectedDate]) }}" id="edit-report-btn" class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700 font-semibold">
            + Buat / Edit Laporan untuk Tanggal Ini
        </a>
    </div>
    
    <div id="summary-content-container">
        @if($report)
            @include('daily_reports.partials._summary-content')
        @else
            @include('daily_reports.partials._report-not-found')
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const dateInput = document.getElementById('date-input');
    const prevDayBtn = document.getElementById('prev-day-btn');
    const nextDayBtn = document.getElementById('next-day-btn');
    const summaryContainer = document.getElementById('summary-content-container');
    const dateHeader = document.getElementById('date-header');
    const editReportBtn = document.getElementById('edit-report-btn');

    async function fetchReportSummary(dateString) {
        const url = `{{ route('daily_reports.index', $package->id) }}?date=${dateString}`;
        
        try {
            summaryContainer.style.opacity = '0.5';
            
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            });

            const data = await response.json();
            
            // Perbarui konten utama dan header tanggal
            summaryContainer.innerHTML = data.html;
            dateHeader.textContent = data.date_header;
            
            // Perbarui URL pada tombol "Buat / Edit Laporan"
            const editUrl = new URL(editReportBtn.href);
            editUrl.searchParams.set('date', dateString);
            editReportBtn.href = editUrl.toString();

            // Perbarui URL di browser tanpa reload halaman
            history.pushState({date: dateString}, '', url);

        } catch (error) {
            console.error('Gagal memuat ringkasan:', error);
            summaryContainer.innerHTML = '<div class="text-red-500 text-center p-4 bg-white shadow rounded">Terjadi kesalahan saat memuat data.</div>';
        } finally {
            summaryContainer.style.opacity = '1';
        }
    }

    function changeDay(offset) {
        const currentDate = new Date(dateInput.value);
        currentDate.setDate(currentDate.getDate() + offset);
        const newDateString = currentDate.toISOString().split('T')[0];
        dateInput.value = newDateString;
        fetchReportSummary(newDateString);
    }

    prevDayBtn.addEventListener('click', () => changeDay(-1));
    nextDayBtn.addEventListener('click', () => changeDay(1));
    dateInput.addEventListener('change', () => fetchReportSummary(dateInput.value));
});
</script>
@endpush