<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
	
	{{-- Kolom Kiri: Daftar Gambar & RAB --}}
	<div class="space-y-4">
		
		{{-- 1. Daftar Gambar (Tidak Berubah) --}}
		<div>
			<h4 class="font-semibold text-sm text-gray-700 mb-2">Daftar Gambar</h4>
			<div class="border rounded-md">
				<table class="min-w-full divide-y divide-gray-200 text-sm">
					<thead class="bg-gray-100">
						<tr>
							<th class="px-4 py-2 text-left font-medium text-gray-600">No. Gambar</th>
							<th class="px-4 py-2 text-left font-medium text-gray-600">Judul Gambar</th>
							<th class="px-4 py-2 text-left font-medium text-gray-600">Keterangan</th>
						</tr>
					</thead>
					<tbody class="bg-white divide-y divide-gray-200">
						@forelse ($document->drawingDetails as $drawing)
							<tr>
								<td class="px-4 py-2 whitespace-nowrap font-mono">{{ $drawing->drawing_number }}</td>
								<td class="px-4 py-2">{{ $drawing->drawing_title }}</td>
								<td class="px-4 py-2">
									<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $drawing->getStatusBadgeClass($document) }}">
										{{ $drawing->getStatusDescription($document) }}
									</span>
								</td>
							</tr>
						@empty
							<tr>
								<td colspan="3" class="px-4 py-2 text-center text-gray-500">Tidak ada detail gambar.</td>
							</tr>
						@endforelse
					</tbody>
				</table>
			</div>
		</div>

		{{-- 2. Pekerjaan RAB Terkait (PERUBAHAN DI SINI) --}}
		<div>
			<h4 class="font-semibold text-sm text-gray-700 mb-2">Pekerjaan RAB Terkait</h4>
			 <div class="border rounded-md max-h-48 overflow-y-auto">
				<table class="min-w-full divide-y divide-gray-200 text-sm">
					 {{-- Header Tabel Ditambahkan --}}
					<thead class="bg-gray-100">
						<tr>
							<th class="px-4 py-2 text-left font-medium text-gray-600">Nama Pekerjaan</th>
							<th class="px-4 py-2 text-left font-medium text-gray-600">Status Klaim</th>
						</tr>
					</thead>
					 <tbody class="bg-white divide-y divide-gray-200">
						@forelse ($document->rabItems as $item)
							<tr>
								<td class="px-4 py-2">{{ $item->item_name }}</td>
								<td class="px-4 py-2">
									@if ($item->pivot->completion_status == 'lengkap')
										<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
											Lengkap
										</span>
									@else
										<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
											Belum Lengkap
										</span>
									@endif
								</td>
							</tr>
						@empty
							<tr>
								<td colspan="2" class="px-4 py-2 text-center text-gray-500">Tidak ada pekerjaan terkait.</td>
							</tr>
						@endforelse
					</tbody>
				</table>
			</div>
		</div>
	</div>

	{{-- Kolom Kanan: Riwayat Persetujuan (PERUBAHAN DI SINI) --}}
	<div>
		{{-- ========================================================== --}}
		{{-- ============ TAMBAHKAN PANEL BARU DI SINI ================ --}}
		{{-- ========================================================== --}}
		<div class="mb-4">
			<h4 class="font-semibold text-sm text-gray-700 mb-2">Progres Review Internal MK</h4>
			<div class="border rounded-md bg-white p-3 space-y-3 text-xs">
				@if($document->status === 'pending')
					@forelse($mkTeamMembers as $member)
						@php
							// Cari apakah member ini sudah mereview dokumen saat ini
							$review = $document->internalReviews->firstWhere('user_id', $member->id);
						@endphp
						<div class="flex items-center justify-between">
							<span class="text-gray-600">{{ $member->name }}</span>
							@if($review)
								@if($review->status == 'revision_needed')
									<span class="font-semibold text-yellow-600">⚠️ Butuh Revisi</span>
								@else
									<span class="font-semibold text-green-600">✅ Direview</span>
								@endif
							@else
								<span class="text-gray-400">Menunggu Review</span>
							@endif
						</div>
					@empty
						<p class="text-gray-500 text-center">Tidak ada anggota tim MK di proyek ini.</p>
					@endforelse
				@else
					<p class="text-gray-500 text-center text-xs">Proses review internal MK telah selesai.</p>
				@endif
			</div>
		</div>
		<h4 class="font-semibold text-sm text-gray-700 mb-2">Riwayat Persetujuan</h4>
		<div class="border rounded-md">
			<table class="min-w-full text-sm bg-white">
				<thead class="bg-gray-100">
					<tr>
						<th class="px-3 py-2 text-left text-xs font-medium text-gray-600 w-1/3">Tanggal</th>
						<th class="px-3 py-2 text-left text-xs font-medium text-gray-600 w-1/3">Oleh</th>
						<th class="px-3 py-2 text-left text-xs font-medium text-gray-600 w-1/3">Status</th>
					</tr>
				</thead>
				<tbody class="divide-y divide-gray-200">
					@forelse ($document->approvals->sortBy('created_at') as $approval)
						<tr>
							<td class="px-3 py-2 text-gray-500 align-top">{{ $approval->created_at->format('d M Y, H:i') }}</td>
							<td class="px-3 py-2 font-medium text-gray-800 align-top">
								{{ $approval->user->name ?? 'Sistem' }}
								<br>
								<span class="text-gray-500 font-normal text-xs">{{ optional($approval->user->projectRoles->first())->position_title ?? 'N/A' }}</span>
							</td>
							<td class="px-3 py-2 align-top">
								<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
									@switch($approval->status)
										@case('submitted')
										@case('pending')
											bg-blue-100 text-blue-800
											@break
										@case('revision')
										@case('owner_rejected')
											bg-yellow-100 text-yellow-800
											@break
										@case('approved')
										@case('owner_approved')
											bg-green-100 text-green-800
											@break
										@case('rejected')
											bg-red-100 text-red-800
											@break
										@case('menunggu_persetujuan_owner')
											bg-purple-100 text-purple-800
											@break
										@default
											bg-gray-100 text-gray-800
									@endswitch
								">
									{{ str_replace('_', ' ', Str::title($approval->status)) }}
								</span>
							</td>
						</tr>
						@if($approval->notes)
							<tr class="bg-gray-50">
								<td colspan="3" class="px-3 py-1 text-xs text-gray-600 italic border-t">
									<strong>Catatan:</strong> "{{ $approval->notes }}"
								</td>
							</tr>
						@endif
					@empty
						<tr>
							<td colspan="3" class="text-center text-gray-500 py-4">
								Belum ada riwayat.
							</td>
						</tr>
					@endforelse
				</tbody>
			</table>
		</div>
	</div>

</div>