<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Miral Project') - Aplikasi Manajemen Proyek</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
    @stack('styles')
    <style>
        #progress-table.details-hidden .detail-column,
        #progress-table.details-hidden .contract-column {
            display: none;
        }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-100">
    <div id="app">
        {{-- HEADER UTAMA --}}
        <header class="bg-white shadow-md sticky top-0 z-50">
            <div class="container mx-auto px-4">
                <div class="flex items-center justify-between h-16">
                    <div class="flex items-center space-x-4">
                        @auth
                        @if (isset($activeProject))
                        <button id="sidebar-toggle" class="text-gray-600 focus:outline-none" aria-label="Toggle sidebar">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                        </button>
                        @endif
                        @endauth
                        <a href="{{ url('/') }}" class="text-xl font-bold text-gray-800">Miral Project</a>
                    </div>
                    @auth
                    <div class="flex items-center space-x-4">
                        <div class="hidden md:block">
                            @include('components.project-selector')
                        </div>
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" class="flex items-center space-x-2 focus:outline-none">
                                <span class="text-sm font-medium text-gray-700">{{ Auth::user()->name }}</span>
                                <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                            <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
                                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profil Saya</a>
                                <div class="border-t border-gray-100"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Logout</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endauth
                </div>
            </div>
        </header>

        {{-- STRUKTUR UTAMA --}}
        <div class="relative min-h-screen md:flex">
            @if (isset($activeProject))
                <div id="sidebar-overlay" class="fixed inset-0 bg-black opacity-50 z-30 hidden md:hidden"></div>
                <aside id="sidebar" class="bg-white text-gray-800 w-64 space-y-6 py-7 px-2 fixed inset-y-0 left-0 h-screen transform -translate-x-full md:relative md:translate-x-0 transition duration-200 ease-in-out z-40 md:shadow-md md:h-auto overflow-y-auto">
                    @include('layouts.partials.sidebar')
                </aside>
            @endif
            <main class="flex-1 p-6">
                @yield('content')
            </main>
        </div>
    </div>
    
    {{-- SEMUA SCRIPT DIPINDAHKAN KE AKHIR BODY --}}
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- SCRIPT UNTUK SIDEBAR DAN INTERAKSI UMUM --}}
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const sidebarToggle = document.getElementById('sidebar-toggle');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebar-overlay');

        if (sidebarToggle && sidebar && sidebarOverlay) {
            sidebarToggle.addEventListener('click', () => {
                sidebar.classList.toggle('-translate-x-full');
                sidebarOverlay.classList.toggle('hidden');
            });
            sidebarOverlay.addEventListener('click', () => {
                sidebar.classList.add('-translate-x-full');
                sidebarOverlay.classList.add('hidden');
            });
        }
		
		document.body.addEventListener('click', function(event) {
            if (event.target && event.target.id === 'toggle-details-btn') {
                const toggleBtn = event.target;
                const progressTable = document.getElementById('progress-table');
                const volumeHeader = document.getElementById('volume-header');
                const weightHeader = document.getElementById('weight-header');

                if (!progressTable) return;

                progressTable.classList.toggle('details-hidden');

                const isHidden = progressTable.classList.contains('details-hidden');
                toggleBtn.textContent = isHidden ? 'Tampilkan Detail' : 'Sembunyikan Detail';
                if (volumeHeader) volumeHeader.colSpan = isHidden ? 1 : 3;
                if (weightHeader) weightHeader.colSpan = isHidden ? 1 : 3;
            }
        });
    });
    </script>

    {{-- "Wadah" untuk skrip khusus dari halaman lain --}}
    @stack('scripts')

</body>

{{-- SCRIPT "PEMAKSA" UNTUK MEMPERBAIKI LEBAR KOLOM TABEL DOKUMEN --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Fungsi ini akan dijalankan setelah seluruh halaman dimuat
            const documentTable = document.querySelector('#document-table');

            if (documentTable) {
                // Atur tabel ke mode 'fixed'
                documentTable.style.tableLayout = 'fixed';
                
                // Cari header pertama (kolom +) dan paksa lebarnya
                const firstHeader = documentTable.querySelector('thead th:first-child');
                if (firstHeader) {
                    firstHeader.style.width = '60px';
                }

                // Cari header "Untuk Pekerjaan" dan paksa lebarnya
                // Kolom ke-4, jadi kita gunakan nth-child(4)
                const workHeader = documentTable.querySelector('thead th:nth-child(4)');
                if (workHeader) {
                    workHeader.style.width = '35%';
                }
            }
        });
    </script>
</html>