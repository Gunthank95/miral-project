@extends('layouts.app')

@section('title', 'Manajemen Token Registrasi')

@section('content')
<div class="md:flex md:space-x-6">
    {{-- Kolom Kiri: Menu Navigasi Admin --}}
    <aside class="md:w-1/4 mb-6 md:mb-0">
        @include('layouts.partials.superadmin-sidebar')
    </aside>

    {{-- Kolom Kanan: Konten Utama Halaman --}}
    <main class="md:w-3/4">
        <div class="bg-white rounded shadow p-6">
            <div class="flex justify-between items-center mb-4">
                <h1 class="text-xl font-semibold text-gray-800">Manajemen Token Registrasi</h1>
                <form action="{{ route('superadmin.tokens.store') }}" method="POST">
                    @csrf
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700">
                        + Buat Token Baru
                    </button>
                </form>
            </div>
            
            @if (session('success'))
                <div class="bg-green-100 text-green-700 p-3 rounded mb-4">{{ session('success') }}</div>
            @endif

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
            <thead class="bg-gray-200">
                <tr>
                    <th class="text-left px-4 py-2">Token</th>
                    <th class="text-left px-4 py-2">Status</th>
                    <th class="text-left px-4 py-2">Email Pengguna</th>
                    <th class="text-left px-4 py-2">Digunakan Pada</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tokens as $token)
                    <tr class="border-t">
                        <td class="px-4 py-2 font-mono">{{ $token->token }}</td>
                        <td class="px-4 py-2">
                            @if ($token->used_at)
                                <span class="bg-green-200 text-green-800 text-xs font-semibold mr-2 px-2.5 py-0.5 rounded">Sudah Digunakan</span>
                            @else
                                <span class="bg-yellow-200 text-yellow-800 text-xs font-semibold mr-2 px-2.5 py-0.5 rounded">Tersedia</span>
                            @endif
                        </td>
                        <td class="px-4 py-2">{{ $token->email ?? '-' }}</td>
                        <td class="px-4 py-2">{{ $token->used_at ? $token->used_at->format('d M Y, H:i') : '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center p-4 text-gray-500">
                            Belum ada token yang dibuat.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
            </div>
            <div class="mt-4">
                {{ $tokens->links() }}
            </div>
        </div>
    </main>
</div>
@endsection