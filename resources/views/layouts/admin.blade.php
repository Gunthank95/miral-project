@extends('layouts.app')

@section('content')
<div class="p-4 sm:p-6">
    <header class="bg-white shadow p-4 rounded-lg mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Manajemen Data Master</h1>
    </header>

    <div class="md:flex md:space-x-6">
        {{-- Kolom Kiri: Menu Navigasi Admin --}}
        <aside class="md:w-1/4 mb-6 md:mb-0">
            <div class="bg-white rounded shadow p-4">
                <nav class="space-y-2">
                    <a href="{{ route('admin.materials.index') }}" 
                       class="block px-4 py-2 text-sm rounded {{ request()->routeIs('admin.materials.index') ? 'bg-blue-500 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                        Manajemen Material
                    </a>
                    <a href="{{ route('admin.work-items.index') }}" 
                       class="block px-4 py-2 text-sm rounded {{ request()->routeIs('admin.work-items.index') ? 'bg-blue-500 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                        Manajemen Pekerjaan
                    </a>
                    {{-- Link menu admin lainnya bisa ditambahkan di sini --}}
                </nav>
            </div>
        </aside>

        {{-- Kolom Kanan: Konten Utama Halaman --}}
        <main class="md:w-3/4">
            @yield('admin_content')
        </main>
    </div>
</div>
@endsection