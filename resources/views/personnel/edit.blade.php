@extends('layouts.app')

@section('title', 'Edit Personil')

@section('content')
<div class="p-4 sm:p-6">
    <header class="bg-white shadow p-4 rounded-lg mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Edit Personil: {{ $personnel->name }}</h1>
        <p class="text-sm text-gray-500">Perusahaan: {{ $company->name }} | Proyek: {{ $project->name }}</p>
    </header>

    <form action="{{ route('personnel.update', ['project' => $project->id, 'company' => $company->id, 'personnel' => $personnel->id]) }}" method="POST" class="bg-white shadow-md rounded-lg p-6">
        @csrf
        @method('PATCH')
        @include('personnel.partials.form-fields')
        <div class="mt-6 pt-6 border-t flex justify-end items-center space-x-4">
            <a href="{{ route('projects.data-proyek', $project->id) }}" class="text-sm text-gray-600 hover:underline">Batal</a>
            <button type="submit" class="bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection