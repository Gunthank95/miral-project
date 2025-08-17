@extends('layouts.app')

@section('title', 'Terima Undangan')

@section('content')
<div class="flex items-center justify-center min-h-screen bg-gray-100">
    <div class="bg-white p-8 rounded shadow-md w-full max-w-md">
        <h1 class="text-2xl font-bold mb-6 text-center">Selamat Datang!</h1>
        <p class="text-center text-sm text-gray-600 mb-4">Selesaikan pendaftaran Anda untuk bergabung dengan proyek.</p>

        <form method="POST" action="{{ route('invitations.process_register') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $invitation->token }}">

            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700">Alamat Email</label>
                <input type="email" name="email" id="email" value="{{ $invitation->email }}" required disabled class="mt-1 w-full border rounded px-3 py-2 bg-gray-100">
            </div>
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                <input type="text" name="name" id="name" required autofocus class="mt-1 w-full border rounded px-3 py-2">
            </div>
            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-700">Buat Password</label>
                <input type="password" name="password" id="password" required class="mt-1 w-full border rounded px-3 py-2">
            </div>
            <div class="mb-6">
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Konfirmasi Password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" required class="mt-1 w-full border rounded px-3 py-2">
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">
                Selesaikan Pendaftaran
            </button>
        </form>
    </div>
</div>
@endsection