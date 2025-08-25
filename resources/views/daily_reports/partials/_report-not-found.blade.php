<div class="bg-white rounded shadow p-10 text-center">
    <h2 class="text-xl font-semibold text-gray-700">Laporan Tidak Ditemukan</h2>
    <p class="text-gray-500 mt-2">Belum ada laporan untuk tanggal {{ \Carbon\Carbon::parse($selectedDate)->isoFormat('D MMMM YYYY') }}.</p>
</div>