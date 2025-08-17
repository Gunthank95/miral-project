<div class="bg-white rounded shadow p-4">
    <nav class="space-y-2">
        <a href="{{ route('superadmin.dashboard') }}" 
           class="block px-4 py-2 text-sm rounded {{ request()->routeIs('superadmin.dashboard') ? 'bg-blue-500 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
            Dashboard
        </a>
        <a href="{{ route('superadmin.tokens.index') }}" 
           class="block px-4 py-2 text-sm rounded {{ request()->routeIs('superadmin.tokens.index') ? 'bg-blue-500 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
            Manajemen Token
        </a>
        <a href="{{ route('admin.materials.index') }}" 
           class="block px-4 py-2 text-sm rounded {{ request()->routeIs('admin.*') ? 'bg-blue-500 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
            Manajemen Data Master
        </a>
    </nav>
</div>