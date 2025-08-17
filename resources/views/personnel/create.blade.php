@extends('layouts.app')

@section('title', 'Tambah Personil')

@section('content')
<div class="p-4 sm:p-6">
    <header class="bg-white shadow p-4 rounded-lg mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Tambah Personil untuk {{ $company->name }}</h1>
        <p class="text-sm text-gray-500">Proyek: {{ $project->name }}</p>
    </header>

    <form action="{{ route('personnel.store', ['project' => $project->id, 'company' => $company->id]) }}" method="POST" class="bg-white shadow-md rounded-lg p-6">
        @csrf
        @include('personnel.partials.form-fields')
        <div class="mt-6 pt-6 border-t flex justify-end items-center space-x-4">
            <a href="{{ route('projects.data-proyek', $project->id) }}" class="text-sm text-gray-600 hover:underline">Batal</a>
            <button type="submit" class="bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700">
                Simpan Personil
            </button>
        </div>
    </form>
</div>
@endsection