<div class="bg-white rounded shadow p-4">
    <div class="px-4 py-2 mb-4 border-b pb-4">
        <h3 class="font-bold text-gray-800">{{ $activeProject->name }}</h3>
        <p class="text-xs text-gray-500">Proyek Aktif</p>
    </div>

    <nav class="space-y-4">
        {{-- Grup Menu: Pelaporan & Progres --}}
        <div>
            <h4 class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Pelaporan & Progres</h4>
            <div class="mt-2 space-y-1">
                <a href="{{ route('project.show', $activeProject->id) }}" class="block px-4 py-2 text-sm rounded {{ request()->routeIs('project.show') ? 'bg-blue-500 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                    Dashboard Proyek
                </a>
                <a href="{{ route('daily_reports.index', $package->id ?? $activeProject->id) }}" class="block px-4 py-2 text-sm rounded {{ request()->routeIs('daily_reports.index') ? 'bg-blue-500 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                    Laporan Harian
                </a>
                <a href="{{ route('periodic_reports.index', $package->id ?? $activeProject->id) }}" class="block px-4 py-2 text-sm rounded {{ request()->routeIs('periodic_reports.index') ? 'bg-blue-500 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                    Laporan Periodik
                </a>
				
				<div x-data="{ open: {{ request()->routeIs('schedules.index') || request()->routeIs('s-curve.index') ? 'true' : 'false' }} }">
                    {{-- Tombol Parent Menu --}}
                    <button @click="open = !open" class="w-full flex justify-between items-center px-4 py-2 text-sm rounded {{ request()->routeIs('schedules.index') || request()->routeIs('s-curve.index') ? 'bg-blue-500 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                        <span>Jadwal & S Curve</span>
                        {{-- Ikon panah --}}
                        <svg class="w-4 h-4 transform transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    {{-- Sub-menu --}}
                    <div x-show="open" class="pl-4 mt-1 space-y-1">
                        <a href="{{ route('schedules.index', $package->id ?? $activeProject->id) }}" class="block px-4 py-2 text-sm rounded {{ request()->routeIs('schedules.index') ? 'bg-blue-100 text-blue-800' : 'text-gray-600 hover:bg-gray-100' }}">
                            Jadwal Proyek
                        </a>
                        <a href="{{ route('s-curve.index', $package->id ?? $activeProject->id) }}" class="block px-4 py-2 text-sm rounded {{ request()->routeIs('s-curve.index') ? 'bg-blue-100 text-blue-800' : 'text-gray-600 hover:bg-gray-100' }}">
                            Kurva S
                        </a>
                    </div>
                </div>

                {{-- TAMBAHKAN: Pindahkan menu Dokumen & RAB ke sini agar lebih relevan --}}
                <a href="{{ route('rab.index', $package->id ?? $activeProject->id) }}" class="block px-4 py-2 text-sm rounded {{ request()->routeIs('rab.index') ? 'bg-blue-500 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                    RAB (Rencana Anggaran Biaya)
                </a>
                <a href="{{ route('documents.index', $package->id ?? $activeProject->id) }}" class="block px-4 py-2 text-sm rounded {{ request()->routeIs('documents.index') ? 'bg-blue-500 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                    Gambar & Dokumen
                </a>
            </div>
        </div>

        {{-- TAMBAHKAN: Grup Menu Baru untuk Informasi Proyek --}}
        <div>
            <h4 class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Informasi Proyek</h4>
            <div class="mt-2 space-y-1">
                <a href="{{ route('projects.data-proyek', $activeProject->id) }}" class="block px-4 py-2 text-sm rounded {{ request()->routeIs('projects.data-proyek') ? 'bg-blue-500 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                    Data Utama Proyek
                </a>
            </div>
        </div>
        
		{{-- GANTI: Grup menu ini diubah menjadi Manajemen Pengguna --}}
		<div>
			<h4 class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Manajemen Pengguna</h4>
			<div class="mt-2 space-y-1">
                {{-- TAMBAHKAN: Link baru untuk melihat daftar pengguna --}}
				<a href="{{ route('users.index', $activeProject->id) }}" class="block px-4 py-2 text-sm rounded {{ request()->routeIs('users.index') ? 'bg-blue-500 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
					Daftar Pengguna
				</a>
				<a href="{{ route('invitations.index', $activeProject->id) }}" class="block px-4 py-2 text-sm rounded {{ request()->routeIs('invitations.index') ? 'bg-blue-500 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
					Undang Pengguna
				</a>
			</div>
		</div>
    </nav>
</div>