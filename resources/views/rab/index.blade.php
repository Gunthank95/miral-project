@extends('layouts.app')

@section('title', 'RAB: ' . $package->name)

@section('content')
<div class="p-4 sm:p-6">
    <header class="bg-white shadow p-4 rounded-lg mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Rencana Anggaran Biaya (RAB)</h1>
                <p class="text-sm text-gray-500">
                    Proyek: {{ $package->project->name }} - Paket: {{ $package->name }}
                </p>
            </div>
            <div>
                <a href="{{ route('daily_reports.index', $package->id) }}" class="bg-green-600 text-white px-4 py-2 rounded text-sm hover:bg-green-700">
                    Laporan Harian
                </a>
            </div>
        </div>
    </header>

    <main>
        <div class="bg-white rounded shadow overflow-x-auto">
            <table class="min-w-full text-sm" id="rab-table">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="text-left px-4 py-2 w-5/12">Item Pekerjaan</th>
                        <th class="text-center px-4 py-2 w-1/12">Volume</th>
                        <th class="text-left px-4 py-2 w-1/12">Satuan</th>
                        <th class="text-right px-4 py-2 w-2/12">Harga Satuan (Rp)</th>
                        <th class="text-right px-4 py-2 w-2/12">Total Harga (Rp)</th>
                        <th class="text-center px-4 py-2 w-1/12">Bobot (%)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rabItemsTree as $item)
                        @include('rab._item-row', ['item' => $item, 'level' => 0])
                    @empty
                        <tr>
                            <td colspan="6" class="text-center p-4 text-gray-500">
                                Belum ada data RAB untuk paket ini. Silakan upload file CSV.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($grandTotal > 0)
                <tfoot class="bg-gray-100 font-bold">
                    <tr>
                        <td colspan="4" class="text-right px-4 py-2">GRAND TOTAL</td>
                        <td class="text-right px-4 py-2">
                            {{ number_format($grandTotal, 2, ',', '.') }}
                        </td>
                        <td class="text-center px-4 py-2">100.00%</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>

        <div class="bg-white rounded shadow p-4 mt-6">
            <h2 class="text-xl font-semibold mb-4">Upload RAB dari File CSV</h2>
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif
            <form action="{{ route('rab.import', $package->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <label for="rab_file" class="block text-sm font-medium text-gray-700">Pilih File CSV (Pemisah ';')</label>
                    <input type="file" name="rab_file" id="rab_file" required class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"/>
                </div>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Upload dan Sinkronkan RAB
                </button>
                <p class="text-xs text-gray-500 mt-2">Catatan: Mengupload file akan memperbarui data yang ada berdasarkan No. Item, dan menghapus item yang tidak ada di file baru.</p>
            </form>
        </div>
    </main>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const table = document.getElementById('rab-table');
    if (!table) return;

    table.addEventListener('click', function (event) {
        const button = event.target.closest('.toggle-btn');
        if (button) {
            const targetId = button.dataset.targetId;
            const isExpanded = button.getAttribute('aria-expanded') === 'true';

            button.setAttribute('aria-expanded', !isExpanded);
            button.textContent = isExpanded ? '[+]' : '[-]';
  
            function toggleAllChildren(parentId, hide) {
                const children = document.querySelectorAll(`tr[data-parent-id="${parentId}"]`);
                
                children.forEach(child => {
                    if (hide) {
                        child.classList.add('hidden');
                    } else {
                        child.classList.remove('hidden');
                    }

                    const childButton = child.querySelector('.toggle-btn');
                    if (hide && childButton) {
                        childButton.setAttribute('aria-expanded', 'false');
                        childButton.textContent = '[+]';
                        toggleAllChildren(child.dataset.id, ture);
                    }
                });
            }

            toggleAllChildren(targetId, isExpanded);
        }
    });
});
</script>
@endpush