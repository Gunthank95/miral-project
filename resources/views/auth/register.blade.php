@extends('layouts.app')

@section('title', 'Registrasi Admin Project')

@section('content')
<div class="flex items-center justify-center min-h-screen bg-gray-100">
    <div class="bg-white p-8 rounded shadow-md w-full max-w-md">
        <h1 class="text-2xl font-bold mb-6 text-center">Registrasi Admin Project</h1>

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register.store') }}">
            @csrf
            
            <div class="mb-4">
                <label for="project_name" class="block text-sm font-medium text-gray-700">Nama Proyek</label>
                <input type="text" name="project_name" id="project_name" value="{{ old('project_name') }}" required class="mt-1 w-full border rounded px-3 py-2">
            </div>

            <hr class="my-4">

            <div class="mb-4">
                <label for="company_name" class="block text-sm font-medium text-gray-700">Nama Perusahaan</label>
                <input type="text" name="company_name" id="company_name" value="{{ old('company_name') }}" required class="mt-1 w-full border rounded px-3 py-2">
            </div>

            <div class="mb-4">
                <label for="company_role" class="block text-sm font-medium text-gray-700">Peran Perusahaan di Proyek</label>
                <select name="company_role" id="company_role" required class="mt-1 w-full border rounded px-3 py-2">
                    <option value="">Pilih Peran...</option>
                    <option value="owner" {{ old('company_role') == 'owner' ? 'selected' : '' }}>Owner (Pemilik Proyek)</option>
                    <option value="mk" {{ old('company_role') == 'mk' ? 'selected' : '' }}>MK (Manajemen Konstruksi)</option>
                    <option value="contractor" {{ old('company_role') == 'contractor' ? 'selected' : '' }}>Kontraktor</option>
                </select>
            </div>
            
            <hr class="my-4">

            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700">Nama Lengkap Anda</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required autofocus class="mt-1 w-full border rounded px-3 py-2">
            </div>

            <div class="mb-4">
                <label for="position" class="block text-sm font-medium text-gray-700">Jabatan Anda</label>
                <input type="text" name="position" id="position" value="{{ old('position') }}" required class="mt-1 w-full border rounded px-3 py-2" placeholder="Contoh: Project Manager">
            </div>

            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700">Alamat Email</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required class="mt-1 w-full border rounded px-3 py-2">
            </div>
            
            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" name="password" id="password" required class="mt-1 w-full border rounded px-3 py-2">
            </div>
            
            <div class="mb-4">
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Konfirmasi Password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" required class="mt-1 w-full border rounded px-3 py-2">
            </div>
            
            <div class="mb-6">
                <label for="token" class="block text-sm font-medium text-gray-700">Token Registrasi</label>
                <input type="text" name="token" id="token" value="{{ old('token') }}" required class="mt-1 w-full border rounded px-3 py-2" placeholder="Masukkan token dari Super Admin">
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">
                Daftar
            </button>
            <p class="text-center mt-4 text-sm">Sudah punya akun? <a href="{{ route('login') }}" class="text-blue-600 hover:underline">Masuk di sini</a></p>
        </form>
    </div>
</div>
@endsection