@extends('layouts.app')

@section('title', 'Manajemen Tim & Undangan')

@section('content')
<div class="p-4 sm:p-6">
    <header class="bg-white shadow p-4 rounded-lg mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Manajemen Tim & Undangan</h1>
        <p class="text-sm text-gray-500">
            Proyek: {{ $project->name }}
        </p>
    </header>

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        {{-- Kolom Kiri: Form Undangan --}}
        <div class="md:col-span-1">
            <div class="bg-white rounded shadow p-4">
                <h2 class="text-lg font-semibold mb-4 border-b pb-2">Kirim Undangan Baru</h2>

                <form action="{{ route('invitations.store', $project->id) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email Pengguna</label>
                        <input type="email" name="email" id="email" required class="mt-1 w-full border rounded px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label for="company_id" class="block text-sm font-medium text-gray-700">Perusahaan</label>
                        <select name="company_id" id="company_id" required class="mt-1 w-full border rounded px-3 py-2 text-sm">
                            <option value="">-- Pilih Perusahaan --</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company->id }}">{{ $company->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="package_id" class="block text-sm font-medium text-gray-700">Paket Pekerjaan (Khusus Kontraktor)</label>
                        <select name="package_id" id="package_id" class="mt-1 w-full border rounded px-3 py-2 text-sm">
                            <option value="">-- Semua Paket (Untuk Owner/MK) --</option>
                            @foreach ($project->packages as $package)
                                <option value="{{ $package->id }}">{{ $package->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
						<label for="role" class="block text-sm font-medium text-gray-700">Peran Sistem</label>
						<select name="role" id="role" required class="mt-1 w-full border rounded px-3 py-2 text-sm">
							<option value="">-- Pilih Peran Sistem --</option>
							@foreach ($roles as $key => $value)
								<option value="{{ $key }}">{{ $value }}</option>
							@endforeach
						</select>
					</div>
                    <div>
                        <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded text-sm hover:bg-blue-700">
                            Kirim Undangan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Kolom Kanan: Daftar Tim & Undangan Terkirim --}}
		<div class="md:col-span-2 space-y-6">
			<div class="bg-white rounded shadow p-4">
				<h2 class="text-lg font-semibold mb-4 border-b pb-2">Anggota Tim Aktif</h2>
				<table class="w-full text-sm">
					<thead class="bg-gray-50">
						<tr>
							<th class="text-left p-2">Nama</th>
							<th class="text-left p-2">Perusahaan</th>
							<th class="text-left p-2">Jabatan</th>
						</tr>
					</thead>
					<tbody>
						@forelse ($teamMembers as $member)
							<tr class="border-t">
								<td class="p-2">{{ $member->user->name }}</td>
								<td class="p-2">{{ $member->user->company->name }}</td>
								<td class="p-2">{{ $member->role }}</td>
							</tr>
						@empty
							<tr>
								<td colspan="3" class="text-center p-4 text-gray-500">Belum ada anggota tim yang aktif.</td>
							</tr>
						@endforelse
					</tbody>
				</table>
			</div>
			<div class="bg-white rounded shadow p-4">
				<h2 class="text-lg font-semibold mb-4 border-b pb-2">Undangan Terkirim (Pending)</h2>
				<table class="w-full text-sm">
					 <thead class="bg-gray-50">
						<tr>
							<th class="text-left p-2">Email</th>
							<th class="text-left p-2">Perusahaan</th>
							<th class="text-left p-2">Jabatan</th>
							<th class="text-left p-2">Berlaku Hingga</th>
						</tr>
					</thead>
					<tbody>
						@forelse ($pendingInvitations as $invitation)
							<tr class="border-t">
								<td class="p-2">{{ $invitation->email }}</td>
								<td class="p-2">{{ $invitation->company->name }}</td>
								<td class="p-2">{{ $invitation->role_in_project }}</td>
								<td class="p-2">{{ \Carbon\Carbon::parse($invitation->expires_at)->isoFormat('D MMM YYYY') }}</td>
							</tr>
						@empty
							<tr>
								<td colspan="4" class="text-center p-4 text-gray-500">Tidak ada undangan yang terkirim.</td>
							</tr>
						@endforelse
					</tbody>
				</table>
			</div>
		</div>
    </div>
</div>
@endsection