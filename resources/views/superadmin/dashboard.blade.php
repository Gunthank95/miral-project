@extends('layouts.app')

@section('title', 'Super Admin Dashboard')

@section('content')
<div class="p-4 sm:p-6">
    <div class="md:flex md:space-x-6">
        {{-- Kolom Kiri: Menu Navigasi Admin --}}
        <aside class="md:w-1/4 mb-6 md:mb-0">
            @include('layouts.partials.superadmin-sidebar')
        </aside>

        {{-- Kolom Kanan: Konten Utama Halaman --}}
        <main class="md:w-3/4">
            <header class="bg-white shadow p-4 rounded-lg mb-6">
                 <h1 class="text-xl font-semibold">Selamat Datang, Super Admin!</h1>
            </header>
            <div class="bg-white rounded shadow p-6">
                <p>Gunakan menu di samping untuk mengelola aplikasi.</p>
            </div>
        </main>
    </div>
</div>
@endsection