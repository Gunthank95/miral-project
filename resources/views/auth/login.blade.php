@extends('layouts.app')

@section('title', 'Masuk')

@section('content')
<div class="flex items-center justify-center min-h-screen bg-gray-100">
  <div class="bg-white p-8 rounded shadow-md w-full max-w-sm">
    <h1 class="text-2xl font-bold mb-6 text-center">Masuk ke Akun Anda</h1>

    <form method="POST" action="{{ route('login') }}">
      {{-- TODO: Tambahkan @csrf jika siap --}}
	  	  @csrf
      <div class="mb-4">
        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
        <input id="email" name="email" type="email" required
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
        Masuk
      </button>
    </form>
  </div>
</div>
@endsection
