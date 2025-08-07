<div class="flex justify-between items-center px-4 mb-4 border-b pb-4">
    <div>
        <h3 class="font-bold text-gray-800">{{ $activeProject->name }}</h3>
        <p class="text-xs text-gray-500">Proyek Aktif</p>
    </div>
    {{-- Tombol Tutup (Hanya Tampil di Layar Kecil) --}}
    <button id="sidebar-close" class="md:hidden text-gray-600 focus:outline-none">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
    </button>
</div>

<nav class="space-y-4">
    {{-- Grup Menu: Pelaporan & Progres --}}
    <div>
        <h4 class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Pelaporan & Progres</h4>
            <div class="mt-2 space-y-1">
                <a href="{{ route('project.show', $activeProject->id) }}" class="block px-4 py-2 text-sm rounded {{ request()->routeIs('project.show') ? 'bg-blue-500 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                    Dashboard Proyek
                </a>
                <a href="{{ route('daily_reports.index', $activeProject->id) }}" class="block px-4 py-2 text-sm rounded {{ request()->routeIs('daily_reports.index') ? 'bg-blue-500 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                    Laporan Harian
                </a>
                <a href="#" class="block px-4 py-2 text-sm rounded text-gray-400 cursor-not-allowed">
                    Laporan Periodik
                </a>
            </div>
    </div>

    {{-- Grup Menu: Dokumen & Persetujuan --}}
    <div>
        <h4 class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Dokumen & Persetujuan</h4>
            <div class="mt-2 space-y-1">
                <a href="{{ route('rab.index', $activeProject->id) }}" class="block px-4 py-2 text-sm rounded {{ request()->routeIs('rab.index') ? 'bg-blue-500 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                    RAB & Pekerjaan
                </a>
                <a href="#" class="block px-4 py-2 text-sm rounded text-gray-400 cursor-not-allowed">
                    Gambar Teknis
                </a>
                <a href="#" class="block px-4 py-2 text-sm rounded text-gray-400 cursor-not-allowed">
                    Metode Kerja
                </a>
                 <a href="#" class="block px-4 py-2 text-sm rounded text-gray-400 cursor-not-allowed">
                    Material
                </a>
            </div>
    </div>
</nav>

{{-- Script khusus untuk tombol tutup di sidebar --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebarClose = document.getElementById('sidebar-close');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebar-overlay');

        if (sidebarClose) {
            sidebarClose.addEventListener('click', function() {
                sidebar.classList.toggle('-translate-x-full');
                sidebarOverlay.classList.toggle('hidden');
            });
        }
    });
</script>