//app.blade.php
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Miral Project') - Aplikasi Manajemen Proyek</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div id="app">
        {{-- HEADER UTAMA (GLOBAL) --}}
        <header class="bg-white shadow-md sticky top-0 z-50">
            <div class="container mx-auto px-4">
                <div class="flex items-center justify-between h-16">
                    <div class="flex items-center space-x-4">
                        {{-- Tombol Hamburger (Hanya Tampil di Layar Kecil) --}}
                        @auth
                        @if (isset($activeProject))
                        <button id="sidebar-toggle" class="md:hidden text-gray-600 focus:outline-none">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                        </button>
                        @endif
                        @endauth
                        
                        <a href="{{ url('/') }}" class="text-xl font-bold text-gray-800">
                            Miral Project
                        </a>
                    </div>
                    @auth
                    <div class="flex items-center space-x-4">
                        <div>
                            @include('components.project-selector')
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-sm text-gray-600 hover:text-gray-900">
                                Logout
                            </button>
                        </form>
                    </div>
                    @endauth
                </div>
            </div>
        </header>

        {{-- STRUKTUR UTAMA DENGAN SIDEBAR DAN KONTEN --}}
        <div class="relative md:flex">
            
            {{-- SIDEBAR --}}
            @if (isset($activeProject))
                {{-- Overlay untuk mode mobile --}}
                <div id="sidebar-overlay" class="fixed inset-0 bg-black opacity-50 z-30 hidden md:hidden"></div>
                
                {{-- PERUBAHAN UTAMA DI SINI: 'absolute' diubah menjadi 'fixed' dan 'h-screen' ditambahkan --}}
                <aside id="sidebar" class="bg-white text-gray-800 w-64 space-y-6 py-7 px-2 fixed inset-y-0 left-0 h-screen transform -translate-x-full md:relative md:translate-x-0 transition duration-200 ease-in-out z-40 md:shadow-md md:h-auto">
                    @include('layouts.partials.sidebar')
                </aside>
            @endif

            {{-- KONTEN UTAMA --}}
            <main class="flex-1">
                {{-- Beri padding atas seukuran tinggi header agar konten tidak tertutup --}}
                <div class="py-6 px-4">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    @stack('scripts')

    {{-- SCRIPT UNTUK SIDEBAR TOGGLE --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sidebarToggle = document.getElementById('sidebar-toggle');
            const sidebar = document.getElementById('sidebar');
            const sidebarOverlay = document.getElementById('sidebar-overlay');

            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('-translate-x-full');
                    sidebarOverlay.classList.toggle('hidden');
                });
            }

            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', function() {
                    sidebar.classList.toggle('-translate-x-full');
                    sidebarOverlay.classList.toggle('hidden');
                });
            }
        });
    </script>
</body>
</html>