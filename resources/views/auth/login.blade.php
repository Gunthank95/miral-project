@extends('layouts.app')

@section('title', 'Masuk')

@section('content')
<div class="flex items-center justify-center min-h-screen bg-gray-100">
  <div class="bg-white p-8 rounded shadow-md w-full max-w-sm">
    <h1 class="text-2xl font-bold mb-6 text-center">Masuk ke Akun Anda</h1>

    {{-- Menampilkan pesan error jika ada --}}
    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
      @csrf
      <div class="mb-4">
        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
        <input id="email" name="email" type="email" required value="{{ old('email') }}"
          class="mt-1 w-full border rounded px-3 py-2" placeholder="you@example.com" />
      </div>

      <div class="mb-4">
        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
        <input id="password" name="password" type="password" required
          class="mt-1 w-full border rounded px-3 py-2" placeholder="********" />
      </div>

      <div class="mb-4 text-right">
        <a href="#" class="text-sm text-blue-600 hover:underline">Lupa Password?</a>
      </div>

      <button id="btn-login" type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">
        Login
      </button>

      {{-- Link Pendaftaran Baru Ditambahkan di Sini --}}
      <div class="text-center mt-4">
          <p class="text-sm text-gray-600">
              Ingin mendaftarkan proyek baru?
              <a href="{{ route('register.show') }}" class="font-medium text-blue-600 hover:underline">Daftar di sini</a>
          </p>
      </div>
      
    </form>
  </div>
</div>
@endsection