@extends('layouts.app')

@section('title', 'Pembuatan Laporan Harian')

@section('content')
<div class="p-4 sm:p-6" x-data="{ openModal: false }">
    <header class="bg-white shadow p-4 rounded-lg mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Pembuatan Laporan Harian</h1>
        <p class="text-sm text-gray-500">
            Tanggal: {{ \Carbon\Carbon::parse($report->report_date)->isoFormat('dddd, D MMMM YYYY') }}
        </p>
    </header>

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    <div class="space-y-6">
        <div class="space-y-6">
        <div class="bg-white rounded shadow p-4">
            <h2 class="text-xl font-semibold mb-4 border-b pb-2">1. Informasi Umum</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                <div><span class="font-semibold block">Nama Proyek:</span> {{ $package->project->name }}</div>
                <div><span class="font-semibold block">Nama Paket:</span> {{ $package->name }}</div>
                <div><span class="font-semibold block">Lokasi:</span> {{ $package->project->location }}</div>
                <div><span class="font-semibold block">Nomor Laporan:</span> #{{ $report->id }}</div>
            </div>
        </div>

        <div class="bg-white rounded shadow p-4">
            <h2 class="text-xl font-semibold mb-4 border-b pb-2">2. Laporan Cuaca</h2>
            {{-- Kode untuk Laporan Cuaca --}}
            <table class="min-w-full text-sm mb-4">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="text-left px-4 py-2 w-1/6">Jam</th>
                        <th class="text-left px-4 py-2 w-1/6">Kondisi</th>
                        <th class="text-left px-4 py-2 w-3/6">Keterangan</th>
                        <th class="text-right px-4 py-2 w-1/6">Aksi</th>
                    </tr>
                </thead>
                <tbody id="weather-table-body">
                    @forelse ($report->weather as $weather_log)
                        @include('daily_reports.partials.weather-row', ['weather_log' => $weather_log])
                    @empty
                        <tr id="weather-empty-row"><td colspan="4" class="text-center p-4 text-gray-500">Belum ada data cuaca.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <form id="weather-form" action="{{ route('daily_reports.weather.store', $report->id) }}" method="POST" class="border-t pt-4 space-y-4">
                @csrf
                <div class="flex items-end space-x-4">
                    <div class="w-1/4">
                        <label for="time" class="block text-xs font-medium text-gray-700">Jam</label>
                        <div class="flex items-center space-x-2 mt-1">
                            <input type="time" name="time" id="time" required class="w-full border rounded px-3 py-1 text-sm">
                            <button type="button" id="time-now-btn" class="bg-gray-200 text-gray-600 px-2.5 py-1 rounded text-xs hover:bg-gray-300 flex-shrink-0">Now</button>
                        </div>
                    </div>
                    <div class="w-1/4">
                        <label for="condition" class="block text-xs font-medium text-gray-700">Kondisi Cuaca</label>
                        <select name="condition" id="condition" required class="mt-1 w-full border rounded px-3 py-1 text-sm">
                            <option>Cerah</option>
                            <option>Berawan</option>
                            <option>Hujan Ringan</option>
                            <option>Hujan Deras</option>
                        </select>
                    </div>
                    <div class="flex-grow">
                        <label for="description" class="block text-xs font-medium text-gray-700">Keterangan (Opsional)</label>
                        <input type="text" name="description" id="description" class="mt-1 w-full border rounded px-3 py-1 text-sm" placeholder="Contoh: Pekerjaan dihentikan">
                    </div>
                </div>
                <div class="text-right">
                    <button type="submit" class="bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600">+ Tambah Cuaca</button>
                </div>
            </form>
        </div>
        
        <div class="bg-white rounded shadow p-4">
            <h2 class="text-xl font-semibold mb-4 border-b pb-2">3. Laporan Tenaga Kerja</h2>
            {{-- Kode untuk Laporan Tenaga Kerja --}}
            <div id="personnel-success-message" class="hidden bg-green-100 text-green-700 p-3 rounded text-sm mb-4"></div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="font-semibold text-gray-700 mb-2">Kontraktor</h3>
                    <table class="w-full text-sm mb-4">
                        <tbody id="personnel-table-kontraktor">
                            @foreach ($report->personnel->where('company_type', 'Kontraktor') as $personnel)
                                @include('daily_reports.partials.personnel-row', ['personnel' => $personnel])
                            @endforeach
                        </tbody>
                        <tfoot class="font-bold bg-gray-50">
                            <tr>
                                <td class="px-2 py-1 text-right">Total</td>
                                <td id="total-kontraktor" class="px-2 py-1 text-center">{{ $report->personnel->where('company_type', 'Kontraktor')->sum('count') }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                    <form class="personnel-form border-t pt-2 flex items-end space-x-2" data-company-type="Kontraktor">
                        @csrf
                        <div class="flex-grow"><label class="text-xs">Jabatan</label><input type="text" name="role" required class="w-full border rounded px-2 py-1 text-sm"></div>
                        <div class="w-1/4"><label class="text-xs">Jumlah</label><input type="number" name="count" value="0" required class="w-full border rounded px-2 py-1 text-sm"></div>
                        <div><button type="submit" class="bg-blue-500 text-white px-3 py-1 rounded text-sm">+</button></div>
                    </form>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-700 mb-2">MK / Pengawas</h3>
                    <table class="w-full text-sm mb-4">
                        <tbody id="personnel-table-mk">
                             @foreach ($report->personnel->where('company_type', 'MK') as $personnel)
                                @include('daily_reports.partials.personnel-row', ['personnel' => $personnel])
                            @endforeach
                        </tbody>
                        <tfoot class="font-bold bg-gray-50">
                             <tr>
                                <td class="px-2 py-1 text-right">Total</td>
                                <td id="total-mk" class="px-2 py-1 text-center">{{ $report->personnel->where('company_type', 'MK')->sum('count') }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                    <form class="personnel-form border-t pt-2 flex items-end space-x-2" data-company-type="MK">
                        @csrf
                        <div class="flex-grow"><label class="text-xs">Jabatan</label><input type="text" name="role" required class="w-full border rounded px-2 py-1 text-sm"></div>
                        <div class="w-1/4"><label class="text-xs">Jumlah</label><input type="number" name="count" value="0" required class="w-full border rounded px-2 py-1 text-sm"></div>
                        <div><button type="submit" class="bg-blue-500 text-white px-3 py-1 rounded text-sm">+</button></div>
                    </form>
                </div>
            </div>
        </div>

        {{-- SEKSI 4: AKTIVITAS PEKERJAAN --}}
        <div class="bg-white rounded shadow p-4">
            <div class="flex justify-between items-center mb-4">
				<h2 class="text-xl font-semibold">Aktivitas Pekerjaan</h2>
				<button @click="openModal = true" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
					+ Tambah Aktivitas
				</button>
			</div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="text-left px-4 py-2 w-1/12"></th>
                            <th class="text-left px-4 py-2 w-7/12">Uraian Pekerjaan</th>
                            <th class="text-center px-4 py-2 w-2/12">Progres Volume</th>
                            <th class="text-center px-4 py-2 w-2/12">Tenaga Kerja</th>
							<th class="text-right px-4 py-2 w-2/12">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($report->activities as $activity)
                            <tr class="border-t">
								<td class="px-4 py-2 text-center">
									<button class="expand-btn text-blue-500" data-target="details-{{ $activity->id }}">▼</button>
								</td>
								<td class="px-4 py-2">
									@if ($activity->rabItem)
										<span class="font-semibold">{{ $activity->rabItem->item_number }}</span>
										{{ $activity->rabItem->item_name }}
									@else
										<span class="font-semibold text-orange-600">(Non-BOQ)</span>
										{{ $activity->custom_work_name }}
									@endif
								</td>
								<td class="text-center px-4 py-2">{{ $activity->progress_volume }} {{ $activity->rabItem->unit ?? '' }}</td>
								{{-- PERBAIKAN: Menjumlahkan total tenaga kerja dari relasi --}}
								<td class="text-center px-4 py-2">{{ $activity->manpower->sum('quantity') }}</td>
								<td class="px-4 py-2 text-right">
									<div class="flex items-center justify-end space-x-2">
										<a href="{{ route('daily_log.edit', $activity->id) }}" class="text-blue-600 hover:underline text-xs">Edit</a>
										{{-- TAMBAHAN: Form untuk tombol Hapus --}}
										<form action="{{ route('daily_log.destroy', $activity->id) }}" method="POST" onsubmit="return confirm('Anda yakin ingin menghapus aktivitas ini?');">
											@csrf
											@method('DELETE')
											<button type="submit" class="text-red-600 hover:underline text-xs">Hapus</button>
										</form>
									</div>
								</td>
							</tr>
                            <tr id="details-{{ $activity->id }}" class="hidden border-t bg-gray-50">
                                <td colspan="4" class="p-4">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <h4 class="font-semibold mb-2">Peralatan:</h4>
                                            <ul class="list-disc list-inside text-xs">
                                                @forelse ($activity->equipment as $eq)
                                                    <li>{{ $eq->name }} ({{ $eq->quantity }} unit)</li>
                                                @empty
                                                    <li>-</li>
                                                @endforelse
                                            </ul>
                                        </div>
                                        <div>
                                            <h4 class="font-semibold mb-2">Material:</h4>
                                            <ul class="list-disc list-inside text-xs">
                                                 @forelse ($activity->materials as $mat)
                                                    <li>{{ $mat->material->name ?? 'N/A' }} ({{ $mat->quantity }} {{ $mat->unit }})</li>
                                                @empty
                                                    <li>-</li>
                                                @endforelse
                                            </ul>
                                        </div>
                                        <div>
                                            <h4 class="font-semibold mb-2">Foto:</h4>
                                            <div class="flex flex-wrap gap-2">
                                                @forelse ($activity->photos as $photo)
                                                    <a href="{{ asset('storage/' . $photo->file_path) }}" target="_blank">
                                                        <img src="{{ asset('storage/' . $photo->file_path) }}" alt="Foto" class="h-16 w-16 object-cover rounded">
                                                    </a>
                                                @empty
                                                    <span class="text-xs">-</span>
                                                @endforelse
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center p-4 text-gray-500">Belum ada aktivitas pekerjaan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Script untuk membuat jam default menjadi jam sekarang
    document.addEventListener('DOMContentLoaded', function() {
        const timeInput = document.getElementById('time');
        const timeNowBtn = document.getElementById('time-now-btn');

        function setTimeToNow() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            timeInput.value = `${hours}:${minutes}`;
        }

        if (timeInput) {
            setTimeToNow(); // Set jam default saat halaman dimuat
        }
        
        if (timeNowBtn) {
            timeNowBtn.addEventListener('click', setTimeToNow); // Set jam saat tombol "Now" diklik
        }
    });
	
	
	document.querySelectorAll('.personnel-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const companyType = this.dataset.companyType;
            formData.append('company_type', companyType);
            
            const actionUrl = "{{ route('daily_reports.personnel.store', $report->id) }}";
            const csrfToken = this.querySelector('input[name="_token"]').value;

            fetch(actionUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: formData,
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const successDiv = document.getElementById('personnel-success-message');
                    successDiv.textContent = data.message;
                    successDiv.classList.remove('hidden');
                    setTimeout(() => successDiv.classList.add('hidden'), 3000);

                    // ======================================================
                    // BAGIAN BARU UNTUK MEMPERBARUI TABEL
                    // ======================================================
                    const newRowHtml = `
                        <tr class="border-t" id="personnel-${data.personnel.id}">
                            <td class="px-2 py-1">${data.personnel.role}</td>
                            <td class="px-2 py-1 text-center">${data.personnel.count}</td>
                            <td class="px-2 py-1 text-right">
                                <form action="/daily-reports/personnel/${data.personnel.id}" method="POST" onsubmit="return confirm('Yakin?');">
                                    <input type="hidden" name="_token" value="${csrfToken}">
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button type="submit" class="text-red-500 text-xs">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    `;

                    // Cek apakah baris dengan ID ini sudah ada (kasus edit)
                    const existingRow = document.getElementById(`personnel-${data.personnel.id}`);
                    if (existingRow) {
                        existingRow.outerHTML = newRowHtml; // Ganti baris yang ada
                    } else {
                        // Tambahkan baris baru ke tabel yang sesuai
                        if (companyType === 'Kontraktor') {
                            document.getElementById('personnel-table-kontraktor').insertAdjacentHTML('beforeend', newRowHtml);
                        } else {
                            document.getElementById('personnel-table-mk').insertAdjacentHTML('beforeend', newRowHtml);
                        }
                    }
                    
                    // Perbarui total (akan kita sempurnakan nanti jika perlu)
                    // updateTotalCount(); 

                    this.reset();
                }
            })
            .catch(error => console.error('Error:', error));
        });
    });
	
	// --- SCRIPT BARU UNTUK AJAX CUACA ---
    const weatherForm = document.getElementById('weather-form');
    const weatherTableBody = document.getElementById('weather-table-body');
    const weatherSuccessMessage = document.getElementById('weather-success-message');

    if (weatherForm) {
        weatherForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch(this.action, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': formData.get('_token'), 'Accept': 'application/json' },
                body: formData,
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Hapus pesan "kosong" jika ada
                    const emptyRow = document.getElementById('weather-empty-row');
                    if (emptyRow) emptyRow.remove();
                    
                    // Tambahkan baris baru ke tabel
                    weatherTableBody.insertAdjacentHTML('beforeend', data.html);

                    // Tampilkan pesan sukses
                    weatherSuccessMessage.textContent = data.message;
                    weatherSuccessMessage.classList.remove('hidden');
                    setTimeout(() => weatherSuccessMessage.classList.add('hidden'), 3000);
                    
                    this.reset(); // Kosongkan form
                    document.getElementById('time-now-btn').click(); // Reset jam ke waktu sekarang
                }
            })
            .catch(error => console.error('Error:', error));
        });
    }

    if (weatherTableBody) {
        weatherTableBody.addEventListener('submit', function (e) {
            if (e.target.classList.contains('weather-delete-form')) {
                e.preventDefault();
                if (confirm('Anda yakin ingin menghapus?')) {
                    const form = e.target;
                    const formData = new FormData(form);
                    fetch(form.action, {
                        method: 'POST', // Form method spoofing
                        headers: { 'X-CSRF-TOKEN': formData.get('_token'), 'Accept': 'application/json' },
                        body: formData,
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            form.closest('tr').remove();
                        }
                    })
                    .catch(error => console.error('Error:', error));
                }
            }
        });
    }

    // Script baru untuk tombol expand/collapse
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.expand-btn').forEach(button => {
            button.addEventListener('click', function() {
                const targetId = this.dataset.target;
                const targetRow = document.getElementById(targetId);
                const isHidden = targetRow.classList.contains('hidden');

                if (isHidden) {
                    targetRow.classList.remove('hidden');
                    this.innerHTML = '▲'; // Ganti ikon menjadi panah atas
                } else {
                    targetRow.classList.add('hidden');
                    this.innerHTML = '▼'; // Ganti ikon menjadi panah bawah
                }
            });
        });
    });
</script>
@endpush