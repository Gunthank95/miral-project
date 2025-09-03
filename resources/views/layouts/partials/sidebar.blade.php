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
                <a href="{{ route('project.show', $activeProject->id) }}" class="flex items-center px-4 py-2 text-sm rounded {{ request()->routeIs('project.show') ? 'bg-blue-500 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    <span>Dashboard Proyek</span>
                </a>
                <a href="{{ route('daily_reports.index', $package->id ?? $activeProject->id) }}" class="flex items-center px-4 py-2 text-sm rounded {{ request()->routeIs('daily_reports.index') ? 'bg-blue-500 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <span>Laporan Harian</span>
                </a>
                <a href="{{ route('periodic_reports.index', $package->id ?? $activeProject->id) }}" class="flex items-center px-4 py-2 text-sm rounded {{ request()->routeIs('periodic_reports.index') ? 'bg-blue-500 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <span>Laporan Periodik</span>
                </a>
				
				<div x-data="{ open: {{ request()->routeIs('schedules.index') || request()->routeIs('s-curve.index') ? 'true' : 'false' }} }">
                    <button @click="open = !open" class="w-full flex justify-between items-center px-4 py-2 text-sm rounded {{ request()->routeIs('schedules.index') || request()->routeIs('s-curve.index') ? 'bg-blue-500 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                        <span class="flex items-center">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <span>Jadwal & S Curve</span>
                        </span>
                        <svg class="w-4 h-4 transform transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div x-show="open" class="pl-8 mt-1 space-y-1">
                        <a href="{{ route('schedules.index', $package->id ?? $activeProject->id) }}" class="block px-4 py-2 text-sm rounded {{ request()->routeIs('schedules.index') ? 'bg-blue-100 text-blue-800' : 'text-gray-600 hover:bg-gray-100' }}">
                            Jadwal Proyek
                        </a>
                        <a href="{{ route('s-curve.index', $package->id ?? $activeProject->id) }}" class="block px-4 py-2 text-sm rounded {{ request()->routeIs('s-curve.index') ? 'bg-blue-100 text-blue-800' : 'text-gray-600 hover:bg-gray-100' }}">
                            Kurva S
                        </a>
                    </div>
                </div>

                <a href="{{ route('rab.index', $package->id ?? $activeProject->id) }}" class="flex items-center px-4 py-2 text-sm rounded {{ request()->routeIs('rab.index') ? 'bg-blue-500 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                    <span>RAB</span>
                </a>
                <div x-data="{ open: {{ request()->routeIs('documents.*') ? 'true' : 'false' }} }">
					<button @click="open = !open" class="w-full flex justify-between items-center px-4 py-2 text-sm rounded {{ request()->routeIs('documents.*') ? 'bg-blue-500 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
						<span class="flex items-center">
							<svg class="w-5 h-5 mr-3" xmlns="http://www.w.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
							</svg>
							<span>Pusat Persetujuan</span>
						</span>
						<svg class="w-4 h-4 transform transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
					</button>
					<div x-show="open" class="pl-8 mt-1 space-y-1">
						<a href="{{ route('documents.index', ['package' => $package->id ?? $activeProject->id]) }}" class="block px-4 py-2 text-sm rounded {{ request()->routeIs('documents.index') ? 'bg-blue-100 text-blue-800' : 'text-gray-600 hover:bg-gray-100' }}">
							Shop Drawing
						</a>
						<a href="#" class="block px-4 py-2 text-sm rounded text-gray-400 cursor-not-allowed">
							Persetujuan Material
						</a>
						<a href="#" class="block px-4 py-2 text-sm rounded text-gray-400 cursor-not-allowed">
							Metode Kerja
						</a>
					</div>
				</div>
            </div>
        </div>

        <div>
            <h4 class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Informasi Proyek</h4>
            <div class="mt-2 space-y-1">
                <a href="{{ route('projects.data-proyek', $activeProject->id) }}" class="flex items-center px-4 py-2 text-sm rounded {{ request()->routeIs('projects.data-proyek') ? 'bg-blue-500 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>Data Utama Proyek</span>
                </a>
            </div>
        </div>
        
		<div>
			<h4 class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Manajemen Pengguna</h4>
			<div class="mt-2 space-y-1">
				<a href="{{ route('users.index', $activeProject->id) }}" class="flex items-center px-4 py-2 text-sm rounded {{ request()->routeIs('users.index') ? 'bg-blue-500 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M15 21a6 6 0 00-9-5.197m0 0A5.975 5.975 0 0112 13a5.975 5.975 0 01-3 5.197z"></path></svg>
					<span>Daftar Pengguna</span>
				</a>
				<a href="{{ route('invitations.index', $activeProject->id) }}" class="flex items-center px-4 py-2 text-sm rounded {{ request()->routeIs('invitations.index') ? 'bg-blue-500 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
					<span>Undang Pengguna</span>
				</a>
			</div>
		</div>
    </nav>
</div>