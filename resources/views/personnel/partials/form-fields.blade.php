@if ($errors->any())
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
        <p class="font-bold">Terjadi Kesalahan</p>
        <ul>
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
        </ul>
    </div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
        <label for="name" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
        <input type="text" name="name" id="name" value="{{ old('name', $personnel->name ?? '') }}" required class="mt-1 w-full border rounded px-3 py-2">
    </div>
    <div>
        <label for="position" class="block text-sm font-medium text-gray-700">Jabatan</label>
        <input type="text" name="position" id="position" value="{{ old('position', $personnel->position ?? '') }}" required class="mt-1 w-full border rounded px-3 py-2">
    </div>
    <div>
        <label for="nik" class="block text-sm font-medium text-gray-700">NIK</label>
        <input type="text" name="nik" id="nik" value="{{ old('nik', $personnel->nik ?? '') }}" class="mt-1 w-full border rounded px-3 py-2">
    </div>
    <div>
        <label for="phone_number" class="block text-sm font-medium text-gray-700">Nomor Telepon</label>
        <input type="text" name="phone_number" id="phone_number" value="{{ old('phone_number', $personnel->phone_number ?? '') }}" class="mt-1 w-full border rounded px-3 py-2">
    </div>
    <div class="md:col-span-2">
        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
        <input type="email" name="email" id="email" value="{{ old('email', $personnel->email ?? '') }}" class="mt-1 w-full border rounded px-3 py-2">
    </div>
</div>