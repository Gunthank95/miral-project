@extends('layouts.app')

@section('title', 'Edit Profil')

@section('content')
<div class="container mx-auto max-w-4xl py-8">
    
    <header class="bg-white shadow p-4 rounded-lg mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Profil Saya</h1>
        <p class="text-sm text-gray-500">Perbarui informasi personal dan profesional Anda.</p>
    </header>

    @if (session('status'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('status') }}</span>
        </div>
    @endif

    <div class="bg-white shadow-md rounded-lg p-6">
        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PATCH')

            <div class="flex items-center space-x-6 mb-6">
                <img id="photo-preview" class="h-24 w-24 rounded-full object-cover" src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}">
                <div>
                    <input type="file" name="profile_photo" id="profile_photo" class="hidden" onchange="previewPhoto()">
                    <label for="profile_photo" class="cursor-pointer bg-white py-2 px-3 border border-gray-300 rounded-md shadow-sm text-sm leading-4 font-medium text-gray-700 hover:bg-gray-50">
                        Ubah Foto
                    </label>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required class="mt-1 w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Alamat Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required class="mt-1 w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label for="phone_number" class="block text-sm font-medium text-gray-700">Nomor Telepon</label>
                    <input type="text" name="phone_number" id="phone_number" value="{{ old('phone_number', $user->phone_number) }}" class="mt-1 w-full border rounded px-3 py-2">
                </div>
                <div class="md:col-span-2">
                    <label for="certifications" class="block text-sm font-medium text-gray-700">Sertifikasi</label>
                    <textarea name="certifications" id="certifications" rows="4" class="mt-1 w-full border rounded px-3 py-2" placeholder="Sebutkan sertifikasi yang Anda miliki, pisahkan dengan koma...">{{ old('certifications', $user->certifications) }}</textarea>
                </div>
            </div>

            <div class="mt-6 pt-6 border-t flex justify-end">
                <button type="submit" class="bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@push('scripts')
<script>
    function previewPhoto() {
        const file = document.getElementById('profile_photo').files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('photo-preview').src = e.target.result;
            }
            reader.readAsDataURL(file);
        }
    }
</script>
@endpush
@endsection